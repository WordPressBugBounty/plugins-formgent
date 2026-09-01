<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Error;

/**
 * Reserves and commits AI-created form quota without double-counting failures.
 */
class AiFormQuotaService {
    private const COUNT_OPTION = 'formgent_ai_created_form';
    private const LOCK_OPTION  = 'formgent_mcp_ai_quota_lock';

    /** @var array<string,bool> */
    private array $reservations = [];

    /** @return string|WP_Error */
    public function reserve() {
        $lock = $this->acquire_lock();

        if ( is_wp_error( $lock ) ) {
            return $lock;
        }

        try {
            $count = max( 0, (int) get_option( self::COUNT_OPTION, 0 ) );
            $limit = max( 0, (int) apply_filters( 'formgent_mcp_ai_form_limit', 5 ) );

            if ( ! function_exists( 'formgent_pro' ) && $count >= $limit ) {
                return McpErrorFactory::limit_exceeded( esc_html__( 'The free AI form creation limit has been reached.', 'formgent' ) );
            }

            $reserved = update_option( self::COUNT_OPTION, $count + 1, false );

            if ( ! $reserved && $count + 1 !== (int) get_option( self::COUNT_OPTION, 0 ) ) {
                return McpErrorFactory::internal();
            }

            $token                      = wp_generate_uuid4();
            $this->reservations[$token] = true;

            return $token;
        } finally {
            $this->release_lock( $lock );
        }
    }

    public function commit( string $token ): void {
        unset( $this->reservations[$token] );
    }

    public function rollback( string $token ): void {
        if ( ! isset( $this->reservations[$token] ) ) {
            return;
        }

        $lock = $this->acquire_lock();

        if ( is_wp_error( $lock ) ) {
            do_action( 'formgent_mcp_quota_rollback_failed', 'lock_unavailable' );
            return;
        }

        try {
            $count   = max( 0, (int) get_option( self::COUNT_OPTION, 0 ) );
            $target  = max( 0, $count - 1 );
            $updated = update_option( self::COUNT_OPTION, $target, false );

            if ( ! $updated && $target !== (int) get_option( self::COUNT_OPTION, 0 ) ) {
                do_action( 'formgent_mcp_quota_rollback_failed', 'option_update_failed' );
                return;
            }

            unset( $this->reservations[$token] );
        } finally {
            $this->release_lock( $lock );
        }
    }

    /** @return string|WP_Error */
    private function acquire_lock() {
        $token = wp_generate_uuid4();
        $value = [
            'token' => $token,
            'time'  => time(),
        ];

        if ( add_option( self::LOCK_OPTION, $value, '', false ) ) {
            return $token;
        }

        $current = get_option( self::LOCK_OPTION, [] );

        if ( is_array( $current ) && isset( $current['time'] ) && time() - (int) $current['time'] > 30 ) {
            delete_option( self::LOCK_OPTION );

            if ( add_option( self::LOCK_OPTION, $value, '', false ) ) {
                return $token;
            }
        }

        return McpErrorFactory::conflict( esc_html__( 'Form creation is busy. Try again.', 'formgent' ) );
    }

    private function release_lock( string $token ): void {
        $current = get_option( self::LOCK_OPTION, [] );

        if ( is_array( $current ) && isset( $current['token'] ) && hash_equals( (string) $current['token'], $token ) ) {
            delete_option( self::LOCK_OPTION );
        }
    }
}
