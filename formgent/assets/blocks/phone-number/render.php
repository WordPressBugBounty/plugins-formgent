<?php

defined( 'ABSPATH' ) || exit;

$unique_id = str_replace( '-', '_', wp_unique_id( 'formgent-store' ) );
?>

<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
    <div class="formgent-field-single formgent-field-single--csr formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
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
            class="formgent-field-single__wrapper"
        >
            <div class="formgent-field-single__phone">
                <div class="formgent-field-single__dialer__content">
                    <input
                        class="formgent-field-single__input <?php echo $attributes['country_code'] ? 'formgent-field-single__input--country-code' : ''; ?>"
                        type="tel"
                        id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                        placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
                        data-wp-init="callbacks.phoneNumberInit"
                    >
                </div>
            </div>
            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>