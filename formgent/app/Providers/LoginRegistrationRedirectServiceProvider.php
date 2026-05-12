<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Contracts\Provider;

class LoginRegistrationRedirectServiceProvider implements Provider {
    /**
     * wp-login actions that should not be redirected.
     *
     * @var string[]
     */
    private array $excluded_login_actions = [
        'logout',
        'lostpassword',
        'retrievepassword',
        'resetpass',
        'rp',
        'postpass',
        'confirm_admin_email',
    ];

    public function boot() {
        add_action( 'init', [$this, 'action_init'], 0 );
        add_filter( 'login_url', [$this, 'filter_login_url'], 10, 3 );
        add_filter( 'register_url', [$this, 'filter_register_url'], 10, 1 );
    }

    /**
     * Fires after WordPress has finished loading but before any headers are sent.
     */
    public function action_init(): void {
        $this->maybe_redirect_auth_routes();
    }

    private function get_settings(): array {
        $settings = formgent_settings_repository()->get_by_key( 'login_registration', [] );
        return is_array( $settings ) ? $settings : [];
    }

    private function get_target_url( string $key ): string {
        $settings = $this->get_settings();
        $section  = isset( $settings[$key] ) && is_array( $settings[$key] ) ? $settings[$key] : [];

        $status = isset( $section['status'] ) ? (string) $section['status'] : '0';
        $page   = isset( $section['page'] ) ? absint( $section['page'] ) : 0;

        if ( '1' !== $status || 0 === $page ) {
            return '';
        }

        $url = get_permalink( $page );
        return is_string( $url ) ? $url : '';
    }

    private function get_login_target_url(): string {
        return $this->get_target_url( 'login' );
    }

    private function get_registration_target_url(): string {
        return $this->get_target_url( 'registration' );
    }

    private function is_wp_login_request(): bool {
        global $pagenow;
        return isset( $pagenow ) && 'wp-login.php' === $pagenow;
    }

    private function is_wp_signup_request(): bool {
        global $pagenow;
        return isset( $pagenow ) && 'wp-signup.php' === $pagenow;
    }

    public function maybe_redirect_auth_routes() {
        if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
            return;
        }

        if ( is_user_logged_in() ) {
            return;
        }

        // Only redirect GET requests to avoid breaking POST login submissions.
        $method = isset( $_SERVER['REQUEST_METHOD'] )
            ? strtoupper( sanitize_key( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) )
            : 'GET';
        if ( 'GET' !== $method ) {
            return;
        }

        $action = (string) filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $action = sanitize_key( $action );

        // Registration endpoints:
        // - wp-login.php?action=register
        // - wp-signup.php (multisite registration)
        if ( ( $this->is_wp_login_request() && 'register' === $action ) || $this->is_wp_signup_request() ) {
            $target = $this->get_registration_target_url();
            if ( '' !== $target ) {
                wp_safe_redirect( $target, 302 );
                exit;
            }
            return;
        }

        // Login endpoint: wp-login.php (exclude special flows that should stay on core page)
        if ( $this->is_wp_login_request() ) {
            if ( $action && in_array( $action, $this->excluded_login_actions, true ) ) {
                return;
            }

            $target = $this->get_login_target_url();
            if ( '' !== $target ) {
                wp_safe_redirect( $target, 302 );
                exit;
            }
        }
    }

    public function filter_login_url( string $login_url, string $redirect, bool $force_reauth ): string {
        $target = $this->get_login_target_url();
        if ( '' === $target ) {
            return $login_url;
        }

        if ( $redirect ) {
            $target = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $target );
        }

        if ( $force_reauth ) {
            $target = add_query_arg( 'reauth', '1', $target );
        }

        return $target;
    }

    public function filter_register_url( string $register_url ): string {
        $target = $this->get_registration_target_url();
        return $target ?: $register_url;
    }
}

