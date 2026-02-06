<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Helpers\Form;
use FormGent\App\Utils\ConditionalLogic;
use FormGent\App\Repositories\FormSettingsRepository;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\Repositories\AnswerRepository;
use FormGent\App\DTO\AnswerFieldDTO;
use FormGent\App\Utils\FieldValueResolver;

function formgent_form_repository(): FormRepository {
    return formgent_singleton( FormRepository::class );
}

function formgent_get_response_columns_names( $form ) {
    return [
        "text",
        "email",
        // "name.first_name",
        // "name.middle_name",
        // "name.last_name",
        // "address.address_line_one",
        // "address.address_line_two",
        // "address.city",
        // "address.state",
        // "address.zip_code"
    ];
    // $table_names = get_post_meta( $form->ID, '_response_table_names', true );

    // if ( ! empty( $table_names ) ) {
    //     return $table_names;
    // }

    // $fields_settings   = formgent_get_form_fields( $form );
    // $registered_fields = formgent_config( 'fields' );

    // $table_names = [];

    // foreach ( $fields_settings as $field ) {
    //     if ( empty( $registered_fields[$field['field_type']]['allowed_in_response_table'] ) ) {
    //         continue;
    //     }

    //     $table_names[] = $field['name'];
    // }

    // return $table_names;
}

function formgent_get_form_by_id( int $form_id, bool $only_publish = false ) {
    if ( $only_publish ) {
        $form = formgent_form_repository()->get_by_id_publish( $form_id );
    } else {
        $form = formgent_form_repository()->get_by_id( $form_id );
    }

    if ( ! $form ) {
        return $form;
    }

    $form->form_type = formgent_get_form_type( $form_id );

    return $form;
}

function formgent_get_form_type( int $form_id ) {
    return get_post_meta( $form_id, '_formgent_type', true );
}

function formgent_is_conversational_form( stdClass $form ) {
    return 'conversational' === $form->form_type;
}

function formgent_form_default_values_functions() {
    return apply_filters(
        'formgent_default_values_functions', [
            'ip'         => 'formgent_get_user_ip_address',
            'site_url'   => 'site_url',
            'site_title' => function() {
                return get_bloginfo( 'name' );
            },
            'user'       => function( $property ) {
                $user = wp_get_current_user();
                return isset( $user->$property ) ? $user->$property : '';
            }
        ]
    );
}

function formgent_form_default_values( array $data ) {
    $values_functions = formgent_form_default_values_functions();
    $dynamic_values   = [];
    $values           = [];

    foreach ( $data as $key => $item ) {
        if ( ! empty( $item['children'] ) ) {
            $children_values = formgent_form_default_values( $item['children'] );

            if ( 'step' === $item['field_type'] ) {
                $values = array_merge( $values, $children_values );
            } elseif ( 'repeater' === $item['field_type'] ) {
                $values[$key][] = $children_values;
            } else {
                $values[$key] = $children_values;
            }
            continue;
        }

        if ( isset( $item['value'] ) && 0 == $item['value'] ) {
            $values[$key] = $item['value'];
            continue;
        }

        if ( ! isset( $item['value'] ) ) {
            $values[$key] = '';
            continue;
        }

        if ( is_array( $item['value'] ) ) {
            $values[$key] = $item['value'];
            continue;
        }

        $value = $item['value'];
        preg_match_all( '/\{(.*?)\}/', $value, $matches );

        foreach ( $matches[1] as $key => $variable ) {
            // Split the variable by dot notation
            $parts = explode( '.', $variable );
            $base  = array_shift( $parts );

            // Check if the value is already cached
            if ( ! isset( $dynamic_values[$variable] ) ) {
                $dynamic_value = '';

                if ( isset( $values_functions[$base] ) && is_callable( $values_functions[$base] ) ) {
                    // Resolve the user property if applicable
                    if ( $base === 'user' && ! empty( $parts ) ) {
                        $property      = implode( '.', $parts );
                        $dynamic_value = $values_functions[$base]( $property );
                    } else {
                        $dynamic_value = $values_functions[$base]();
                    }
                }
                $dynamic_values[$variable] = $dynamic_value;
            }

            // Replace the placeholder with the resolved value
            $value = str_replace( $matches[0][$key], $dynamic_values[$variable], $value );
        }

        $values[$key] = $value;
    }

    return $values;
}

function formgent_field_id_prefix( string $id ) {
    return "formgent-{$id}";
}

function formgent_get_form_fields( stdClass $form, string $array_key = 'name' ) {
    $fields_cache = wp_cache_get( "form_{$form->ID}_fields_{$array_key}", "formgent" );

    if ( $fields_cache ) {
        return $fields_cache;
    }

    $blocks = parse_blocks( $form->post_content );

    $form_helper = new Form;

    if ( ! formgent_is_conversational_form( $form ) ) {
        $fields = $form_helper->get_form_field_settings( $blocks, false, $array_key );
    } else {
        $fields = formgent_form_steps_to_classic_fields( $form_helper->get_conversational_form_field_settings( $blocks, false, $array_key ) );
    }

    wp_cache_add( "form_{$form->ID}_fields_{$array_key}", $fields, "formgent", 3600 );

    return $fields;
}

