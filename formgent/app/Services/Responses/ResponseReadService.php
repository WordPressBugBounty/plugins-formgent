<?php

namespace FormGent\App\Services\Responses;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AllResponsesReadDTO;
use FormGent\App\Repositories\ResponseRepository;
use FormGent\App\Services\Mcp\McpErrorFactory;
use FormGent\App\Services\Mcp\McpInputValidator;
use WP_Error;

/**
 * Provides bounded response summaries and mandatory-redacted details.
 */
class ResponseReadService {
    private ResponseRepository $repository;

    private ResponseRedactor $redactor;

    public function __construct( ResponseRepository $repository, ResponseRedactor $redactor ) {
        $this->repository = $repository;
        $this->redactor   = $redactor;
    }

    /** @param array<string,mixed> $input List filters. @return array<string,mixed>|WP_Error */
    public function list( array $input ) {
        $frame = McpInputValidator::date_frame( $input['date_type'] ?? 'all', $input['date_frame'] ?? [] );

        if ( is_wp_error( $frame ) ) {
            return $frame;
        }

        $dto = new AllResponsesReadDTO();
        $dto->set_page( max( 1, absint( $input['page'] ?? 1 ) ) );
        $dto->set_per_page( min( 100, max( 1, absint( $input['per_page'] ?? 20 ) ) ) );
        $dto->set_form_id( isset( $input['form_id'] ) ? absint( $input['form_id'] ) : null );
        $dto->set_is_read( isset( $input['is_read'] ) ? (int) $input['is_read'] : null );
        $dto->set_is_starred( isset( $input['is_starred'] ) ? (int) $input['is_starred'] : null );
        $dto->set_is_completed( isset( $input['is_completed'] ) ? (int) $input['is_completed'] : null );
        $dto->set_search( isset( $input['search'] ) ? sanitize_text_field( $input['search'] ) : null );
        $dto->set_date_type( $input['date_type'] ?? 'all' );
        $dto->set_date_frame( $frame );
        $dto->set_sort_by( $input['sort_by'] ?? 'date_created' );
        $dto->set_order( $input['order'] ?? 'desc' );
        $dto->set_order_by( 'id' );

        $data      = $this->repository->get_all( $dto );
        $responses = [];

        foreach ( $data['responses'] as $response ) {
            $responses[] = [
                'id'           => absint( $response['id'] ),
                'form_id'      => absint( $response['form_id'] ),
                'form_title'   => sanitize_text_field( $response['form_title'] ),
                'created_at'   => mysql2date( 'c', $response['submitted_on'], false ),
                'is_completed' => ! empty( $response['is_completed'] ),
                'is_read'      => ! empty( $response['is_read'] ),
                'is_starred'   => ! empty( $response['is_starred'] ),
            ];
        }

        $total = absint( $data['total'] );

        return [
            'responses'  => $responses,
            'pagination' => [
                'page'        => $dto->get_page(),
                'per_page'    => $dto->get_per_page(),
                'total_items' => $total,
                'total_pages' => (int) ceil( $total / $dto->get_per_page() ),
            ],
        ];
    }

    /** @return array<string,mixed>|WP_Error */
    public function get( int $response_id ) {
        $responses = $this->repository->get_mcp_details( [$response_id] );

        if ( ! isset( $responses[$response_id] ) ) {
            return McpErrorFactory::response_not_found();
        }

        return $this->redactor->response( $responses[$response_id] );
    }

    /** @param array<int,int> $ids Response IDs. @return array<string,mixed>|WP_Error */
    public function bulk( array $ids ) {
        $ids = McpInputValidator::response_ids( $ids );

        if ( is_wp_error( $ids ) ) {
            return $ids;
        }

        $found     = $this->repository->get_mcp_details( $ids );
        $responses = [];
        $errors    = [];

        foreach ( $ids as $id ) {
            if ( isset( $found[$id] ) ) {
                $responses[] = $this->redactor->response( $found[$id] );
            } else {
                $errors[] = [
                    'id'      => $id,
                    'code'    => 'formgent_mcp_response_not_found',
                    'message' => esc_html__( 'Response not found.', 'formgent' ),
                ];
            }
        }

        return [
            'responses' => $responses,
            'errors'    => $errors,
            'requested' => count( $ids ),
            'succeeded' => count( $responses ),
            'failed'    => count( $errors ),
        ];
    }
}
