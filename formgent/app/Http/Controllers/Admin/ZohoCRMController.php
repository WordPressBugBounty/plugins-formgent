<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Integrations\ZohoCRM\ZohoCRM;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class ZohoCRMController extends Controller {
    protected ZohoCRM $zohocrm;
    
    public function __construct( ZohoCRM $zohocrm ) {
        $this->zohocrm = $zohocrm;
    }

    public function get_modules() {
        $modules = $this->zohocrm->get_modules();
        
        if ( is_wp_error( $modules ) ) {
            return Response::send(
                [
                    'messages' => $modules->get_error_message()
                ], 400 
            );
        }

        return Response::send( $modules );
    }

    public function get_fields( Validator $validator, WP_REST_Request $request ) {
        $fields = $this->zohocrm->get_fields( $request->get_param( 'module' ) );

        if ( is_wp_error( $fields ) ) {
            return Response::send(
                [
                    'messages' => $fields->get_error_message()
                ], 400 
            );
        }

        return Response::send( $fields );
    }
}
