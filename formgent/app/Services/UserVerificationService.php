<?php

namespace FormGent\App\Services;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use WP_User;

class UserVerificationService {
    public const METHOD_USER_EMAIL = 'user_email';
    public const METHOD_MANUAL     = 'manual';

    public const STATUS_VERIFIED       = 'verified';
    public const STATUS_PENDING_EMAIL  = 'pending_email';
    public const STATUS_PENDING_MANUAL = 'pending_manual';

    private const STATUS_META            = '_formgent_verification_status';
    private const METHOD_META            = '_formgent_verification_method';
    private const TOKEN_HASH_META        = '_formgent_verification_token_hash';
    private const TOKEN_EXPIRES_AT_META  = '_formgent_verification_expires_at';
    private const FORM_ID_META           = '_formgent_verification_form_id';
    private const REGISTRATION_ID_META   = '_formgent_verification_registration_id';
    private const CONFIRMATION_PAGE_META = '_formgent_verification_confirmation_page';

    public function setup_user_verification( int $user_id, array $registration, int $form_id = 0 ): array {
        if ( ! $this->requires_verification( $registration ) ) {
            return [
                'status'  => self::STATUS_VERIFIED,
                'message' => '',
            ];
        }

        $method = $this->get_verification_method( $registration );

        if ( self::METHOD_MANUAL === $method ) {
            $this->mark_pending( $user_id, self::STATUS_PENDING_MANUAL, $method, $registration, $form_id );

            return [
                'status'  => self::STATUS_PENDING_MANUAL,
                'message' => esc_html__( 'Registration successful. Your account is awaiting manual approval.', 'formgent' ),
            ];
        }

        $token = wp_generate_password( 64, false, false );
        $this->mark_pending( $user_id, self::STATUS_PENDING_EMAIL, $method, $registration, $form_id, $token );
        $this->send_verification_email( $user_id, $registration, $token );

        return [
            'status'  => self::STATUS_PENDING_EMAIL,
            'message' => esc_html__( 'Registration successful. Please check your email to verify your account.', 'formgent' ),
        ];
    }

    public function requires_verification( array $registration ): bool {
        return array_key_exists( 'auto_login', $registration ) && false === (bool) $registration['auto_login'];
    }

    public function get_verification_method( array $registration ): string {
        $method = isset( $registration['verification_method'] ) ? sanitize_key( (string) $registration['verification_method'] ) : self::METHOD_USER_EMAIL;

        if ( in_array( $method, [ self::METHOD_USER_EMAIL, self::METHOD_MANUAL ], true ) ) {
            return $method;
        }

        return self::METHOD_USER_EMAIL;
    }

    public function verify_user_by_token( int $user_id, string $token ) {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user instanceof WP_User || '' === $token ) {
            return new WP_Error( 'formgent_invalid_verification_link', esc_html__( 'Invalid verification link.', 'formgent' ) );
        }

        $status = $this->get_status( $user_id );
        if ( self::STATUS_VERIFIED === $status ) {
            return true;
        }

        if ( self::STATUS_PENDING_EMAIL !== $status ) {
            return new WP_Error( 'formgent_invalid_verification_link', esc_html__( 'Invalid verification link.', 'formgent' ) );
        }

        $expires_at = absint( get_user_meta( $user_id, self::TOKEN_EXPIRES_AT_META, true ) );
        if ( $expires_at && time() > $expires_at ) {
            return new WP_Error( 'formgent_expired_verification_link', esc_html__( 'Verification link has expired.', 'formgent' ) );
        }

        $stored_hash = (string) get_user_meta( $user_id, self::TOKEN_HASH_META, true );
        if ( '' === $stored_hash || ! hash_equals( $stored_hash, $this->hash_token( $token ) ) ) {
            return new WP_Error( 'formgent_invalid_verification_link', esc_html__( 'Invalid verification link.', 'formgent' ) );
        }

        $this->mark_verified( $user_id );

