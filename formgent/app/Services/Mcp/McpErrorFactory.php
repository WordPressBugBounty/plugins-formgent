<?php

namespace FormGent\App\Services\Mcp;

defined( 'ABSPATH' ) || exit;

use WP_Error;

/**
 * Creates stable, sanitized MCP errors.
 */
final class McpErrorFactory {
    public static function create( string $code, string $message, int $status, array $details = [] ): WP_Error {
        $data = ['status' => $status];

        if ( ! empty( $details ) ) {
            $data['details'] = array_slice( $details, 0, 100 );
        }

        return new WP_Error( $code, self::transport_message( $code, $message, $status ), $data );
    }

    public static function disabled(): WP_Error {
        return self::create( 'formgent_mcp_disabled', esc_html__( 'FormGent abilities are disabled.', 'formgent' ), 403 );
    }

    public static function scope_disabled( string $scope ): WP_Error {
        return self::create(
            'formgent_mcp_scope_disabled',
            sprintf(
                /* translators: %s: disabled MCP access scope. */
                esc_html__( 'The required FormGent MCP scope is disabled: %s.', 'formgent' ),
                sanitize_key( $scope )
            ),
            403
        );
    }

    public static function forbidden(): WP_Error {
        return self::create( 'formgent_mcp_forbidden', esc_html__( 'You are not allowed to perform this FormGent operation.', 'formgent' ), 403 );
    }

    public static function invalid_input( string $message, array $details = [] ): WP_Error {
        return self::create( 'formgent_mcp_invalid_input', $message, 400, $details );
    }

    public static function form_not_found(): WP_Error {
        return self::create( 'formgent_mcp_form_not_found', esc_html__( 'Form not found.', 'formgent' ), 404 );
    }

    public static function response_not_found(): WP_Error {
        return self::create( 'formgent_mcp_response_not_found', esc_html__( 'Response not found.', 'formgent' ), 404 );
    }

    public static function conflict( string $message ): WP_Error {
        return self::create( 'formgent_mcp_conflict', $message, 409 );
    }

    public static function limit_exceeded( string $message ): WP_Error {
        return self::create( 'formgent_mcp_limit_exceeded', $message, 422 );
    }

    public static function dependency_missing( string $message ): WP_Error {
        return self::create( 'formgent_mcp_dependency_missing', $message, 503 );
    }

    public static function rate_limited( int $retry_after ): WP_Error {
        return new WP_Error(
            'formgent_mcp_rate_limited',
            self::transport_message(
                'formgent_mcp_rate_limited',
                esc_html__( 'Too many FormGent MCP requests. Try again shortly.', 'formgent' ),
                429
            ),
            [
                'status'      => 429,
                'retry_after' => max( 1, $retry_after ),
            ]
        );
    }

    public static function internal(): WP_Error {
        return self::create( 'formgent_mcp_internal_error', esc_html__( 'FormGent could not complete the operation.', 'formgent' ), 500 );
    }

    /**
     * Keep the stable application code and status visible when an MCP
     * transport preserves only WP_Error::get_error_message().
     */
    private static function transport_message( string $code, string $message, int $status ): string {
        return sprintf(
            '%1$s [code=%2$s; status=%3$d]',
            $message,
            sanitize_key( $code ),
            $status
        );
    }
}
