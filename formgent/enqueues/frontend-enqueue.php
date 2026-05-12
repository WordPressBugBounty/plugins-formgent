<?php

use FormGent\WpMVC\Enqueue\Enqueue;

defined( 'ABSPATH' ) || exit;

include_once __DIR__ . '/register.php';

/**
 * Load block styles for elementor builder
 */
//phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( isset( $_GET['elementor-preview'] ) ) {
    wp_enqueue_style( 'formgent/blocks-frontend', formgent_url( 'assets/build/css/blocks-frontend.css' ), [], formgent_version() );
}



wp_register_script( 'formgent/jquery-input-mask', formgent_url( 'assets/js/jquery.mask.min.js' ), ['jquery'], formgent_version(), [ 'in_footer' => true ] );
$payment_settings         = formgent_get_setting( 'payment', [] );
$payment_gateways         = array_keys( formgent_get_payment_gateways() );
$payment_gateway_statuses = [];
foreach ( $payment_gateways as $gateway_key ) {
    $payment_gateway_statuses[ $gateway_key ] = ! empty( $payment_settings[ $gateway_key ]['status'] );
}

wp_localize_script(
    'formgent/jquery-input-mask', 'formgent_data', [
        'validation_messages' => formgent_get_setting( 'validation_messages', [] ),
        'payment_gateways'    => $payment_gateways,
        // Public, non-sensitive payment configuration for frontend behavior.
        // IMPORTANT: Keep this to booleans only (no keys/tokens).
        'payment_config'      => [
            'status'   => ! empty( $payment_settings['status'] ),
            'gateways' => $payment_gateway_statuses,
        ],
        'ajax_url'            => admin_url( 'admin-ajax.php' ),
        'login_nonce'         => wp_create_nonce( 'formgent_login_nonce' ),
        'register_nonce'      => wp_create_nonce( 'formgent_register_nonce' ),
        'i18n'                => [
            'login_username_required' => esc_html__( 'Please enter your email or username.', 'formgent' ),
            'login_password_required' => esc_html__( 'Please enter your password.', 'formgent' ),
            'login_success'           => esc_html__( 'Login successful.', 'formgent' ),
            'login_failed'            => esc_html__( 'Login failed. Please try again.', 'formgent' ),
            'register_success'        => esc_html__( 'Registration successful.', 'formgent' ),
            'register_failed'         => esc_html__( 'Registration failed. Please try again.', 'formgent' ),
            'generic_error'           => esc_html__( 'An error occurred. Please try again.', 'formgent' ),
        ],
    ]
);

$block_frontend_asset = include formgent_dir( 'assets/build/js/blocks-frontend.asset.php' );
wp_register_script_module( 'formgent/blocks-frontend', formgent_url( 'assets/build/js/blocks-frontend.js' ), $block_frontend_asset['dependencies'], $block_frontend_asset['version'] );

$payment_failed_asset = include formgent_dir( 'assets/build/js/payment-failed.asset.php' );
wp_register_script_module( 'formgent/payment-failed', formgent_url( 'assets/build/js/payment-failed.js' ), $payment_failed_asset['dependencies'], $payment_failed_asset['version'] );