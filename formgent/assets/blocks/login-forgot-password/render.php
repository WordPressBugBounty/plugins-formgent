<?php defined( 'ABSPATH' ) || exit;

$raw_url = $attributes['url'] ?? '';
$url     = ( '' !== $raw_url && false === strpos( $raw_url, '{{' ) ) ? $raw_url : wp_lostpassword_url();

?>

<div
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo wp_json_encode( [ 'name' => $attributes['name'] ] ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?> formgent-login-forgot-password-wrapper formgent-text-align-<?php echo esc_attr( $attributes['text_alignment'] ); ?>"
>
    <?php if ( ! empty( $attributes['prefix_text'] ) ) : ?>
        <span class="formgent-login-forgot-prefix"><?php echo wp_kses_post( $attributes['prefix_text'] ); ?></span>
    <?php endif; ?>

    <a
        class="formgent-login-forgot"
        href="<?php echo esc_url( $url ); ?>"
        tabindex="0"
    >
        <?php echo wp_kses_post( $attributes['link_text'] ); ?>
    </a>

    <?php if ( ! empty( $attributes['suffix_text'] ) ) : ?>
        <span class="formgent-login-forgot-suffix"><?php echo wp_kses_post( $attributes['suffix_text'] ); ?></span>
    <?php endif; ?>
</div>
