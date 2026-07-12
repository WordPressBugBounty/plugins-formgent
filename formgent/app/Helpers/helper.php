<?php

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\App;
use FormGent\DI\Container;
use FormGent\App\Fields\Field;
use FormGent\App\Utils\DateTime;
use FormGent\App\DTO\ResponseDTO;
use FormGent\App\Repositories\ResponseRepository;
use FormGent\App\Repositories\ResponseTokenRepository;
use FormGent\App\Repositories\MailchimpRepository;
use FormGent\App\Repositories\SettingsRepository;
use FormGent\App\Repositories\FormPresetFieldRepository;

function formgent():App {
    return App::$instance;
}

function formgent_config( string $config_key ) {
    return formgent()::$config->get( $config_key );
}

function formgent_app_config( string $config_key ) {
    return formgent_config( "app.{$config_key}" );
}

function formgent_version() {
    return formgent_app_config( 'version' );
}

function formgent_container():Container {
    return formgent()::$container;
}

function formgent_make( string $class ) {
    return formgent_container()->make( $class );
}

/**
 * Get a singleton instance from the container.
 *
 * @template T
 * @param class-string<T> $class Class name to resolve.
 * @return T Instance of the requested class.
 */
function formgent_singleton( string $class ) {
    return formgent_container()->get( $class );
}

function formgent_url( string $url = '' ) {
    return formgent()->get_url( $url );
}

function formgent_dir( string $dir = '' ) {
    return formgent()->get_dir( $dir );
}

function formgent_render_icon( string $icon, string $path_type = 'blocks-icon' ) {
    $dir = $path_type === 'blocks-icon'
        ? 'blocks-icon'
        : 'svg/icons';

    $svg = formgent_dir( "resources/{$dir}/{$icon}.svg" );

    if ( ! is_file( $svg ) ) {
        return;
    }

    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    echo file_get_contents( $svg );
}

/**
 * Allowed SVG tags and attributes for sanitizing inline icons.
 *
 * @return array<string, array<string, bool>>
 */
function formgent_allowed_svg_tags(): array {
    return [
        'svg'      => [
            'xmlns'       => true,
            'viewbox'     => true,
            'width'       => true,
            'height'      => true,
            'fill'        => true,
            'class'       => true,
            'aria-hidden' => true,
            'focusable'   => true,
            'role'        => true,
        ],
        'path'     => [
            'd'            => true,
            'fill'         => true,
            'fill-rule'    => true,
            'clip-rule'    => true,
            'stroke'       => true,
            'stroke-width' => true,
        ],
        'circle'   => [
            'cx'     => true,
            'cy'     => true,
            'r'      => true,
            'fill'   => true,
            'stroke' => true,
        ],
        'rect'     => [
            'x'      => true,
            'y'      => true,
            'width'  => true,
            'height' => true,
            'rx'     => true,
            'ry'     => true,
            'fill'   => true,
        ],
        'g'        => [
            'fill'      => true,
            'transform' => true,
        ],
        'polyline' => [
            'points' => true,
            'fill'   => true,
            'stroke' => true,
        ],
        'polygon'  => [
            'points' => true,
            'fill'   => true,
        ],
        'line'     => [
            'x1'     => true,
            'y1'     => true,
            'x2'     => true,
            'y2'     => true,
            'stroke' => true,
        ],
        'defs'     => [],
        'clippath' => [
            'id' => true,
        ],
        'span'     => [
            'class' => true,
        ],
    ];
}

function formgent_choice_option_image_size(): string {
    return (string) apply_filters( 'formgent_choice_option_image_size', 'formgent-option-thumb' );
}

/**
 * Image size used when rendering tiny choice/dropdown option thumbnails.
 *
 * @return array{width:int,height:int,crop:bool}
 */
function formgent_choice_option_image_size_args(): array {
    $args = apply_filters(
        'formgent_choice_option_image_size_args',
        [
            'width'  => 64,
            'height' => 64,
            'crop'   => true,
        ]
    );

    return [
        'width'  => max( 1, absint( $args['width'] ?? 64 ) ),
        'height' => max( 1, absint( $args['height'] ?? 64 ) ),
        'crop'   => (bool) ( $args['crop'] ?? true ),
    ];
}

