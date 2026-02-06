<?php defined( 'ABSPATH' ) || exit;

$context = [
    'name'       => $attributes['name'],
    'field_type' => 'number',
];

?>
<div data-wp-interactive="formgent/form" data-wp-context='<?php echo wp_json_encode( $context ); ?>' data-wp-bind--hidden="state.hideField" class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>">
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
            <input
                class="formgent-field-single__input <?php echo esc_attr( $attributes['is_calculation_enabled'] ) ? 'formgent-form-field-disabled' : ''; ?>"
                type="number"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
                data-wp-on--input="actions.updateNumber"
                data-wp-bind--value="state.getValue"
                <?php if ( $attributes['limit'] ) : ?>
                    min="<?php echo esc_attr( $attributes['min'] ?? '1' ); ?>"
                    max="<?php echo esc_attr( $attributes['max'] ?? '' ); ?>"
                <?php else : ?>
                    min="0"
                <?php endif; ?>
                <?php if ( esc_attr( $attributes['is_calculation_enabled'] ) ) {
                    echo 'disabled';
                }
                ?>
            />
            <?php if ( ! empty( $attributes['sub_label'] ) ) : ?>
                <span class="formgent-field-sub-label">
                    <?php echo wp_kses_post( $attributes['sub_label'] ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>