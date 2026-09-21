<?php

namespace FormGent\App\Integrations\ZohoCRM;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ZohoCRMApi
{
    protected $account_url = '';

    protected $api_url = 'https://www.zohoapis.com/crm/v2/';

    protected $client_id = null;

    protected $client_secret = null;

    protected $callback_url = null;

    protected $settings = [];

    public function __construct( $account_url, $settings ) {
        if ( substr( $account_url, -1 ) == '/' ) {
            $account_url = substr( $account_url, 0, -1 );
        }

        $api_data_server              = explode( '.', $account_url );
        $api_data_server_country_code = end( $api_data_server );

        if ( $api_data_server_country_code === 'cn' || $api_data_server_country_code === 'au' ) {
            $this->api_url = 'https://www.zohoapis.com.' . end( $api_data_server ) . '/crm/v2/';
        } else {
            $this->api_url = 'https://www.zohoapis.' . end( $api_data_server ) . '/crm/v2/';
        }

        $this->account_url   = $account_url;
        $this->client_id     = $settings['client_id'];
        $this->client_secret = $settings['client_secret'];
        $this->settings      = $settings;
        $this->callback_url  = admin_url( '?fg_zohocrm_confirm=1' );
    }

    public function get_auth_url( string $code_challenge = '', string $state = '' ) {
        $query = [
            'scope'         => 'ZohoCRM.users.ALL,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL',
            'client_id'     => $this->client_id,
            'access_type'   => 'offline',
            'redirect_uri'  => $this->callback_url,
            'response_type' => 'code',
        ];

        if ( '' !== $code_challenge ) {
            $query['code_challenge']        = $code_challenge;
            $query['code_challenge_method'] = 'S256';
        }

        if ( '' !== $state ) {
            $query['state'] = $state;
        }

        return add_query_arg( $query, $this->account_url . '/oauth/v2/auth' );
    }

    public function generate_access_token( $code, $settings, string $code_verifier = '' ) {
        $body = [
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->callback_url,
            'scope'         => 'ZohoCRM.users.ALL,ZohoCRM.modules.ALL,ZohoCRM.settings.ALL',
            'code'          => $code,
        ];

        if ( '' !== $code_verifier ) {
            $body['code_verifier'] = $code_verifier;
        }

        $response = wp_remote_post(
            $this->account_url . '/oauth/v2/token', [
                'body' => $body,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = wp_remote_retrieve_body( $response );
        $body = \json_decode( $body, true );

        if ( isset( $body['error_description'] ) ) {
            return new \WP_Error( 'invalid_client', $body['error_description'] );
        }

        if ( isset( $body['error'] ) ) {
            return new \WP_Error( 'invalid_client', $body['error'] );
        }

        $settings['access_token']  = $body['access_token'];
        $settings['refresh_token'] = $body['refresh_token'];
        $settings['expire_at']     = time() + intval( $body['expires_in'] );
        
        return $settings;
    }

    public function make_request( $action, $data = [], $method = 'GET' ) {
        $settings = $this->get_api_settings();
        if ( is_wp_error( $settings ) ) {
            return $settings;
        }

        $url = $this->api_url . $action;

        $response = false;
        $args     = [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $settings['access_token']
            ]
        ];
        if ( $method == 'GET' ) {
            $url      = add_query_arg( $data, $url );
            $response = wp_remote_get( $url, $args );
        } else if ( $method == 'POST' ) {
            $args['body'] = json_encode( ['data' => [$data]] );
            $response     = wp_remote_post( $url, $args );
        }

        if ( ! $response ) {
            return new \WP_Error( 'invalid', 'Request could not be performed' );
        }
        if ( is_wp_error( $response ) ) {
            return new \WP_Error( 'wp_error', $response->get_error_message() );
        }

        $body = wp_remote_retrieve_body( $response );
        $body = \json_decode( $body, true );

        if ( isset( $body['status'] ) && $body['status'] == 'error' ) {
            $message = $body['message'];
            return new \WP_Error( 'request_error', $message );
        }

        return $body;
    }

    protected function get_api_settings() {
        $refreshed = $this->maybe_refresh_token();
        if ( is_wp_error( $refreshed ) ) {
            return $refreshed;
        }

        $api_settings = $this->settings;

        if ( ! $api_settings['status'] || ! $api_settings['expire_at'] ) {
            return new \WP_Error( 'invalid', 'API key is invalid' );
        }

        return [
            'baseUrl'       => $this->api_url,
            'version'       => 'OAuth2',
            'clientKey'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'callback'      => $this->callback_url,
            'access_token'  => $api_settings['access_token'],
            'refresh_token' => $api_settings['refresh_token'],
            'expire_at'     => $api_settings['expire_at']
        ];
    }

    protected function maybe_refresh_token() {
        $settings  = $this->settings;
        $expire_at = $settings['expire_at'];
        if ( ! $expire_at || $expire_at > ( time() - 10 ) ) {
            return true;
        }

        $response = wp_remote_post(
            $this->account_url . '/oauth/v2/token', [
                'body' => [
                    'client_id'     => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $settings['refresh_token'],
                    'redirect_uri'  => $this->callback_url,
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = \json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $body ) ||
            ! empty( $body['error'] ) ||
            ! empty( $body['error_description'] ) ||
            empty( $body['access_token'] ) ||
            empty( $body['expires_in'] )
        ) {
            $message = is_array( $body ) ? ( $body['error_description'] ?? $body['error'] ?? 'Invalid token refresh response' ) : 'Invalid token refresh response';

            return new \WP_Error( 'zohocrm_token_refresh_failed', (string) $message );
        }

        $settings['access_token'] = $body['access_token'];
        $settings['expire_at']    = time() + intval( $body['expires_in'] );
        $this->settings           = $settings;

        update_option( '_fg_zohocrm_tokens', $settings, false );

        return true;
    }

    public function get_all_modules() {
        return $this->make_request( 'settings/modules', [], 'GET' );
    }

    public function get_all_fields( $module_name ) {
        return $this->make_request( "settings/fields?module=$module_name", [], 'GET' );
    }

    public function insert_module_data( $module_name, $data ) {
        $response = $this->make_request( $module_name, $data, 'POST' );

        if ( ! empty( $response['data'][0]['details']['id'] ) ) {
            return $response;
        }
        $err_msg = 'Date insert failed';
        if ( $response['data'][0]['status'] == 'error' ) {
            $err_msg = $response['data'][0]['message'];
        }
        return new \WP_Error( 'error', $err_msg );
    }

    public function add_tags( $module_name, $record_id, $tags ) {
        return $this->make_request( "$module_name/$record_id/actions/add_tags?tag_names=$tags", null, 'POST' );
    }
}
