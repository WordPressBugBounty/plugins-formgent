<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\App\Models\Payment;

class PaymentRepository extends Repository {
    public function get_query_builder(): Builder {
        return Payment::query();
    }

    public function get_by_order_id_last( $order_id ) {
        return $this->get_query_builder()->where( 'order_id', $order_id )->order_by_desc( 'id' )->first();
    }

    public function first_by_response_id( int $response_id ) {
        return $this->get_query_builder()->where( 'response_id', $response_id )->first();
    }
}