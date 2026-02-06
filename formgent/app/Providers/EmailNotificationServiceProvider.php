<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\QueueDTO;
use FormGent\App\EnumeratedList\QueueStatus;
use FormGent\App\DTO\EmailNotificationDTO;
use FormGent\App\DTO\FormDTO;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\App\Repositories\EmailNotificationRepository;
use stdClass;

class EmailNotificationServiceProvider implements Provider {
    const TASK_TYPE = 'email_notification';

    public function boot() {
        $task_type = self::TASK_TYPE;
        add_action( "formgent_process_{$task_type}", [$this, 'process_task'], 10, 2 );
        add_action( 'formgent_push_queue_items', [$this, 'push_queue_items'], 10, 3 );
        add_action( "formgent_after_create_form", [$this, "after_create_form"] );
    }

    public function push_queue_items( array &$dtos, int $response_id, stdClass $form ) {
        $repository = formgent_singleton( EmailNotificationRepository::class );
        $emails     = $repository->get_by_form_id( $form->ID );

        if ( empty( $emails ) ) {
            return [];
        }

        foreach ( $emails as $email ) {
            $dtos[] = ( new QueueDTO )->set_form_id( $form->ID )->set_response_id( $response_id )->set_task_type( self::TASK_TYPE )->set_task_id( $email->id );
        }
    }

    public function process_task( stdClass $queue, callable $callback ) {
        $repository = formgent_singleton( EmailNotificationRepository::class );
        $email      = $repository->get_by_id( $queue->task_id );

        if ( ! $email ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        if ( ! apply_filters( 'formgent_is_allowed_sending_mail', true, $email, $queue ) ) {
            $callback( QueueStatus::COMPLETED );
            return;
        }

        $preset_field_repository = formgent_form_preset_field_repository();
        $form_answers_data       = formgent_get_form_answers( $queue->form_id, $queue->response_id, false );
        $response                = formgent_response_repository()->get_response_dto( $queue->response_id );

        $email->subject = formgent_replace_placeholders( $email->subject, $preset_field_repository, $form_answers_data, $response );
        $email->send_to = formgent_replace_placeholders( $email->send_to, $preset_field_repository, $form_answers_data, $response );
        $email->body    = formgent_replace_placeholders( $email->body, $preset_field_repository, $form_answers_data, $response );
        $email->body    = formgent_replace_html_dynamic_tags( $email->body, $queue->form_id, $queue->response_id );

        // Optional fields (support placeholders too)
        if ( ! empty( $email->cc ) ) {
            $email->cc = formgent_replace_placeholders( $email->cc, $preset_field_repository, $form_answers_data, $response );
        }

        if ( ! empty( $email->bcc ) ) {
            $email->bcc = formgent_replace_placeholders( $email->bcc, $preset_field_repository, $form_answers_data, $response );
        }

        if ( ! empty( $email->reply_to ) ) {
            $email->reply_to = formgent_replace_placeholders( $email->reply_to, $preset_field_repository, $form_answers_data, $response );
        }

        if ( ! empty( $email->from_name ) ) {
            $email->from_name = formgent_replace_placeholders( $email->from_name, $preset_field_repository, $form_answers_data, $response );
        }

        if ( ! empty( $email->from_email ) ) {
            $email->from_email = formgent_replace_placeholders( $email->from_email, $preset_field_repository, $form_answers_data, $response );
        }

        // Build the headers array
        $headers = [
            'Content-Type: text/html; charset=UTF-8'
        ];

        if ( ! empty( $email->cc ) ) {
            $headers[] = 'Cc: ' . $email->cc;
        }

        if ( ! empty( $email->bcc ) ) {
            $headers[] = 'Bcc: ' . $email->bcc;
        }

        if ( ! empty( $email->reply_to ) ) {
            $headers[] = 'Reply-To: ' . $email->reply_to;
        }

        if ( ! empty( $email->from_email ) ) {
            if ( ! empty( $email->from_name ) ) {
                $headers[] = 'From: ' . $email->from_name . ' <' . $email->from_email . '>';
            } else {
                $headers[] = 'From: ' . $email->from_email;
            }
        }

        $headers = apply_filters( 'formgent_email_headers', $headers, $email, $response, $form_answers_data, $queue );

        wp_mail( $email->send_to, $email->subject, $email->body, $headers );

        $callback( QueueStatus::COMPLETED );
    }

    public function after_create_form( FormDTO $form_dto ) {
        $dto = ( new EmailNotificationDTO )->set_form_id( $form_dto->get_id() )
        ->set_name( "Admin Notification Email" )
        ->set_send_to( "{{admin_email}}" )
        ->set_subject( "New Form Submission" )
        ->set_body( "You received a new response in the form of Formgent" )
        ->set_from_name( "{{site_name}}" )
        ->set_from_email( "{{admin_email}}" )
        ->set_reply_to( "{{admin_email}}" )
        ->set_status( "publish" );

        /**
         * @var EmailNotificationRepository $repository
         */
        $repository = formgent_singleton( EmailNotificationRepository::class );
        $repository->create( $dto );
    }
}