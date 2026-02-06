<?php defined( 'ABSPATH' ) || exit; ?>

<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?> formgent-field-email">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?> formgent-field-single-email-primary">
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
            <div class='formgent-has-input-icon'>
                <?php if ( $attributes['show_field_icon'] ) : ?>
                    <span class='formgent-input-icon'>
                        <?php formgent_render_icon( 'envelope' ); ?>
                    </span>
                <?php endif; ?>

                <input
                    class="formgent-field-single__input"
                    type="email"
                    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                    placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
                    data-wp-on--input="actions.updateInput"
                    data-wp-bind--value="state.getValue"
                />
            </div>

            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( $attributes['enable_confirmation_field'] ) :
        $name = $attributes['name'] . '_confirm'; ?>
        <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
            <?php if ( ! empty( $attributes['confirm_label'] ) ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>_confirmation"
                class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['confirm_label'] ); ?>
                <span class="formgent-field-required">*</span>
            </label>
            <?php endif; ?>
        <div class="formgent-field-single__wrapper">
            <div class='formgent-has-input-icon'>
                <?php if ( $attributes['show_field_icon'] ) : ?>
                    <span class='formgent-input-icon'><?php formgent_render_icon( 'envelope' ); ?></span>
                <?php endif; ?>

                <input
                    class="formgent-field-single__input"
                    type="email"
                    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>_confirmation"
                    data-wp-context='{ "name": "<?php echo esc_attr( $name ); ?>" }'
                    placeholder="<?php echo esc_attr( $attributes['confirm_placeholder'] ); ?>"
                    data-wp-on--input="actions.updateInput"
                    data-wp-bind--value="state.getValue"
                />
            </div>

            <?php if ( ! empty( $attributes['confirm_sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['confirm_sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>