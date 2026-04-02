<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ResponseReadDTO;
use FormGent\App\DTO\ResponseSingleDTO;
use FormGent\App\DTO\AllResponsesReadDTO;
use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\ResponseRepository;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\Repositories\QuizRepository;
use FormGent\App\Repositories\SummaryRepository;
use FormGent\App\Repositories\AnswerRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;
use Exception;

class ResponseController extends Controller
{
    public ResponseRepository $repository;

    public FormRepository $form_repository;

    public QuizRepository $quiz_repository;

    public SummaryRepository $summary_repository;

    public AnswerRepository $answer_repository;

    public function __construct( ResponseRepository $repository, FormRepository $form_repository, QuizRepository $quiz_repository, SummaryRepository $summary_repository, AnswerRepository $answer_repository ) {
        $this->repository         = $repository;
        $this->form_repository    = $form_repository;
        $this->quiz_repository    = $quiz_repository;
        $this->summary_repository = $summary_repository;
        $this->answer_repository  = $answer_repository;
    }

    public function index( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'per_page'         => 'numeric',
                'page'             => 'numeric',
                's'                => 'string|max:255',
                'form_id'          => 'numeric',
                'is_read'          => 'numeric|accepted:0,1',
                'is_starred'       => 'numeric|accepted:0,1',
                'order_by'         => 'string|max:50',
                'order'            => 'string|accepted:asc,desc',
                'order_field_type' => 'string|accepted:response,answer',
                'is_completed'     => 'numeric|accepted:0,1',
                'date_type'        => 'string|accepted:all,today,yesterday,last_week,last_month,date_frame',
                'date_frame'       => 'array',
            ]
        );

        $dto = new ResponseReadDTO;
        $dto->set_page( intval( $wp_rest_request->get_param( 'page' ) ) );
        $dto->set_per_page( intval( $wp_rest_request->get_param( 'per_page' ) ) );
        $dto->set_search( (string) $wp_rest_request->get_param( 's' ) );

        if ( $wp_rest_request->has_param( 'form_id' ) ) {
            $dto->set_form_id( intval( $wp_rest_request->get_param( 'form_id' ) ) );
        }

        if ( $wp_rest_request->has_param( 'is_read' ) ) {
            $dto->set_is_read( $wp_rest_request->get_param( 'is_read' ) );
        }

        if ( $wp_rest_request->has_param( 'is_starred' ) ) {
            $dto->set_is_starred( $wp_rest_request->get_param( 'is_starred' ) );
        }

        $dto->set_order( $wp_rest_request->get_param( 'order' ) ?? 'desc' )
            ->set_order_by( $wp_rest_request->get_param( 'order_by' ) ?? 'id' )
            ->set_order_field_type( $wp_rest_request->get_param( 'order_field_type' ) ?? 'response' );

        if ( $wp_rest_request->has_param( 'is_completed' ) ) {
            $value = $wp_rest_request->get_param( 'is_completed' );
            if ( $value !== null && $value !== '' ) {
                $dto->set_is_completed( (int) $value );
            }
        }

        // Set date filtering
        $dto->set_date_type( $wp_rest_request->get_param( 'date_type' ) );
        $date_frame = $wp_rest_request->get_param( 'date_frame' );
        $dto->set_date_frame( is_array( $date_frame ) ? $date_frame : [] );

        $data      = $this->repository->get( $dto );
        $responses = $data['responses'];

        if ( ! empty( $responses ) ) {
            foreach ( $responses as &$item ) {
                if ( empty( $item->answers ) ) {
                    continue;
                }

                foreach ( $item->answers as &$answer ) {
                    if ( isset( $answer->field_type ) && 'html' === $answer->field_type && ! empty( $answer->value ) ) {
                        $answer->value = formgent_replace_html_dynamic_tags(
                            (string) $answer->value,
                            (int) $item->form_id,
                            (int) $item->id
                        );
                    }
                }
            }
            $data['responses'] = $responses;
        }

        $response              = $this->pagination( $wp_rest_request, $data['total'], $dto->get_per_page() );
        $response['responses'] = $data['responses'];

        return Response::send( $response );
    }

    public function all_responses( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'per_page'     => 'numeric',
                'page'         => 'numeric',
                's'            => 'string|max:255',
                'is_read'      => 'numeric|accepted:0,1',
                'is_starred'   => 'numeric|accepted:0,1',
                'order_by'     => 'string|max:50',
                'order'        => 'string|accepted:asc,desc',
                'is_completed' => 'numeric|accepted:0,1',
                'date_type'    => 'string|accepted:all,today,yesterday,last_week,last_month,date_frame',
                'date_frame'   => 'array',
                'sort_by'      => 'string|accepted:alphabetical,date_created,read,unread,complete,incomplete,starred',
            ]
        );

        $dto = new AllResponsesReadDTO;

        // Set pagination with proper defaults
        $page = intval( $wp_rest_request->get_param( 'page' ) );
        $dto->set_page( $page > 0 ? $page : 1 );

        $per_page = intval( $wp_rest_request->get_param( 'per_page' ) );
        $dto->set_per_page( $per_page > 0 ? $per_page : 10 );

        // Set search with proper sanitization and null handling
        $search = $wp_rest_request->get_param( 's' );
        $dto->set_search( ! empty( $search ) ? sanitize_text_field( $search ) : null );

        // Set optional filters
        if ( $wp_rest_request->has_param( 'is_read' ) ) {
            $dto->set_is_read( $wp_rest_request->get_param( 'is_read' ) );
        }

        if ( $wp_rest_request->has_param( 'is_starred' ) ) {
            $dto->set_is_starred( $wp_rest_request->get_param( 'is_starred' ) );
        }

        if ( $wp_rest_request->has_param( 'is_completed' ) ) {
            $dto->set_is_completed( $wp_rest_request->get_param( 'is_completed' ) );
        }

        // Set date filtering
        $dto->set_date_type( $wp_rest_request->get_param( 'date_type' ) );
        $date_frame = $wp_rest_request->get_param( 'date_frame' );
        $dto->set_date_frame( is_array( $date_frame ) ? $date_frame : [] );

        // Set sorting
        $dto->set_sort_by( $wp_rest_request->get_param( 'sort_by' ) );

        // Set ordering with defaults (used when sort_by is not provided)
        $dto->set_order( $wp_rest_request->get_param( 'order' ) ?? 'desc' )
            ->set_order_by( $wp_rest_request->get_param( 'order_by' ) ?? 'id' );

        $data                  = $this->repository->get_all( $dto );
        $response              = $this->pagination( $wp_rest_request, $data['total'], $dto->get_per_page() );
        $response['responses'] = $data['responses'];

        return Response::send( $response );
    }

    public function delete_all_responses( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'ids' => 'required|array',
            ]
        );

        $response_ids = $wp_rest_request->get_param( 'ids' );

        if ( empty( $response_ids ) || ! formgent_is_one_level_array( $response_ids ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Sorry, Something was wrong.', 'formgent' )
                ],
                400
            );
        }

        try {
            do_action( 'formgent_before_delete_all_responses', $response_ids, $wp_rest_request );

            $deleted_count = $this->repository->delete_by_ids( $response_ids );

            do_action( 'formgent_after_delete_all_responses', $response_ids, $wp_rest_request );

            return Response::send(
                [
                    'message'       => sprintf(
                        esc_html( _n( '%d response has been successfully deleted.', '%d responses have been successfully deleted.', $deleted_count, 'formgent' ) ),
                        $deleted_count
                    ),
                    'deleted_count' => $deleted_count
                ]
            );
        } catch ( \Exception $e ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Failed to delete responses.', 'formgent' )
                ],
                500
            );
        }
    }

    public function show( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'               => 'numeric',
                'page'             => 'numeric',
                's'                => 'string|max:255',
                'form_id'          => 'numeric',
                'is_read'          => 'numeric|accepted:0,1',
                'order_by'         => 'string|max:50',
                'order'            => 'string|accepted:asc,desc',
                'order_field_type' => 'string|accepted:response,answer',
                'is_completed'     => 'numeric|accepted:0,1',
            ]
        );

        $dto = new ResponseSingleDTO;

        // If id is provided, use it directly (new approach)
        // Otherwise, fall back to page-based approach (backward compatibility)
        if ( $wp_rest_request->has_param( 'id' ) ) {
            $dto->set_id( intval( $wp_rest_request->get_param( 'id' ) ) );
        } elseif ( $wp_rest_request->has_param( 'page' ) ) {
            $dto->set_page( intval( $wp_rest_request->get_param( 'page' ) ) );
        } else {
            return Response::send(
                [
                    'message' => esc_html__( 'Either "id" or "page" parameter is required.', 'formgent' )
                ],
                400
            );
        }

        $dto->set_search( (string) $wp_rest_request->get_param( 's' ) );

        $form_id_param = $wp_rest_request->get_param( 'form_id' );
        if ( $wp_rest_request->has_param( 'form_id' ) && $form_id_param !== '' && (int) $form_id_param > 0 ) {
            $dto->set_form_id( intval( $form_id_param ) );
        }

        // When fetching by id, always look up the response to get canonical form_id and is_completed.
        // This prevents stale client-side filters (e.g. is_completed=0) from hiding the response
        // after it transitions to completed.
        $response_by_id = null;

        if ( $dto->get_id() !== null ) {
            $response_by_id = $this->repository->get_by_id( $dto->get_id() );
            if ( ! $response_by_id ) {
                return Response::send(
                    [
                        'message' => esc_html__( 'Response not found.', 'formgent' )
                    ],
                    404
                );
            }
            $dto->set_form_id( (int) $response_by_id->form_id );
            $dto->set_is_completed( (int) $response_by_id->is_completed );
            $dto->set_is_read( (int) $response_by_id->is_read );
        } elseif ( $wp_rest_request->has_param( 'is_completed' ) ) {
            $value = $wp_rest_request->get_param( 'is_completed' );
            if ( $value !== null && $value !== '' ) {
                $dto->set_is_completed( (int) $value );
            }
        }

        if ( $dto->get_id() === null && $wp_rest_request->has_param( 'is_read' ) ) {
            $dto->set_is_read( $wp_rest_request->get_param( 'is_read' ) );
        }

        $dto->set_order( $wp_rest_request->get_param( 'order' ) ?? 'desc' )
            ->set_order_by( $wp_rest_request->get_param( 'order_by' ) ?? 'id' )
            ->set_order_field_type( $wp_rest_request->get_param( 'order_field_type' ) ?? 'response' );

        // When using page (not id), form_id is required
        if ( $dto->get_id() === null && ( ! $dto->get_form_id() || $dto->get_form_id() === 0 ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Parameter "form_id" is required when using "page".', 'formgent' )
                ],
                400
            );
        }

        // This endpoint returns a single response per page (for drawer navigation).
        // Use the paginated variant; get_single() requires an explicit response_id.
        $data = $this->repository->get_single_with_pagination( $dto );

        if ( ! empty( $data['responses'] ) ) {
            foreach ( $data['responses'] as &$item ) {
                if ( empty( $item->answers ) ) {
                    continue;
                }

                foreach ( $item->answers as &$answer ) {
                    if ( isset( $answer->field_type ) && 'html' === $answer->field_type && ! empty( $answer->value ) ) {
                        $answer->value = formgent_replace_html_dynamic_tags(
                            (string) $answer->value,
                            (int) $item->form_id,
                            (int) $item->id
                        );
                    }
                }
            }
        }

        $response              = $this->pagination( $wp_rest_request, $data['total'], 1 );
        $response['responses'] = $data['responses'];

        return Response::send( $response );
    }

    public function update( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'response_id' => 'required|numeric',
                'form_data'   => 'required|array',
            ]
        );

        $response_id = intval( $wp_rest_request->get_param( 'response_id' ) );

        // Get the response
        $response = $this->repository->get_by_id( $response_id );

        if ( ! $response ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Response not found', 'formgent' )
                ],
                404
            );
        }

        // Get the form
        $form = formgent_get_form_by_id( $response->form_id, true );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ],
                404
            );
        }

        // Set additional form properties
        $form->save_incomplete_data = formgent_is_save_incompleted_data( $form->ID );

        // Trigger before update hook
        do_action( 'formgent_before_update_response', $response_id, $form, $wp_rest_request );

        // Validate and process form data
        $validate_data = $this->validate_and_process_form_data( $form, $validator, $wp_rest_request, $response_id );

        if ( ! empty( $validate_data['errors'] ) ) {
            return Response::send(
                [
                    'messages'         => $validate_data['errors'],
                    'processed_fields' => $validate_data['processed_fields'] ?? [],
                    'skipped_fields'   => $validate_data['skipped_fields'] ?? []
                ],
                422
            );
        }

        // Check if any fields were actually processed
        if ( empty( $validate_data['processed_fields'] ) && ! empty( $wp_rest_request->get_param( 'form_data' ) ) ) {
            return Response::send(
                [
                    'message'          => esc_html__( 'No fields were processed. Please check field names.', 'formgent' ),
                    'skipped_fields'   => $validate_data['skipped_fields'] ?? [],
                    'available_fields' => array_keys( formgent_get_form_fields( $form ) )
                ],
                422
            );
        }

        // Update response timestamp
        $this->repository->update_response_timestamp( $response_id );

        // Trigger after update hook
        do_action( 'formgent_after_update_response', $response_id, $form, $wp_rest_request );

        return Response::send(
            [
                'message'          => esc_html__( 'Response has been updated successfully.', 'formgent' ),
                'processed_fields' => $validate_data['processed_fields'] ?? []
            ]
        );
    }

    /**
     * Validates and processes form data for updating responses.
     *
     * @param \stdClass $form
     * @param Validator $validator
     * @param WP_REST_Request $request
     * @param int $response_id
     * @return array
     */
    private function validate_and_process_form_data( \stdClass $form, Validator $validator, WP_REST_Request $request, int $response_id ): array {
        $bypass_required = (bool) $request->get_param( 'bypass_required' );
        $form_data       = $request->get_param( 'form_data' );
        if ( ! is_array( $form_data ) ) {
            $form_data = [];
        }

        // Promote submitted values into top-level request params so validators
        // (which rely on has_param/get_param) can reliably see them.
        foreach ( $form_data as $field_name => $field_value ) {
            $request->set_param( (string) $field_name, $field_value );
        }

        // Also mirror the values into the body params bucket for any code paths
        // that look specifically at body params.
        $request->set_body_params( $form_data );
        // Mark this request as an admin-side edit of an existing entry.
        // Field validators can use this to skip user-facing constraints (e.g. email confirmation).
        $request->set_param( '_formgent_admin_edit', true );
        // Optional: allow bypassing required rules for admin edits.
        $request->set_param( '_formgent_bypass_required', $bypass_required );
        // IMPORTANT: ensure the validator reads from the normalized body params (form_data),
        // not from the original request payload that still contains the form_data wrapper.
        $validator->wp_rest_request = $request;

        $registered_fields  = formgent_config( 'fields' );
        $fields             = formgent_get_form_fields( $form );
        $errors             = [];
        $parent_field_names = [];
        $processed_fields   = []; // Track processed fields
        $skipped_fields     = []; // Track skipped fields

        foreach ( $form_data as $field_name => $field_value ) {
            // Skip if the field is not found in the form's field settings
            if ( empty( $fields[$field_name] ) ) {
                $skipped_fields[$field_name] = esc_html__( 'Field not found in form settings', 'formgent' );
                continue;
            }

            $field = $fields[$field_name];

            // Skip if the field type is not allowed in the response
            if ( empty( $registered_fields[$field['field_type']]['allowed_in_response'] ) ) {
                $skipped_fields[$field_name] = esc_html__( 'Field type not allowed in response', 'formgent' );
                continue;
            }

            try {
                // Get the field handler for this field type
                $field_handler = formgent_field_handler( $field['field_type'] );

                // Skip if the form type is not supported by the field handler
                if ( ! in_array( $form->form_type, $field_handler::get_supported_form_types(), true ) ) {
                    $skipped_fields[$field_name] = esc_html__( 'Form type not supported by field handler', 'formgent' );
                    continue;
                }

                // Validate the field
                $field_handler->validate( $field, $request, $validator, $form );

                // Get existing answer or create new
                $existing_answer = $this->answer_repository->get_by_field_by_name( $response_id, $field_name );

                // Create DTO
                $dto = $field_handler->get_field_dto( $field, $request, $form );
                $dto->set_response_id( $response_id );

                // Ensure form_id is set (should already be set by get_field_dto, but ensure it)
                if ( ! $dto->get_form_id() ) {
                    $dto->set_form_id( $form->ID );
                }

                // Handle child fields if present
                if ( $field_handler->has_children ) {
                    $children = $this->get_children_dtos( $form, $validator, $request, $field, $response_id );

                    if ( ! empty( $children['errors'] ) ) {
                        $errors[$field['name']] = $children['errors'];
                    }

                    $parent_field_names[] = $field['name'];
                }

                // Update or create parent answer
                if ( $existing_answer ) {
                    $dto->set_id( intval( $existing_answer->id ) );
                    $result = $this->answer_repository->update( $dto );

                    // Check if update was successful
                    if ( $result === false ) {
                        $errors[$field_name] = esc_html__( 'Failed to update answer in database', 'formgent' );
                    } elseif ( $result === 0 ) {
                        // Update returned 0 rows affected - might mean ID doesn't exist or data is identical
                        // Still mark as processed since the operation completed
                        $processed_fields[] = $field_name;
                    } else {
                        $processed_fields[] = $field_name;
                    }
                } else {
                    $result = $this->answer_repository->create( $dto );

                    // Check if create was successful
                    if ( $result === false || $result === 0 ) {
                        $errors[$field_name] = esc_html__( 'Failed to create answer in database', 'formgent' );
                    } else {
                        $processed_fields[] = $field_name;
                    }
                }

                // Handle child fields update/create
                if ( $field_handler->has_children && isset( $children ) && ! empty( $children['field_dtos'] ) ) {
                    $this->handle_child_fields_update( $response_id, $field_name, $children['field_dtos'] );
                }

            } catch ( \FormGent\WpMVC\Exceptions\Exception $exception ) {
                // Merge any validation errors from the field handler
                $errors = array_merge( $errors, $exception->get_messages() );
            }
        }

        // If no fields were processed, add error
        if ( empty( $processed_fields ) && ! empty( $form_data ) ) {
            $errors['_no_fields_processed'] = esc_html__( 'No fields were processed. Please check field names and ensure they exist in the form.', 'formgent' );

            // Add skipped field details for debugging
            if ( ! empty( $skipped_fields ) ) {
                $errors['_skipped_fields'] = $skipped_fields;
            }
        }

        return compact( 'errors', 'parent_field_names', 'processed_fields', 'skipped_fields' );
    }

    /**
     * Handles updating child fields for a response.
     *
     * @param int $response_id
     * @param string $parent_field_name
     * @param array $children_dtos
     */
    private function handle_child_fields_update( int $response_id, string $parent_field_name, array $children_dtos ): void {
        // Get parent answer
        $parent_answer = $this->answer_repository->get_by_field_by_name( $response_id, $parent_field_name );

        if ( ! $parent_answer ) {
            return;
        }

        // Get existing child answers
        $existing_children = \FormGent\App\Models\Answer::query()
            ->where( 'response_id', $response_id )
            ->where( 'parent_id', $parent_answer->id )
            ->get();

        $existing_children_map = [];
        foreach ( $existing_children as $child ) {
            $existing_children_map[$child->field_name] = $child;
        }

        // Update or create child answers
        foreach ( $children_dtos as $child_dto ) {
            /**
             * @var \FormGent\App\DTO\AnswerDTO $child_dto
             */
            $child_dto->set_response_id( $response_id )->set_parent_id( $parent_answer->id );

            if ( isset( $existing_children_map[$child_dto->get_field_name()] ) ) {
                // Update existing child
                $child_dto->set_id( intval( $existing_children_map[$child_dto->get_field_name()]->id ) );
                $result = $this->answer_repository->update( $child_dto );

                // Check if update was successful
                if ( $result === false ) {
                    // Log error but continue processing other children
                    error_log(
                        sprintf(
                            'Failed to update child field %s for response %d',
                            $child_dto->get_field_name(),
                            $response_id
                        )
                    );
                }
            } else {
                // Create new child
                $result = $this->answer_repository->create( $child_dto );

                // Check if create was successful
                if ( $result === false || $result === 0 ) {
                    // Log error but continue processing other children
                    error_log(
                        sprintf(
                            'Failed to create child field %s for response %d',
                            $child_dto->get_field_name(),
                            $response_id
                        )
                    );
                }
            }
        }
    }

    /**
     * Retrieves and validates child field DTOs for update.
     *
     * @param \stdClass $form
     * @param Validator $validator
     * @param WP_REST_Request $request
     * @param array $parent_field
     * @param int $response_id
     * @return array
     */
    private function get_children_dtos( \stdClass $form, Validator $validator, WP_REST_Request $request, array $parent_field, int $response_id ): array {
        $children_request = new WP_REST_Request( 'POST', '/' );
        $form_data        = $request->get_param( $parent_field['name'] );

        $field_dtos = [];
        $errors     = [];

        if ( ! is_array( $form_data ) ) {
            return compact( 'field_dtos', 'errors' );
        }

        $children_request->set_body_params( $form_data );
        $validator->wp_rest_request = $children_request;
        $registered_fields          = formgent_config( 'fields' );
        $fields                     = $parent_field['children'];

        foreach ( $form_data as $field_name => $field_data ) {
            // Skip if the field is not found in the parent field's children
            if ( empty( $fields[$field_name] ) ) {
                continue;
            }

            $field = $fields[$field_name];

            // Skip if the field type is not allowed in the response
            if ( empty( $registered_fields[$field['field_type']]['allowed_in_response'] ) ) {
                continue;
            }

            try {
                // Get the field handler for this field type
                $field_handler = formgent_field_handler( $field['field_type'] );

                // Skip if the form type is not supported by the field handler
                if ( ! in_array( $form->form_type, $field_handler::get_supported_form_types(), true ) ) {
                    continue;
                }

                // Validate the field and create its DTO
                $field_handler->validate( $field, $children_request, $validator, $form );
                $dto = $field_handler->get_field_dto( $field, $children_request, $form );

                $field_dtos[$field['name']] = $dto;

            } catch ( \FormGent\WpMVC\Exceptions\Exception $exception ) {
                // Merge any validation errors from the field handler
                $errors = array_merge( $errors, $exception->get_messages() );
            }
        }

        return compact( 'field_dtos', 'errors' );
    }

    public function get_fields( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'form_id' => 'required|numeric'
            ]
        );

        $form_id = intval( $wp_rest_request->get_param( 'form_id' ) );
        $form    = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ],
                404
            );
        }

        // $selected_fields = get_post_meta( $form->ID, '_response_table_names', true );
        $fields_settings = formgent_get_form_fields( $form );
        $fields          = $this->prepare_fields( $fields_settings );

        $completed_response   = $this->repository->get_count_completed_by_form_id( $form_id );
        $incompleted_response = $this->repository->get_count_incompleted_by_form_id( $form_id );

        return Response::send(
            [
                'form'            => [
                    'title'      => $form->post_title,
                    'type'       => formgent_get_form_type( $form->ID ),
                    'is_payment' => formgent_is_payment_form( $fields_settings )
                ],
                'total'           => [
                    'completed' => $completed_response,
                    'partial'   => $incompleted_response
                ],
                'selected_fields' => array_column( $fields, 'name' ),
                // 'selected_fields' => is_array( $selected_fields ) ? $selected_fields : array_column( $fields, 'name' ),
                'fields'          => $fields
            ]
        );
    }

    /**
     * Prepares fields for response table display with additional configuration data.
     *
     * @param array  $fields_settings Array of field settings from the form.
     * @param string $parent_name     Parent field name for nested fields.
     * @param string $parent_type     Parent field type for nested fields.
     * @return array Prepared fields array with configuration data.
     */
    function prepare_fields( array $fields_settings, string $parent_name = '', string $parent_type = '' ) {
        $registered_fields = formgent_config( 'fields' );

        $fields = [];

        foreach ( $fields_settings as $field ) {
            if ( empty( $registered_fields[$field['field_type']]['allowed_in_response_table'] ) ) {
                continue;
            }

            $name = ! empty( $parent_name ) ? $parent_name . '.' . $field['name'] : $field['name'];

            if ( ! empty( $field['children'] ) ) {
                $fields = array_merge( $fields, $this->prepare_fields( $field['children'], $name, $field['field_type'] ) );
                continue;
            }

            // Build field data array with base properties.
            $field_data = [
                'name'  => sanitize_text_field( $name ),
                'label' => isset( $field['label'] ) ? sanitize_text_field( $field['label'] ) : '',
                'type'  => sanitize_text_field( $field['field_type'] ),
            ];

            // Add options for Dropdown, SingleChoice, and MultipleChoice fields.
            // Exclude options for dropdown fields that are children of address fields.
            if ( in_array( $field['field_type'], ['dropdown', 'single-choice', 'multiple-choice'], true ) ) {
                // Skip adding options if this is a dropdown child of an address field.
                if ( 'dropdown' === $field['field_type'] && 'address' === $parent_type ) {
                    // Do not add options for address field children.
                } elseif ( ! empty( $field['options'] ) && is_array( $field['options'] ) ) {
                    $field_data['options'] = $this->sanitize_field_options( $field['options'] );
                }
            }

            // Include "other option" config for choice fields so the edit UI
            // can show/hide the "Other" input without heuristics.
            if ( in_array( $field['field_type'], ['single-choice', 'multiple-choice'], true ) ) {
                if ( isset( $field['allow_user_add_other_option'] ) ) {
                    $field_data['allow_user_add_other_option'] = (bool) $field['allow_user_add_other_option'];
                }
                if ( isset( $field['other_label'] ) ) {
                    $field_data['other_label'] = sanitize_text_field( $field['other_label'] );
                }
                if ( isset( $field['other_placeholder'] ) ) {
                    $field_data['other_placeholder'] = sanitize_text_field( $field['other_placeholder'] );
                }
            }

            // Add min & max value for RangeSlider field.
            if ( 'range-slider' === $field['field_type'] ) {
                if ( isset( $field['min_value'] ) && is_numeric( $field['min_value'] ) ) {
                    $field_data['min_value'] = floatval( $field['min_value'] );
                }
                if ( isset( $field['max_value'] ) && is_numeric( $field['max_value'] ) ) {
                    $field_data['max_value'] = floatval( $field['max_value'] );
                }
            }

            // Add icon & rating limit for Rating field.
            if ( 'rating' === $field['field_type'] ) {
                if ( isset( $field['rating_icon'] ) ) {
                    $field_data['rating_icon'] = sanitize_text_field( $field['rating_icon'] );
                }
                if ( isset( $field['rating_limit'] ) && is_numeric( $field['rating_limit'] ) ) {
                    $field_data['rating_limit'] = absint( $field['rating_limit'] );
                }
            }

            // Add picker type, range, date format, separator & allowed days for DatePicker field.
            if ( 'date-picker' === $field['field_type'] ) {
                if ( isset( $field['option'] ) && is_string( $field['option'] ) ) {
                    $field_data['option'] = sanitize_text_field( $field['option'] );
                }
                if ( isset( $field['range'] ) ) {
                    $field_data['range'] = (bool) $field['range'];
                }
                if ( isset( $field['date_format'] ) && is_string( $field['date_format'] ) ) {
                    $field_data['date_format'] = sanitize_text_field( $field['date_format'] );
                }
                if ( isset( $field['separator'] ) && is_string( $field['separator'] ) ) {
                    $field_data['separator'] = sanitize_text_field( $field['separator'] );
                }
                if ( isset( $field['allowed_days'] ) && is_array( $field['allowed_days'] ) ) {
                    $field_data['allowed_days'] = array_map(
                        function ( $day ) {
                            return sanitize_text_field( $day );
                        },
                        $field['allowed_days']
                    );
                }
            }

            // Add the complete field data once.
            $fields[] = $field_data;
        }

        return $fields;
    }

    /**
     * Sanitizes field options array.
     *
     * @param array $options Array of option objects/arrays with 'label' and 'value' keys.
     * @return array Sanitized options array.
     */
    protected function sanitize_field_options( array $options ): array {
        $sanitized_options = [];

        foreach ( $options as $option ) {
            if ( ! is_array( $option ) ) {
                continue;
            }

            $sanitized_option = [];

            if ( isset( $option['label'] ) ) {
                $sanitized_option['label'] = sanitize_text_field( $option['label'] );
            }

            if ( isset( $option['value'] ) ) {
                $sanitized_option['value'] = sanitize_text_field( $option['value'] );
            }

            if ( ! empty( $sanitized_option ) ) {
                $sanitized_options[] = $sanitized_option;
            }
        }

        return $sanitized_options;
    }

    public function update_fields( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'form_id'     => 'required|numeric',
                'field_names' => 'required|array'
            ]
        );

        $field_names = $wp_rest_request->get_param( 'field_names' );

        if ( ! formgent_is_one_level_array( $field_names ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Sorry, Something was wrong.', 'formgent' )
                ],
                500
            );
        }

        $form_id = intval( $wp_rest_request->get_param( 'form_id' ) );
        $form    = $this->form_repository->get_by_id( $form_id, [1] );

        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found', 'formgent' )
                ],
                404
            );
        }

        $field_names = map_deep( $field_names, "sanitize_text_field" );

        update_post_meta( $form_id, "_response_table_names", $field_names );

        return Response::send( [] );
    }

    public function update_starred( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'         => 'required|numeric',
                'is_starred' => 'required|numeric|accepted:0,1'
            ]
        );

        do_action( 'formgent_before_update_response_starred', $wp_rest_request );

        $this->repository->update_starred( intval( $wp_rest_request->get_param( 'id' ) ), $wp_rest_request->get_param( 'is_starred' ) );

        do_action( 'formgent_after_update_response_starred', $wp_rest_request );

        return Response::send(
            [
                'message' => esc_html__( 'The response starred has been updated successfully.', 'formgent' )
            ]
        );
    }

    public function update_read( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id'      => 'required|numeric',
                'is_read' => 'required|numeric|accepted:0,1'
            ]
        );

        do_action( 'formgent_before_update_response_read', $wp_rest_request );

        $this->repository->update_read( intval( $wp_rest_request->get_param( 'id' ) ), $wp_rest_request->get_param( 'is_read' ) );

        do_action( 'formgent_after_update_response_read', $wp_rest_request );

        return Response::send(
            [
                'message' => esc_html__( 'The response read has been updated successfully.', 'formgent' )
            ]
        );
    }

    public function update_read_bulk( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'ids'     => 'required|array',
                'is_read' => 'required|numeric|accepted:0,1',
            ]
        );

        $ids = $wp_rest_request->get_param( 'ids' );

        if ( empty( $ids ) || ! formgent_is_one_level_array( $ids ) ) {
            return Response::send(
                ['message' => esc_html__( 'Invalid IDs.', 'formgent' )],
                422
            );
        }

        do_action( 'formgent_before_bulk_update_response_read', $ids, $wp_rest_request );

        $this->repository->update_read_bulk( array_map( 'intval', $ids ), (int) $wp_rest_request->get_param( 'is_read' ) );

        do_action( 'formgent_after_bulk_update_response_read', $ids, $wp_rest_request );

        return Response::send(
            ['message' => esc_html__( 'Responses read state updated successfully.', 'formgent' )]
        );
    }

    public function quiz_result( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'id' => 'required|numeric',
            ]
        );

        do_action( 'formgent_before_get_quiz_result', $wp_rest_request );

        $result = $this->quiz_repository->get_result( intval( $wp_rest_request->get_param( 'id' ) ) );

        do_action( 'formgent_after_quiz_result', $wp_rest_request, $result );

        return Response::send(
            [
                'result' => $result,
            ]
        );
    }

    public function export( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'form_id'  => 'required|numeric',
                'page'     => 'numeric',
                'per_page' => 'numeric',
            ]
        );

        $response_ids = $wp_rest_request->get_param( 'response_ids' ) ?? [];

        if ( ! empty( $response_ids ) && ! formgent_is_one_level_array( $response_ids ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Sorry, Something was wrong.', 'formgent' )
                ],
                500
            );
        }

        $form_id  = intval( $wp_rest_request->get_param( 'form_id' ) );
        $page     = max( 1, intval( $wp_rest_request->get_param( 'page' ) ?? 1 ) );
        $per_page = min( 500, max( 1, intval( $wp_rest_request->get_param( 'per_page' ) ?? 500 ) ) );
        $form     = formgent_get_form_by_id( $form_id );

        $total     = $this->repository->get_export_count( $form_id, $response_ids );
        $responses = $this->repository->get_export_data( $form_id, $response_ids, $page, $per_page );

        if ( ! empty( $responses ) ) {
            foreach ( $responses as &$response_item ) {
                if ( empty( $response_item->answers ) ) {
                    continue;
                }

                foreach ( $response_item->answers as &$answer ) {
                    if ( isset( $answer->field_type ) && 'html' === $answer->field_type && ! empty( $answer->value ) ) {
                        $answer->value = formgent_replace_html_dynamic_tags(
                            (string) $answer->value,
                            (int) $response_item->form_id,
                            (int) $response_item->id
                        );
                    }
                }
            }
        }

        return Response::send(
            [
                'form'        => [
                    'title' => $form->post_title,
                ],
                'fields'      => $this->summary_repository->get_fields( $form_id ),
                'responses'   => $responses,
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $per_page,
                'total_pages' => $total > 0 ? (int) ceil( $total / $per_page ) : 1,
            ]
        );
    }

    public function delete_bulk_response( Validator $validator, WP_REST_Request $wp_rest_request ) {
        $validator->validate(
            [
                'ids'     => 'required|array',
                'form_id' => 'required|numeric'
            ]
        );

        $response_ids = $wp_rest_request->get_param( 'ids' );

        if ( empty( $response_ids ) || ! formgent_is_one_level_array( $response_ids ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Sorry, Something was wrong.', 'formgent' )
                ]
            );
        }

        try {
            do_action( 'formgent_before_delete_responses', $response_ids, $wp_rest_request );

            $this->repository->deletes( intval( $wp_rest_request->get_param( 'form_id' ) ), $response_ids );

            do_action( 'formgent_after_delete_responses', $response_ids, $wp_rest_request );

            return Response::send(
                [
                    'message' => esc_html__( 'Responses have been successfully deleted.', 'formgent' )
                ]
            );
        } catch ( Exception $exception ) {
            return Response::send(
                [
                    'message' => $exception->getMessage()
                ],
                $exception->getCode()
            );
        }
    }

    /**
     * Return total unread responses count across all forms (for admin menu badge).
     *
     * @param WP_REST_Request $wp_rest_request
     * @return array|\FormGent\WpMVC\Routing\Response
     */
    public function total_unread_count( WP_REST_Request $wp_rest_request ) {
        $count = $this->repository->get_total_unread_count();
        return Response::send( [ 'total_unread' => $count ] );
    }
}