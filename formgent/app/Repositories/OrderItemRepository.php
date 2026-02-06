<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\App\Models\OrderItem;
use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\WpMVC\Exceptions\Exception;

class OrderItemRepository extends Repository {
    public function get_query_builder(): Builder {
        return OrderItem::query();
    }

    public function get_by_order_id( $order_id ) {
        return $this->get_query_builder()->where( 'order_id', $order_id )->get();
    }
}