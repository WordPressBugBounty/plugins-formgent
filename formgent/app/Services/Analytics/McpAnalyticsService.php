<?php

namespace FormGent\App\Services\Analytics;

defined( 'ABSPATH' ) || exit;

use DateTimeImmutable;
use FormGent\App\Repositories\AnalyticRepository;
use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Error;
use WP_Post;

/**
 * Validates ranges and normalizes aggregate analytics for MCP consumers.
 */
class McpAnalyticsService {
    private AnalyticRepository $repository;

    public function __construct( AnalyticRepository $repository ) {
        $this->repository = $repository;
    }

    /**
     * @param array<string,mixed> $input Optional form and date filters.
     * @return array<string,mixed>|WP_Error
     */
    public function get( array $input ) {
        $form_id = isset( $input['form_id'] ) ? absint( $input['form_id'] ) : null;

        if ( null !== $form_id ) {
            $post = get_post( $form_id );

            if ( ! $post instanceof WP_Post || formgent_post_type() !== $post->post_type ) {
                return McpErrorFactory::form_not_found();
            }
        }

        $range = $this->range( $input['date_from'] ?? null, $input['date_to'] ?? null );

        if ( is_wp_error( $range ) ) {
            return $range;
        }

        $analytics = $this->repository->mcp_aggregate( $form_id, $range['query_from'], $range['query_to'] );

        return array_merge(
            $analytics,
            [
                'form_id' => $form_id ?: 0,
                'range'   => [
                    'from'        => $range['from'],
                    'to'          => $range['to'],
                    'views_scope' => 'lifetime',
                ],
            ]
        );
    }

    /**
     * @param mixed $from Public from date.
     * @param mixed $to Public to date.
     * @return array<string,string|null>|WP_Error
     */
    private function range( $from, $to ) {
        if ( null === $from && null === $to ) {
            return [
                'from'       => '',
                'to'         => '',
                'query_from' => null,
                'query_to'   => null,
            ];
        }

        if ( ! is_string( $from ) || ! is_string( $to ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Both analytics range dates are required.', 'formgent' ) );
        }

        $timezone  = wp_timezone();
        $from_date = DateTimeImmutable::createFromFormat( '!Y-m-d', $from, $timezone );
        $to_date   = DateTimeImmutable::createFromFormat( '!Y-m-d', $to, $timezone );

        if ( ! $from_date || ! $to_date || $from_date->format( 'Y-m-d' ) !== $from || $to_date->format( 'Y-m-d' ) !== $to || $from_date > $to_date ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The analytics date range is invalid.', 'formgent' ) );
        }

        if ( 366 < (int) $from_date->diff( $to_date )->format( '%a' ) + 1 ) {
            return McpErrorFactory::limit_exceeded( esc_html__( 'The analytics date range cannot exceed 366 days.', 'formgent' ) );
        }

        return [
            'from'       => $from,
            'to'         => $to,
            'query_from' => $from_date->format( 'Y-m-d 00:00:00' ),
            'query_to'   => $to_date->modify( '+1 day' )->format( 'Y-m-d 00:00:00' ),
        ];
    }
}
