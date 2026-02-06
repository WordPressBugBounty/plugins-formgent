<?php

namespace FormGent\App\Integrations\ZohoCRM;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Repositories\SettingsRepository;
use FormGent\App\Integrations\ZohoCRM\ZohoCRMApi;

class ZohoCRM {
    protected $option_key = '_fg_zohocrm_tokens';

    public SettingsRepository $settings;

    public function __construct( SettingsRepository $settings ) {
        $this->settings = $settings;
    }

    public function get_settings() {
        return $this->settings->get_by_key( 'zoho' );
    }

    public function set_settings( $key, $value ) {
        $settings         = $this->get_settings();
        $settings[ $key ] = $value;
        $this->settings->update_setting( 'zoho', $settings );
    }

    public function is_active() {
        $settings = $this->get_settings();

        return isset( $settings['status'] ) && $settings['status'];
    }

    public function set_tokens( $tokens = [] ) {
        update_option( $this->option_key, $tokens, false );
    }

    public function get_global_settings() {
        $tokens = get_option( $this->option_key );
        if ( ! $tokens ) {
            $tokens = [];
        }
        $settings = $this->get_settings();

        $defaults = [
            'accountUrl'    => $settings['data_center'] ?? '',
            'client_id'     => $settings['client_id'] ?? '',
            'client_secret' => $settings['client_secret'] ?? '',
            'status'        => false,
            'access_token'  => '',
            'refresh_token' => '',
            'expire_at'     => true
        ];

        return wp_parse_args( $tokens, $defaults );
    }

    public function get_modules() {
        $client   = $this->get_client();
        $response = $client->get_all_modules();
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        
        $services_options = [];
        if ( $response['modules'] ) {
            $services = $response['modules'];

            $available_services = [
                'Leads', 'Contacts', 'Accounts', 'Deals', 'Tasks', 'Cases', 'Vendors', 'Solutions', 'Campaigns'
            ];

            foreach ( $services as $service ) {
                $valid_service = $service['creatable'] &&
                                $service['global_search_supported'] &&
                                in_array( $service['api_name'], $available_services );

                if ( $valid_service ) {
                    $services_options[$service['api_name']] = $service['singular_label'];
                }
            }
        }
        return $services_options;
    }

    public function get_fields( $module_key ) {
        $client   = $this->get_client();
        $response = $client->get_all_fields( $module_key );
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        $fields = [];

        if ( $response['fields'] ) {
            foreach ( $response['fields'] as $field ) {
                $data = [
                    'key'         => $field['api_name'],
                    'placeholder' => $field['display_label'],
                    'label'       => $field['field_label'],
                    'required'    => false,
                    'tips'        => sprintf( 
                        /* translators: %s: field display label */
                        __( 'Enter %s value or choose form input provided by shortcode.', 'formgent' ), 
                        $field['display_label'] 
                    ),
                    'component'   => 'value_text'
                ];

                if ( $field['system_mandatory'] ) {
                    $data['required'] = true;
                    $data['tips']     = sprintf( 
                        /* translators: %s: field display label */
                        __( '%s is a required field. Enter value or choose form input provided by shortcode.', 'formgent' ), 
                        $field['display_label'] 
                    );
                }
                if ( $field['data_type'] == 'datetime' ) {
                    $data['tips'] = sprintf( 
                        /* translators: %s: field display label */
                        __( '%s is a required field. Enter value or choose form input shortcode. <br> Make sure format is (01/01/2022 00:00 +0:00)', 'formgent' ), 
                        $field['display_label'] 
                    );
                }
                if ( $field['data_type'] == 'picklist' && $field['pick_list_values'] ) {
                    $data['component'] = 'select';
                    $data['tips']      = sprintf( 
                        /* translators: %s: field display label */
                        __( "Choose %s type in select list.", 'formgent' ), 
                        $field['display_label'] 
                    );
                    $data_options = [];
                    foreach ( $field['pick_list_values'] as $option ) {
                        $data_options[$option['display_value']] = $option['display_value'];
                    }
                    $data['options'] = $data_options;
                }
                if ( $field['data_type'] == 'textarea' ) {
                    $data['component'] = 'value_textarea';
                }

                $fields[] = $data;
            }
        }

        return $fields;
    }

    protected function is_main_field( $field ) {
        return $field['system_mandatory'] ||
            'picklist' == $field['data_type'] ||
            'email' == $field['data_type'] ||
            'Tag' == $field['api_name'];
    }

    protected function is_other_field( $field ) {
        $field_type = $field['data_type'] ?? '';
        return in_array( $field_type, ['text', 'textarea', 'integer', 'website', 'phone', 'double', 'currency'], true );
    }

    public function get_client() {
        $settings = $this->get_global_settings();
        return new ZohoCRMApi(
            $settings['accountUrl'],
            $settings
        );
    }

    public function get_auth_url() {
        return $this->get_client()->get_auth_url();
    }

    public function generate_access_token( $code ) {
        return $this->get_client()->generate_access_token( $code, $this->get_global_settings() );
    }
}