function formgent_form_steps_to_classic_fields( array $steps ) {
    $fields = [];

    foreach ( $steps as $value ) {
        if ( ! empty( $value['children'] ) ) {
            $fields = array_merge( $fields, $value['children'] );
        }
    }

    return $fields;
}

function formgent_is_save_incompleted_data( int $form_id ) {
    $save_incompleted_data = formgent_form_repository()->get_setting_by_key( $form_id, 'save_incompleted_data', 'no' );
    return 'yes' === $save_incompleted_data;
}

function formgent_form_settings_repository(): FormSettingsRepository {
    return formgent_singleton( FormSettingsRepository::class );
}

function formgent_form_get_setting( int $form_id, string $key, $default = null ) {
    return formgent_form_settings_repository()->get_setting_by_key( $form_id, $key, $default );
}

function formgent_form_update_setting( int $form_id, string $key, $value ) {
    return formgent_form_settings_repository()->update_setting( $form_id, $key, $value );
}

function formgent_conditional_logic() {
    return new ConditionalLogic;
}

function formgent_get_form_answers( int $form_id, int $response_id, bool $flatten_children = true ): array {
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

    return formgent_form_answer_field_dtos( $form_answers_data, $fields, $flatten_children );
}

function formgent_form_answer_field_dtos( array $answers, array $fields, bool $flatten_children = true ): array {
    $flattened_answers = [];

    foreach ( $answers as $answer ) {
        if ( ! isset( $fields[ $answer->field_name ] ) ) {
            continue;
        }

        $form_field = $fields[ $answer->field_name ];

        $field_dto = formgent_make_answer_field_dto( $answer, $form_field );

        $flattened_answers[ $field_dto->get_field_id() ] = $field_dto;

        if ( empty( $answer->children ) ) {
            continue;
        }

        $child_answers = formgent_form_answer_field_dtos( $answer->children, $form_field['children'] );

        if ( $flatten_children ) {
            $flattened_answers = array_merge( $flattened_answers, $child_answers );
            continue;
        }

        $field_dto->set_children( $child_answers );
    }

    return $flattened_answers;
}

function formgent_make_answer_field_dto( stdClass $answer, array $form_field ): AnswerFieldDTO {
    $field_dto = new AnswerFieldDTO();

    $field_dto->set_field_id( $form_field['id'] )
        ->set_field_label( isset( $form_field['label'] ) ? $form_field['label'] : $answer->field_name )
        ->set_id( $answer->id )
        ->set_form_id( $answer->form_id )
        ->set_response_id( $answer->response_id )
        ->set_field_type( $answer->field_type )
        ->set_field_name( $answer->field_name )
        ->set_value( $answer->value );

    if ( ! empty( $form_field['options'] ) ) {
        $field_dto->set_options( $form_field['options'] );
    }

    return $field_dto;
}

/**
 * Get preset values for HTML block dynamic tags.
 *
 * @param int $form_id Form post ID.
 *
 * @return array
 */
function formgent_get_preset_values( int $form_id ): array {
    $form_post    = formgent_get_form_by_id( $form_id );
    $current_user = is_user_logged_in() ? wp_get_current_user() : null;
    $embed_post   = get_post();

    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
    ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
    : '';
    $browser    = '';
    $platform   = '';

    if ( $user_agent ) {
        if ( stripos( $user_agent, 'chrome' ) !== false ) {
            $browser = 'Chrome';
        } elseif ( stripos( $user_agent, 'safari' ) !== false ) {
            $browser = 'Safari';
        } elseif ( stripos( $user_agent, 'firefox' ) !== false ) {
            $browser = 'Firefox';
        } elseif ( stripos( $user_agent, 'edge' ) !== false ) {
            $browser = 'Edge';
        } elseif ( stripos( $user_agent, 'opera' ) !== false || stripos( $user_agent, 'opr/' ) !== false ) {
            $browser = 'Opera';
        } elseif ( stripos( $user_agent, 'msie' ) !== false || stripos( $user_agent, 'trident/' ) !== false ) {
            $browser = 'Internet Explorer';
        }

        if ( stripos( $user_agent, 'windows' ) !== false ) {
            $platform = 'Windows';
        } elseif ( stripos( $user_agent, 'mac' ) !== false ) {
            $platform = 'macOS';
        } elseif ( stripos( $user_agent, 'linux' ) !== false ) {
            $platform = 'Linux';
        } elseif ( stripos( $user_agent, 'iphone' ) !== false || stripos( $user_agent, 'ipad' ) !== false ) {
            $platform = 'iOS';
        } elseif ( stripos( $user_agent, 'android' ) !== false ) {
            $platform = 'Android';
        }
    }

    $now        = current_datetime();
    $admin_user = get_user_by( 'email', get_option( 'admin_email', '' ) );

    $preset = [
        'site_title'          => get_option( 'blogname', '' ),
        'site_url'            => esc_url_raw( site_url() ),
        'form_title'          => $form_post ? $form_post->post_title : '',
        'browser_name'        => $browser,
        'browser_platform'    => $platform,
        'embedded_post_id'    => $embed_post instanceof WP_Post ? (string) $embed_post->ID : '',
        'embedded_post_title' => $embed_post instanceof WP_Post ? $embed_post->post_title : '',
        'current_date'        => $now->format( 'm/d/Y' ),
        'admin_name'          => $admin_user ? sanitize_text_field( $admin_user->display_name ) : '',
    ];

    if ( $current_user ) {
        $preset += [
            'user_id'           => (string) $current_user->ID,
            'user_display_name' => $current_user->display_name,
            'user_first_name'   => $current_user->first_name,
            'user_last_name'    => $current_user->last_name,
            'user_email'        => $current_user->user_email,
            'user_username'     => $current_user->user_login,
            'ip_address'        => formgent_get_user_ip_address(),
        ];
    }

    if ( current_user_can( 'manage_options' ) ) {
        $get_params = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ?: [];
        $cookies    = [];

        foreach ( $_COOKIE as $key => $value ) {
            $cookies[ $key ] = is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : $value;
        }

        $preset += [
            'admin_email'       => get_option( 'admin_email', '' ),
            'login_url'         => esc_url_raw( wp_login_url() ),
            'registration_url'  => esc_url_raw( wp_registration_url() ),
            'lost_password_url' => esc_url_raw( wp_lostpassword_url() ),
            'logout_url'        => esc_url_raw( wp_logout_url() ),
            'get_params'        => $get_params,
            'cookie_values'     => $cookies,
        ];
    }

    return $preset;
}

