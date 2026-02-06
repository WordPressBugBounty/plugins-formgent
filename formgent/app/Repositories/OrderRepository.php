<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\App\Models\Order;
use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;

class OrderRepository extends Repository {
    public function get_query_builder(): Builder {
        return Order::query();
    }

    public function first_by_response_id( int $response_id, bool $data = false ) {
        $query = $this->get_query_builder()->where( 'response_id', $response_id );

        if ( $data ) {
            $query->with(
                'payment', function( $query ) {
                    $query->order_by_desc( 'id' );
                }
            )->with( 'items' );
        }

        return $query->first();
    }
}