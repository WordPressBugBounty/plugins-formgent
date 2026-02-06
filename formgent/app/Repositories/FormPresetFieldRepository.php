<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ResponseDTO;
use FormGent\App\DTO\AnswerFieldDTO;
use Exception;

class FormPresetFieldRepository {
    public FormRepository $form_repository;

    public function __construct() {
        $this->form_repository = formgent_singleton( FormRepository::class );
    }

    public function transform_value( string $key, array $answers, ResponseDTO $response, $default_value = '' ) {
        // If the key is already an answer field, return its value
        if ( isset( $answers[ $key ] ) ) {
            /**
             * @var AnswerFieldDTO $answer_dto
             */
            $answer_dto = $answers[ $key ];

            return $answer_dto->get_value();
        }

        // Transform preset value
        $preset_fields = [
            "{{site_url}}",
            "{{site_name}}",
            "{{admin_email}}",
            "{{admin_name}}",
            "{{user_email}}",
            "{{user_name}}",
            "{{random_string}}",
        ];

        if ( in_array( $key, $preset_fields, true ) ) {
            switch ( $key ) {
                case '{{site_url}}':
                    return get_option( 'siteurl', '' );

                case '{{site_name}}':
                    return get_option( 'blogname', '' );

                case '{{admin_email}}':
                    return get_option( 'admin_email', '' );

                case '{{admin_name}}':
                    $admin_email = get_option( 'admin_email', '' );
                    $user        = get_user_by( 'email', $admin_email );

                    return $user->data->display_name;

                case '{{user_email}}':
                    if ( ! $response->get_created_by() ) {
                        return $default_value;
                    }

                    $user = get_user_by( 'id', $response->get_created_by() );

                    if ( false === $user ) {
                        return $default_value;
                    }

                    return $user->data->user_email;
                case '{{user_name}}':
                    if ( ! $response->get_created_by() ) {
                        return $default_value;
                    }

                    $user = get_user_by( 'id', $response->get_created_by() );

                    if ( false === $user ) {
                        return $default_value;
                    }

                    return $user->data->display_name;

                case '{{random_string}}':
                    return formgent_generate_token();

                default:
                    return $default_value;
            }
        }

        // Transform response value
        $matches = [];

        preg_match( '/^{{response_(.+)}}$/', $key, $matches );

        if ( ! empty( $matches ) ) {
            $data   = $response->set_exclude_to_array( [] )->to_array();
            $column = $matches[1];

            if ( isset( $data[ $column ] ) ) {
                return $data[ $column ];
            }

            if ( 'date_mm_dd_yyyy' === $matches[1] ) {
                return gmdate( "m-d-Y", strtotime( $response->get_created_at() ) );
            }

            if ( 'date_dd_mm_yyyy' === $matches[1] ) {
                return gmdate( "d-m-Y", strtotime( $response->get_created_at() ) );
            }

            return $default_value;
        }

        // Transform form field value
        $matches = [];

        preg_match( '/^{{field:(.+)}}$/', $key, $matches );

        if ( ! empty( $matches ) ) {
            $field = $this->find_answer_field( explode( '.', $matches[1] ), $answers );

            if ( ! $field ) {
                return $default_value;
            }

            return $field->get_value();
        }

        // Transform form field label
        $matches = [];

        preg_match( '/^{{label:(.+)}}$/', $key, $matches );

        if ( ! empty( $matches ) ) {
            $field = $this->find_answer_field( explode( '.', $matches[1] ), $answers );

            if ( ! $field ) {
                return $default_value;
            }

            return $field->get_field_label();
        }

        return $default_value;
    }

    public function get_preset_fields( int $form_id ) {
        $form = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Form not found', 'formgent' ), 404 );
        }

        $fields = formgent_get_form_fields( $form );

        $preset_fields = [
            // Response Fields
            [
                'type'        => 'response',
                'value'       => "{{response_id}}",
                'description' => __( 'Prints the response ID', 'formgent' ),
            ],
            [
                'type'        => 'response',
                'value'       => "{{response_ip}}",
                'description' => __( 'Prints the IP address', 'formgent' ),
            ],
            [
                'type'        => 'response',
                'value'       => "{{response_device}}",
                'description' => __( 'Prints the device name', 'formgent' ),
            ],
            [
                'type'        => 'response',
                'value'       => "{{response_browser}}",
                'description' => __( 'Prints the browser name', 'formgent' ),
            ],
            [
                'type'        => 'response',
                'value'       => "{{response_browser_version}}",
                'description' => __( 'Prints the browser version name', 'formgent' ),
            ],
            // Other Info
            [
                'type'        => 'preset',
                'value'       => "{{site_url}}",
                'description' => __( 'Prints the website URL', 'formgent' ),
            ],
            [
                'type'        => 'preset',
                'value'       => "{{site_name}}",
                'description' => __( 'Prints the website name', 'formgent' ),
            ],
            [
                'type'        => 'preset',
                'value'       => "{{admin_email}}",
                'description' => __( 'Prints the admin email address', 'formgent' ),
            ],
            [
                'type'        => 'preset',
                'value'       => "{{admin_name}}",
                'description' => __( 'Prints the admin name', 'formgent' ),
            ],
            [
                'type'        => 'preset',
                'value'       => "{{user_email}}",
                'description' => __( 'Prints the user email address', 'formgent' ),
            ],
            [
                'type'        => 'preset',
                'value'       => "{{user_name}}",
                'description' => __( 'Prints user name', 'formgent' ),
            ],
        ];

