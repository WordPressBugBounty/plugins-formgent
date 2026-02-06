<?php defined( 'ABSPATH' ) || exit; ?>
<div 
    data-wp-interactive="formgent/form" 
    data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }'
    data-wp-class--formgent-display-none="context.is_multi_step"
    data-wp-bind--hidden="state.hideField" class="display-none formgent-field  formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">

    <div
        class="formgent-field-single formgent-field-single--button formgent-field-align-<?php echo esc_attr( $attributes['button_alignment'] ); ?>"
        style="--formgent-btn-bg-color: <?php echo esc_attr( $attributes['background_color'] ); ?>; --formgent-btn-text-color: <?php echo esc_attr( $attributes['text_color'] ); ?>; --formgent-btn-border-color: <?php echo esc_attr( $attributes['border_color'] ); ?>;"
        data-wp-class--formgent-show-button="!state.showPaypalButton"
        data-wp-class--formgent-hide-button="state.showPaypalButton"
    >
        <button type="submit" 
            class="formgent-btn formgent-btn-md formgent-btn-<?php echo esc_attr( $attributes['button_style'] ); ?>"
            data-wp-on--keydown="actions.handleSubmitButtonKeydown"
        >
            <?php echo wp_kses_post( $attributes['button_text'] ); ?>
        </button>
    </div>
    <?php \FormGent\WpMVC\View\View::render( 'paypal-button', compact( 'attributes' ) )?>
</div>