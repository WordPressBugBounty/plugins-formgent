<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

use FormGent\App\Models\MailchimpFeed;
use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\App\DTO\Mailchimp\FeedDTO;
use FormGent\WpMVC\Exceptions\Exception;
use stdClass;

class MailchimpRepository extends Repository {
    public function get_query_builder(): Builder {
        return MailchimpFeed::query();
    }

    public function get_feeds( $form_id ): array {
        $items = $this->get_query_builder()->where( 'form_id', $form_id )->order_by_desc( 'id' )->get();

        return array_map( [ $this, 'prepare_item' ], $items );
    }

    public function create_feed( FeedDTO $dto ) {
        return $this->get_query_builder()->insert_get_id( $this->process_values( $dto->to_array() ) );
    }

    public function update_feed( FeedDTO $dto ) {
        return $this->get_query_builder()->where( 'id', $dto->get_id() )->update( $this->process_values( $dto->to_array() ) );
    }

    public function update_status( int $id, string $status ) {
        $item = $this->get_by_id( $id );

        if ( ! $item ) {
            throw new Exception( esc_html__( 'The item not found.', 'formgent' ), 404 );
        }

        return $this->get_query_builder()->where( 'id', $id )->update(
            [
                'status' => $status
            ]
        );
    }

    public function prepare_item( stdClass $item ): stdClass {
        $item->id                           = (int) $item->id;
        $item->form_id                      = (int) $item->form_id;
        $item->double_opt_in                = (int) $item->double_opt_in;
        $item->resubscribe_existing_contact = (int) $item->resubscribe_existing_contact;
        $item->update_existing_contact      = (int) $item->update_existing_contact;
        $item->mark_as_vip                  = (int) $item->mark_as_vip;
        $item->condition_status             = (int) $item->condition_status;
        $item->status                       = (int) $item->status;

        return $item;
    }
}