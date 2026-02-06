<?php defined( 'ABSPATH' ) || exit; ?>
<div data-wp-interactive="formgent/form" data-wp-context='{ "name": "<?php echo esc_attr( $attributes['name'] ); ?>" }' data-wp-bind--hidden="state.hideField" class="display-none formgent-field   formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>" id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>">
    <div class="formgent-field-single formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>" data-wp-context='{ "parent_name": "<?php echo esc_attr( $attributes['name'] ); ?>" }'>
    <?php if ( ! empty( $attributes['label'] ) ) : ?>
    <span class="formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>">
        <?php echo wp_kses_post( $attributes['label'] ); ?>
    </span>
    <?php endif;
//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo $content;
    ?>
    </div>
</div>