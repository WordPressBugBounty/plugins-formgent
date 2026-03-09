<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\ResponseLogDTO;
use FormGent\App\DTO\ResponseLogReadDTO;
use FormGent\App\Models\ResponseLog;

class ResponseLogRepository {
    public function get_paginated( ResponseLogReadDTO $dto ): array {
        $page     = max( 1, $dto->get_page() );
        $per_page = max( 1, $dto->get_per_page() );
        $offset   = ( $page - 1 ) * $per_page;
        $base     = ResponseLog::query()->where( 'response_id', $dto->get_response_id() );
        $total    = ( clone $base )->count();
        $logs     = $base->order_by( 'created_at', 'desc' )->limit( $per_page )->offset( $offset )->get();

        return compact( 'logs', 'total' );
    }

    public function create( ResponseLogDTO $dto ): int {
        return ResponseLog::query()->insert_get_id(
            [
                'response_id' => $dto->get_response_id(),
                'action'      => $dto->get_action(),
                'created_by'  => $dto->get_created_by(),
                'meta'        => $dto->get_meta(),
            ] 
        );
    }

    public function delete( int $response_id, int $id ): bool {
        return (bool) ResponseLog::query()
            ->where( 'id', $id )
            ->where( 'response_id', $response_id )
            ->delete();
    }
}
