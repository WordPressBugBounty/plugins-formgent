<?php

namespace FormGent\App\Services\Mcp;

defined( 'ABSPATH' ) || exit;

use Throwable;
use WP_Error;

/**
 * Emits data-minimized MCP execution events.
 */
class AbilityAuditService {
    /**
     * @param array<string,mixed> $input Validated ability input.
     * @return array<string,mixed>
     */
    public function start( string $ability_id, array $input, bool $destructive ): array {
        $context = [
            'ability'     => $ability_id,
            'user_id'     => get_current_user_id(),
            'blog_id'     => get_current_blog_id(),
            'started_at'  => microtime( true ),
            'targets'     => $this->extract_targets( $input ),
            'destructive' => $destructive,
        ];

        try {
            do_action( 'formgent_before_mcp_ability_execute', $context );
        } catch ( Throwable $throwable ) {
            // Audit observers cannot interrupt the operation or expose their errors.
        }

        return $context;
    }

    /**
     * @param array<string,mixed> $context Audit context from start().
     * @param mixed $result Ability result.
     */
    public function finish( array $context, $result ): void {
        $error               = is_wp_error( $result ) ? $result : null;
        $context['ended_at'] = microtime( true );
        $context['duration'] = max( 0, $context['ended_at'] - (float) $context['started_at'] );
        $context['outcome']  = $this->outcome( $error );
        $context['count']    = $this->extract_count( $result );
        unset( $context['started_at'] );

        try {
            do_action( 'formgent_after_mcp_ability_execute', $context );
        } catch ( Throwable $throwable ) {
            // A completed operation must not become retryable because an observer failed.
        }
    }

    /**
     * @param array<string,mixed> $input Ability input.
     * @return array<int,int>
     */
    private function extract_targets( array $input ): array {
        $targets = [];

        foreach ( ['form_id', 'response_id'] as $key ) {
            if ( isset( $input[$key] ) && 0 < absint( $input[$key] ) ) {
                $targets[] = absint( $input[$key] );
            }
        }

        if ( isset( $input['response_ids'] ) && is_array( $input['response_ids'] ) ) {
            $targets = array_merge( $targets, array_map( 'absint', array_slice( $input['response_ids'], 0, 50 ) ) );
        }

        return array_values( array_unique( array_filter( $targets ) ) );
    }

    private function outcome( ?WP_Error $error ): string {
        if ( null === $error ) {
            return 'success';
        }

        $data   = $error->get_error_data();
        $status = is_array( $data ) ? (int) ( $data['status'] ?? 500 ) : 500;

        if ( 403 === $status ) {
            return 'denied';
        }

        if ( 400 === $status || 409 === $status || 422 === $status ) {
            return 'validation_error';
        }

        if ( 404 === $status ) {
            return 'not_found';
        }

        if ( 429 === $status ) {
            return 'rate_limited';
        }

        return 'server_error';
    }

    /**
     * @param mixed $result Ability result.
     */
    private function extract_count( $result ): int {
        if ( ! is_array( $result ) ) {
            return 0;
        }

        foreach ( ['succeeded', 'changed_count', 'deleted_count', 'total'] as $key ) {
            if ( isset( $result[$key] ) ) {
                return absint( $result[$key] );
            }
        }

        return 1;
    }
}
