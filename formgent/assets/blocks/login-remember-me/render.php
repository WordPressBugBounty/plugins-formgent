<?php defined( 'ABSPATH' ) || exit; ?>

<div
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo wp_json_encode( [ 'name' => $attributes['name'] ] ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
>
    <label
        class="formgent-login-remember"
        for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
    >
        <input
            type="checkbox"
            id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
            name="<?php echo esc_attr( $attributes['name'] ); ?>"
            data-wp-on--change="actions.updateLoginRemember"
            data-wp-bind--checked="state.loginRememberChecked"
        />
        <span><?php echo wp_kses_post( $attributes['label'] ); ?></span>
        <?php if ( $attributes['required'] ) : ?>
            <span class="formgent-field-required">*</span>
        <?php endif; ?>
    </label>
</div>
