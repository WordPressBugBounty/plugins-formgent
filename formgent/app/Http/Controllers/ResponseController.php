<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Answer;
use FormGent\App\DTO\ResponseDTO;
use FormGent\App\DTO\AnswerDTO;
use FormGent\App\EnumeratedList\ResponseStatus;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\ResponseRepository;
use FormGent\App\Repositories\AnswerRepository;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\Repositories\PdfRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use stdClass;
use Throwable;
use WP_REST_Request;

class ResponseController extends Controller {
    private ResponseRepository $repository;

    private AnswerRepository $answer_repository;

    private FormRepository $form_repository;

    /**
     * Constructor for initializing repositories.
     *
     * @param ResponseRepository $repository
     * @param AnswerRepository   $answer_repository
     * @param FormRepository     $form_repository
     */
    public function __construct( ResponseRepository $repository, AnswerRepository $answer_repository, FormRepository $form_repository ) {
        $this->repository        = $repository;
        $this->answer_repository = $answer_repository;
        $this->form_repository   = $form_repository;
    }

    /**
     * Handles the storage of form responses.
     *
     * @param Validator $validator
     * @param WP_REST_Request $request
     * @return array
     */
    public function store( Validator $validator, WP_REST_Request $request ) {
        // Validate the request parameters.
        $validator->validate(
            [
                'id'             => 'required|numeric',
                'form_data'      => 'required|array',
                'response_token' => 'required|string',
            ]
        );

        // Retrieve the form by its ID.
        $form_id = intval( $request->get_param( 'id' ) );
        $form    = formgent_get_form_by_id( $form_id, true );

        // Return 404 if the form is not found.
        if ( ! $form ) {
            return Response::send( ['message' => esc_html__( 'Form not found', 'formgent' )], 404 );
        }

        $response = formgent_get_response_by_token( $request->get_param( 'response_token' ), $form->ID );

        if ( ! $response ) {
            return Response::send(
                [
                    'message' => esc_html__( "Response not found.", "formgent" )
                ], 404
            );
        }

        if ( '1' === $response->is_completed ) {
            return Response::send(
                [
                    'message' => esc_html__( "Skipping submission: Response is already completed.", "formgent" )
                ], 409
            );
        }

        if ( $form_id != $response->form_id ) {
            return Response::send(
                [
                    'message' => esc_html__( "Oops, something went wrong. Please try again.", "formgent" )
                ], 404
            );
        }

        // Set additional form properties.
        $form->save_incomplete_data = formgent_is_save_incompleted_data( $form->ID );

        // Validate form data and create DTOs.
        $validate_data = $this->validate_form_data( $form, $validator, $request );
        if ( ! empty( $validate_data['errors'] ) ) {
            return Response::send( ['messages' => $validate_data['errors']], 422 );
        }

        // Allow pro features (e.g. OTP verification) to gate submission before data is stored.
        // Returning a WP_Error from this filter aborts the submission with a proper REST response.
        $gate_result = apply_filters( 'formgent_before_store_form_response', true, $response, $form, $request );

        if ( is_wp_error( $gate_result ) ) {
            return Response::send(
                [ 'message' => $gate_result->get_error_message() ],
                $gate_result->get_error_code() && is_numeric( $gate_result->get_error_code() ) ? (int) $gate_result->get_error_code() : 403
            );
        }

        if ( false === $gate_result ) {
            return Response::send(
                [ 'message' => esc_html__( 'Verification required.', 'formgent' ) ],
                403
            );
        }

        if ( ! empty( $validate_data['field_dtos'] ) ) {
            // Save & Resume / partial-entry flows may have already stored draft answers
            // against this response token. On final submit, replace existing answers
            // to avoid duplicated rows in the completed entry.
            try {
                Answer::query()->where( 'response_id', $response->id )->delete();
            } catch ( \Throwable $e ) {
                // If deletion fails for any reason, continue with insert to avoid blocking submit.
            }

            $this->answer_repository->creates( $response->id, $validate_data['field_dtos'] );

            // Handle child fields if present.
            if ( ! empty( $validate_data['parent_field_names'] ) ) {
                $this->handle_child_fields( $response->id, $validate_data );
            }
        }

        $this->repository->mark_as_completed( $response->id );

        $response->is_completed = 1;

        // Trigger the after response creation hook.
        do_action( "formgent_after_create_form_response", $response->id, $form, $request );

        // Skip PDF generation for payment forms — PDFs are generated after
        // payment completes in PaymentController::success() when payment data exists.
        $pdf_links = [];
        if ( ! $request->get_param( 'payment_gateway' ) ) {
            $pdf_links = $this->generate_confirmation_pdf_links( (int) $form->ID, (int) $response->id );
        }

        // Return a success response.
        return Response::send(
            apply_filters(
                'formgent_form_submission_response',
                [
                    'message'   => esc_html__( 'The form was submitted successfully!', 'formgent' ),
                    'pdf_links' => $pdf_links,
                ],
                $request, $form, $response
            ),
            201
        );
    }

