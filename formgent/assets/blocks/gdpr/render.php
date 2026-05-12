<?php defined( 'ABSPATH' ) || exit; ?>
<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single">
        <div class="formgent-field-single__wrapper formgent-field-single__wrapper--gdpr formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
            <input
                class="formgent-field-single__input formgent-field-single__input--checkbox"
                type="checkbox"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                data-wp-on--change="actions.updateGdpr"
                data-wp-bind--checked="state.getValue"
            />
            <label for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>" class="formgent-field-label">
                <span class="formgent-field-gdpr-checkbox">
                    <?php formgent_render_icon( 'check-thin', 'general' ); ?>
                </span>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['description'] ); ?>
                    <span class="formgent-field-required">*</span>
                </span>
            </label>
        </div>
    </div>
</div>