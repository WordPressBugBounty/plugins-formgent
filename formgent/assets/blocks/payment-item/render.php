<?php defined( 'ABSPATH' ) || exit;

if ( ! formgent_is_payment_enabled() ) {
    return;
}

$context = [
    'name'                 => $attributes['name'],
    'single_item_amount'   => $attributes['single_amount'],
    'options'              => map_deep( $attributes['options'], 'esc_attr' ),
    'field_type'           => 'payment-item',
    'product_display_type' => $attributes['product_display_type'],
    'field_label'          => $attributes['label'],
];
?>

<div data-wp-interactive="formgent/form" data-wp-init="callbacks.initSelectField" data-wp-context='<?php echo wp_json_encode( $context ); ?>' data-wp-bind--hidden="state.hideField" class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['name'] ) ); ?>"
                class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( $attributes['required'] && $attributes['product_display_type'] !== 'single' ) : ?>
                    <span class="formgent-field-required">
                        *
                    </span>
                <?php endif; ?>
            </label>
        <?php endif; ?>
        <div class="formgent-field-single__wrapper formgent-field-single__wrapper--<?php echo esc_attr( $attributes['product_display_type'] === 'checkbox' ? 'multi-choice' : 'single-choice' ) ?>">
            <?php if ( $attributes['product_display_type'] === 'single' ) : ?>
                <div class="formgent-field-single__wrapper">
                    <div class="formgent-single-product-display">
                        <?php if ( $attributes['single_amount_label'] ) : ?>
                            <span class="formgent-single-product-amount-label"><?php echo esc_attr( $attributes['single_amount_label'] ); ?></span>
                        <?php endif; ?>
                        <span class="formgent-single-product-amount formgent-payment-currency-position formgent-payment-currency-position-<?php echo esc_attr( formgent_get_currency_position() ) ?>">
                            <span><?php echo esc_html( formgent_get_currency_symbol() ); ?></span>
                            <span><?php echo esc_attr( $attributes['single_amount'] ); ?></span>
                        </span>
                        <input type="hidden" id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>" value="<?php echo esc_attr( $attributes['single_amount'] ) ?>">
                    </div>
                </div>
            <?php elseif ( $attributes['product_display_type'] === 'select' ) : ?>
                <select
                    class="formgent-field-single__input formgent-field-single__input--select"
                    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                    data-wp-init="callbacks.dropdownInit"
                    data-placeholder="<?php echo esc_attr( $attributes['placeholder'] ) ?>"
                    autocomplete="off"
                >
                    <?php if ( ! empty( $attributes['options'] ) && is_array( $attributes['options'] ) ) : ?>
                        <option value=""><?php echo esc_html( $attributes['placeholder'] ) ?></option>
                        <?php foreach ( $attributes['options'] as $index => $option ) : ?>
                            <option
                                value="<?php echo esc_attr( $option['value'] ); ?>"
                            >
                                <?php echo esc_html( $option['label'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            <?php else : ?>
                <div
                    class="formgent-field-single__box <?php echo esc_attr( $attributes['product_display_type'] === 'checkbox' ? 'formgent-multiple-choice-' : 'formgent-single-choice-' ) ?><?php echo esc_attr( $attributes['name'] ) ?> formgent-field-single__box--<?php echo esc_attr( $attributes['layout'] ); ?>"
                    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                    data-wp-init="callbacks.<?php echo $attributes['product_display_type'] === 'checkbox'
                    ? 'multipleChoiceKeyboard'
                    : 'singleChoiceKeyboard'; ?>"
                >

                    <?php if ( ! empty( $attributes['options'] ) && is_array( $attributes['options'] ) ) : ?>
                        <template data-wp-each--option="context.options">
                        <div class="formgent-field-single__box__choice formgent-field-single__box__choice--<?php echo esc_attr( $attributes['style'] ); ?>" >
                            <?php if ( $attributes['product_display_type'] === 'checkbox' ) : ?>
                                <input
                                    class="formgent-field-single__input formgent-field-single__input--checkbox"
                                    type="checkbox"
                                    name="<?php echo esc_attr( $attributes['name'] ); ?>"
                                    data-wp-on--change="actions.updateMultiChoice"
                                    data-wp-bind--id="state.getOptionId"
                                    data-wp-bind--checked="state.isMultiSelectOptionChecked"
                                    data-wp-bind--disabled="state.isMultiSelectOptionDisabled"
                                />
                            <?php else : ?>
                                <input
                                    class="formgent-field-single__input formgent-field-single__input--radio"
                                    type="radio"
                                    data-wp-bind--id="state.getOptionId"
                                    data-wp-bind--checked="state.isSingleSelectOptionSelect"
                                    data-wp-on--change="actions.updateSingleChoice"
                                    name="<?php echo esc_attr( $attributes['name'] ); ?>"
                                />
                            <?php endif; ?>
                                <label data-wp-bind--for="state.getOptionId" class="formgent-field-single__label">
                                    <?php if ( $attributes['product_display_type'] === 'checkbox' ) : ?>
                                        <span class="formgent-field-single__checkbox">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </span>
                                    <?php endif; ?>

                                    <span data-wp-text="context.option.label"></span>
                                </label>

                            </div>
                        </template>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                    <span class="formgent-field-sub-label">
                        <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                    </span>
                <?php endif; ?>
            </div>
    </div>
</div>