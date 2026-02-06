<?php defined( 'ABSPATH' ) || exit; ?>
<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
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
        <div class="formgent-field-single__wrapper">
            <select
                class="formgent-field-single__input formgent-field-single__input--select"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                data-wp-init="callbacks.dropdownInit"
                data-placeholder="<?php echo esc_attr( $attributes['placeholder'] )?>"
                autocomplete="off"
            >
                <?php if ( ! empty( $attributes['options'] ) && is_array( $attributes['options'] ) ) : ?>
                    <option value=""><?php echo esc_html( $attributes['placeholder'] )?></option>
                    <?php foreach ( $attributes['options'] as $index => $option ) : ?>
                        <option
                            value="<?php echo esc_attr( $option['value'] ); ?>"
                        >
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
</div>