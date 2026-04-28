<?php defined( 'ABSPATH' ) || exit;
// Block render receives $attributes, $content, $block (no form $context). Get form ID from block context.
$form_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();

$save_resume = function_exists( 'formgent_form_get_setting' ) ? formgent_form_get_setting( $form_id, 'save_resume', [] ) : [];

$show_disclaimer          = ! empty( $save_resume['show_disclaimer_message'] )
    && ( ! empty( $save_resume['auto_save_partial_entries'] ) || ! empty( $context['is_save_incomplete_data'] ) );
$disclaimer_message       = isset( $save_resume['disclaimer_message'] ) ? $save_resume['disclaimer_message'] : '';
$save_resume_link_enabled = ! empty( $save_resume['enable_save_resume_link'] );
$save_resume_link_text    = isset( $save_resume['save_resume_link_text'] ) ? $save_resume['save_resume_link_text'] : 'Save & Resume';

?>

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
        <span data-wp-text="state.conversationalButtonText" data-default-text="<?php echo esc_attr( $attributes['button_text'] ); ?>"><?php echo wp_kses_post( $attributes['button_text'] ); ?></span>
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

<?php if ( $save_resume_link_enabled && function_exists( 'formgent_pro' ) ) : ?>
    <div class="formgent-save-resume-link-container">
        <a class="formgent-save-resume-link" tabindex="0">
            <?php echo wp_kses_post( $save_resume_link_text ); ?>
        </a>
    </div>
<?php endif; ?>

<?php if ( $show_disclaimer && $disclaimer_message !== '' && function_exists( 'formgent_pro' ) ) : ?>
    <div class="formgent-save-resume-disclaimer">
        <span><?php echo wp_kses_post( $disclaimer_message ); ?></span>
    </div>
<?php endif; ?>
