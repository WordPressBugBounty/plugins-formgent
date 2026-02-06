<?php defined( 'ABSPATH' ) || exit;

$settings = get_post_meta( get_post()->ID, '_formgent_form_settings', true );

if ( empty( $settings ) ) {
    return;
}

?>

<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field-page-break">
    <div class="formgent-field-single formgent-field-single--page-break formgent-page-break">
        <div
            data-wp-bind--hidden="!state.showMultiStepPrev"
            style="--formgent-btn-bg-color: <?php echo esc_attr( $settings['back_button_bg_color'] ); ?>; --formgent-btn-text-color: <?php echo esc_attr( $settings['back_button_text_color'] ); ?>; --formgent-btn-border-color: <?php echo esc_attr( $settings['back_button_border_color'] ); ?>;"
            class="formgent-page-break__back-button"
        >
            <button
                type="button"
                class="formgent-btn formgent-btn-md formgent-btn-<?php echo esc_attr( $settings['back_button_style'] ); ?>"
                data-wp-on--click="actions.multiStepPrev"
            >
                <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <?php echo wp_kses_post( $attributes['back_button_text'] ); ?>
            </button>
        </div>
        <div
            style="--formgent-btn-bg-color: <?php echo esc_attr( $settings['next_button_bg_color'] ); ?>; --formgent-btn-text-color: <?php echo esc_attr( $settings['next_button_text_color'] ); ?>; --formgent-btn-border-color: <?php echo esc_attr( $settings['next_button_border_color'] ); ?>;"
            class="formgent-page-break__next-button"
            data-wp-class--formgent-show-paypal-next="!state.showPaypalNext.show"
            data-wp-class--formgent-hide-paypal-next="state.showPaypalNext.show"
        >
            <button
                type="button"
                class="formgent-btn formgent-btn-md formgent-btn-<?php echo esc_attr( $settings['next_button_style'] ); ?>"
                data-wp-on--click="actions.multiStepNext"
            >
                <span data-wp-text="state.nextButtonText"><?php echo wp_kses_post( $attributes['next_button_text'] ); ?></span>
                <svg width="100%" height="100%" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div
            class="formgent-page-break__paypal-button"
            data-wp-class--formgent-show-paypal-next="state.showPaypalNext.show"
            data-wp-class--formgent-hide-paypal-next="!state.showPaypalNext.show"
        >
            <?php \FormGent\WpMVC\View\View::render( 'paypal-button', compact( 'attributes' ) )?>
        </div>
    </div>
</div>
