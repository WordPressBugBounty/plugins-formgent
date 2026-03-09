<?php defined( 'ABSPATH' ) || exit;

$settings                 = get_post_meta( get_post()->ID, '_formgent_form_settings', true );
$use_label_as_placeholder = ! empty( $settings['use_label_as_placeholder'] ?? null );
?>
<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single">
        <?php if ( ! empty( $attributes['label'] ) && ! $use_label_as_placeholder ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['name'] ) ); ?>"
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
        <div class="formgent-field-single__wrapper">
            <input
                class="formgent-field-single__input"
                type="text"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                placeholder="<?php echo esc_attr( $use_label_as_placeholder ? $attributes['label'] : $attributes['placeholder'] ); ?>"
                data-wp-bind--value="state.getValue"
                data-wp-init="callbacks.inputMaskInit"
            />
            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
            <span class="formgent-field-sub-label">
                <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>