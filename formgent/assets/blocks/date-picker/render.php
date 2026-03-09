<?php defined( 'ABSPATH' ) || exit;
// Check if we're in the editor context
$is_editor = ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->editor ?? null, 'is_edit_mode' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->preview ?? null, 'is_preview_mode' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) );

$settings                 = get_post_meta( get_post()->ID, '_formgent_form_settings', true );
$use_label_as_placeholder = ! empty( $settings['use_label_as_placeholder'] ?? null );

// Placeholder priority:
// 1) If range is enabled, always use start/end placeholders.
// 2) If range is disabled and "use label as placeholder" is enabled, use the field label.
// 3) Otherwise, fall back to option-based placeholders.
$placeholder = '';
if ( ! empty( $attributes['range'] && $attributes['option'] !== 'time' ) ) {
    $start       = wp_strip_all_tags( $attributes['start_placeholder'] ?? '' );
    $end         = wp_strip_all_tags( $attributes['end_placeholder'] ?? '' );
    $placeholder = trim( $start ) . ' - ' . trim( $end );
} elseif ( $use_label_as_placeholder && ! empty( $attributes['label'] ) ) {
    $placeholder = wp_strip_all_tags( $attributes['label'] );
} else {
    if ( ( $attributes['option'] ?? '' ) === 'time' ) {
        $placeholder = wp_strip_all_tags( $attributes['time_placeholder'] ?? '' );
    } elseif ( ( $attributes['option'] ?? '' ) === 'date' ) {
        $placeholder = wp_strip_all_tags( $attributes['date_placeholder'] ?? '' );
    } else {
        $placeholder = wp_strip_all_tags( $attributes['date_time_placeholder'] ?? '' );
    }
}
?>

<div data-wp-interactive="formgent/form" data-wp-init="callbacks.initDatePicker" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) && ! $use_label_as_placeholder ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( $attributes['required'] ) : ?>
                    <span class="formgent-field-required">
                        *
                    </span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div
            class='formgent-has-input-icon formgent-suffix <?php echo wp_kses_post( $attributes['option'] ); ?>'
            style="<?php echo $is_editor ? 'width: 100%;' : ''; ?>"
        >
            <input
                autocomplete="off"
                class='formgent-field-single__input formgent-datepicker'
                type="text"
                readonly
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                data-wp-bind--value="state.getDatePickerValue"
                placeholder="<?php echo esc_attr( $placeholder ); ?>"
            />
        </div>
        <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
            <span class="formgent-field-sub-label">
                <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
            </span>
        <?php endif; ?>
    </div>
</div>
