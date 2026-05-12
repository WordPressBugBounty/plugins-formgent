<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\UserVerificationService;
use FormGent\WpMVC\Contracts\Provider;
use WP_User;

class UserVerificationServiceProvider implements Provider {
    public function boot() {
        add_filter( 'wp_authenticate_user', [ $this, 'block_pending_user_login' ], 10, 1 );
        add_action( 'show_user_profile', [ $this, 'render_profile_field' ] );
        add_action( 'edit_user_profile', [ $this, 'render_profile_field' ] );
        add_action( 'personal_options_update', [ $this, 'save_profile_field' ] );
        add_action( 'edit_user_profile_update', [ $this, 'save_profile_field' ] );
    }

    public function block_pending_user_login( $user ) {
        return formgent_singleton( UserVerificationService::class )->maybe_block_pending_user_login( $user );
    }

    public function render_profile_field( WP_User $user ): void {
        formgent_singleton( UserVerificationService::class )->render_profile_field( $user );
    }

    public function save_profile_field( int $user_id ): void {
        formgent_singleton( UserVerificationService::class )->save_profile_field( $user_id );
    }
}
