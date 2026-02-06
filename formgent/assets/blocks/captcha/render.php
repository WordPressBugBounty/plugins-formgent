<?php defined( 'ABSPATH' ) || exit;

$unique_id  = wp_unique_id( 'formgent_captcha' );
$unique_key = "formgent_{$unique_id}_captcha";

$settings_repository = formgent_settings_repository();
$settings            = $settings_repository->get_by_key( "captcha_keys" );

?>
<script>
    var <?php echo esc_js( $unique_key ) ?> = function(response) {
        wp.hooks.doAction("<?php echo esc_js( $unique_key ) ?>", response);
    };
</script>

<div
    data-wp-interactive="formgent/form"
    data-wp-context='{ "captcha_hook": "<?php echo esc_attr( $unique_key ) ?>", "name": "<?php echo esc_attr( $attributes['name'] ); ?>"}'
    class="formgent-field "
    data-wp-bind--hidden="state.hideField"
>
    <div
        class="formgent-field-single formgent-field-single--captcha formgent-field-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
        id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
    >

        <?php if ( ! empty( $attributes['label'] ) ) : ?>
            <label
                for="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
                class= "formgent-field-label formgent-label-align-<?php echo esc_attr( $attributes['label_alignment'] ); ?>"
            >
                <?php echo wp_kses_post( $attributes['label'] ); ?>
            </label>
        <?php endif; ?>

        <?php if ( 'google_recaptcha' === $attributes['captcha'] ) {?>
            <div
                class="g-recaptcha"
                data-wp-init="callbacks.initCaptcha"
                data-theme="<?php echo esc_attr( $attributes['theme'] ) ?>"
                data-sitekey="<?php echo esc_attr( $settings['recaptcha_site_key'] ) ?>"
                data-callback="<?php echo esc_attr( $unique_key ) ?>"
            >
            </div>
            <?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php }?>

        <?php if ( 'h_captcha' === $attributes['captcha'] ) {?>
            <div
                data-wp-init="callbacks.initCaptcha"
                class="h-captcha"
                data-theme="<?php echo esc_attr( $attributes['theme'] ) ?>"
                data-sitekey="<?php echo esc_attr( $settings['hcaptcha_site_key'] ) ?>"
                data-callback="<?php echo esc_attr( $unique_key ) ?>"
            >
            </div>
            <?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
            <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
        <?php }?>

        <?php if ( 'cloudflare_turnstile' === $attributes['captcha'] ) {?>
            <div
                data-wp-init="callbacks.initCaptcha"
                class="cf-turnstile"
                data-theme="<?php echo esc_attr( $attributes['theme'] ) ?>"
                data-sitekey="<?php echo esc_attr( $settings['turnstile_site_key'] ) ?>"
                data-callback="<?php echo esc_attr( $unique_key ) ?>"
            >
            </div>
            <?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, PluginCheck.CodeAnalysis.Offloading.OffloadedContent ?>
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
        <?php }?>
    </div>
    <div
        class="formgent-captcha-error"
        data-wp-bind--hidden="!context.data.captcha"
        data-wp-class--hide="context.data.captcha"
    >
        <?php echo esc_html__( "Captcha validation failed. Please complete the captcha.", 'formgent' ) ?>
    </div>
</div>