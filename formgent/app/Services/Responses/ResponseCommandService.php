<?php

namespace FormGent\App\Services\Responses;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Repositories\ResponseRepository;
use FormGent\App\Services\Mcp\McpErrorFactory;
use FormGent\App\Services\Mcp\McpInputValidator;
use WP_Error;
use WP_REST_Request;
use Throwable;

/**
 * Performs bounded response state changes and permanent deletion.
 */
class ResponseCommandService {
    private ResponseRepository $repository;

    public function __construct( ResponseRepository $repository ) {
        $this->repository = $repository;
    }

    /**
     * @param array<int,int> $ids Response IDs.
     * @return array<string,mixed>|WP_Error
     */
    public function update_state( array $ids, string $operation ) {
        $ids = McpInputValidator::response_ids( $ids );

        if ( is_wp_error( $ids ) ) {
            return $ids;
        }

        $operations = [
            'mark_read'   => ['field' => 'is_read', 'value' => 1, 'state' => 'read'],
            'mark_unread' => ['field' => 'is_read', 'value' => 0, 'state' => 'unread'],
            'star'        => ['field' => 'is_starred', 'value' => 1, 'state' => 'starred'],
            'unstar'      => ['field' => 'is_starred', 'value' => 0, 'state' => 'unstarred'],
        ];

        if ( ! isset( $operations[$operation] ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The response state operation is invalid.', 'formgent' ) );
        }

        $records = $this->repository->get_mcp_records( $ids );
        $results = [];
        $errors  = [];
        $change  = $operations[$operation];

        foreach ( $ids as $id ) {
            if ( ! isset( $records[$id] ) ) {
                $errors[] = $this->not_found( $id );
                continue;
            }

            $current_value = (int) $records[$id]->{$change['field']};

            if ( $current_value === $change['value'] ) {
                $results[] = [
                    'id'    => $id,
                    'state' => $change['state'],
                ];
                continue;
            }

            $request = $this->state_request( $id, $change['field'], $change['value'] );
            $hook    = 'is_read' === $change['field'] ? 'read' : 'starred';

            try {
                do_action( 'formgent_before_update_response_' . $hook, $request );
            } catch ( Throwable $throwable ) {
                $errors[] = $this->internal_error( $id );
                continue;
            }

            $updated = 'is_read' === $change['field']
                ? $this->repository->update_read( $id, $change['value'] )
                : $this->repository->update_starred( $id, $change['value'] );

            if ( false === $updated ) {
                $errors[] = [
                    'id'      => $id,
                    'code'    => 'formgent_mcp_internal_error',
                    'message' => esc_html__( 'The response state could not be updated.', 'formgent' ),
                ];
                continue;
            }

            try {
                do_action( 'formgent_after_update_response_' . $hook, $request );
                do_action( 'formgent_mcp_after_update_response_state', $id, $operation );
            } catch ( Throwable $throwable ) {
                // The mutation succeeded. Do not report a retryable failure for observer errors.
            }
            $results[] = [
                'id'    => $id,
                'state' => $change['state'],
            ];
        }

        return $this->batch_result( $ids, $results, $errors );
    }

    /**
     * @param array<int,int> $ids Response IDs.
     * @return array<string,mixed>|WP_Error
     */
    public function delete( array $ids, bool $confirm ) {
        if ( ! $confirm ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Permanent response deletion requires confirm=true.', 'formgent' ) );
        }

        $ids = McpInputValidator::response_ids( $ids );

        if ( is_wp_error( $ids ) ) {
            return $ids;
        }

        $records = $this->repository->get_mcp_records( $ids );
        $valid   = [];
        $errors  = [];

        foreach ( $ids as $id ) {
            if ( isset( $records[$id] ) ) {
                $valid[] = $id;
            } else {
                $errors[] = $this->not_found( $id );
            }
        }

        if ( ! empty( $valid ) ) {
            $request = new WP_REST_Request( 'DELETE' );
            $request->set_param( 'ids', $valid );

            try {
                do_action( 'formgent_before_delete_all_responses', $valid, $request );
            } catch ( Throwable $throwable ) {
                return McpErrorFactory::internal();
            }

            $this->repository->delete_by_ids( $valid );

            try {
                do_action( 'formgent_after_delete_all_responses', $valid, $request );
            } catch ( Throwable $throwable ) {
                // Deletion already succeeded. A retry could destroy unrelated future data.
            }
        }

        $remaining = $this->repository->get_mcp_records( $valid );
        $results   = [];

        foreach ( $valid as $id ) {
            if ( isset( $remaining[$id] ) ) {
                $errors[] = [
                    'id'      => $id,
                    'code'    => 'formgent_mcp_internal_error',
                    'message' => esc_html__( 'The response could not be deleted.', 'formgent' ),
                ];
            } else {
                $results[] = [
                    'id'      => $id,
                    'deleted' => true,
                ];
            }
        }

        if ( ! empty( $results ) ) {
            do_action( 'formgent_mcp_after_delete_responses', array_column( $results, 'id' ) );
        }

        return $this->batch_result( $ids, $results, $errors );
    }

    private function state_request( int $id, string $field, int $value ): WP_REST_Request {
        $request = new WP_REST_Request( 'PATCH' );
        $request->set_param( 'id', $id );
        $request->set_param( $field, $value );

        return $request;
    }

    /** @return array<string,mixed> */
    private function not_found( int $id ): array {
        return [
            'id'      => $id,
            'code'    => 'formgent_mcp_response_not_found',
            'message' => esc_html__( 'Response not found.', 'formgent' ),
        ];
    }

    /** @return array<string,mixed> */
    private function internal_error( int $id ): array {
        return [
            'id'      => $id,
            'code'    => 'formgent_mcp_internal_error',
            'message' => esc_html__( 'The response state could not be updated.', 'formgent' ),
        ];
    }

    /**
     * @param array<int,int> $ids Requested IDs.
     * @param array<int,array<string,mixed>> $results Successful results.
     * @param array<int,array<string,mixed>> $errors Per-ID errors.
     * @return array<string,mixed>
     */
    private function batch_result( array $ids, array $results, array $errors ): array {
        return [
            'results'   => $results,
            'errors'    => $errors,
            'requested' => count( $ids ),
            'succeeded' => count( $results ),
            'failed'    => count( $errors ),
        ];
    }
}
