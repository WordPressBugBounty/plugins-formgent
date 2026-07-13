<?php defined( 'ABSPATH' ) || exit;

$payment_settings = formgent_get_setting( "payment" );
$payment_type     = $attributes['payment_type'] ?? 'one_time';
$payment_methods  = $attributes['payment_methods'] ?? ['stripe'];
$amount_type      = $attributes['amount_type'] ?? 'fixed';

// Determine which gateways are enabled and selected
$enabled_methods = [];
foreach ( $payment_methods as $method ) {
    if ( isset( $payment_settings[$method]['status'] ) && $payment_settings[$method]['status'] ) {
        $enabled_methods[] = $method;
    }
}

// For subscriptions, only show gateways allowed by the filter (default: Stripe only).
if ( $payment_type === 'subscription' ) {
    $subscription_gateways = formgent_get_subscription_payment_gateways( $attributes );
    $enabled_methods       = array_values( array_filter( $enabled_methods, fn( $m ) => in_array( $m, $subscription_gateways, true ) ) );
}

// Build context for the frontend interactivity API
$context = [
    'name'                       => $attributes['name'],
    'field_type'                 => 'payment',
    'payment_type'               => $payment_type,
    'payment_methods'            => $enabled_methods,
    'amount_type'                => $amount_type,
    'fixed_label'                => $attributes['fixed_label'] ?? '',
    'fixed_price'                => $attributes['fixed_price'] ?? 0,
    'amount_fields'              => $attributes['amount_fields'] ?? [],
    'quantity_enabled'           => $attributes['quantity_enabled'] ?? false,
    'quantity_label'             => $attributes['quantity_label'] ?? '',
    'quantity_apply_to'          => $attributes['quantity_apply_to'] ?? [],
    'quantity_min'               => $attributes['quantity_min'] ?? 1,
    'quantity_max'               => $attributes['quantity_max'] ?? 10,
    'subscription_plan_name'     => $attributes['subscription_plan_name'] ?? '',
    'subscription_amount_type'   => $attributes['subscription_amount_type'] ?? 'fixed',
    'subscription_fixed_label'   => $attributes['subscription_fixed_label'] ?? '',
    'subscription_fixed_price'   => $attributes['subscription_fixed_price'] ?? 0,
    'subscription_amount_fields' => $attributes['subscription_amount_fields'] ?? [],
    'billing_interval'           => $attributes['billing_interval'] ?? 'monthly',
    'total_billing_times'        => $attributes['total_billing_times'] ?? 0,
    'free_trial_enabled'         => $attributes['free_trial_enabled'] ?? false,
    'trial_days'                 => $attributes['trial_days'] ?? 0,
    'customer_name_field'        => $attributes['customer_name_field'] ?? '',
    'customer_email_field'       => $attributes['customer_email_field'] ?? '',
    'info_text'                  => $attributes['info_text'] ?? '',
];

$has_multiple_methods = count( $enabled_methods ) > 1;
$has_single_method    = count( $enabled_methods ) === 1;
?>

