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
            'response_id'  => 0,
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
$payment_gateway  = $payment_gateways[$payment->method] ?? ( $show_dummy ? ['label' => 'PayPal'] : null );
if ( ! $payment_gateway ) {
    return;
}
$payment_gateway_label = $payment_gateway['label'] ?? '';

// -------------------------------------------------------------------------
// Subscription detection: parse the form's payment block attributes.
// -------------------------------------------------------------------------
$subscription_data = [
    'is_subscription'     => false,
    'payment_type'        => 'one_time',
    'plan_name'           => '',
    'billing_interval'    => '',
    'total_billing_times' => 0,
    'trial_days'          => 0,
];

if ( ! $show_dummy && ! empty( $order->response_id ) ) {
    // order → response → form post.
    $response_repo = formgent_response_repository();
    $response      = $response_repo->get_by_id( $order->response_id );

    if ( $response && ! empty( $response->form_id ) ) {
        $form_post = get_post( $response->form_id );

        if ( $form_post ) {
            $blocks = parse_blocks( $form_post->post_content );

            // Recursively find the payment block.
            $payment_block_attrs = null;
            $block_stack         = $blocks;
            while ( ! empty( $block_stack ) ) {
                $block = array_shift( $block_stack );
                if ( $block['blockName'] === 'formgent/payment' ) {
                    $payment_block_attrs = $block['attrs'] ?? [];
                    break;
                }
                if ( ! empty( $block['innerBlocks'] ) ) {
                    $block_stack = array_merge( $block_stack, $block['innerBlocks'] );
                }
            }

            if ( $payment_block_attrs && ( $payment_block_attrs['payment_type'] ?? '' ) === 'subscription' ) {
                $subscription_data['is_subscription']     = true;
                $subscription_data['payment_type']        = 'subscription';
                $subscription_data['plan_name']           = $payment_block_attrs['subscription_plan_name'] ?? '';
                $subscription_data['billing_interval']    = $payment_block_attrs['billing_interval'] ?? 'monthly';
                $subscription_data['total_billing_times'] = (int) ( $payment_block_attrs['total_billing_times'] ?? 0 );
                $subscription_data['trial_days']          = ! empty( $payment_block_attrs['free_trial_enabled'] )
                    ? (int) ( $payment_block_attrs['trial_days'] ?? 0 )
                    : 0;
            }
        }
    }
} elseif ( $show_dummy ) {
    // Demo subscription data for admin preview.
    $subscription_data = [
        'is_subscription'     => true,
        'payment_type'        => 'subscription',
        'plan_name'           => 'Test Plan',
        'billing_interval'    => 'monthly',
        'total_billing_times' => 0,
        'trial_days'          => 7,
    ];
}

$is_subscription  = $subscription_data['is_subscription'];
$billing_interval = $subscription_data['billing_interval'];

// Build interval label map.
$interval_labels = [
    'daily'   => __( 'day', 'formgent' ),
    'weekly'  => __( 'week', 'formgent' ),
    'monthly' => __( 'month', 'formgent' ),
    'yearly'  => __( 'year', 'formgent' ),
];

$interval_display_labels = [
    'daily'   => __( 'Daily', 'formgent' ),
    'weekly'  => __( 'Weekly', 'formgent' ),
    'monthly' => __( 'Monthly', 'formgent' ),
    'yearly'  => __( 'Yearly', 'formgent' ),
];

$interval_label         = $interval_labels[$billing_interval] ?? $billing_interval;
$interval_display_label = $interval_display_labels[$billing_interval] ?? ucfirst( $billing_interval );

include_once __DIR__ . '/payment-success-style.php';
?>

