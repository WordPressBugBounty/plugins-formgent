<?php defined( 'ABSPATH' ) || exit;
// Check if we're in the editor context
$is_editor = ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->editor ?? null, 'is_edit_mode' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
    ( defined( 'ELEMENTOR_VERSION' ) && method_exists( \Elementor\Plugin::$instance->preview ?? null, 'is_preview_mode' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) );
?>
<div data-wp-interactive="formgent/form" data-wp-init="callbacks.initDatePicker" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
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
                placeholder="<?php echo esc_attr(
                    $attributes['option'] === 'time'
                    ? wp_kses_post( $attributes['time_placeholder'] )
                    : ( $attributes['range']
                    ? wp_kses_post( $attributes['start_placeholder'] ) . ' - ' . wp_kses_post( $attributes['end_placeholder'] )
                    : ( $attributes['option'] === 'date'
                    ? wp_kses_post( $attributes['date_placeholder'] )
                    : wp_kses_post( $attributes['date_time_placeholder'] ) ) )
); ?>"


            />
        </div>
        <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
            <span class="formgent-field-sub-label">
                <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
            </span>
        <?php endif; ?>
    </div>
</div>
