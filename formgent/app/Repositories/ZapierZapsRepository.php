<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\App\Models\ZapierZaps;
use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;

class ZapierZapsRepository extends Repository {
    public function get_query_builder(): Builder {
        return ZapierZaps::query();
    }
    
    public function first( $zap_id, $form_id ) {
        return $this->get_query_builder()->where( 'form_id', $form_id )->where( 'zap_id', $zap_id )->first();
    }
}