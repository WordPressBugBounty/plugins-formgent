<?php
/**
 * Submit Button Template
 *
 * This template renders the fixed submit button for the Formgent form.
 *
 * @package FormGent
 */

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\View\View;

// Bail early if $form or $form->ID is not available.
if ( empty( $form ) || empty( $form->ID ) ) {
    return;
}

// Retrieve and normalize form settings.
$form_settings = (array) get_post_meta( $form->ID, '_formgent_form_settings', true );

// Extract button settings with sanitization and default fallbacks.
$button_text = isset( $form_settings['submit_button_label'] )
    ? wp_kses_post( $form_settings['submit_button_label'] )
    : __( 'Submit', 'formgent' );

$button_alignment = isset( $form_settings['submit_button_alignment'] )
    ? sanitize_html_class( $form_settings['submit_button_alignment'] )
    : 'left';

$button_style = isset( $form_settings['submit_button_style'] )
    ? sanitize_html_class( $form_settings['submit_button_style'] )
    : 'default';

$background_color = isset( $form_settings['submit_button_background_color'] )
    ? sanitize_hex_color( $form_settings['submit_button_background_color'] )
    : '#000000';

$text_color = isset( $form_settings['submit_button_text_color'] )
    ? sanitize_hex_color( $form_settings['submit_button_text_color'] )
    : '#ffffff';

$border_color = isset( $form_settings['submit_button_border_color'] )
    ? sanitize_hex_color( $form_settings['submit_button_border_color'] )
    : '#000000';

// Handle optional "disabled" state.
$button_disabled = ! empty( $form_settings['submit_button_disabled'] );

// Generate a unique, sanitized button ID.
$button_id = esc_attr( 'formgent-fixed-submit-' . absint( $form->ID ) );

// Bail if the button is disabled.
if ( $button_disabled ) {
    return;
}
?>
<div
    class="formgent-fixed-submit-button formgent-field-single formgent-field-single--button formgent-field-align-<?php echo esc_attr( $button_alignment ); ?>"
    style="--formgent-btn-bg-color: <?php echo esc_attr( $background_color ); ?>;
        --formgent-btn-text-color: <?php echo esc_attr( $text_color ); ?>;
        --formgent-btn-border-color: <?php echo esc_attr( $border_color ); ?>;"
>
    <button
        type="submit"
        id="<?php echo esc_attr( $button_id ); ?>"
        class="formgent-field-list__button-text formgent-btn formgent-btn-md formgent-btn-<?php echo esc_attr( $button_style ); ?>"
        data-wp-bind--disabled="context.global.is_response_submitting"
        data-wp-on--keydown="actions.handleSubmitButtonKeydown"
    >
        <?php echo wp_kses_post( $button_text ); ?>
    </button>
</div>
