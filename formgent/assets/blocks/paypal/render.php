<?php defined( 'ABSPATH' ) || exit;

$settings = formgent_get_setting( "payment" );

if ( ! $settings['status'] ) {
    return;
}

if ( ! $settings['paypal']['status'] ) {
    return;
}
?>

<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field formgent-field-width-100">
    <!-- <input type="radio" name="payment_gateway"
        id="paypal"
        data-wp-context='{ "value": "paypal" }'
        data-wp-bind--checked="state.isPaymentGatewayChecked"
        data-wp-on--change="actions.onChangePaymentGateway"
        required="1"
        />
    <label for="paypal">Paypal</label> -->

    <div class="formgent-field-single">
        <div class="formgent-field-single__wrapper formgent-field-single__wrapper--single-choice">
            <div
                class="formgent-field-single__box formgent-single-choice-payment-method formgent-field-single__box--full_width"
                id="<?php echo esc_attr( formgent_field_id_prefix( 'payment-gateways' ) ); ?>"
                
            >
                <div class="formgent-field-single__box__choice formgent-field-single__box__choice--frame">
                    <input
                        class="formgent-field-single__input formgent-field-single__input--radio"
                        type="radio"
                        name="payment_gateway"
                        id="paypal"
                        data-wp-context='{ "value": "paypal" }'
                        data-wp-bind--checked="state.isPaymentGatewayChecked"
                        data-wp-on--change="actions.onChangePaymentGateway"
                        data-wp-bind--required="!state.isPaymentGatewayChecked"
                    />
                    <label
                        for="paypal"
                        class="formgent-field-single__label formgent-payment-method-label"
                        data-wp-class--formgent-has-single-payment-method="state.hasSinglePaymentMethod"
                    >
                        <span class='formgent-payment-method-icon'>
                            <?php formgent_render_icon( 'paypal' ); ?>
                        </span>
                        <div class='formgent-payment-method-info'>
                            <strong class="formgent-field-paypal-title"><?php echo esc_attr( $attributes['title'] ); ?></strong>
                            <span class="formgent-field-paypal-subtitle"><?php echo esc_attr( $attributes['subtitle'] ); ?></span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>