function formgent_get_choice_option_image_id( array $image ): int {
    $image_id = absint( $image['id'] ?? 0 );

    if ( $image_id ) {
        return $image_id;
    }

    foreach ( [ $image['thumbnail'] ?? '', $image['url'] ?? '' ] as $image_url ) {
        if ( empty( $image_url ) ) {
            continue;
        }

        $image_id = attachment_url_to_postid( $image_url );

        if ( $image_id ) {
            return absint( $image_id );
        }
    }

    return 0;
}

function formgent_get_attachment_intermediate_image_url( int $image_id, string $size ): string {
    if ( ! $image_id || 'full' === $size || ! image_get_intermediate_size( $image_id, $size ) ) {
        return '';
    }

    $image_url = wp_get_attachment_image_url( $image_id, $size );
    return $image_url ? esc_url( $image_url ) : '';
}

function formgent_generate_choice_option_image_size( int $image_id, string $size ): string {
    if ( ! $image_id || formgent_choice_option_image_size() !== $size ) {
        return '';
    }

    $metadata = wp_get_attachment_metadata( $image_id );

    if ( ! is_array( $metadata ) ) {
        return '';
    }

    if ( ! empty( $metadata['sizes'][ $size ]['file'] ) ) {
        return formgent_get_attachment_intermediate_image_url( $image_id, $size );
    }

    $file = get_attached_file( $image_id );

    if ( ! $file || ! is_file( $file ) ) {
        return '';
    }

    if ( ! function_exists( 'image_make_intermediate_size' ) ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $size_args = formgent_choice_option_image_size_args();
    $resized   = image_make_intermediate_size( $file, $size_args['width'], $size_args['height'], $size_args['crop'] );

    if ( ! is_array( $resized ) || empty( $resized['file'] ) ) {
        return '';
    }

    $metadata['sizes'][ $size ] = $resized;
    wp_update_attachment_metadata( $image_id, $metadata );

    return formgent_get_attachment_intermediate_image_url( $image_id, $size );
}

function formgent_get_choice_option_image_url( array $image, string $size = '' ): string {
    $image_size = $size ?: formgent_choice_option_image_size();
    $image_id   = formgent_get_choice_option_image_id( $image );

    if ( $image_id ) {
        $image_url = formgent_get_attachment_intermediate_image_url( $image_id, $image_size );

        if ( ! $image_url ) {
            $image_url = formgent_generate_choice_option_image_size( $image_id, $image_size );
        }

        if ( ! $image_url && 'thumbnail' !== $image_size ) {
            $image_url = formgent_get_attachment_intermediate_image_url( $image_id, 'thumbnail' );
        }

        if ( $image_url ) {
            return esc_url( $image_url );
        }
    }

    return esc_url( $image['thumbnail'] ?? $image['url'] ?? '' );
}

function formgent_get_choice_option_media( array $option ): array {
    $allowed_svg_tags = formgent_allowed_svg_tags();
    $media            = [];

    if ( ! empty( $option['icon'] ) && is_array( $option['icon'] ) && ! empty( $option['icon']['svg'] ) ) {
        $media['icon'] = [
            'name' => esc_attr( $option['icon']['name'] ?? '' ),
            'svg'  => wp_kses( $option['icon']['svg'], $allowed_svg_tags ),
        ];
    }

    if ( ! empty( $option['image'] ) && is_array( $option['image'] ) ) {
        $image_url = formgent_get_choice_option_image_url( $option['image'] );

        if ( $image_url ) {
            $media['image'] = [
                'id'        => absint( $option['image']['id'] ?? 0 ),
                'url'       => esc_url( $option['image']['url'] ?? '' ),
                'alt'       => esc_attr( $option['image']['alt'] ?? '' ),
                'thumbnail' => $image_url,
            ];
        }
    }

    return $media;
}

function formgent_render_choice_option_media( array $option ): void {
    $media = formgent_get_choice_option_media( $option );

    if ( ! empty( $media['icon']['svg'] ) ) {
        echo '<span class="formgent-option-icon">' . wp_kses( $media['icon']['svg'], formgent_allowed_svg_tags() ) . '</span>';
        return;
    }

    if ( ! empty( $media['image']['thumbnail'] ) ) {
        printf(
            '<img class="formgent-option-image" src="%s" alt="%s" width="24" height="24" loading="lazy" decoding="async" />',
            esc_url( $media['image']['thumbnail'] ),
            esc_attr( $media['image']['alt'] ?? '' )
        );
    }
}

/**
 * Sanitize choice options including icon SVG and image data.
 *
 * @param mixed $options
 * @return array<int, array<string, mixed>>
 */
function formgent_sanitize_choice_options( $options ): array {
    if ( empty( $options ) || ! is_array( $options ) ) {
        return [];
    }

    return array_map(
        static function( $option ) {
            if ( ! is_array( $option ) ) {
                return [];
            }

            $sanitized = map_deep(
                array_diff_key( $option, array_flip( [ 'icon', 'image' ] ) ),
                'esc_attr'
            );

            if ( ! empty( $option['icon'] ) && is_array( $option['icon'] ) ) {
                $media = formgent_get_choice_option_media( $option );

                if ( ! empty( $media['icon'] ) ) {
                    $sanitized['icon'] = $media['icon'];
                }
            }

            if ( ! empty( $option['image'] ) && is_array( $option['image'] ) ) {
                $media = $media ?? formgent_get_choice_option_media( $option );

                if ( ! empty( $media['image'] ) ) {
                    $sanitized['image'] = $media['image'];
                }
            }

            return $sanitized;
        },
        $options
    );
}

function formgent_date_time_format() {
    return apply_filters( 'formgent_date_time_format', 'Y-m-d H:i:s' );
}

function formgent_now() {
    return DateTime::now();
}

function formgent_is_valid_date( string $date, string $format ) {
    $date_time = \DateTime::createFromFormat( $format, $date );
    return $date_time && $date_time->format( $format ) === $date;
}

/**
 * Get current user ip address
 *
 * @return string|null
 */
function formgent_get_user_ip_address() {
    // Check for shared Internet/ISP IP
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) && filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
    }

    // Check for IP addresses passed by proxies
    if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        // Extract IP addresses
        $ip_addresses = explode(
            ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
        );

        // Check each IP address
        foreach ( $ip_addresses as $ip ) {
            $ip = trim( $ip );
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                return $ip;
            }
        }
    }

    // Check for the remote IP address
    if ( ! empty( $_SERVER['REMOTE_ADDR'] ) && filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ) ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    }

    return null;
}

