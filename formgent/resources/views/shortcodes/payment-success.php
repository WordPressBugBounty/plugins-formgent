<?php defined( 'ABSPATH' ) || exit;

use FormGent\App\EnumeratedList\OrderStatus;

//phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order_hash = ! empty( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : null;

// Get order or set dummy if admin and not found
$order_repository = formgent_order_repository();
$order            = $order_hash ? $order_repository->get_by( 'hash', $order_hash ) : null;
$show_dummy       = false;

if ( ! $order ) {
    // Prepare user and admin check
    $current_user  = wp_get_current_user();
    $is_admin_user = in_array( 'administrator', (array) $current_user->roles, true );

    if ( $is_admin_user ) {
        $show_dummy = true;
        $order      = (object) [
            'final_amount' => 99.99,
            'currency'     => 'USD',
            'status'       => OrderStatus::PAID,
            'id'           => 0,
        ];
    } else {
        return;
    }
}

// Only show if paid
if ( OrderStatus::PAID !== $order->status ) {
    return;
}

// Get payment or set dummy if admin
$payment_repository = formgent_payment_repository();
$payment            = ! $show_dummy ? $payment_repository->get_by_order_id_last( $order->id ) : (object) [
    'method'         => 'paypal',
    'transaction_id' => 'DUMMY123456',
    'updated_at'     => gmdate( 'Y-m-d H:i:s' ),
];

// Get payment gateway or set dummy if admin
$payment_gateways = formgent_get_payment_gateways();
$payment_gateway  = $payment_gateways[ $payment->method ] ?? ( $show_dummy ? [ 'label' => 'PayPal' ] : null );
if ( ! $payment_gateway ) {
    return;
}
$payment_gateway_label = $payment_gateway['label'] ?? '';

include_once __DIR__ . '/payment-success-style.php';
?>

<div class="formgent-card">
    <?php if ( $show_dummy ) {?>
    <div class="formgent-payment-notice">
        <span><?php esc_html_e( "You are viewing sample order data.", 'formgent' )?></span>
    </div>
    <?php }?>
    <div class="formgent-success-icon">
        <?php formgent_render_icon( 'check-circle' ); ?>
    </div>

    <h1 class="formgent-title"><?php esc_html_e( "Payment Success", "formgent" )?></h1>
    <p class="formgent-subtitle"><?php esc_html_e( "Your transaction has been completed successfully.", "formgent" )?></p>

    <div class="formgent-details">
        <div class="formgent-detail-row">
            <span class="formgent-detail-label"><?php esc_html_e( "Amount:", "formgent" )?></span>
            <span class="formgent-detail-value formgent-amount"><?php
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo formgent_price(
                $order->final_amount, [
                    'currency' => $order->currency
                ]
            );?></span>
        </div>

        <div class="formgent-detail-row">
            <span class="formgent-detail-label"><?php esc_html_e( "Payment Method:", "formgent" )?></span>
            <div class="formgent-payment-method">
                <div class="formgent-paypal-logo">
                    <?php formgent_render_icon( $payment->method ); ?>
                </div>
                <span class="formgent-detail-value"><?php 
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $payment_gateway_label?></span>
            </div>
        </div>

        <div class="formgent-detail-row">
            <span class="formgent-detail-label"><?php esc_html_e( "Transaction ID:", "formgent" )?></span>
            <span class="formgent-detail-value"><?php
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $payment->transaction_id?></span>
        </div>

        <div class="formgent-detail-row">
            <span class="formgent-detail-label"><?php esc_html_e( "Date:", "formgent" )?></span>
            <span class="formgent-detail-value"><?php
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $payment->updated_at?></span>
        </div>
    </div>

    <!-- <button class="formgent-done-button">Done</button> -->
</div>