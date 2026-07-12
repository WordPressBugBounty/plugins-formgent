<?php defined( 'ABSPATH' ) || exit;

$allow_multi_select = ! empty( $attributes['allow_multi_select'] );
$sanitized_options  = formgent_sanitize_choice_options( $attributes['options'] ?? [] );

$context = [
    'name'               => $attributes['name'],
    'field_label'        => $attributes['label'] ?? '',
    'options'            => $sanitized_options,
    'allow_multi_select' => $allow_multi_select,
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
                name="<?php echo esc_attr( $attributes['name'] . ( $allow_multi_select ? '[]' : '' ) ); ?>"
                autocomplete="off"
                <?php echo $allow_multi_select ? 'multiple' : ''; ?>
            >
                <?php if ( ! empty( $sanitized_options ) ) : ?>
                    <?php if ( ! $allow_multi_select ) : ?>
                        <option value=""><?php echo esc_html( $attributes['placeholder'] ) ?></option>
                    <?php endif; ?>
                    <?php foreach ( $sanitized_options as $index => $option ) :
                        $option_label = $option['label'] ?? '';
                        $option_value = $option['value'] ?? sanitize_title( wp_strip_all_tags( $option_label ) );
                        $media        = formgent_get_choice_option_media( $option );
                        $icon_svg     = $media['icon']['svg'] ?? '';
                        $image_url    = $media['image']['thumbnail'] ?? '';
                        $image_alt    = $media['image']['alt'] ?? '';
                        ?>
                        <option value="<?php echo esc_attr( $option_value ); ?>"
                            <?php if ( $icon_svg ) :
                                ?>data-icon="<?php echo esc_attr( $icon_svg ); ?>"<?php endif; ?>
                            <?php if ( $image_url ) :
                                ?>data-image="<?php echo esc_attr( $image_url ); ?>"<?php endif; ?>
                            <?php if ( $image_alt ) :
                                ?>data-image-alt="<?php echo esc_attr( $image_alt ); ?>"<?php endif; ?>
                        >
                            <?php echo esc_html( $option_label ); ?>
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
