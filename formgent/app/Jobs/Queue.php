<?php

namespace FormGent\App\Jobs;

defined( "ABSPATH" ) || exit;

use FormGent\App\EnumeratedList\QueueStatus;
use FormGent\App\Repositories\QueueRepository;
use FormGent\WpMVC\Queue\Sequence;

class Queue extends Sequence {
    protected $prefix = 'formgent';

    protected $action = 'queue'; 

    protected function sleep_on_rest_time() {
        return true;
    }

    protected function triggered_error( ?array $error ) {
        update_option( 'formgent_lock_queue', 1 );
    }

    protected function get_item( $item ) {
        $repository = formgent_singleton( QueueRepository::class );
        $queue      = $repository->get_by_first_queue();

        if ( ! $queue ) {
            return false;
        }

        $repository->update_status( $queue->id, QueueStatus::IN_PROGRESS );

        $item['queue'] = $queue;

        return $item;
    }

    protected function perform_sequence_task( $item ) {
        if ( empty( $item['queue'] ) ) {
            return true;
        }

        $queue      = $item['queue'];
        $repository = formgent_singleton( QueueRepository::class );

        $is_queue_processed = false;

        do_action(
            "formgent_process_{$queue->task_type}", $queue, function( $status ) use ( $repository, $queue, &$is_queue_processed ) {
                if ( QueueStatus::COMPLETED === $status ) {
                    $repository->delete_by_id( $queue->id );
                } else {
                    $repository->update_status( $queue->id, $status );
                }
                $is_queue_processed = true;
            } 
        );

        if ( ! $is_queue_processed ) {
            $repository->update_status( $queue->id, QueueStatus::FAILED );
        }

        return $item;
    }

    public function dispatch_queue() {
        if ( $this->is_active() ) {
            $lock = get_option( 'formgent_lock_queue', 0 );

            if ( 1 == $lock ) {
                update_option( 'formgent_lock_queue', 0 );
                $this->unlock_process()->dispatch();
            }

            return;
        }

        $this->push_to_queue( [] )->save()->dispatch();
    }
}