        $field_description = __( 'Prints the fields value', 'formgent' );
        $label_description = __( 'Prints the fields label', 'formgent' );

        foreach ( $fields as $field_key => $field ) {
            if ( empty( $field['children'] ) ) {
                $preset_fields[] = [
                    'type'        => 'form_field',
                    'value'       => "{{field:{$field_key}}}",
                    'description' => $field_description,
                ];

                if ( ! empty( $field['label'] ) ) {
                    $preset_fields[] = [
                        'type'        => 'form_field',
                        'value'       => "{{label:{$field_key}}}",
                        'description' => $label_description,
                    ];
                }

                continue;
            }

            foreach ( $field['children'] as $child_key => $child_field ) {
                $preset_fields[] = [
                    'type'        => 'form_field',
                    'value'       => "{{field:{$field_key}.{$child_key}}}",
                    'description' => $field_description,
                ];

                if ( ! empty( $child_field['label'] ) ) {
                    $preset_fields[] = [
                        'type'        => 'form_field',
                        'value'       => "{{label:{$field_key}.{$child_key}}}",
                        'description' => $label_description,
                    ];
                }
            }
        }

        return $preset_fields;
    }

    public function find_answer_field( array $input_names, array $answer_items ): ?AnswerFieldDTO {
        $target_field = null;

        foreach ( $answer_items as $answer ) {
            /**
             * @var AnswerFieldDTO $answer
             */

            if ( $input_names[ 0 ] === $answer->get_field_name() ) {
                $target_field = $answer;
            }
        }

        if ( ! $target_field ) {
            return null;
        }

        if ( count( $input_names ) === 1 ) {
            return $target_field;
        }

        /**
         * @var AnswerFieldDTO $target_field
         */

        return $this->find_answer_field( [$input_names[1]], $target_field->get_children() );
    }

    /**
     * @param int $form_id
     * @param int $response_id
     * @return AnswerFieldDTO[]
     */
    public function get_form_answers_flattened( int $form_id, int $response_id ): array {
        $form = formgent_get_form_by_id( $form_id );

        if ( ! $form ) {
            return [];
        }

        /**
         * @var AnswerRepository $answer_repository
         */
        $answer_repository = formgent_singleton( AnswerRepository::class );
        $form_answers_data = $answer_repository->get( $response_id );

        if ( empty( $form_answers_data ) ) {
            return [];
        }

        $fields = formgent_get_form_fields( $form );

        $form_fields = [];

        foreach ( $form_answers_data as $form_answer_field ) {
            try {
                $form_field = $fields[ $form_answer_field->field_name ];

                if ( ! $form_field ) {
                    continue;
                }

                $field_dto = new AnswerFieldDTO();

                $field_dto->set_id( $form_answer_field->id )
                    ->set_response_id( $response_id )
                    ->set_form_id( $form_answer_field->form_id )
                    ->set_field_name( $form_answer_field->field_name )
                    ->set_field_id( $form_field['id'] )
                    ->set_field_type( $form_answer_field->field_type )
                    ->set_field_label( isset( $form_field['label'] ) ? $form_field['label'] : $form_answer_field->field_name )
                    ->set_value( $form_answer_field->value );

                if ( ! empty( $form_field['options'] ) ) {
                    $field_dto->set_options( $form_field['options'] );
                }

                if ( empty( $form_answer_field->children ) ) {
                    $form_fields[$field_dto->get_field_id()] = $field_dto;
                    continue;
                }

                $form_child_fields = [];

                foreach ( $form_answer_field->children as $child_field ) {
                    if ( empty( $form_field['children'][$child_field->field_name] ) ) {
                        continue;
                    }

                    $child_form_field = $form_field['children'][$child_field->field_name];

                    $child_field_dto = new AnswerFieldDTO();

                    $child_field_dto->set_id( $child_field->id )
                        ->set_response_id( $response_id )
                        ->set_form_id( $form_answer_field->form_id )
                        ->set_parent_id( $form_answer_field->id )
                        ->set_field_id( $child_form_field['id'] )
                        ->set_field_name( $child_field->field_name )
                        ->set_field_type( $child_field->field_type )
                        ->set_field_label( $child_form_field['label'] )
                        ->set_value( $child_field->value );

                    if ( ! empty( $child_form_field['options'] ) ) {
                        $child_field_dto->set_options( $child_form_field['options'] );
                    }

                    $form_fields[$child_field_dto->get_field_id()] = $child_field_dto;
                }
            } catch ( Exception $e ) {
                continue;
            }
        }

        return $form_fields;
    }
}