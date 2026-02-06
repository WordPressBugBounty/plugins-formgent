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
wp_localize_script(
    'formgent/jquery-input-mask', 'formgent_data', [
        'validation_messages' => formgent_get_setting( 'validation_messages', [] ),
        'payment_gateways'    => array_keys( formgent_get_payment_gateways() )
    ]
);

$block_frontend_asset = include formgent_dir( 'assets/build/js/blocks-frontend.asset.php' );
wp_register_script_module( 'formgent/blocks-frontend', formgent_url( 'assets/build/js/blocks-frontend.js' ), $block_frontend_asset['dependencies'], $block_frontend_asset['version'] );

$payment_failed_asset = include formgent_dir( 'assets/build/js/payment-failed.asset.php' );
wp_register_script_module( 'formgent/payment-failed', formgent_url( 'assets/build/js/payment-failed.js' ), $payment_failed_asset['dependencies'], $payment_failed_asset['version'] );