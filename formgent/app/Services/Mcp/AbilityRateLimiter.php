<?php

namespace FormGent\App\Services\Mcp;

defined( 'ABSPATH' ) || exit;

use WP_Error;

/**
 * Bounded per-user/site/ability fixed-window limiter.
 */
class AbilityRateLimiter {
    private const WINDOW = 60;

    /**
     * @return true|WP_Error
     */
    public function consume( string $ability_id, string $rate_class = 'read' ) {
        $limit = $this->get_limit( $ability_id, $rate_class );

        if ( 1 > $limit ) {
            return true;
        }

        $window = (int) floor( time() / self::WINDOW );
        $key    = 'mcp_rate_' . md5( get_current_blog_id() . '|' . get_current_user_id() . '|' . $ability_id . '|' . $window );
        $count  = $this->increment( $key );

        if ( $count > $limit ) {
            return McpErrorFactory::rate_limited( self::WINDOW - ( time() % self::WINDOW ) );
        }

        return true;
    }

    private function get_limit( string $ability_id, string $rate_class ): int {
        $limits = [
            'read'        => 60,
            'response'    => 30,
            'write'       => 20,
            'bulk'        => 10,
            'destructive' => 5,
        ];

        $limit = $limits[$rate_class] ?? $limits['read'];

        return max( 0, (int) apply_filters( 'formgent_mcp_rate_limit', $limit, $ability_id, $rate_class ) );
    }

    private function increment( string $key ): int {
        if ( wp_using_ext_object_cache() ) {
            if ( wp_cache_add( $key, 1, 'formgent', self::WINDOW + 1 ) ) {
                return 1;
            }

            $count = wp_cache_incr( $key, 1, 'formgent' );

            return false === $count ? 1 : (int) $count;
        }

        $lock_key = 'formgent_' . $key . '_lock';
        $token    = wp_generate_uuid4();

        if ( ! add_option( $lock_key, [ 'token' => $token, 'time' => time() ], '', false ) ) {
            $lock = get_option( $lock_key, [] );

            if ( ! is_array( $lock ) || ! isset( $lock['time'] ) || time() - (int) $lock['time'] <= 5 ) {
                // Deny conservatively instead of allowing concurrent requests to bypass the limit.
                return PHP_INT_MAX;
            }

            delete_option( $lock_key );

            if ( ! add_option( $lock_key, [ 'token' => $token, 'time' => time() ], '', false ) ) {
                return PHP_INT_MAX;
            }
        }

        try {
            $count = (int) get_site_transient( $key );
            $count++;
            set_site_transient( $key, $count, self::WINDOW + 1 );
        } finally {
            $lock = get_option( $lock_key, [] );

            if ( is_array( $lock ) && isset( $lock['token'] ) && hash_equals( (string) $lock['token'], $token ) ) {
                delete_option( $lock_key );
            }
        }

        return $count;
    }
}
