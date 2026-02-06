<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\App\Models\Queue;
use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\App\DTO\QueueDTO;

class QueueRepository extends Repository {
    public function get_query_builder(): Builder {
        return Queue::query();
    }

    public function get_by_first_queue() {
        return $this->get_query_builder()->where( 'status', 'in_queue' )->first();
    }

    public function update_status( $id, $status ) {
        return $this->get_query_builder()->where( 'id', $id )->update( [ 'status' => $status ] );
    }

    /**
     * Create multiple queues
     *
     * @param array<QueueDTO> $dtos
     * @return int
     */
    public function create_many( array $dtos ) {
        return Queue::query()->insert(
            array_map(
                function( QueueDTO $dto ) {
                    return $dto->to_array();
                }, $dtos 
            ) 
        );
    }
}