<?php defined( 'ABSPATH' ) || exit; ?>

<div
    class="formgent-field-single formgent-field-single--button formgent-field-single--paypal-button formgent-field-align-<?php echo isset( $attributes['button_alignment'] ) ? esc_attr( $attributes['button_alignment'] ) : 'left'; ?>"
    data-wp-class--formgent-show-button="state.showPaypalButton"
    data-wp-class--formgent-hide-button="!state.showPaypalButton"
>
    <button type="submit" class="formgent-btn formgent-btn-md formgent-btn-paypal">
        <span><?php formgent_render_icon( 'paypal-logo' ); ?></span>
    </button>
</div>