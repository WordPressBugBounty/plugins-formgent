<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use Exception;
use stdClass;
use FormGent\App\DTO\QueueDTO;
use FormGent\App\DTO\AnswerFieldDTO;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\App\Integrations\Mailchimp\Mailchimp as MailchimpService;
use FormGent\App\EnumeratedList\QueueStatus;

class MailchimpProvider implements Provider {
    const TASK_TYPE = 'mailchimp';

    protected ?MailchimpService $mailchimp = null;

    public function boot() {   
        $task_type = self::TASK_TYPE;
        add_action( "formgent_process_{$task_type}", [ $this, 'process_task' ], 10, 2 );
        add_action( 'formgent_push_queue_items', [ $this, 'push_queue_items' ], 10, 3 );
    }

    public function push_queue_items( array &$dtos, int $response_id, stdClass $form ) {
        if ( ! formgent_is_mailchimp_enabled() ) {
            return;
        }

        $response = formgent_response_repository()->get_by_id( $response_id );

        if ( ! $response ) {
            return;
        }

        if ( '1' !== strval( $response->is_completed ) ) {
            return;
        }

        $feeds = formgent_mailchimp_repository()->get_feeds( $form->ID );

        if ( empty( $feeds ) ) {
            return;
        }

        foreach ( $feeds as $feed ) {
            $dtos[] = ( new QueueDTO )->set_form_id( $form->ID )->set_response_id( $response_id )->set_task_type( self::TASK_TYPE )->set_task_id( $feed->id );
        }
    }

    public function process_task( stdClass $queue, callable $callback ) {
        if ( ! formgent_is_mailchimp_enabled() ) {
            $callback( QueueStatus::SKIPPED );
            return;
        }

        $response = formgent_response_repository()->get_response_dto( $queue->response_id );

        if ( ! $response ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        $feed = \FormGent\App\Models\MailchimpFeed::query( 'feed' )->where( 'feed.id', $queue->task_id )->first();

        if ( ! $feed ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        $is_passed = formgent_is_passed_conditional_logic( $response, $feed->condition_status, $feed->condition_type, $feed->conditions );

        if ( ! apply_filters( 'formgent_is_allowed_mailchimp_feed', $is_passed, $feed, $response ) ) {
            $callback( QueueStatus::SKIPPED );
            return;
        }

        try {
            $this->process_feed( $feed, $queue->response_id );
            $callback( QueueStatus::COMPLETED );
        } catch ( Exception $e ) {
            $callback( QueueStatus::FAILED );
        }
    }

    protected function process_feed( stdClass $feed, int $response_id ) {
        $this->mailchimp = formgent_singleton( MailchimpService::class );

        $merge_fields = $this->get_list_merge_fields( $feed, $response_id );

        if ( empty( $merge_fields ) ) {
            throw new Exception( esc_html__( 'Merge fields is empty', 'formgent' ) );
        }

        if ( empty( $merge_fields['EMAIL'] ) ) {
            throw new Exception( esc_html__( 'Email field is empty', 'formgent' ) );
        }

        $email = $merge_fields['EMAIL'];

        unset( $merge_fields['EMAIL'] );

        $data = [
            'email_address' => $email,
            'merge_fields'  => $merge_fields,
            'vip'           => '1' === strval( $feed->mark_as_vip ),
        ];

        $this->add_list_member( $feed, $email, $data );
    }

    protected function get_list_merge_fields( $feed, $response_id ) {
        $field_mapping = json_decode( $feed->field_mapping, true );

        if ( empty( $field_mapping ) ) {
            return null;
        }

        $answers = formgent_get_form_answers( $feed->form_id, $response_id );

        $this->fill_field_map_with_answer( $field_mapping, $answers );

        return $field_mapping;
    }

    protected function fill_field_map_with_answer( array &$fields_map, array $answers ) {
        if ( ! $this->mailchimp ) {
            return;
        }

        foreach ( $fields_map as $field_tag => $field_id ) {
            if ( ! isset( $answers[ $field_id ] ) ) {
                unset( $fields_map[ $field_tag ] );
                continue;
            }

            $option_value_type = $field_tag === 'ADDRESS.COUNTRY' ? 'value' : 'label';
            $value             = $this->get_field_value( $answers[ $field_id ], $option_value_type );

            if ( empty( $value ) ) {
                unset( $fields_map[ $field_tag ] );
                continue;
            }

            $fields_map[ $field_tag ] = $value;
        }

        $this->parse_address_data( $fields_map );
    }

    protected function parse_address_data( array &$merge_fields ) {
        $address = [];

        $field_map = [
            'ADDRESS.ADDR1'   => 'addr1',
            'ADDRESS.ADDR2'   => 'addr2',
            'ADDRESS.CITY'    => 'city',
            'ADDRESS.STATE'   => 'state',
            'ADDRESS.ZIP'     => 'zip',
            'ADDRESS.COUNTRY' => 'country',
        ];

        foreach ( $field_map as $alias_key => $main_key ) {
            if ( isset( $merge_fields[ $alias_key ] ) ) {
                $address[ $main_key ] = $merge_fields[ $alias_key ];
                unset( $merge_fields[ $alias_key ] );
            }
        }

        $required_keys = [ 'addr1', 'city', 'state', 'zip' ];

        foreach ( $required_keys as $required_key ) {
            if ( empty( $address[ $required_key ] ) ) {
                return;
            }
        }

        $merge_fields['ADDRESS'] = $address;
    }

    protected function get_field_value( AnswerFieldDTO $answer, string $option_value_type = 'label' ) {
        if ( 'label' === $option_value_type && ( 'single-choice' === $answer->get_field_type() || 'dropdown' === $answer->get_field_type() ) ) {
            foreach ( $answer->get_options() as $option ) {
                if ( $option['value'] === $answer->get_value() ) {
                    return $option['label'];
                }
            }
        }

        return $answer->get_value();
    }

    protected function add_list_member( stdClass $feed, string $email, array $data ) {
        if ( ! $this->mailchimp ) {
            return;
        }

        $tags = ! empty( $feed->tags ) ? json_decode( $feed->tags, true ) : [];

        if ( ! empty( $tags ) ) {
            $data['tags'] = $tags;
        }

        $member_exists = $this->mailchimp->is_exists_list_member( $feed->list_id, $email );

        if ( $member_exists ) {
            $resubscribe_existing_contact = '1' === strval( $feed->resubscribe_existing_contact );

            if ( '1' === strval( $feed->update_existing_contact ) ) {
                if ( $resubscribe_existing_contact ) {
                    $data['status'] = 'subscribed';
                }

                $this->mailchimp->update_list_member( $feed->list_id, $data, true );
                return;
            }

            if ( $resubscribe_existing_contact ) {
                $this->mailchimp->update_list_member( $feed->list_id, [ 'status' => 'subscribed' ], true );
            }
        } else {
            $data['status'] = '1' === strval( $feed->double_opt_in ) ? 'pending' : 'subscribed';
            $this->mailchimp->add_list_member( $feed->list_id, $data, true );
        }
    }
}