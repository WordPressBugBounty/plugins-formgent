<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\App\Models\Spreadsheet;

class SpreadsheetRepository extends Repository {
    public function get_query_builder(): Builder {
        return Spreadsheet::query();
    }

    public function get( $form_id ) {
        return $this->get_query_builder()->where( 'form_id', $form_id )->order_by_desc( 'id' )->get();
    }

    public function get_by_form_id( int $form_id, string $status = 'publish' ) {
        return $this->get_query_builder()->where( 'form_id', $form_id )->where( 'status', $status )->get();
    }

    public function update_status( int $id, string $status ) {
        $form = $this->get_by_id( $id );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Spreadsheet not found.', 'formgent' ), 404 );
        }

        return $this->get_query_builder()->where( 'id', $id )->update(
            [
                'status' => $status
            ]
        );
    }
}