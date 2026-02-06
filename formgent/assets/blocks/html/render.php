<?php

defined( 'ABSPATH' ) || exit;

$raw_html = isset( $attributes['html_content'] ) ? $attributes['html_content'] : '';

$context = [
    'name'       => $attributes['name'],
    'field_type' => 'html',
    'html'       => $raw_html,
];

?>
<div
    class="display-none formgent-html-block__content formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo esc_attr( wp_json_encode( $context, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP ) ); ?>'
    data-wp-bind--hidden="state.hideField"
    data-wp-init="callbacks.initHtmlBlock"
    data-wp-watch="callbacks.updateHtmlBlock"
></div>