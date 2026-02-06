<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Contracts\Provider;
use FormGent\App\Repositories\QueueRepository;
use FormGent\App\Jobs\Queue;
use stdClass;

class QueueServiceProvider implements Provider {
    public Queue $background_process;

    public function __construct() {
        $this->background_process = formgent_singleton( Queue::class );
    }

    public function boot() {
        add_action( 'formgent_after_create_form_response', [$this, 'add_queue_data'], 10, 3 );
        add_action( 'formgent_after_submit_form', [$this, 'after_submit_form'], 10, 2 );
    }

    public function after_submit_form( int $response_id, stdClass $form ) {
        $this->add_queue_data( $response_id, $form );
    }

    public function add_queue_data( int $response_id, stdClass $form ) {
        $dtos = [];

        do_action_ref_array( 'formgent_push_queue_items', [&$dtos, $response_id, $form] );

        if ( empty( $dtos ) ) {
            return;
        }

        $repository = formgent_singleton( QueueRepository::class );
        $repository->create_many( $dtos );
    }
}
