<?php defined( 'ABSPATH' ) || exit;
$context = [
    'name'        => $attributes['name'],
    'field_label' => $attributes['label'] ?? '',
    'options'     => ! empty( $attributes['options'] ) ? map_deep( $attributes['options'], 'esc_attr' ) : [],
];

$settings                 = get_post_meta( get_post()->ID, '_formgent_form_settings', true );
$use_label_as_placeholder = ! empty( $settings['use_label_as_placeholder'] ?? null );
?>

<div data-wp-interactive="formgent/form" data-wp-context='<?php echo esc_attr( wp_json_encode( $context, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP ) ); ?>' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
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
        <div class="formgent-field-single__wrapper">
            <select class="formgent-field-single__input formgent-field-single__input--select"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                data-wp-init="callbacks.dropdownInit"
                data-placeholder="<?php echo esc_attr( $use_label_as_placeholder ? $attributes['label'] : $attributes['placeholder'] ); ?>"
                autocomplete="off"
            >
                <?php if ( ! empty( $attributes['options'] ) && is_array( $attributes['options'] ) ) : ?>
                    <option value=""><?php echo esc_html( $attributes['placeholder'] ) ?></option>
                    <?php foreach ( $attributes['options'] as $index => $option ) : ?>
                        <option value="<?php echo esc_attr( $option['value'] ); ?>">
                            <?php echo esc_html( $option['label'] ); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div data-wp-bind--hidden="!state.showFieldQuantityInput" class="formgent-field-single formgent-field-quantity" data-wp-class--formgent-field-quantity-hidden="!state.showFieldQuantityInput">
        <label for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['name'] . '_quantity' ) ); ?>"
            class="formgent-field-label" data-wp-text="state.getQuantityLabel">
        </label>
        <input class="formgent-field-single__input" type="number"
            id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['name'] . '_quantity' ) ); ?>"
            name="<?php echo esc_attr( $attributes['name'] ); ?>_quantity"
            data-wp-on--input="actions.updatePaymentQuantity"
            data-wp-bind--value="state.getFieldQuantity"
            data-wp-bind--min="state.getFieldQuantityMin"
            data-wp-bind--max="state.getFieldQuantityMax"
            value="1" />
    </div>
</div>