/**
 * Get value form nested array
 *
 * @param string $keys Comma separated array keys
 * @param array $values
 * @param mixed $default
 * @return mixed
 */
function formgent_get_nested_value( string $keys, array $values, $default = null ) {
    $keys = explode( '.', $keys );

    $item = $values;

    foreach ( $keys as $key ) {
        if ( ! isset( $item[ $key ] ) ) {
            return $default;
        }
        $item = $item[$key];
    }
    return $item;
}

function formgent_field_handler( string $field_type ):Field {
    $field_handler_class = formgent_config( "fields.{$field_type}.class" );

    if ( ! class_exists( $field_handler_class ) ) {
        throw new Exception( esc_html__( 'Field handler not found.', 'formgent' ), 500 );
    }

    $field_handler = formgent_make( $field_handler_class );

    if ( ! $field_handler instanceof Field ) {
        throw new Exception( esc_html__( 'Please use a valid field handler.', 'formgent' ), 500 );
    }

    return $field_handler;
}

function formgent_is_one_level_array( array $array ) {
    foreach ( $array as $value ) {
        if ( is_array( $value ) ) {
            return false; // Contains nested array
        }
    }
    return true; // Valid one-level array
}

function formgent_generate_token() {
    $time   = microtime( true );
    $random = bin2hex( random_bytes( 5 ) );
    $token  = $random . $time;
    return $token;
}

function formgent_post_type() {
    return formgent_app_config( 'post_type' );
}

function formgent_get_response_by_token( string $token, int $form_id ) {
    /**
     * @var ResponseTokenRepository $response_token_repository
     */
    $response_token_repository = formgent_singleton( ResponseTokenRepository::class );
    $token_data                = $response_token_repository->get_by_token( $form_id, $token );

    if ( ! $token_data ) {
        return false;
    }

    /**
     * @var ResponseRepository $response_repository
     */
    $response_repository = formgent_singleton( ResponseRepository::class );
    return $response_repository->get_by_id( $token_data->response_id );
}