    /**
     * Validates form data and creates DTOs for fields.
     *
     * @param stdClass $form
     * @param Validator $validator
     * @param WP_REST_Request $request
     * @return array
     */
    private function validate_form_data( stdClass $form, Validator $validator, WP_REST_Request $request ): array {
        $form_data = $request->get_param( 'form_data' );
        $request->set_body_params( $form_data );

        $registered_fields  = formgent_config( "fields" );
        $fields             = formgent_get_form_fields( $form );
        $errors             = [];
        $field_dtos         = [];
        $children_dtos      = [];
        $parent_field_names = [];

        foreach ( $form_data as $field_name => $field_data ) {
            // Skip if the field is not found in the form's field settings.
            if ( empty( $fields[$field_name] ) ) {
                unset( $form_data[$field_name] );
                continue;
            }

            $field = $fields[$field_name];

            // Skip if the field type is not allowed in the response.
            if ( empty( $registered_fields[$field['field_type']]['allowed_in_response'] ) ) {
                continue;
            }

            try {
                // Get the field handler for this field type.
                $field_handler = formgent_field_handler( $field['field_type'] );

                // Skip if the form type is not supported by the field handler.
                if ( ! in_array( $form->form_type, $field_handler::get_supported_form_types(), true ) ) {
                    continue;
                }

                // Validate the field and create its DTO.
                $field_handler->validate( $field, $request, $validator, $form );
                $dto = $field_handler->get_field_dto( $field, $request, $form );

                // Handle child fields if present.
                if ( $field_handler->has_children ) {
                    $children                      = $this->get_children_dtos( $form, $validator, $request, $field );
                    $validator->wp_rest_request    = $request;
                    $children_dtos[$field['name']] = $children['field_dtos'];

                    if ( ! empty( $children['errors'] ) ) {
                        $errors[$field['name']] = $children['errors'];
                    }

                    $parent_field_names[] = $dto->get_field_name();
                }

                $field_dtos[$field['name']] = $dto;

            } catch ( Exception $exception ) {
                // Merge any validation errors from the field handler.
                $errors = array_merge( $errors, $exception->get_messages() );
            }
        }

        return compact( 'field_dtos', 'children_dtos', 'parent_field_names', 'errors' );
    }

    /**
     * Handles child fields and stores them in the database.
     *
     * @param int $response_id
     * @param array $validate_data
     */
    private function handle_child_fields( int $response_id, array $validate_data ): void {
        $parent_fields = Answer::query()
            ->select( 'id', 'field_name' )
            ->where( 'response_id', $response_id )
            ->where_in( 'field_name', $validate_data['parent_field_names'] )
            ->get();

        // Extract parent field IDs indexed by field names.
        $parent_names = wp_list_pluck( $parent_fields, 'id', 'field_name' );

        $children_items = [];

        foreach ( $validate_data['children_dtos'] as $key => $dtos ) {
            foreach ( $dtos as $dto ) {
                /**
                 * @var AnswerDTO $dto
                 *
                 * Prepare the AnswerDTO for storing in the database.
                 *
                 * - Set the parent ID of the answer (from the previously retrieved parent field IDs).
                 * - Set the response ID to associate this answer with the current form response.
                 * - Convert the DTO to an array for insertion into the database.
                 */
                $children_items[] = $dto->set_parent_id( $parent_names[$key] )->set_response_id( $response_id )->to_array();
            }
        }

        // Store child answers in the database.
        if ( ! empty( $children_items ) ) {
            $this->answer_repository->creates_from_array( $children_items );
        }
    }

