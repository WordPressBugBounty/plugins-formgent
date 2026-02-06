<?php

namespace FormGent\App\Integrations\Mailchimp;

defined( "ABSPATH" ) || exit;

use Exception;
use FormGent\MailchimpMarketing\ApiClient as MailchimpAPIClient;
use FormGent\MailchimpMarketing\Api\ListsApi;

class Mailchimp {
    public MailchimpAPIClient $client;

    public ListsApi $list;

    public function __construct() {
        $settings = formgent_get_setting( 'mailchimp', null );


        if ( empty( $settings ) ) {
            throw new Exception( "Authorization failed", 400 );
        }

        if ( empty( $settings['data'] ) ) {
            throw new Exception( "Authorization failed", 400 );
        }

        $credentials = $settings['data'];

        if ( empty( $credentials ) ) {
            throw new Exception( "Authorization failed", 400 );
        }

        if ( empty( $credentials['access_token'] ) || empty( $credentials['server'] ) ) {
            throw new Exception( "Authorization failed", 400 );
        }

        $this->client = new MailchimpAPIClient();

        $this->client->setConfig(
            [
                'accessToken' => $credentials['access_token'],
                'server'      => $credentials['server'],
            ] 
        );
    }

    public function get_list() {
        return $this->list()->getAllLists( null, null, 100 );
    }

    public function get_categories( string $list_id ) {
        return $this->list()->getListInterestCategories( $list_id, null, null, 100 );
    }

    public function get_category_options( string $list_id, string $category_id ) {
        return $this->list()->listInterestCategoryInterests( $list_id, $category_id, null, null, 100 );
    }

    public function get_fields( string $list_id ) {
        return $this->list()->getListMergeFields( $list_id, null, null, 100 );
    }

    public function get_tags( string $list_id ) {
        return $this->list()->tagSearch( $list_id );
    }

    public function add_list_member( string $list_id, array $data, bool $skip_merge_validation = false ) {
        if ( empty( $data['email_address'] ) ) {
            throw new Exception( esc_html__( 'The email is required', 'formgent' ), 433 );
        }
        
        return $this->list()->addListMember( $list_id, $data, $skip_merge_validation );
    }

    public function set_list_member( string $list_id, array $data, bool $skip_merge_validation = false ) {
        if ( empty( $data['email_address'] ) ) {
            throw new Exception( esc_html__( 'The email is required', 'formgent' ), 433 );
        }

        $email = md5( strtolower( $data['email_address'] ) );

        return $this->list()->setListMember( $list_id, $email, $data, $skip_merge_validation );
    }

    public function update_list_member( string $list_id, array $data, bool $skip_merge_validation = false ) {
        if ( empty( $data['email_address'] ) ) {
            throw new Exception( esc_html__( 'The email is required', 'formgent' ), 433 );
        }

        $email = md5( strtolower( $data['email_address'] ) );

        return $this->list()->updateListMember( $list_id, $email, $data, $skip_merge_validation );
    }
    
    public function get_list_member( string $list_id, string $email ) {
        return $this->list()->getListMember( $list_id, md5( strtolower( $email ) ) );
    }
    
    public function is_exists_list_member( string $list_id, string $email ) {
        try {
            $this->list()->getListMember( $list_id, md5( strtolower( $email ) ) );
            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }

    public function list(): ListsApi {
        return $this->client->lists;
    }
}