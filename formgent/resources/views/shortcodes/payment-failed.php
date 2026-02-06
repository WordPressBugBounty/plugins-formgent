<?php defined( 'ABSPATH' ) || exit; 

use FormGent\App\EnumeratedList\OrderStatus; 

//phpcs:ignore WordPress.Security.NonceVerification.Recommended
$order_hash = ! empty( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : null;

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
            'status' => OrderStatus::FAILED,
        ];
    } else {
        return;
    }
}

if ( ! in_array( $order->status, [OrderStatus::FAILED, OrderStatus::CANCELLED], true ) ) {
    return;
}

if ( ! $show_dummy ) {
    wp_enqueue_script_module( 'formgent/payment-failed' );
    wp_enqueue_script( 'lodash' );
    wp_enqueue_script( 'wp-api-fetch' );
}

include_once __DIR__ . '/payment-failed-style.php';
?>
<div 
    class="formgent-card"
    data-wp-interactive="formgent/payment-failed"
    data-wp-context='{
    "isTrying": false, 
    "buttonText": "<?php echo esc_html__( 'Try again', 'formgent' ); ?>", 
    "buttonLoadingText": "<?php echo esc_html__( 'Trying', 'formgent' ); ?>", 
    "errorMessage": null
    }'
>
    <div class="formgent-payment-notice formgent-payment-notice--error" data-wp-bind--hidden="!context.errorMessage">
        <span data-wp-text="context.error"></span>
    </div>
    <?php if ( $show_dummy ) { ?>
    <div class="formgent-payment-notice">
        <span><?php esc_html_e( 'You are viewing sample order data.', 'formgent' ); ?></span>
    </div>
    <?php } ?>
    <div class="formgent-error-icon">
        <?php formgent_render_icon( 'exclamation' ); ?>
    </div>

    <h1 class="formgent-title"><?php esc_html_e( 'Payment Failed', 'formgent' ); ?></h1>
    <p class="formgent-subtitle"><?php esc_html_e( 'Please try again or contact support if the problem continues.', 'formgent' ); ?></p>

    <button 
        class="formgent-try-again-button"
        data-wp-on--click="actions.retryPayment"
        data-wp-bind--disabled="context.isTrying"
    >
        <?php if ( $show_dummy ) { ?>
            <span><?php echo esc_html__( 'Try again', 'formgent' ); ?></span>
        <?php } else { ?>
        <span data-wp-text="state.getTryButtonText"></span>
        <?php } ?>
    </button>
    <a class="formgent-go-home-button" href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'Go Home', 'formgent' ); ?></a>
</div>