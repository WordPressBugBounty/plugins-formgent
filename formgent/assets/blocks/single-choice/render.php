<?php defined( 'ABSPATH' ) || exit;

if ( $attributes['allow_user_add_other_option'] ) {
    $attributes['options'][] = [
        'id'       => 'other-' . esc_attr( formgent_field_id_prefix( $attributes['id'] ) ),
        'label'    => esc_attr( $attributes['other_label'] ),
        'value'    => "formgent_other",
        'is_other' => true,
    ];
}

$form_id           = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
$is_quiz_enabled   = 0 < $form_id && formgent_is_quiz_enabled( $form_id );
$sanitized_options = formgent_sanitize_choice_options( $attributes['options'] ?? [], ! $is_quiz_enabled );

$context = [
    'name'        => $attributes['name'],
    'field_label' => $attributes['label'] ?? '',
    'options'     => $sanitized_options,
];
?>

<div data-wp-interactive="formgent/form" data-wp-init="callbacks.initSelectField"
    data-wp-context='<?php echo esc_attr( wp_json_encode( $context, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP ) ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <label for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['name'] ) ); ?>"
                class="formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( $attributes['required'] ) : ?>
                    <span class="formgent-field-required">
                        *
                    </span>
                <?php endif; ?>
            </label>
        <?php endif; ?>
        <div class="formgent-field-single__wrapper formgent-field-single__wrapper--single-choice">
            <div class="formgent-field-single__box formgent-single-choice-<?php echo esc_attr( $attributes['name'] ); ?> formgent-field-single__box--<?php echo esc_attr( $attributes['layout'] ); ?>"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                data-wp-init="callbacks.singleChoiceKeyboard">
                <?php foreach ( $sanitized_options as $option ) : ?>
                        <div
                            class="formgent-field-single__box__choice formgent-field-single__box__choice--<?php echo esc_attr( $attributes['style'] ); ?>"
                            data-wp-context='<?php echo esc_attr( wp_json_encode( [ 'option' => $option ], JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP ) ); ?>'>
                            <input class="formgent-field-single__input formgent-field-single__input--radio" type="radio"
                                data-wp-bind--id="state.getOptionId"
                                data-wp-bind--checked="state.isSingleSelectOptionSelect"
                                data-wp-on--change="actions.updateSingleChoice" data-wp-bind--name="state.getOptionName" />
                            <label data-wp-bind--for="state.getOptionId" class="formgent-field-single__label">
                                <?php formgent_render_choice_option_media( $option ); ?>
                                <span data-wp-text="state.getOptionLabel"></span>
                                <?php if ( ! empty( $option['is_other'] ) ) : ?>
                                    <input type="text" class="formgent-field-other-input" required="1"
                                        data-wp-bind--value="state.getOtherOptionValue"
                                        data-wp-on--input="actions.updateSingleSelectSpecify"
                                        placeholder="<?php echo esc_attr( $attributes['other_placeholder'] ); ?>" />
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
            </div>
            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- <div data-wp-bind--hidden="!state.showFieldQuantityInput" class="formgent-field-single formgent-field-quantity" data-wp-class--formgent-field-quantity-hidden="!state.showFieldQuantityInput">
        <label for="<?php //echo esc_attr( formgent_field_id_prefix( $attributes['name'] . '_quantity' ) ); ?>"
            class="formgent-field-label" data-wp-text="state.getQuantityLabel">
        </label>
        <input class="formgent-field-single__input" type="number"
            id="<?php //echo esc_attr( formgent_field_id_prefix( $attributes['name'] . '_quantity' ) ); ?>"
            name="<?php //echo esc_attr( $attributes['name'] ); ?>_quantity"
            data-wp-on--input="actions.updatePaymentQuantity"
            data-wp-bind--value="state.getFieldQuantity"
            data-wp-bind--min="state.getFieldQuantityMin"
            data-wp-bind--max="state.getFieldQuantityMax"
            value="1" />
    </div> -->
</div>

<script>
    document.querySelectorAll('.formgent-other__option').forEach(element => {
        element.addEventListener('focus', (evt) => {
            evt.target.closest('div').querySelector('label').click();
        })
    });
</script>
