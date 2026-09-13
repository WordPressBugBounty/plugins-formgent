<?php

namespace FormGent\App\Support\Routing;

defined( 'ABSPATH' ) || exit;

use WP_Error;

/**
 * Validates a configurable, single-segment WordPress rewrite base.
 *
 * This class deliberately has no FormGent settings dependencies so the same
 * contract can be reused by other plugins that expose configurable URL bases.
 */
final class RewriteBase {
    public const MAX_LENGTH = 64;

    /**
     * WordPress and server paths that should not be claimed by a plugin.
     *
     * @return array<int,string>
     */
    public static function reserved(): array {
        return [
            'attachment',
            'author',
            'category',
            'comments',
            'embed',
            'feed',
            'page',
            'search',
            'tag',
            'wp-admin',
            'wp-content',
            'wp-includes',
            'wp-json',
            'wp-login-php',
        ];
    }

    /**
     * Return a normalized base or a validation error.
     *
     * Leading and trailing slashes are accepted for convenience, but nested
     * paths are rejected so every adopter gets the same predictable contract.
     *
     * @param mixed                $value    Candidate setting value.
     * @param array<int,string>    $reserved Additional reserved bases.
     * @param array<string,string> $messages Optional adopter-owned messages keyed by error code.
     * @return string|WP_Error
     */
    public static function validate( $value, array $reserved = [], array $messages = [] ) {
        if ( ! is_scalar( $value ) ) {
            return self::error(
                'invalid_rewrite_base',
                'The URL base must be a single text value.',
                $messages
            );
        }

        $candidate = trim( (string) $value );
        $candidate = trim( $candidate, '/' );

        if ( '' === $candidate ) {
            return self::error(
                'empty_rewrite_base',
                'The URL base cannot be empty.',
                $messages
            );
        }

        if ( false !== strpos( $candidate, '/' ) ) {
            return self::error(
                'nested_rewrite_base',
                'Use one URL segment without additional slashes.',
                $messages
            );
        }

        $base = sanitize_title( $candidate );

        if ( '' === $base ) {
            return self::error(
                'invalid_rewrite_base',
                'Enter a URL base containing letters or numbers.',
                $messages
            );
        }

        if ( self::MAX_LENGTH < strlen( $base ) ) {
            return self::error(
                'rewrite_base_too_long',
                'The URL base must be 64 characters or fewer.',
                $messages
            );
        }

        $reserved = array_unique( array_merge( self::reserved(), $reserved ) );
        $reserved = array_map( 'sanitize_title', $reserved );

        if ( in_array( $base, $reserved, true ) ) {
            return self::error(
                'reserved_rewrite_base',
                'That URL base is reserved by WordPress. Choose another value.',
                $messages
            );
        }

        return $base;
    }

    /**
     * Normalize a stored or filtered value with a known-safe fallback.
     *
     * @param mixed             $value    Candidate value.
     * @param string            $fallback Known-safe fallback.
     * @param array<int,string> $reserved Additional reserved bases.
     */
    public static function normalize( $value, string $fallback, array $reserved = [] ): string {
        $validated = self::validate( $value, $reserved );

        if ( ! is_wp_error( $validated ) ) {
            return $validated;
        }

        $fallback = sanitize_title( $fallback );

        return '' === $fallback ? 'form' : $fallback;
    }

    /**
     * Create an error while allowing each adopting plugin to own translation.
     *
     * @param array<string,string> $messages Adopter-owned messages.
     */
    private static function error( string $code, string $fallback, array $messages ): WP_Error {
        return new WP_Error( $code, $messages[$code] ?? $fallback );
    }
}
