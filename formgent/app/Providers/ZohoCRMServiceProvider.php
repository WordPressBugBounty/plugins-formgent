<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use stdClass;
use WP_REST_Request;
use FormGent\App\DTO\QueueDTO;
use FormGent\App\EnumeratedList\QueueStatus;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\App\Integrations\ZohoCRM\ZohoCRM;
use FormGent\App\Repositories\ZohoCRMFeedRepository;
use FormGent\App\Repositories\FormPresetFieldRepository;

class ZohoCRMServiceProvider implements Provider {
    const TASK_TYPE = 'zohocrm';

    public function boot() {
        add_action( 'admin_init', [$this, 'save_and_redirect_oauth_confirm'] );
        add_filter( 'formgent_rest_settings_saved_response', [$this, 'filter_rest_settings_saved_response'], 10, 2 );

        $task_type = self::TASK_TYPE;
        add_action( "formgent_process_{$task_type}", [$this, 'process_task'], 10, 2 );
        add_action( 'formgent_push_queue_items', [$this, 'push_queue_items'], 10, 3 );
    }

    public function save_and_redirect_oauth_confirm() {
        if ( ! isset( $_GET['fg_zohocrm_confirm'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $zohocrm = formgent_singleton( ZohoCRM::class );

        if ( ! isset( $_GET['code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_redirect( $zohocrm->get_auth_url() ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
            exit();
        }

        // Get the access token now
        $code   = sanitize_text_field( wp_unslash( $_GET['code'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tokens = $zohocrm->generate_access_token( $code );

        if ( is_wp_error( $tokens ) ) {
            wp_die(
                sprintf(
                    /* translators: %s: error message */
                    esc_html__( 'ZohoCRM connection failed (%s). Please try again.', 'formgent' ),
                    esc_html( $tokens->get_error_message() )
                ),
                esc_html__( 'ZohoCRM connection failed', 'formgent' ),
                [
                    'response'  => 400,
                    'link_text' => esc_html__( '« Back To ZohoCRM Integration', 'formgent' ),
                    'link_url'  => esc_url( admin_url( 'admin.php?page=formgent#/settings/integrations' ) ),
                ]
            );
        } else {
            $tokens['status'] = true;
            $zohocrm->set_settings( 'connected', true );
            $zohocrm->set_tokens( $tokens );
        }

        wp_redirect( admin_url( 'admin.php?page=formgent#/settings/integrations' ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
        exit();
    }

    public function filter_rest_settings_saved_response( $response, WP_REST_Request $request ) {
        if ( ! isset( $request['triggered_from'] ) || $request['triggered_from'] !== 'zoho-integration' ) {
            return $response;
        }

        $zohocrm = formgent_singleton( ZohoCRM::class );

        $response['redirect_url'] = $zohocrm->get_auth_url();

        return $response;
    }

    public function push_queue_items( array &$dtos, int $response_id, stdClass $form ) {
        if ( ! formgent_is_zoho_crm_enabled() ) {
            return;
        }

        $repository = formgent_singleton( ZohoCRMFeedRepository::class );
        $feeds      = $repository->get_by_form_id( $form->ID );

        if ( empty( $feeds ) ) {
            return [];
        }

        foreach ( $feeds as $feed ) {
            $dtos[] = ( new QueueDTO )->set_form_id( $form->ID )->set_response_id( $response_id )->set_task_type( self::TASK_TYPE )->set_task_id( $feed->id );
        }
    }

    public function process_task( stdClass $queue, callable $callback ) {
        if ( ! formgent_is_zoho_crm_enabled() ) {
            $callback( QueueStatus::COMPLETED );
            return;
        }

        $feed = \FormGent\App\Models\ZohoCRMFeed::query( 'feed' )->where( 'feed.id', $queue->task_id )->first();

        if ( ! $feed ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        $response = formgent_response_repository()->get_response_dto( $queue->response_id );

        if ( ! $response ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        $fields_map = $feed->fields;

        if ( empty( $fields_map ) ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        $zoho_fields_map = json_decode( $fields_map, true );
        $zoho_fields     = array_values( $zoho_fields_map );

        if ( empty( $zoho_fields ) ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        if ( $feed->condition_status ) {
            $response     = formgent_response_repository()->get_response_dto( $queue->response_id );
            $answers_data = formgent_get_form_answers( $feed->form_id, $queue->response_id, false );
            
            $condition = formgent_conditional_logic()
            ->set_response( $response )
            ->set_answers_items( $answers_data )
            ->set_logical_type( $feed->condition_type )
            ->set_conditions( json_decode( $feed->conditions, true ) );
            
            if ( ! apply_filters( 'formgent_is_allowed_zohocrm_feed', $condition->is_passed(), $feed, $response, $answers_data ) ) {
                $callback( QueueStatus::COMPLETED );
                return;
            }
        }

        $form_fields = formgent_singleton( FormPresetFieldRepository::class )->get_form_answers_flattened( $feed->form_id, $queue->response_id );

        $data_map = [];
        $tags     = null;

        foreach ( $form_fields as $id => $field ) {
            if ( ! isset( $zoho_fields_map[ $id ] ) ) {
                continue;
            }

            $zoho_field_name = $zoho_fields_map[ $id ];
            $value           = $field->get_value();

            if ( empty( $value ) ) {
                continue;
            }

            if ( $zoho_field_name === 'Tag' ) {
                $tags = $value;
                continue;
            }

            switch ( $field->get_field_type() ) {
                case 'email':
                    $value = sanitize_email( $value );
                    break;

                case 'date-picker':
                    if ( strstr( $value, '/' ) ) {
                        $value = str_replace( '/', '.', $value );
                    }
                    $date  = new \Datetime( $value );
                    $value = $date->format( 'Y-m-d' );
                    break;

                case 'textarea':
                    $value = sanitize_textarea_field( $value );
                    break;

                default:
                    $value = sanitize_text_field( $value );
                    break;
            }

            $data_map[ $zoho_field_name ] = $value;
        }

        $zohocrm = formgent_singleton( ZohoCRM::class );
        $client  = $zohocrm->get_client();

        $response = $client->insert_module_data( $feed->module, $data_map );

        if ( is_wp_error( $response ) ) {
            $callback( QueueStatus::FAILED );
            return;
        }

        $has_record_id = ( isset( $response['data'] ) && isset( $response['data'][0] ) && isset( $response['data'][0]['details'] ) && isset( $response['data'][0]['details']['id'] ) );

        if ( $tags && $has_record_id ) {
            $client->add_tags( $feed->module, $response['data'][0]['details']['id'], $tags );
        }

        $callback( QueueStatus::COMPLETED );
    }
}