        return true;
    }

    public function maybe_block_pending_user_login( $user ) {
        if ( ! $user instanceof WP_User ) {
            return $user;
        }

        $status = $this->get_status( (int) $user->ID );
        if ( self::STATUS_PENDING_EMAIL === $status ) {
            return new WP_Error(
                'formgent_registration_pending_email',
                esc_html__( 'Please verify your account from the email we sent before signing in.', 'formgent' )
            );
        }

        if ( self::STATUS_PENDING_MANUAL === $status ) {
            return new WP_Error(
                'formgent_registration_pending_manual',
                esc_html__( 'Your account is awaiting manual approval.', 'formgent' )
            );
        }

        return $user;
    }

    public function get_login_error_message( WP_Error $error ): string {
        $code = $error->get_error_code();

        if ( 'formgent_registration_pending_email' === $code || 'formgent_registration_pending_manual' === $code ) {
            return $error->get_error_message();
        }

        return esc_html__( 'Invalid username/email or password.', 'formgent' );
    }

    public function get_status( int $user_id ): string {
        return sanitize_key( (string) get_user_meta( $user_id, self::STATUS_META, true ) );
    }

    public function mark_verified( int $user_id ): void {
        update_user_meta( $user_id, self::STATUS_META, self::STATUS_VERIFIED );
        delete_user_meta( $user_id, self::TOKEN_HASH_META );
        delete_user_meta( $user_id, self::TOKEN_EXPIRES_AT_META );
    }

    public function get_redirect_url( string $result, int $user_id = 0 ): string {
        $page_id = $user_id ? absint( get_user_meta( $user_id, self::CONFIRMATION_PAGE_META, true ) ) : 0;
        $url     = $page_id ? get_permalink( $page_id ) : home_url( '/' );

        if ( ! $url ) {
            $url = home_url( '/' );
        }

        return add_query_arg( 'formgent_verification', sanitize_key( $result ), $url );
    }

    public function render_profile_field( WP_User $user ): void {
        $status = $this->get_status( (int) $user->ID );
        if ( '' === $status ) {
            return;
        }

        $labels = [
            self::STATUS_VERIFIED       => esc_html__( 'Verified', 'formgent' ),
            self::STATUS_PENDING_EMAIL  => esc_html__( 'Pending email verification', 'formgent' ),
            self::STATUS_PENDING_MANUAL => esc_html__( 'Pending manual approval', 'formgent' ),
        ];

        ?>
        <h2><?php esc_html_e( 'FormGent account verification', 'formgent' ); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><?php esc_html_e( 'Verification status', 'formgent' ); ?></th>
                <td>
                    <p><?php echo esc_html( $labels[ $status ] ?? $status ); ?></p>
                    <?php if ( self::STATUS_VERIFIED !== $status && current_user_can( 'edit_user', $user->ID ) ) : ?>
                        <label>
                            <input type="checkbox" name="formgent_mark_user_verified" value="1" />
                            <?php esc_html_e( 'Mark this user as verified', 'formgent' ); ?>
                        </label>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_profile_field( int $user_id ): void {
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return;
        }

        // WordPress core verifies the user profile form nonce before this hook runs.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( empty( $_POST['formgent_mark_user_verified'] ) ) {
            return;
        }

        $this->mark_verified( $user_id );
    }

    private function mark_pending( int $user_id, string $status, string $method, array $registration, int $form_id, string $token = '' ): void {
        update_user_meta( $user_id, self::STATUS_META, $status );
        update_user_meta( $user_id, self::METHOD_META, $method );
        update_user_meta( $user_id, self::FORM_ID_META, $form_id );
        update_user_meta( $user_id, self::REGISTRATION_ID_META, absint( $registration['id'] ?? 0 ) );
        update_user_meta( $user_id, self::CONFIRMATION_PAGE_META, absint( $registration['verification_confirmation_page'] ?? 0 ) );

        if ( '' === $token ) {
            delete_user_meta( $user_id, self::TOKEN_HASH_META );
            delete_user_meta( $user_id, self::TOKEN_EXPIRES_AT_META );
            return;
        }

        update_user_meta( $user_id, self::TOKEN_HASH_META, $this->hash_token( $token ) );
        update_user_meta( $user_id, self::TOKEN_EXPIRES_AT_META, time() + DAY_IN_SECONDS * 2 );
    }

    private function send_verification_email( int $user_id, array $registration, string $token ): bool {
        $user = get_user_by( 'id', $user_id );
        if ( ! $user instanceof WP_User ) {
            return false;
        }

        $template = isset( $registration['verification_email_template'] ) ? (string) $registration['verification_email_template'] : '';
        if ( '' === trim( wp_strip_all_tags( $template ) ) ) {
            $template = $this->get_default_email_template();
        }

        $subject = apply_filters(
            'formgent_user_verification_email_subject',
            esc_html__( 'Verify your account', 'formgent' ),
            $user,
            $registration
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        return (bool) wp_mail(
            $user->user_email,
            $subject,
            $this->render_email_template( $template, $user, $this->get_verification_url( (int) $user->ID, $token ) ),
            $headers
        );
    }

    private function render_email_template( string $template, WP_User $user, string $verification_url ): string {
        $replacements = [
            '{{user_name}}'             => $user->display_name ?: $user->user_login,
            '{{user_email}}'            => $user->user_email,
            '{{user_verification_url}}' => esc_url( $verification_url ),
            '{{site_url}}'              => home_url( '/' ),
            '{{site_name}}'             => get_bloginfo( 'name' ),
        ];

        return wp_kses_post( str_replace( array_keys( $replacements ), array_values( $replacements ), $template ) );
    }

    private function get_verification_url( int $user_id, string $token ): string {
        return add_query_arg(
            [
                'user_id' => $user_id,
                'token'   => rawurlencode( $token ),
            ],
            rest_url( 'formgent/user-verification/confirm' )
        );
    }

    private function get_default_email_template(): string {
        return '<p>' . esc_html__( 'Hi {{user_name}},', 'formgent' ) . '</p><p>' . esc_html__( 'Please verify your account by clicking the link below:', 'formgent' ) . '</p><p><a href="{{user_verification_url}}">' . esc_html__( 'Verify account', 'formgent' ) . '</a></p>';
    }

    private function hash_token( string $token ): string {
        return hash_hmac( 'sha256', $token, wp_salt( 'auth' ) );
    }
}
