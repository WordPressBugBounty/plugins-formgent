<?php

namespace FormGent\App\Fields\FileUpload;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Summary\Pagination;
use FormGent\App\Utils\UploadFileToken;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\RequestValidator\Validator;
use stdClass;
use WP_REST_Request;

trait MethodResolver {

    use Pagination;

    public static function get_key(): string {
        return 'file-upload';
    }

    protected function get_validation_rules( array $field ): array {
        //TODO: Implement file upload validation
        return ['array'];
    }

    public function get_field_dto( array $field, WP_REST_Request $wp_rest_request, stdClass $form ): AnswerDTO {
        $values = $wp_rest_request->get_param( $field['name'] );
        $limit  = ! empty( $field['is_limit_files'] ) ? max( 1, min( 20, (int) ( $field['limit_files'] ?? 5 ) ) ) : 20;

        if ( ! is_array( $values ) || count( $values ) > $limit ) {
            throw new Exception( esc_html__( 'Too many files were uploaded.', 'formgent' ), 400 );
        }

        $response_id = absint( $wp_rest_request->get_param( '_formgent_response_id' ) );

        if ( 0 === $response_id ) {
            throw new Exception( esc_html__( 'Invalid form session', 'formgent' ), 400 );
        }

        $field_identity    = (string) $field['name'];
        $parent_field_name = sanitize_key( (string) $wp_rest_request->get_param( '_formgent_parent_field_name' ) );

        if ( '' !== $parent_field_name && $wp_rest_request->has_param( '_formgent_field_index' ) ) {
            $field_identity = sanitize_key( $parent_field_name . '_' . absint( $wp_rest_request->get_param( '_formgent_field_index' ) ) . '_' . $field['name'] );
        }

        $values = array_map(
            function ( $value ) use ( $form, $response_id, $field_identity ) {
                $relative_path = UploadFileToken::path_from_token( (string) $value, (int) $form->ID, 'file_upload', $response_id, $field_identity );

                if ( null === $relative_path || null === UploadFileToken::resolve_existing_upload_path( $relative_path ) ) {
                    throw new Exception( esc_html__( 'Invalid file token', 'formgent' ), 400 );
                }

                return $relative_path;
            }, $values 
        );

        return ( new AnswerDTO() )->set_form_id( $form->ID )->set_field_type( $field['field_type'] )->set_field_name( $field['name'] )->set_value( $values );
    }
}
