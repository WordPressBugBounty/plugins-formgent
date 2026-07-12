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

        $values = array_map(
            function ( $value ) {
                $relative_path = UploadFileToken::path_from_token( (string) $value );

                if ( null === $relative_path ) {
                    throw new Exception( esc_html__( 'Invalid file token', 'formgent' ), 400 );
                }

                return $relative_path;
            }, $values 
        );

        return ( new AnswerDTO() )->set_form_id( $form->ID )->set_field_type( $field['field_type'] )->set_field_name( $field['name'] )->set_value( $values );
    }
}
