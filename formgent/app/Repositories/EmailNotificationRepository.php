<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\App\Models\EmailNotification;
use FormGent\App\DTO\EmailNotificationReadDTO;
use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;

class EmailNotificationRepository extends Repository {
    public function get_query_builder() : Builder {
        return EmailNotification::query();
    }

    public function get( EmailNotificationReadDTO $dto ) {
        $query = $this->get_query_builder();

        if ( $dto->is_initialized( 'form_id' ) ) {
            $query->where( 'form_id', $dto->get_form_id() );
        }

        if ( $dto->is_initialized( 'sort_by' ) ) {
            switch ( $dto->get_sort_by() ) {
                case 'last_modified':
                    $query->order_by( 'updated_at', 'desc' );
                    break;
                case 'date_created_asc':
                    $query->order_by( 'created_at', 'asc' );
                    break;
                case 'date_created_desc':
                    $query->order_by( 'created_at', 'desc' );
                    break;
                case 'alphabetical':
                    $query->order_by( 'name', 'asc' );
                    break;
            }
        }

        $count_query = clone $query;

        return [
            'total'  => $count_query->count(),
            'emails' => $query->pagination( $dto->get_page(), $dto->get_per_page() )
        ];
    }

    public function get_by_form_id( int $form_id, string $status = 'publish' ) {
        return EmailNotification::query()->where( 'form_id', $form_id )->where( 'status', $status )->get();
    }

    public function update_status( int $id, string $status ) {
        return EmailNotification::query()->where( 'id', $id )->update(
            [
                'status' => $status
            ] 
        );
    }
}