    /**
     * Retrieves and validates child field DTOs.
     *
     * @param stdClass $form
     * @param Validator $validator
     * @param WP_REST_Request $request
     * @param array $parent_field
     * @return array
     */
    public function get_children_dtos( stdClass $form, Validator $validator, WP_REST_Request $request, array $parent_field ): array {
        $children_request = new WP_REST_Request( 'POST', '/' );
        $form_data        = $request->get_param( $parent_field['name'] );

        $field_dtos = [];
        $errors     = [];

        if ( ! is_array( $form_data ) ) {
            return compact( 'field_dtos', 'errors' );
        }

        $children_request->set_body_params( $form_data );
        $validator->wp_rest_request = $children_request;
        $registered_fields          = formgent_config( "fields" );
        $fields                     = $parent_field['children'];

        foreach ( $form_data as $field_name => $field_data ) {
            // Skip if the field is not found in the parent field's children.
            if ( empty( $fields[$field_name] ) ) {
                unset( $form_data[$field_name] );
                continue;
            }

            $field = $fields[$field_name];

            // Skip if the field type is not allowed in the response.
            if ( empty( $registered_fields[$field['field_type']]['allowed_in_response'] ) ) {
                continue;
            }

            try {
                // Get the field handler for this field type.
                $field_handler = formgent_field_handler( $field['field_type'] );

                // Skip if the form type is not supported by the field handler.
                if ( ! in_array( $form->form_type, $field_handler::get_supported_form_types(), true ) ) {
                    continue;
                }

                // Validate the field and create its DTO.
                $field_handler->validate( $field, $children_request, $validator, $form );
                $dto = $field_handler->get_field_dto( $field, $children_request, $form );

                $field_dtos[$field['name']] = $dto;

            } catch ( Exception $exception ) {
                // Merge any validation errors from the field handler.
                $errors = array_merge( $errors, $exception->get_messages() );
            }
        }

        return compact( 'field_dtos', 'errors' );
    }

