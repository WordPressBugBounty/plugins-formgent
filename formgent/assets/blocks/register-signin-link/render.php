<?php defined( 'ABSPATH' ) || exit;

$raw_url = $attributes['url'] ?? '';
$url     = ( '' !== $raw_url && false === strpos( $raw_url, '{{' ) ) ? $raw_url : wp_login_url();

?>
<div
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo wp_json_encode( [ 'name' => $attributes['name'] ] ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
>
    <div class="formgent-register-signin formgent-text-align-<?php echo esc_attr( $attributes['text_alignment'] ); ?>">
        <?php if ( ! empty( $attributes['prefix_text'] ) ) : ?>
            <span class="formgent-register-signin-prefix"><?php echo wp_kses_post( $attributes['prefix_text'] ); ?></span>
        <?php endif; ?>

        <a
            class="formgent-register-signin-link"
            href="<?php echo esc_url( $url ); ?>"
            tabindex="0"
        >
            <?php echo wp_kses_post( $attributes['link_text'] ); ?>
        </a>

        <?php if ( ! empty( $attributes['suffix_text'] ) ) : ?>
            <span class="formgent-register-signin-suffix"><?php echo wp_kses_post( $attributes['suffix_text'] ); ?></span>
        <?php endif; ?>
    </div>
</div>
