<?php

use FormGent\WpMVC\Enqueue\Enqueue;

defined( 'ABSPATH' ) || exit;

include_once __DIR__ . '/register.php';

Enqueue::register_script( 'formgent/gutenberg', 'build/js/formgent-gutenberg' );

/**
 * Builder scripts
 */
if ( formgent_post_type() === get_post_type() ) {
    include_once __DIR__ . '/i18n.php';

    wp_enqueue_code_editor( [] );

    Enqueue::script( 'formgent/blocks-editor', 'build/js/blocks-editor', ['lodash', 'formgent/notification'] );
    Enqueue::style( 'formgent/blocks-editor', 'build/css/blocks-editor' );

    wp_localize_script(
        'formgent/blocks-editor', 'formgent_editor_data', [
            'form_type'        => get_post_meta( get_post()->ID, "_formgent_type", true ),
            'dummy_image_url'  => formgent_url( 'assets/images/dummy.webp' ),
            'colors'           => wp_get_global_settings( [ 'color', 'palette', 'default' ] ),
            'payment_gateways' => array_keys( formgent_get_payment_gateways() )
        ]
    );

    wp_localize_script(
        "formgent-form-editor-script", 'formgent_editor_data', [
            'form_type'        => get_post_meta( get_post()->ID, "_formgent_type", true ),
            'currency_symbols' => formgent_get_currency_symbols(),
        ]
    );

    wp_localize_script(
        'formgent-phone-number-editor-script', 'formgent_phone_number', [
            'assetUrl' => formgent_url( 'assets' ),
        ]
    );

    wp_localize_script(
        'formgent-file-upload-editor-script', 'formgent_file_upload', [
            'mime_types' => get_allowed_mime_types(),
        ]
    );
}

/**
 * Dashboard scripts
 */
if ( 'formgent_page_formgent' === $hook_suffix ) {
    include_once __DIR__ . '/i18n.php';
    wp_enqueue_style( 'wp-components' );
    wp_enqueue_style( 'formgent/style' );
    Enqueue::script( 'formgent/admin', 'build/js/admin', ['formgent/notification'] );
    wp_localize_script(
        'formgent/admin', 'formgent_data', [
            'ai_created_forms' => get_option( 'formgent_ai_created_form', 0 ),
            'zapier_endpoint'  => rest_url( 'formgent/zapier' ),
            'http_headers'     => apply_filters( 'formgent_http_headers', [] ),
            'admin_url'        => admin_url(),
            'home_url'         => home_url(),
            'currencies'       => formgent_get_currencies(),
            'currency_symbols' => formgent_get_currency_symbols(),
            'upload_url'       => WP_CONTENT_URL . '/uploads',
        ]
    );
}