/**
 * Replace HTML block dynamic tags with actual values.
 *
 * @param string      $html_content Raw HTML that may contain {{tags}}.
 * @param int         $form_id      Current form ID.
 * @param int|null    $response_id  Response ID when processing stored data (emails/admin).
 * @param array|null  $form_data    Front-end form data (context.data) for initial render.
 *
 * @return string Sanitized HTML with placeholders replaced.
 */
function formgent_replace_html_dynamic_tags( string $html_content, int $form_id, ?int $response_id = null, ?array $form_data = null ): string {
    if ( '' === $html_content || false === strpos( $html_content, '{{' ) ) {
        return $html_content;
    }

    preg_match_all( '/{{\s*(.*?)\s*}}/', $html_content, $matches );

    if ( empty( $matches[1] ) ) {
        return $html_content;
    }

    $preset_values = array_change_key_case( formgent_get_preset_values( $form_id ), CASE_LOWER );
    $form          = formgent_get_form_by_id( $form_id );
    $form_fields   = $form ? formgent_get_form_fields( $form ) : [];
    $resolver      = formgent_singleton( FieldValueResolver::class );
    $answers       = null;
    $replacements  = [];

    foreach ( $matches[1] as $index => $raw_token ) {
        $placeholder = $matches[0][ $index ];
        $token       = trim( $raw_token );
        $token_key   = strtolower( $token );

        // 1. Preset tags (user/site/admin)
        if ( isset( $preset_values[ $token_key ] ) ) {
            $replacements[ $placeholder ] = esc_html( $preset_values[ $token_key ] );
            continue;
        }

        // 2. Field tags (supports {{field:name}} or {{name}})
        $field_reference = '';
        if ( 0 === stripos( $token, 'field:' ) ) {
            $field_reference = trim( substr( $token, 6 ) );
        } elseif ( $form_data ) {
            $field_reference = $token;
        }

        if ( $field_reference ) {
            if ( $form_data ) {
                $resolved = $resolver->resolve_from_form_data( $form_data, $form_fields, $field_reference );

                if ( null !== $resolved ) {
                    $replacements[ $placeholder ] = esc_html( $resolved );
                    continue;
                }
            }

            if ( $response_id ) {
                if ( null === $answers ) {
                    $answers = formgent_get_form_answers( $form_id, $response_id, true );
                }

                $resolved = $resolver->resolve_from_answers( $answers, $form_fields, $field_reference );

                if ( null !== $resolved ) {
                    $replacements[ $placeholder ] = esc_html( $resolved );
                    continue;
                }
            }
        }

        // 3. Response-only tokens (e.g., {{response_*}})
        if ( $response_id && 0 === stripos( $token, 'response_' ) ) {
            $response_repo = formgent_response_repository();
            $preset_repo   = formgent_form_preset_field_repository();
            $response_dto  = $response_repo->get_response_dto( $response_id );
            $answers       = $answers ?? formgent_get_form_answers( $form_id, $response_id, true );

            if ( $response_dto ) {
                $resolved                     = $preset_repo->transform_value( '{{' . $raw_token . '}}', $answers, $response_dto, '' );
                $replacements[ $placeholder ] = esc_html( (string) $resolved );
                continue;
            }
        }

        // 4. Fallback: leave untouched so frontend can handle it later
        $replacements[ $placeholder ] = $placeholder;
    }

    return wp_kses_post( strtr( $html_content, $replacements ) );
}