function formgent_settings_repository(): SettingsRepository {
    return formgent_singleton( SettingsRepository::class );
}

function formgent_response_repository(): ResponseRepository {
    return formgent_singleton( ResponseRepository::class );
}

function formgent_mailchimp_repository(): MailchimpRepository {
    return formgent_singleton( MailchimpRepository::class );
}

function formgent_form_preset_field_repository(): FormPresetFieldRepository {
    return formgent_singleton( FormPresetFieldRepository::class );
}

function formgent_get_setting( string $key, $default = null ) {
    return formgent_settings_repository()->get_by_key( $key, $default );
}

function formgent_update_setting( string $key, $value ) {
    return formgent_settings_repository()->update_setting( $key, $value );
}

function formgent_replace_placeholders( string $content, FormPresetFieldRepository $preset_field_repository, array $form_answers_data, ResponseDTO $response ) {
    return preg_replace_callback(
        '/{{(.*?)}}/', function ( $matches ) use ( $preset_field_repository, $form_answers_data, $response ) {
            return $preset_field_repository->transform_value( trim( $matches[0] ), $form_answers_data, $response );
        }, $content
    );
}

function formgent_is_form_visible( Wp_Post $form ) {
    if ( 'publish' === $form->post_status ) {
        return true;
    }

    if ( 'future' === $form->post_status ) {
        $current_time = current_time( 'mysql', 1 ); // Get the current time in GMT
        return strtotime( $current_time ) >= strtotime( $form->post_date_gmt );
    }

    if ( 'private' === $form->post_status && current_user_can( 'read_private_posts' ) ) {
        return true;
    }

    // Get the current user object
    $current_user = wp_get_current_user();

    // Check if the user has either 'administrator' or 'editor' role
    if ( in_array( 'administrator', $current_user->roles ) || in_array( 'editor', $current_user->roles ) ) {
        return true;
    }

    return false;
}

/**
 * Sort characters by length and then alphabetically
 *
 * @param array $characters
 */
function formgent_sort_characters( array &$characters ) {
    uasort(
        $characters, function ( $a, $b ) {
            $len_a = strlen( $a );
            $len_b = strlen( $b );
            if ( $len_a === $len_b ) {
                return strcmp( $a, $b );
            }
            return $len_a <=> $len_b;
        }
    );
}

function formgent_is_google_sheet_enabled() {
    return formgent_settings_repository()->get_by_key( 'google_sheet', ['status' => false] )['status'] ? true : false;
}

function formgent_is_zoho_crm_enabled() {
    $settings = formgent_settings_repository()->get_by_key( 'zoho', ['status' => false, 'connected' => false] );
    return ! empty( $settings['status'] ) && ! empty( $settings['connected'] ) ? true : false;
}

function formgent_is_mailchimp_enabled() {
    $settings = formgent_settings_repository()->get_by_key( 'mailchimp', ['status' => false ] );
    return $settings['status'] ? true : false;
}

function formgent_is_passed_conditional_logic( ResponseDTO $response, int $condition_status, string $logical_type, string $conditions ): bool {
    /**
     * @var FormPresetFieldRepository $form_preset_field_repository
     */
    $form_preset_field_repository = formgent_singleton( FormPresetFieldRepository::class );

    // Form Answers
    $form_answers_data = formgent_get_form_answers( $response->get_form_id(), $response->get_id(), false );

    if ( $condition_status === 0 ) {
        return true;
    }

    $condition = formgent_conditional_logic()
        ->set_response( $response )
        ->set_answers_items( $form_answers_data )
        ->set_logical_type( $logical_type )
        ->set_conditions( json_decode( $conditions, true ) );

    return $condition->is_passed();
}

function formgent_payment_ipn_url( $args = [] ) {
    $url = home_url( 'formgent-payment-notify' );

    if ( empty( $args ) ) {
        return $url;
    }

    return add_query_arg( $args, $url );
}

function formgent_is_plugin_active( string $path ) {
    if ( ! function_exists( '\is_plugin_active' ) ) {
        return false;
    }

    return \is_plugin_active( $path );
}

require_once __DIR__ . '/form-helpers.php';
require_once __DIR__ . '/payment.php';
require_once __DIR__ . '/form-helpers.php';
