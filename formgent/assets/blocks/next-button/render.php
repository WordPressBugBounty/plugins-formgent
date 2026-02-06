<?php defined( 'ABSPATH' ) || exit; ?>

<div
    class="
        formgent-field-single
        formgent-field-single--button
        formgent-field-single--next-button
        formgent-field-single--csr
        formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>
        formgent-field-align-<?php echo esc_attr( $attributes['button_alignment'] ); ?>
    "
    style="
        --formgent-btn-bg-color: <?php echo esc_attr( $attributes['background_color'] ); ?>;
        --formgent-btn-text-color: <?php echo esc_attr( $attributes['text_color'] ); ?>;
        --formgent-btn-border-color: <?php echo esc_attr( $attributes['border_color'] ); ?>;
    "
>
    <span class="formgent-conversation-loading" data-wp-bind--hidden="!state.isSubmittingStep"></span>
    <button
        type="button"
        class="formgent-btn formgent-btn-md formgent-btn-<?php echo esc_attr( $attributes['button_style'] ); ?>"
        data-wp-on--click="actions.nextStep"
    >
        <?php echo wp_kses_post( $attributes['button_text'] ); ?>
        <?php if ( ! empty( $attributes['arrow_icon'] ) ) : ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 24 24" fill="none">
                <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        <?php endif; ?>
    </button>
    <?php if ( ! empty( $attributes['skip_button'] ) ) : ?>
        <button
            type="button"
            class="formgent-btn-link"
            data-wp-on--click="actions.skipStep"
            data-wp-bind--hidden="!state.showSkipButton"
        >
            <?php echo wp_kses_post( $attributes['skip_button_text'] ); ?>
        </button>
    <?php endif; ?>
</div>
