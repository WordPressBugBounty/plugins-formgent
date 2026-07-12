<?php defined( 'ABSPATH' ) || exit; ?>

<div
    class="formgent-field formgent-hidden-field-wrapper formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ?? 100 ) ); ?>"
    aria-hidden="true"
>
    <input
        type="hidden"
        name="<?php echo esc_attr( $attributes['name'] ); ?>"
        id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
        data-wp-interactive="formgent/form"
        data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }'
        data-wp-on--input="actions.updateInput"
        data-wp-bind--value="state.getValue"
    />
</div>