<div data-wp-interactive="formgent/form" data-wp-init="callbacks.initPaymentBlock"
    data-wp-context='<?php echo wp_json_encode( $context ); ?>' data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?> formgent-field-payment">

    <?php if ( $payment_type === 'one_time' && $amount_type === 'fixed' ) : ?>
        <!-- Fixed Amount Display -->
        <div class="formgent-field-single">
            <div class="formgent-field-single__wrapper">
                <div class="formgent-single-product-display">
                    <?php if ( ! empty( $attributes['fixed_label'] ) ) : ?>
                        <span class="formgent-single-product-amount-label">
                            <?php echo esc_attr( $attributes['fixed_label'] ); ?>
                        </span>
                    <?php endif; ?>
                    <span
                        class="formgent-single-product-amount formgent-payment-currency-position formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>">
                        <span><?php echo esc_html( formgent_get_currency_symbol() ); ?></span>
                        <span><?php echo esc_attr( $attributes['fixed_price'] ?? 0 ); ?></span>
                    </span>
                    <input type="hidden" name="<?php echo esc_attr( $attributes['name'] ); ?>_fixed_amount"
                        value="<?php echo esc_attr( $attributes['fixed_price'] ?? 0 ); ?>">
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Payment Summary Table -->
    <div class="formgent-field-single">
        <div class="formgent-field-single__wrapper formgent-field-payment-summary-wrap">
            <span class="formgent-payment-summary-info" data-wp-bind--hidden="state.enableSummary">
                <?php echo esc_attr( $attributes['info_text'] ?? __( 'No payment items has been selected yet.', 'formgent' ) ) ?>
            </span>
            <div class="formgent-payment-summary-table" data-wp-bind--hidden="!state.enableSummary">
                <table>
                    <thead>
                        <tr>
                            <th colspan="2" class="formgent-payment-table-th">
                                <div class="formgent-grid-combined">
                                    <div class="formgent-grid-row">
                                        <span>
                                            <?php echo esc_html__( 'Item', 'formgent' ) ?>
                                        </span>
                                        <span>
                                            <?php echo esc_html__( 'Price', 'formgent' ) ?>
                                        </span>
                                    </div>
                                </div>
                            </th>
                            <!-- <th>
                                <?php //echo esc_html__( 'Quantity', 'formgent' ) ?>
                            </th> -->
                            <th class="formgent-payment-th-total">
                                <?php echo esc_html__( 'Total', 'formgent' ) ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template data-wp-each="state.paymentItems">
                            <tr>
                                <td colspan="2">
                                    <span data-wp-text="context.item.field_label"
                                        data-wp-bind--hidden="!state.showPaymentItemHeading"></span>
                                    <ul class="formgent-grid-combined">
                                        <template data-wp-each--option="context.item.options">
                                            <li class="formgent-grid-row">
                                                <div class="formgent-payment-item--label">
                                                    <span data-wp-text="context.option.name"></span>
                                                </div>
                                                <div
                                                    class="formgent-payment-item--price formgent-payment-currency-position formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>">
                                                    <span>
                                                        <?php echo esc_html( formgent_get_currency_symbol() ); ?>
                                                    </span>
                                                    <span data-wp-text="context.option.price"></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </td>
                                <!-- <td>
                                    <span
                                        data-wp-text="context.item.quantity"
                                        class="formgent-payment-item-quantity"
                                        data-wp-class--formgent-mt-0="!state.showPaymentItemHeading"
                                    ></span>
                                </td> -->
                                <td>
                                    <div
                                        class="formgent-payment-item-total formgent-payment-currency-position formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>"
                                        data-wp-class--formgent-mt-0="!state.showPaymentItemHeading"
                                    >
                                        <span>
                                            <?php echo esc_html( formgent_get_currency_symbol() ); ?>
                                        </span>
                                        <span data-wp-text="context.item.line_total"></span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr>
                            <td colspan="2" class="formgent-payment-summary-total">
                                <span class="formgent-payment-summary-total-label">
                                    <?php echo esc_html__( 'Total:', 'formgent' ) ?>
                                </span>
                            </td>
                            <td class="formgent-payment-summary-total-amount-wrapper">
                                <div
                                    class="formgent-payment-item-price formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>">
                                    <span>
                                        <?php echo esc_html( formgent_get_currency_symbol() ); ?>
                                    </span>
                                    <span class="formgent-payment-summary-total-amount"
                                        data-wp-text="context.global.payment.total_amount">0</span>
                                    <span class="formgent-payment-summary-billing-interval"
                                        data-wp-text="state.billingIntervalLabel"
                                        data-wp-bind--hidden="!state.showBillingInterval"></span>
                                </div>
                            </td>
                        </tr>
                        <tr data-wp-bind--hidden="!state.showTrialInfo" class="formgent-payment-summary-trial-row">
                            <td colspan="3" class="formgent-payment-summary-trial-info">
                                <span data-wp-text="state.trialInfoText"></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Gateway Selection -->
    <?php if ( ! empty( $enabled_methods ) ) : ?>
        <div class="formgent-field-single">
            <div class="formgent-field-single__wrapper formgent-field-single__wrapper--single-choice">
                <?php foreach ( $enabled_methods as $method ) : ?>
                    <div class="formgent-field-single__box formgent-single-choice-payment-method formgent-field-single__box--full_width"
                        id="<?php echo esc_attr( formgent_field_id_prefix( 'payment-gateways' ) ); ?>">
                        <div class="formgent-field-single__box__choice formgent-field-single__box__choice--frame">
                            <input class="formgent-field-single__input formgent-field-single__input--radio" type="radio"
                                name="payment_gateway" id="<?php echo esc_attr( $method ); ?>"
                                data-wp-context='{ "value": "<?php echo esc_attr( $method ); ?>" }'
                                data-wp-bind--checked="state.isPaymentGatewayChecked"
                                data-wp-on--change="actions.onChangePaymentGateway"
                                data-wp-bind--required="!state.isPaymentGatewayChecked" />
                            <label for="<?php echo esc_attr( $method ); ?>"
                                class="formgent-field-single__label formgent-payment-method-label<?php echo $has_single_method ? ' formgent-has-single-payment-method' : ''; ?>">
                                <span class='formgent-payment-method-icon'>
                                    <?php formgent_render_icon( $method ); ?>
                                </span>
                                <div class='formgent-payment-method-info'>
                                    <?php if ( $method === 'stripe' ) : ?>
                                        <strong class="formgent-field-stripe-title">
                                            <?php echo esc_html__( 'Credit Card (Stripe)', 'formgent' ); ?>
                                        </strong>
                                        <span class="formgent-field-stripe-subtitle">
                                            <?php echo esc_html__( 'Pay securely with Stripe', 'formgent' ); ?>
                                        </span>
                                    <?php elseif ( $method === 'paypal' ) : ?>
                                        <strong class="formgent-field-paypal-title">
                                            <?php echo esc_html__( 'Paypal checkout', 'formgent' ); ?>
                                        </strong>
                                        <span class="formgent-field-paypal-subtitle">
                                            <?php echo esc_html__( 'Pay securely with PayPal', 'formgent' ); ?>
                                        </span>
                                    <?php elseif ( $method === 'mollie' ) : ?>
                                        <strong class="formgent-field-mollie-title">
                                            <?php echo esc_html__( 'Mollie', 'formgent' ); ?>
                                        </strong>
                                        <span class="formgent-field-mollie-subtitle">
                                            <?php echo esc_html__( 'Pay securely with Mollie', 'formgent' ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ( $method === 'stripe' ) : ?>
                                    <div class="formgent-payment-method-supported-cards">
                                        <span>
                                            <?php formgent_render_icon( 'visa' ); ?>
                                        </span>
                                        <span>
                                            <?php formgent_render_icon( 'american-express' ); ?>
                                        </span>
                                        <span>
                                            <?php formgent_render_icon( 'master-card' ); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