    public function generate_token( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'form_id' => 'required|numeric'
            ]
        );

        $form_id = intval( $wp_rest_request->get_param( 'form_id' ) );

        $form = $this->form_repository->get_by_id_publish( $form_id );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ], 404
            );
        }

        $dto = new ResponseDTO;
        $dto->set_status( ResponseStatus::DRAFT )->set_is_completed( 0 )->set_form_id( $form_id );

        if ( 'no' === formgent_settings_repository()->get_by_key( 'disable_ip_logging', 'no' ) ) {
            $dto->set_ip( formgent_get_user_ip_address() );
        }

        $this->store_browser_info( $dto, $wp_rest_request );

        if ( is_user_logged_in() ) {
            $dto->set_created_by( wp_get_current_user()->ID );
        }

        $response_id = $this->repository->create( $dto );

        $dto->set_id( $response_id );

        $response_token = $this->repository->create_token( $response_id, $dto->get_form_id() );

        do_action( 'formgent_after_create_form_response_token', $response_token, $dto, $wp_rest_request );

        return Response::send(
            [
                'response_token' => $response_token
            ]
        );
    }

    /**
     * Generate and stream a response PDF from a template token in confirmation message.
     *
     * @param Validator       $validator
     * @param WP_REST_Request $request
     * @return array|WP_REST_Response
     */
    public function download_pdf( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'id'             => 'required|numeric',
                'pdf_id'         => 'required|numeric',
                'response_token' => 'required|string',
            ]
        );

        $form_id        = absint( $request->get_param( 'id' ) );
        $pdf_id         = absint( $request->get_param( 'pdf_id' ) );
        $response_token = sanitize_text_field( (string) $request->get_param( 'response_token' ) );

        $form = $this->form_repository->get_by_id_publish( $form_id );
        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found.', 'formgent' ),
                ],
                404
            );
        }

        $response = formgent_get_response_by_token( $response_token, $form_id );
        if ( ! $response || '1' !== (string) $response->is_completed ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Response not found.', 'formgent' ),
                ],
                404
            );
        }

        /** @var PdfRepository $pdf_repository */
        $pdf_repository = formgent_singleton( PdfRepository::class );

        [ $pdf, $decrypted_password ] = $pdf_repository->get_by_id_and_form_with_decrypted_password( $pdf_id, $form_id );

        if ( ! $pdf ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF template not found.', 'formgent' ),
                ],
                404
            );
        }

        $pdf_path = formgent_get_pdf_library_path();
        $autoload = $pdf_path ? trailingslashit( $pdf_path ) . 'vendor/autoload.php' : '';

        if ( empty( $autoload ) || ! is_readable( $autoload ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF library is not installed for this form.', 'formgent' ),
                ],
                400
            );
        }

        if ( ! class_exists( '\Dompdf\Dompdf', false ) ) {
            require_once $autoload;
        }

        if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF engine could not be loaded.', 'formgent' ),
                ],
                500
            );
        }

        $template_content = isset( $pdf->content ) ? (string) $pdf->content : '';
        $payment_values   = formgent_get_payment_preset_values( (int) $response->id, $form_id );
        $html             = formgent_replace_html_dynamic_tags( $template_content, $form_id, (int) $response->id, null, $payment_values ?: null );
        $paper_size       = ! empty( $pdf->paper_size ) ? sanitize_text_field( (string) $pdf->paper_size ) : 'A4';
        $orientation      = strtolower( (string) ( $pdf->orientation ?? '' ) );
        $orientation      = 'landscape' === $orientation || 'l' === $orientation ? 'L' : 'P';
        $direction        = strtolower( (string) ( $pdf->direction ?? '' ) );
        $password         = sanitize_text_field( $decrypted_password );
        $html             = formgent_apply_pdf_direction( $html, $direction );

        try {
            $pdf_binary = formgent_render_pdf_with_dompdf( $html, $paper_size, $orientation, $password, $form_id, $pdf_id );
        } catch ( Throwable $exception ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Failed to generate PDF.', 'formgent' ),
                ],
                500
            );
        }

        $template_name = isset( $pdf->template_name ) ? (string) $pdf->template_name : '';
        $base_name     = sanitize_file_name( $template_name ?: 'formgent-generated-pdf' );
        $filename      = $base_name . '.pdf';

        // Stream the PDF directly through PHP — never expose raw file URLs.
        // This avoids the Nginx .htaccess bypass and ensures auth is always checked.
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: inline; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $pdf_binary ) );
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- binary PDF data
        echo $pdf_binary;
        exit;
    }

    /**
     * Generate PDF files for {{pdf:id}} tags used in confirmation message and End block content.
     *
     * @param int $form_id
     * @param int $response_id
     * @return array<string, array{url:string,name:string}> [pdf_id => {url,name}]
     */
    private function generate_confirmation_pdf_links( int $form_id, int $response_id ): array {
        // Skip all DB/parsing work when no PDF templates exist for this form.
        $pdf_repository = formgent_singleton( PdfRepository::class );
        if ( $pdf_repository->count_by_form_id( $form_id ) === 0 ) {
            return [];
        }

        $settings             = $this->form_repository->get_settings( $form_id );
        $confirmation_message = isset( $settings['confirmation']['message'] ) ? (string) $settings['confirmation']['message'] : '';

        $form              = $this->form_repository->get_by_id_publish( $form_id );
        $post_content      = $form && isset( $form->post_content ) ? (string) $form->post_content : '';
        $end_block_content = formgent_get_end_block_content( $post_content );

        $content_with_pdf_refs = trim( $confirmation_message . "\n" . $end_block_content );
        return formgent_generate_pdf_links_from_content( $content_with_pdf_refs, $form_id, $response_id );
    }

    /**
     * Serve a generated PDF file through PHP.
     *
     * Files are stored on disk but never exposed via direct URL — this endpoint
     * streams the file content after validating the filename exists in the
     * protected PDF directory. The filename contains 32 hex chars of randomness
     * making it effectively an unguessable bearer token.
     *
     * @param Validator       $validator
     * @param WP_REST_Request $request
     */
    public function serve_pdf( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'file' => 'required|string',
            ]
        );

        $filename = sanitize_file_name( (string) $request->get_param( 'file' ) );

        if ( empty( $filename ) || ! preg_match( '/\.pdf$/i', $filename ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Invalid file name.', 'formgent' ),
                ],
                400
            );
        }

        $uploads = wp_upload_dir();
        if ( ! empty( $uploads['error'] ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Upload directory is not available.', 'formgent' ),
                ],
                500
            );
        }

        $file_path = trailingslashit( $uploads['basedir'] ) . 'formgent/pdfs/' . $filename;

        // Verify the resolved path stays within the PDF directory (prevent path traversal).
        $real_path = realpath( $file_path );
        $pdf_dir   = realpath( trailingslashit( $uploads['basedir'] ) . 'formgent/pdfs' );

        if ( false === $real_path || false === $pdf_dir || 0 !== strpos( $real_path, $pdf_dir ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF file not found.', 'formgent' ),
                ],
                404
            );
        }

        if ( ! is_readable( $real_path ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF file not found.', 'formgent' ),
                ],
                404
            );
        }

        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: inline; filename="' . $filename . '"' );
        header( 'Content-Length: ' . filesize( $real_path ) );
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- streaming binary PDF data
        readfile( $real_path );
        exit;
    }

    /**
     * Stores browser information in the ResponseDTO.
     *
     * @param ResponseDTO $response_dto
     * @param WP_REST_Request $request
     */
    private function store_browser_info( ResponseDTO $response_dto, WP_REST_Request $request ): void {
        $which_browser = new \FormGent\WhichBrowser\Parser( $request->get_header( 'user-agent' ) );
        $browser       = $which_browser->browser;

        if ( $browser ) {
            $response_dto->set_browser( $browser->name );
            $response_dto->set_browser_version( $browser->version instanceof \FormGent\WhichBrowser\Model\Version ? $browser->version->value : null );
            $response_dto->set_device( $which_browser->os->name );
        }
    }
}
