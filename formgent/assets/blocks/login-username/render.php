<?php defined( 'ABSPATH' ) || exit;

$form_id                  = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : (int) get_the_ID();
$settings                 = $form_id ? get_post_meta( $form_id, '_formgent_form_settings', true ) : [];
$use_label_as_placeholder = ! empty( $settings['use_label_as_placeholder'] ?? null );

?>

<div
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo wp_json_encode( [ 'name' => $attributes['name'] ] ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
>
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['label'] ) && ! $use_label_as_placeholder ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                class="formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
                <?php if ( $attributes['required'] ) : ?>
                    <span class="formgent-field-required">*</span>
                <?php endif; ?>
            </label>
        <?php endif; ?>

        <div class="formgent-field-single__wrapper">
            <input
                class="formgent-field-single__input"
                type="text"
                id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                name="<?php echo esc_attr( $attributes['name'] ); ?>"
                placeholder="<?php echo esc_attr( $use_label_as_placeholder ? $attributes['label'] : $attributes['placeholder'] ); ?>"
                autocomplete="username"
                data-wp-on--input="actions.updateLoginUsername"
            />
        </div>
    </div>
</div>
