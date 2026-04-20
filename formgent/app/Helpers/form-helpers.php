<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Helpers\Form;
use FormGent\App\Utils\ConditionalLogic;
use FormGent\App\Repositories\FormSettingsRepository;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\Repositories\AnswerRepository;
use FormGent\App\Repositories\PdfRepository;
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
        'formgent_default_values_functions',
        [
            'ip'         => 'formgent_get_user_ip_address',
            'site_url'   => 'site_url',
            'site_title' => function () {
                return get_bloginfo( 'name' );
            },
            'user'       => function ( $property ) {
                // For non-logged-in users, keep user-based defaults blank.
                if ( ! is_user_logged_in() ) {
                    return '';
                }

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

        $value            = $item['value'];
        $raw_placeholders = [];
        $tokens           = [];

        // Default value UI inserts tokens like {{site_url}} and {{user.user_email}}.
        // Support both {{token}} (preferred) and legacy {token}.
        if ( false !== strpos( $value, '{{' ) ) {
            preg_match_all( '/{{\s*(.*?)\s*}}/', $value, $matches );
            $raw_placeholders = $matches[0] ?? [];
            $tokens           = $matches[1] ?? [];
        } else {
            preg_match_all( '/{\s*(.*?)\s*}/', $value, $matches );
            $raw_placeholders = $matches[0] ?? [];
            $tokens           = $matches[1] ?? [];
        }

        foreach ( $tokens as $match_index => $token ) {
            $token = trim( (string) $token );
            if ( '' === $token ) {
                continue;
            }

            // Split the token by dot notation (e.g., user.user_email).
            $parts = explode( '.', $token );
            $base  = array_shift( $parts );

            // Cache resolved values per token so repeated placeholders are cheap.
            if ( ! isset( $dynamic_values[$token] ) ) {
                $dynamic_value = '';

                if ( isset( $values_functions[$base] ) && is_callable( $values_functions[$base] ) ) {
                    // Resolve the user property if applicable.
                    if ( 'user' === $base && ! empty( $parts ) ) {
                        $property      = implode( '.', $parts );
                        $dynamic_value = $values_functions[$base]( $property );
                    } else {
                        $dynamic_value = $values_functions[$base]();
                    }
                }

                $dynamic_values[$token] = $dynamic_value;
            }

            // Replace the placeholder with the resolved value.
            $placeholder = $raw_placeholders[$match_index] ?? '';
            if ( '' !== $placeholder ) {
                $value = str_replace( $placeholder, $dynamic_values[$token], $value );
            }
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
        if ( ! isset( $fields[$answer->field_name] ) ) {
            continue;
        }

        $form_field = $fields[$answer->field_name];

        $field_dto = formgent_make_answer_field_dto( $answer, $form_field );

        $flattened_answers[$field_dto->get_field_id()] = $field_dto;

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
        'site_name'           => get_option( 'blogname', '' ),
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
            'user_name'         => $current_user->display_name,
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
            $cookies[$key] = is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : $value;
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
 * Get payment and subscription preset values for a response.
 *
 * Returns an associative array keyed by tag name (lowercase) containing
 * order, payment, order-item, and subscription data for tag resolution.
 *
 * @param int $response_id Response ID.
 * @param int $form_id     Form post ID (used to read subscription block attrs).
 *
 * @return array<string, string>
 */
function formgent_get_payment_preset_values( int $response_id, int $form_id = 0 ): array {
    $order = formgent_order_repository()->first_by_response_id( $response_id, true );

    if ( ! $order ) {
        return [];
    }

    $payment = isset( $order->payment ) ? $order->payment : null;
    $items   = isset( $order->items ) ? $order->items : [];

    // Resolve gateway label.
    $payment_gateways = formgent_get_payment_gateways();
    $method_label     = '';
    if ( $payment && ! empty( $payment->method ) && isset( $payment_gateways[ $payment->method ] ) ) {
        $method_label = $payment_gateways[ $payment->method ]['label'] ?? ucfirst( $payment->method );
    }

    $currency = $order->currency ?? 'USD';

    $values = [
        'payment_amount'          => $order->final_amount
            ? html_entity_decode( formgent_price( $order->final_amount, [ 'currency' => $currency ] ), ENT_QUOTES, 'UTF-8' )
            : '',
        'payment_currency'        => $currency,
        'payment_method'          => $method_label,
        'payment_transaction_id'  => $payment ? ( $payment->transaction_id ?? '' ) : '',
        'payment_status'          => $payment ? ucfirst( $payment->status ?? '' ) : '',
        'payment_billing_name'    => $payment ? ( $payment->billing_name ?? '' ) : '',
        'payment_billing_email'   => $payment ? ( $payment->billing_email ?? '' ) : '',
        'payment_billing_country' => $payment ? ( $payment->billing_country ?? '' ) : '',
        'payment_date'            => '',
    ];

    if ( $payment && ! empty( $payment->updated_at ) ) {
        $timestamp = strtotime( (string) $payment->updated_at );
        if ( false !== $timestamp ) {
            $values['payment_date'] = wp_date( get_option( 'date_format', 'Y-m-d' ), $timestamp );
        }
    }

    // Order items table.
    $values['payment_items_table'] = formgent_build_order_items_table( $items, $currency );

    // Subscription data (from the payment block attributes in the form).
    $sub_values = formgent_get_subscription_preset_values( $form_id, $payment );
    $values     = array_merge( $values, $sub_values );

    return $values;
}

/**
 * Build an HTML table for order items.
 *
 * @param array  $items    Order items (objects with title, quantity, unit_amount, total_amount).
 * @param string $currency Currency code.
 *
 * @return string HTML table or empty string.
 */
function formgent_build_order_items_table( $items, string $currency = 'USD' ): string {
    if ( empty( $items ) ) {
        return '';
    }

    $price_args = [ 'currency' => $currency ];
    $html       = '<table style="width:100%;border-collapse:collapse;">';
    $html      .= '<thead><tr style="background:#f1f5f9;">';
    $html      .= '<th style="text-align:left;padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html__( 'Item', 'formgent' ) . '</th>';
    $html      .= '<th style="text-align:right;padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html__( 'Qty', 'formgent' ) . '</th>';
    $html      .= '<th style="text-align:right;padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html__( 'Unit Price', 'formgent' ) . '</th>';
    $html      .= '<th style="text-align:right;padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html__( 'Total', 'formgent' ) . '</th>';
    $html      .= '</tr></thead><tbody>';

    foreach ( $items as $item ) {
        $html .= '<tr>';
        $html .= '<td style="padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html( $item->title ) . '</td>';
        $html .= '<td style="text-align:right;padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html( $item->quantity ) . '</td>';
        $html .= '<td style="text-align:right;padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html( html_entity_decode( formgent_price( $item->unit_amount, $price_args ), ENT_QUOTES, 'UTF-8' ) ) . '</td>';
        $html .= '<td style="text-align:right;padding:8px 12px;border:1px solid #e2e8f0;">' . esc_html( html_entity_decode( formgent_price( $item->total_amount, $price_args ), ENT_QUOTES, 'UTF-8' ) ) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    return $html;
}

/**
 * Get subscription-related preset values from the payment block attributes.
 *
 * Uses the same block-parsing approach as payment-success.php.
 *
 * @param int         $form_id Form post ID.
 * @param object|null $payment Payment record (for start date).
 *
 * @return array<string, string>
 */
function formgent_get_subscription_preset_values( int $form_id, $payment = null ): array {
    $defaults = [
        'subscription_plan_name'         => '',
        'subscription_billing_interval'  => '',
        'subscription_billing_times'     => '',
        'subscription_trial_days'        => '',
        'subscription_start_date'        => '',
        'subscription_next_billing_date' => '',
    ];

    if ( $form_id <= 0 ) {
        return $defaults;
    }

    $form_post = get_post( $form_id );
    if ( ! $form_post ) {
        return $defaults;
    }

    // Memoize parse_blocks per form_id to avoid expensive re-parsing in the same request.
    static $parsed_blocks_cache = [];
    if ( ! isset( $parsed_blocks_cache[ $form_id ] ) ) {
        $parsed_blocks_cache[ $form_id ] = parse_blocks( $form_post->post_content );
    }

    $blocks      = $parsed_blocks_cache[ $form_id ];
    $block_stack = $blocks;
    $block_attrs = null;

    while ( ! empty( $block_stack ) ) {
        $block = array_shift( $block_stack );
        if ( isset( $block['blockName'] ) && 'formgent/payment' === $block['blockName'] ) {
            $block_attrs = $block['attrs'] ?? [];
            break;
        }
        if ( ! empty( $block['innerBlocks'] ) ) {
            $block_stack = array_merge( $block_stack, $block['innerBlocks'] );
        }
    }

    if ( ! $block_attrs || ( $block_attrs['payment_type'] ?? '' ) !== 'subscription' ) {
        return $defaults;
    }

    $interval_labels = [
        'daily'   => __( 'Daily', 'formgent' ),
        'weekly'  => __( 'Weekly', 'formgent' ),
        'monthly' => __( 'Monthly', 'formgent' ),
        'yearly'  => __( 'Yearly', 'formgent' ),
    ];

    $billing_interval = $block_attrs['billing_interval'] ?? 'monthly';
    $trial_days       = ! empty( $block_attrs['free_trial_enabled'] )
        ? (int) ( $block_attrs['trial_days'] ?? 0 )
        : 0;

    $values = [
        'subscription_plan_name'         => $block_attrs['subscription_plan_name'] ?? '',
        'subscription_billing_interval'  => $interval_labels[ $billing_interval ] ?? ucfirst( $billing_interval ),
        'subscription_billing_times'     => (string) (int) ( $block_attrs['total_billing_times'] ?? 0 ),
        'subscription_trial_days'        => $trial_days > 0 ? (string) $trial_days : '',
        'subscription_start_date'        => '',
        'subscription_next_billing_date' => '',
    ];

    // Compute dates from the payment timestamp.
    if ( $payment && ! empty( $payment->updated_at ) ) {
        $start_ts = strtotime( (string) $payment->updated_at );
        if ( false !== $start_ts ) {
            $date_format                       = get_option( 'date_format', 'Y-m-d' );
            $values['subscription_start_date'] = wp_date( $date_format, $start_ts );

            $interval_map = [
                'daily'   => '+1 day',
                'weekly'  => '+1 week',
                'monthly' => '+1 month',
                'yearly'  => '+1 year',
            ];

            $next_ts = $start_ts;
            if ( $trial_days > 0 ) {
                $next_ts = strtotime( "+{$trial_days} days", $start_ts );
            } else {
                $modifier = $interval_map[ $billing_interval ] ?? '+1 month';
                $next_ts  = strtotime( $modifier, $start_ts );
            }

            if ( false !== $next_ts ) {
                $values['subscription_next_billing_date'] = wp_date( $date_format, $next_ts );
            }
        }
    }

    return $values;
}

/**
 * Replace HTML block dynamic tags with actual values.
 *
 * @param string      $html_content    Raw HTML that may contain {{tags}}.
 * @param int         $form_id         Current form ID.
 * @param int|null    $response_id     Response ID when processing stored data (emails/admin).
 * @param array|null  $form_data       Front-end form data (context.data) for initial render.
 * @param array|null  $payment_values  Payment preset values (from formgent_get_payment_preset_values).
 *
 * @return string Sanitized HTML with placeholders replaced.
 */
function formgent_replace_html_dynamic_tags( string $html_content, int $form_id, ?int $response_id = null, ?array $form_data = null, ?array $payment_values = null ): string {
    if ( '' === $html_content || false === strpos( $html_content, '{{' ) ) {
        return $html_content;
    }

    preg_match_all( '/{{\s*(.*?)\s*}}/', $html_content, $matches );

    if ( empty( $matches[1] ) ) {
        return $html_content;
    }

    $preset_values      = array_change_key_case( formgent_get_preset_values( $form_id ), CASE_LOWER );
    $form               = formgent_get_form_by_id( $form_id );
    $form_fields        = $form ? formgent_get_form_fields( $form ) : [];
    $resolver           = formgent_singleton( FieldValueResolver::class );
    $answers            = null;
    $response_dto       = null;
    $replacements       = [];
    $rendered_repeaters = [];

    foreach ( $matches[1] as $index => $raw_token ) {
        $placeholder = $matches[0][$index];
        $token       = trim( $raw_token );
        $token_key   = strtolower( $token );

        // 1. Preset tags (user/site/admin)
        if ( isset( $preset_values[$token_key] ) ) {
            $replacements[$placeholder] = esc_html( $preset_values[$token_key] );
            continue;
        }

        // 1.1 Payment / subscription tags.
        if ( null !== $payment_values && isset( $payment_values[ $token_key ] ) ) {
            $value = $payment_values[ $token_key ];
            // payment_items_table contains pre-escaped HTML; other values are plain text.
            $replacements[ $placeholder ] = 'payment_items_table' === $token_key
                ? wp_kses_post( $value )
                : esc_html( $value );
            continue;
        }

        // 1.2 Submission date alias commonly used in templates.
        if ( 'submission_date' === $token_key ) {
            if ( $response_id ) {
                if ( null === $response_dto ) {
                    $response_dto = formgent_response_repository()->get_response_dto( $response_id );
                }

                if ( $response_dto && $response_dto->get_created_at() ) {
                    $timestamp = strtotime( (string) $response_dto->get_created_at() );
                    if ( false !== $timestamp ) {
                        $replacements[ $placeholder ] = esc_html( wp_date( get_option( 'date_format', 'Y-m-d' ), $timestamp ) );
                        continue;
                    }
                }
            }

            $replacements[ $placeholder ] = esc_html( wp_date( get_option( 'date_format', 'Y-m-d' ) ) );
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
            // Repeater deduplication: if a repeater child tag (e.g., "repeater.child")
            // was already rendered as a full table, replace subsequent child tags with ''.
            $ref_segments    = explode( '.', $field_reference );
            $repeater_parent = count( $ref_segments ) > 1 ? $ref_segments[0] : null;

            if ( $repeater_parent && isset( $rendered_repeaters[ $repeater_parent ] ) ) {
                $replacements[ $placeholder ] = '';
                continue;
            }

            if ( $form_data ) {
                $resolved = $resolver->resolve_from_form_data( $form_data, $form_fields, $field_reference );

                if ( null !== $resolved ) {
                    // format_value() already returns HTML-safe output.
                    $replacements[$placeholder] = $resolved;
                    continue;
                }
            }

            if ( $response_id ) {
                if ( null === $answers ) {
                    $answers = formgent_get_form_answers( $form_id, $response_id, true );
                }

                $resolved = $resolver->resolve_from_answers( $answers, $form_fields, $field_reference );

                if ( null !== $resolved ) {
                    // format_value() already returns HTML-safe output.
                    $replacements[$placeholder] = $resolved;

                    // Track rendered repeater parents to avoid duplicate tables.
                    if ( $repeater_parent
                        && isset( $form_fields[ $repeater_parent ]['field_type'] )
                        && 'repeater' === $form_fields[ $repeater_parent ]['field_type']
                    ) {
                        $rendered_repeaters[ $repeater_parent ] = true;
                    }

                    continue;
                }
            }

            // Field tag recognized but no value found — remove the placeholder
            // instead of showing raw tags like {{field:text}} for empty fields.
            $replacements[$placeholder] = '';
            continue;
        }

        // 3. Response-only tokens (e.g., {{response_*}})
        if ( $response_id && 0 === stripos( $token, 'response_' ) ) {
            $response_repo = formgent_response_repository();
            $preset_repo   = formgent_form_preset_field_repository();
            $response_dto  = $response_dto ?? $response_repo->get_response_dto( $response_id );
            $answers       = $answers ?? formgent_get_form_answers( $form_id, $response_id, true );

            if ( $response_dto ) {
                $resolved                   = $preset_repo->transform_value( '{{' . $raw_token . '}}', $answers, $response_dto, '' );
                $replacements[$placeholder] = esc_html( (string) $resolved );
                continue;
            }
        }

        // 4. Fallback: leave untouched so frontend can handle it later
        $replacements[$placeholder] = $placeholder;
    }

    return wp_kses_post( strtr( $html_content, $replacements ) );
}

/**
 * Get content from the formgent/end block (paragraph/heading text) for PDF placeholder scanning.
 *
 * @param string $post_content Form post_content (block markup).
 * @return string Concatenated inner content of the End block.
 */
function formgent_get_end_block_content( string $post_content ): string {
    if ( '' === $post_content ) {
        return '';
    }

    $blocks = parse_blocks( $post_content );
    foreach ( $blocks as $block ) {
        if ( isset( $block['blockName'] ) && $block['blockName'] === 'formgent/end' ) {
            $parts = [];
            if ( ! empty( $block['innerBlocks'] ) ) {
                foreach ( $block['innerBlocks'] as $inner ) {
                    // Prefer rendered innerHTML; fall back to the block attribute only when empty.
                    if ( ! empty( $inner['innerHTML'] ) ) {
                        $parts[] = $inner['innerHTML'];
                    } elseif ( isset( $inner['attrs']['content'] ) && '' !== (string) $inner['attrs']['content'] ) {
                        $parts[] = (string) $inner['attrs']['content'];
                    }
                }
            }
            return implode( "\n", $parts );
        }
    }
    return '';
}

/**
 * Render HTML to PDF binary using Dompdf.
 * Shared by formgent_generate_pdf_links_from_content() and ResponseController::download_pdf().
 * Caller must ensure Dompdf is loaded (require autoload) before calling.
 *
 * @param string $html        Full HTML to render (after tag replacement and direction applied).
 * @param string $paper_size  e.g. 'A4'.
 * @param string $orientation 'P' for portrait, 'L' for landscape.
 * @param string $password    Optional password for encryption (empty string = no encryption).
 * @param int    $form_id     Form ID (for filters).
 * @param int    $pdf_id      PDF template ID (for filters).
 * @return string PDF binary content.
 * @throws Throwable On Dompdf or output failure.
 */
function formgent_render_pdf_with_dompdf(
    string $html,
    string $paper_size,
    string $orientation,
    string $password,
    int $form_id,
    int $pdf_id
): string {
    $options_class = '\Dompdf\Options';
    $dompdf_class  = '\Dompdf\Dompdf';

    $options = new $options_class();
    if ( method_exists( $options, 'set' ) ) {
        $allowed_remote_hosts = apply_filters( 'formgent_pdf_dompdf_allowed_remote_hosts', [], $form_id, $pdf_id );
        $allowed_remote_hosts = is_array( $allowed_remote_hosts ) ? $allowed_remote_hosts : [];
        $allowed_remote_hosts = array_values(
            array_filter(
                array_map(
                    static function ( $host ) {
                        $host = strtolower( trim( sanitize_text_field( (string) $host ) ) );
                        $host = preg_replace( '#^https?://#', '', $host );
                        return trim( (string) $host, "/ \t\n\r\0\x0B" );
                    },
                    $allowed_remote_hosts
                )
            )
        );

        $remote_enabled = (bool) apply_filters( 'formgent_pdf_dompdf_remote_enabled', false, $form_id, $pdf_id );
        $remote_enabled = $remote_enabled && ! empty( $allowed_remote_hosts );

        $options->set( 'isRemoteEnabled', $remote_enabled );
        $options->set( 'isHtml5ParserEnabled', true );

        if ( method_exists( $options, 'setProtocolAllowedPaths' ) ) {
            $options->setProtocolAllowedPaths(
                'file://',
                [
                    wp_normalize_path( WP_CONTENT_DIR ),
                    wp_normalize_path( WP_PLUGIN_DIR ),
                ]
            );
        } elseif ( method_exists( $options, 'setChroot' ) ) {
            $options->setChroot( [ WP_CONTENT_DIR, WP_PLUGIN_DIR ] );
        }

        if ( $remote_enabled ) {
            if ( method_exists( $options, 'setAllowedRemoteHosts' ) ) {
                $options->setAllowedRemoteHosts( $allowed_remote_hosts );
            } elseif ( method_exists( $options, 'set' ) ) {
                $options->set( 'allowedRemoteHosts', $allowed_remote_hosts );
            }
        }
    }

    $dompdf = new $dompdf_class( $options );
    $dompdf->loadHtml( $html );
    $dompdf->setPaper( $paper_size, 'L' === $orientation ? 'landscape' : 'portrait' );
    $dompdf->render();

    if ( '' !== $password ) {
        $canvas = $dompdf->getCanvas();
        if ( method_exists( $canvas, 'get_cpdf' ) ) {
            $cpdf = $canvas->get_cpdf();
            if ( $cpdf && method_exists( $cpdf, 'setEncryption' ) ) {
                // Use a random owner password so the user password gates opening,
                // while the owner password (never shared) gates permission flags.
                $owner_password = wp_generate_password( 32, true, true );
                $cpdf->setEncryption( $password, $owner_password );
            }
        }
    }

    return $dompdf->output();
}

/**
 * Get the global PDF library path. The library is installed once site-wide, not per form.
 * Stored in option 'formgent_pdf_library_path'.
 *
 * @return string Absolute path to library root (contains vendor/autoload.php), or empty.
 */
function formgent_get_pdf_library_path(): string {
    $path = get_option( 'formgent_pdf_library_path', '' );
    if ( ! is_string( $path ) || '' === trim( $path ) ) {
        return '';
    }

    $path = trim( $path );

    // Validate the stored path is still under WP_PLUGIN_DIR to prevent path traversal
    // if the option value has been tampered with.
    $real = realpath( $path );
    if ( false === $real ) {
        return '';
    }

    $plugins_dir = wp_normalize_path( WP_PLUGIN_DIR );
    if ( 0 !== strpos( wp_normalize_path( $real ), $plugins_dir ) ) {
        return '';
    }

    return $real;
}

/**
 * Set the global PDF library path (e.g. after installing PDF resources).
 *
 * @param string $path Absolute path to library root.
 * @return bool True if option was updated.
 */
function formgent_set_pdf_library_path( string $path ): bool {
    return update_option( 'formgent_pdf_library_path', trim( $path ) );
}

/**
 * Add index.php and .htaccess to a directory to block direct access.
 *
 * @param string $dir Absolute path to directory.
 */
function formgent_protect_pdf_directory( string $dir ): void {
    global $wp_filesystem;
    if ( empty( $wp_filesystem ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    $index = trailingslashit( $dir ) . 'index.php';
    if ( ! file_exists( $index ) ) {
        $wp_filesystem->put_contents( $index, '<?php // Silence is golden.', FS_CHMOD_FILE );
    }

    $htaccess = trailingslashit( $dir ) . '.htaccess';
    if ( ! file_exists( $htaccess ) ) {
        $wp_filesystem->put_contents( $htaccess, "Options -Indexes\ndeny from all\n", FS_CHMOD_FILE );
    }
}

/**
 * Build the public REST API URL for serving a generated PDF file.
 *
 * PDFs are never served directly by the web server. Instead they go through
 * the serve-pdf REST endpoint which validates the filename and streams the file.
 *
 * @param string $filename The generated PDF filename (includes random hash).
 * @return string Full REST API URL.
 */
function formgent_get_pdf_serve_url( string $filename ): string {
    return add_query_arg( 'file', rawurlencode( $filename ), rest_url( 'formgent/responses/serve-pdf' ) );
}

/**
 * Generate PDF links for all {{pdf:id}} (or {pdf:id}) tokens found in content.
 *
 * @param string $content
 * @param int    $form_id
 * @param int    $response_id
 * @return array<string, array{url:string,name:string}>
 */
function formgent_generate_pdf_links_from_content( string $content, int $form_id, int $response_id ): array {
    if ( '' === $content ) {
        return [];
    }

    preg_match_all( '/{{\s*pdf:(\d+)\s*}}|{\s*pdf:(\d+)\s*}/i', $content, $matches );
    $pdf_ids = [];

    if ( ! empty( $matches[1] ) ) {
        $pdf_ids = array_merge( $pdf_ids, array_filter( $matches[1] ) );
    }

    if ( ! empty( $matches[2] ) ) {
        $pdf_ids = array_merge( $pdf_ids, array_filter( $matches[2] ) );
    }

    $pdf_ids = array_values( array_unique( array_map( 'absint', $pdf_ids ) ) );
    if ( empty( $pdf_ids ) ) {
        return [];
    }

    $pdf_path = formgent_get_pdf_library_path();
    $autoload = $pdf_path ? trailingslashit( $pdf_path ) . 'vendor/autoload.php' : '';

    if ( empty( $autoload ) || ! is_readable( $autoload ) ) {
        return [];
    }

    if ( ! class_exists( '\Dompdf\Dompdf', false ) ) {
        require_once $autoload;
    }

    if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
        return [];
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) ) {
        return [];
    }

    $pdf_dir = trailingslashit( $uploads['basedir'] ) . 'formgent/pdfs';
    if ( ! wp_mkdir_p( $pdf_dir ) ) {
        return [];
    }

    formgent_protect_pdf_directory( $pdf_dir );

    /** @var PdfRepository $pdf_repository */
    $pdf_repository = formgent_singleton( PdfRepository::class );
    $pdf_links      = [];

    foreach ( $pdf_ids as $pdf_id ) {
        if ( $pdf_id <= 0 ) {
            continue;
        }

        [ $pdf, $decrypted_password ] = $pdf_repository->get_by_id_and_form_with_decrypted_password( $pdf_id, $form_id );
        if ( ! $pdf ) {
            continue;
        }

        $template_content = isset( $pdf->content ) ? (string) $pdf->content : '';
        $paper_size       = ! empty( $pdf->paper_size ) ? sanitize_text_field( (string) $pdf->paper_size ) : 'A4';
        $orientation      = strtolower( (string) ( $pdf->orientation ?? '' ) );
        $orientation      = 'landscape' === $orientation || 'l' === $orientation ? 'L' : 'P';
        $direction        = strtolower( (string) ( $pdf->direction ?? '' ) );
        $password         = sanitize_text_field( $decrypted_password );
        $html             = formgent_replace_html_dynamic_tags( $template_content, $form_id, $response_id );
        $html             = formgent_apply_pdf_direction( $html, $direction );

        try {
            $pdf_binary = formgent_render_pdf_with_dompdf( $html, $paper_size, $orientation, $password, $form_id, $pdf_id );
        } catch ( Throwable $exception ) {
            continue;
        }

        $template_name = isset( $pdf->template_name ) ? (string) $pdf->template_name : '';
        $base_name     = sanitize_file_name( $template_name ?: 'formgent-generated-pdf' );
        $hash          = bin2hex( random_bytes( 16 ) );
        $filename      = $base_name . '-' . $hash . '.pdf';
        $file_path     = trailingslashit( $pdf_dir ) . $filename;

        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if ( false === $wp_filesystem->put_contents( $file_path, $pdf_binary, FS_CHMOD_FILE ) ) {
            continue;
        }

        $pdf_links[ (string) $pdf_id ] = [
            'url'  => formgent_get_pdf_serve_url( $filename ),
            'name' => $template_name ?: __( 'Generated PDF', 'formgent' ),
        ];
    }

    return $pdf_links;
}

/**
 * Generate PDF links for all templates of a payment form, resolving payment tags.
 *
 * Called after payment success, when order/payment data exists.
 * Unlike formgent_generate_pdf_links_from_content() which generates only templates
 * referenced via {{pdf:id}}, this generates ALL templates for the form.
 *
 * @param int $form_id     Form post ID.
 * @param int $response_id Response ID.
 *
 * @return array<string, array{url:string,name:string}>
 */
function formgent_generate_payment_pdf_links( int $form_id, int $response_id ): array {
    /** @var PdfRepository $pdf_repository */
    $pdf_repository = formgent_singleton( PdfRepository::class );

    // Single query: get all PDFs with decrypted passwords (avoids N+1).
    $pdf_items = $pdf_repository->get_all_with_decrypted_passwords( $form_id );
    if ( empty( $pdf_items ) ) {
        return [];
    }

    $pdf_path = formgent_get_pdf_library_path();
    $autoload = $pdf_path ? trailingslashit( $pdf_path ) . 'vendor/autoload.php' : '';

    if ( empty( $autoload ) || ! is_readable( $autoload ) ) {
        return [];
    }

    if ( ! class_exists( '\Dompdf\Dompdf', false ) ) {
        require_once $autoload;
    }

    if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
        return [];
    }

    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) ) {
        return [];
    }

    $pdf_dir = trailingslashit( $uploads['basedir'] ) . 'formgent/pdfs';
    if ( ! wp_mkdir_p( $pdf_dir ) ) {
        return [];
    }

    formgent_protect_pdf_directory( $pdf_dir );

    $payment_values = formgent_get_payment_preset_values( $response_id, $form_id );
    $pdf_links      = [];

    global $wp_filesystem;
    if ( empty( $wp_filesystem ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    foreach ( $pdf_items as $item ) {
        $pdf                = $item['pdf'];
        $decrypted_password = $item['password'];
        $pdf_id             = (int) $pdf->id;

        if ( $pdf_id <= 0 ) {
            continue;
        }

        $template_content = isset( $pdf->content ) ? (string) $pdf->content : '';
        $paper_size       = ! empty( $pdf->paper_size ) ? sanitize_text_field( (string) $pdf->paper_size ) : 'A4';
        $orientation      = strtolower( (string) ( $pdf->orientation ?? '' ) );
        $orientation      = 'landscape' === $orientation || 'l' === $orientation ? 'L' : 'P';
        $direction        = strtolower( (string) ( $pdf->direction ?? '' ) );
        $password         = sanitize_text_field( $decrypted_password );
        $html             = formgent_replace_html_dynamic_tags( $template_content, $form_id, $response_id, null, $payment_values );
        $html             = formgent_apply_pdf_direction( $html, $direction );

        try {
            $pdf_binary = formgent_render_pdf_with_dompdf( $html, $paper_size, $orientation, $password, $form_id, $pdf_id );
        } catch ( Throwable $exception ) {
            continue;
        }

        $template_name = isset( $pdf->template_name ) ? (string) $pdf->template_name : '';
        $base_name     = sanitize_file_name( $template_name ?: 'formgent-generated-pdf' );
        $hash          = bin2hex( random_bytes( 16 ) );
        $filename      = $base_name . '-' . $hash . '.pdf';
        $file_path     = trailingslashit( $pdf_dir ) . $filename;

        if ( false === $wp_filesystem->put_contents( $file_path, $pdf_binary, FS_CHMOD_FILE ) ) {
            continue;
        }

        $pdf_links[ (string) $pdf_id ] = [
            'url'  => formgent_get_pdf_serve_url( $filename ),
            'name' => $template_name ?: __( 'Generated PDF', 'formgent' ),
        ];
    }

    return $pdf_links;
}

/**
 * Replace {{pdf:id}} placeholders in content with anchor links.
 *
 * @param string $content
 * @param array  $pdf_links
 * @return string
 */
function formgent_replace_pdf_placeholders_with_links( string $content, array $pdf_links ): string {
    if ( '' === $content || empty( $pdf_links ) ) {
        return $content;
    }

    return preg_replace_callback(
        '/{{\s*pdf:(\d+)\s*}}|{\s*pdf:(\d+)\s*}/i',
        function( $matches ) use ( $pdf_links ) {
            $pdf_id = isset( $matches[1] ) && '' !== $matches[1] ? $matches[1] : ( $matches[2] ?? '' );
            if ( '' === $pdf_id || empty( $pdf_links[ (string) $pdf_id ] ) ) {
                return '';
            }

            $link_item = $pdf_links[ (string) $pdf_id ];
            $url       = is_array( $link_item ) ? ( $link_item['url'] ?? '' ) : (string) $link_item;
            $name      = is_array( $link_item ) ? ( $link_item['name'] ?? __( 'Generated PDF', 'formgent' ) ) : __( 'Generated PDF', 'formgent' );

            if ( '' === $url ) {
                return '';
            }

            return sprintf(
                '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
                esc_url( $url ),
                esc_html( $name )
            );
        },
        $content
    );
}

/**
 * Apply direction-aware HTML/CSS for PDF rendering.
 *
 * @param string $html
 * @param string $direction
 * @return string
 */
function formgent_apply_pdf_direction( string $html, string $direction ): string {
    if ( 'rtl' !== strtolower( $direction ) ) {
        return $html;
    }

    $rtl_style = '<style>
        body, .formgent-pdf-root {
            direction: rtl;
            unicode-bidi: bidi-override;
            text-align: right;
            font-family: DejaVu Sans, sans-serif;
        }
    </style>';

    // If the HTML already has structure tags, inject RTL attributes and style inside <head>.
    if ( false !== stripos( $html, '<html' ) || false !== stripos( $html, '<body' ) ) {
        $html = preg_replace( '/<html(?![^>]*\bdir=)([^>]*)>/i', '<html$1 dir="rtl">', $html );
        $html = preg_replace( '/<body(?![^>]*\bdir=)([^>]*)>/i', '<body$1 dir="rtl">', $html );

        // Place style inside <head> if it exists, otherwise before <body>.
        if ( false !== stripos( $html, '</head>' ) ) {
            $html = str_ireplace( '</head>', $rtl_style . '</head>', $html );
        } elseif ( false !== stripos( $html, '<body' ) ) {
            $html = preg_replace( '/(<body)/i', $rtl_style . '$1', $html );
        } else {
            $html = $rtl_style . $html;
        }

        return $html;
    }

    // Wrap partial HTML in a proper structure for Dompdf.
    return '<html dir="rtl"><head>' . $rtl_style . '</head><body><div class="formgent-pdf-root" dir="rtl">' . $html . '</div></body></html>';
}

/**
 * Replace save-resume-specific placeholders in a string.
 *
 * Supported tokens (double-curly-brace style):
 *   {{resume_url}}  – the unique resume URL with the token.
 *   {{form_title}}  – the form's post_title.
 *   {{site_name}}   – get_bloginfo('name').
 *   {{save_email}}  – the email address the user entered.
 *
 * This helper is intentionally standalone: it does NOT depend on ResponseDTO
 * or any response data, making it safe to call for transactional save-resume
 * emails where no submission has been created yet.
 *
 * @param string $content      Raw content (HTML or plain text) with {{token}} placeholders.
 * @param array  $replacements Associative array of token_key => replacement_value.
 *
 * @return string
 */
function formgent_replace_save_resume_placeholders( string $content, array $replacements ): string {
    if ( '' === $content || false === strpos( $content, '{{' ) ) {
        return $content;
    }

    $search  = [];
    $replace = [];

    foreach ( $replacements as $key => $value ) {
        $search[]  = '{{' . $key . '}}';
        $replace[] = (string) $value;
    }

    return str_replace( $search, $replace, $content );
}