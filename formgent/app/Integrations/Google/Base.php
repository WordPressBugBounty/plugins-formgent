<?php

namespace FormGent\App\Integrations\Google;

defined( "ABSPATH" ) || exit;

use FormGent\App\Repositories\SettingsRepository;
use FormGent\Google\Service\Drive;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\Google\Client;

class Base {
    public SettingsRepository $settings_repository;

    protected ?Client $client = null;

    public function __construct( SettingsRepository $settings_repository ) {
        $this->settings_repository = $settings_repository;
    }

    protected function get_google_client() {
        if ( $this->client ) {
            return $this->client;
        }

        $client = new Client();

        $client->setClientId( "123104365122-hg8vtu8r7u4scc0gs9l2ibt5f8bgs26l.apps.googleusercontent.com" );
        $client->addScope( 'email' );
        $client->addScope( Drive::DRIVE_FILE );

        $settings = $this->settings_repository->get_by_key( 'google_sheet', [] );

        if ( empty( $settings['data'] ) ) {
            throw new Exception( "Authorization failed", 400 );
        }

        $client->setAccessToken( $settings['data'] );

        $this->client = $this->maybe_update_access_token( $client );

        return $this->client;
    }

    protected function maybe_update_access_token( Client $client ) {
        if ( ! $client->isAccessTokenExpired() ) {
            return $client;
        }

        $responses = wp_remote_get(
            add_query_arg(
                [
                    'refresh_token' => $client->getRefreshToken()
                ], 'https://app.formgent.com/wp-json/formgent-google-sheet/refresh-token'
            )
        );
        
        $data             = json_decode( wp_remote_retrieve_body( $responses ), true )['data'];
        $settings         = $this->settings_repository->get_by_key( 'google_sheet', [] );
        $settings['data'] = $data;

        $this->settings_repository->update_setting( 'google_sheet', $settings );

        $client->setAccessToken( $data );

        return $client;
    }

    public function disconnect() {
        return $this->get_google_client()->revokeToken();
    }
}