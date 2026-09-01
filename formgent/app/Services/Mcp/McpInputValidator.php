<?php

namespace FormGent\App\Services\Mcp;

defined( 'ABSPATH' ) || exit;

use DateTimeImmutable;
use WP_Error;

/**
 * Shared validation for bounded MCP identifiers and date frames.
 */
final class McpInputValidator {
    /**
     * Normalize bounded MCP pagination values.
     *
     * @param mixed $page Requested page.
     * @param mixed $per_page Requested page size.
     * @return array{page:int,per_page:int}
     */
    public static function pagination( $page, $per_page ): array {
        return [
            'page'     => max( 1, absint( $page ) ),
            'per_page' => min( 100, max( 1, absint( $per_page ) ) ),
        ];
    }

    /** @return array<int,int>|WP_Error */
    public static function response_ids( array $ids ) {
        if ( empty( $ids ) || 50 < count( $ids ) || count( $ids ) !== count( array_filter( $ids, 'is_int' ) ) ) {
            return McpErrorFactory::limit_exceeded( esc_html__( 'Response operations require between 1 and 50 positive unique IDs.', 'formgent' ) );
        }

        foreach ( $ids as $id ) {
            if ( 1 > $id ) {
                return McpErrorFactory::limit_exceeded( esc_html__( 'Response operations require between 1 and 50 positive unique IDs.', 'formgent' ) );
            }
        }

        $normalized = array_values( array_unique( $ids ) );

        if ( count( $normalized ) !== count( $ids ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Response IDs must be unique.', 'formgent' ) );
        }

        return $normalized;
    }

    /**
     * @param mixed $frame Date frame input.
     * @return array<string,string>|WP_Error
     */
    public static function date_frame( string $date_type, $frame, bool $timestamps = false ) {
        if ( 'date_frame' !== $date_type ) {
            return [];
        }

        if ( ! is_array( $frame ) || ! isset( $frame['from'], $frame['to'] ) || ! is_string( $frame['from'] ) || ! is_string( $frame['to'] ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A from and to date are required for a custom date frame.', 'formgent' ) );
        }

        $timezone = wp_timezone();
        $from     = DateTimeImmutable::createFromFormat( '!Y-m-d', $frame['from'], $timezone );
        $to       = DateTimeImmutable::createFromFormat( '!Y-m-d', $frame['to'], $timezone );

        if ( ! $from || ! $to || $from->format( 'Y-m-d' ) !== $frame['from'] || $to->format( 'Y-m-d' ) !== $frame['to'] || $from > $to ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The custom date frame is invalid.', 'formgent' ) );
        }

        return [
            'from' => $from->format( $timestamps ? 'Y-m-d 00:00:01' : 'Y-m-d' ),
            'to'   => $to->format( $timestamps ? 'Y-m-d 23:59:59' : 'Y-m-d' ),
        ];
    }
}