<div class="formgent-card">
    <?php if ( $show_dummy ) { ?>
        <div class="formgent-payment-notice">
            <span><?php esc_html_e( "You are viewing sample order data.", 'formgent' ); ?></span>
        </div>
    <?php } ?>
    <div class="formgent-success-icon">
        <?php formgent_render_icon( 'check-circle' ); ?>
    </div>

    <h1 class="formgent-title"><?php esc_html_e( "Payment Success", "formgent" ); ?></h1>
    <p class="formgent-subtitle">
        <?php
        if ( $is_subscription ) {
            esc_html_e( "Your subscription has been activated successfully.", "formgent" );
        } else {
            esc_html_e( "Your transaction has been completed successfully.", "formgent" );
        }
        ?>
    </p>

    <div class="formgent-details">
        <!-- Amount -->
        <div class="formgent-detail-row">
            <span class="formgent-detail-label"><?php esc_html_e( "Amount:", "formgent" ); ?></span>
            <span class="formgent-detail-value formgent-amount">
                <?php
                if ( $is_subscription && $subscription_data['trial_days'] > 0 ) {
                    // Trial active: $0.00 ($45/month after 7 days)
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo formgent_price( 0, ['currency' => $order->currency] );
                    echo ' <span class="formgent-amount-after-trial">(';
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo formgent_price( $order->final_amount, ['currency' => $order->currency] );
                    echo '/' . esc_html( $interval_label );
                    echo ' ' . esc_html(
                        sprintf(
                            /* translators: %d: number of trial days */
                            __( 'after %d days', 'formgent' ),
                            $subscription_data['trial_days']
                        )
                    );
                    echo ')</span>';
                } elseif ( $is_subscription ) {
                    // Subscription without trial: $45/month
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo formgent_price( $order->final_amount, ['currency' => $order->currency] );
                    echo ' / ' . esc_html( $interval_label );
                } else {
                    // One-time payment
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo formgent_price( $order->final_amount, ['currency' => $order->currency] );
                }
                ?>
            </span>
        </div>

        <!-- Payment Method -->
        <div class="formgent-detail-row">
            <span class="formgent-detail-label"><?php esc_html_e( "Payment Method:", "formgent" ); ?></span>
            <div class="formgent-payment-method">
                <div class="formgent-paypal-logo">
                    <?php formgent_render_icon( $payment->method ); ?>
                </div>
                <span class="formgent-detail-value">
                    <?php
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo $payment_gateway_label;
                    ?>
                </span>
            </div>
        </div>

        <?php if ( $is_subscription ) : ?>
            <!-- Subscription ID -->
            <div class="formgent-detail-row">
                <span class="formgent-detail-label"><?php esc_html_e( "Subscription ID:", "formgent" ); ?></span>
                <span class="formgent-detail-value">
                    <?php
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo $payment->transaction_id;
                    ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Transaction ID -->
        <div class="formgent-detail-row">
            <span class="formgent-detail-label"><?php esc_html_e( "Transaction ID:", "formgent" ); ?></span>
            <span class="formgent-detail-value">
                <?php
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $payment->transaction_id;
                ?>
            </span>
        </div>

        <?php if ( $is_subscription ) : ?>
            <!-- Status -->
            <div class="formgent-detail-row">
                <span class="formgent-detail-label"><?php esc_html_e( "Status:", "formgent" ); ?></span>
                <span class="formgent-detail-value formgent-status-active">
                    <?php esc_html_e( "Active", "formgent" ); ?>
                </span>
            </div>

            <!-- Start Date -->
            <div class="formgent-detail-row">
                <span class="formgent-detail-label"><?php esc_html_e( "Start Date:", "formgent" ); ?></span>
                <span class="formgent-detail-value">
                    <?php
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo date_i18n( get_option( 'date_format' ), strtotime( $payment->updated_at ) );
                    ?>
                </span>
            </div>

            <!-- Next Billing Date -->
            <div class="formgent-detail-row">
                <span class="formgent-detail-label"><?php esc_html_e( "Next Billing Date:", "formgent" ); ?></span>
                <span class="formgent-detail-value">
                    <?php
                    $start_time       = strtotime( $payment->updated_at );
                    $trial_days       = $subscription_data['trial_days'];
                    $interval_offsets = [
                        'daily'   => '+1 day',
                        'weekly'  => '+1 week',
                        'monthly' => '+1 month',
                        'yearly'  => '+1 year',
                    ];
                    if ( $trial_days > 0 ) {
                        $next_billing = strtotime( "+{$trial_days} days", $start_time );
                    } else {
                        $offset       = $interval_offsets[$billing_interval] ?? '+1 month';
                        $next_billing = strtotime( $offset, $start_time );
                    }
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo date_i18n( get_option( 'date_format' ), $next_billing );
                    ?>
                </span>
            </div>

            <!-- Billing Cycle -->
            <div class="formgent-detail-row">
                <span class="formgent-detail-label"><?php esc_html_e( "Billing Cycle:", "formgent" ); ?></span>
                <span class="formgent-detail-value">
                    <?php echo esc_html( $interval_display_label ); ?>
                </span>
            </div>

            <?php if ( ! empty( $subscription_data['plan_name'] ) ) : ?>
                <!-- Plan Name -->
                <div class="formgent-detail-row">
                    <span class="formgent-detail-label"><?php esc_html_e( "Plan:", "formgent" ); ?></span>
                    <span class="formgent-detail-value">
                        <?php echo esc_html( $subscription_data['plan_name'] ); ?>
                    </span>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <!-- Date (one-time only) -->
            <div class="formgent-detail-row">
                <span class="formgent-detail-label"><?php esc_html_e( "Date:", "formgent" ); ?></span>
                <span class="formgent-detail-value">
                    <?php
                    //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo $payment->updated_at;
                    ?>
                </span>
            </div>
        <?php endif; ?>

    </div>

    <!-- <button class="formgent-done-button">Done</button> -->
</div>