<?php defined( 'ABSPATH' ) || exit;

?>

<div
    data-wp-interactive="formgent/form"
    data-wp-context='<?php echo wp_json_encode(
        [
            'name'        => $attributes['name'],
            'field_type'  => 'login',
            'login_state' => [
                'username'        => '',
                'password'        => '',
                'remember'        => false,
                'show_password'   => false,
                'is_submitting'   => false,
                'error_message'   => '',
                'success_message' => '',
                'username_error'  => '',
                'password_error'  => '',
            ],
        ]
    ); ?>'
    data-wp-bind--hidden="state.hideField"
    class="display-none formgent-field formgent-field-width-<?php echo esc_attr( number_format( $attributes['block_width'] ) ); ?>"
    id="<?php echo esc_attr( formgent_field_id_prefix( $attributes['id'] ) ); ?>"
>
    <?php if ( is_user_logged_in() ) : ?>
        <p class="formgent-login-logged-in-message">
            <?php
            printf(
                /* translators: %s: user display name */
                esc_html__( 'You are already logged in as %s.', 'formgent' ),
                '<strong>' . esc_html( wp_get_current_user()->display_name ) . '</strong>'
            );
            ?>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>">
                <?php esc_html_e( 'Log out', 'formgent' ); ?>
            </a>
        </p>
    <?php else : ?>
        <div class="formgent-login-block">
            <div class="formgent-login-error"></div>
            <div class="formgent-login-success"></div>

            <?php
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $content;
            ?>
        </div>
    <?php endif; ?>
</div>
