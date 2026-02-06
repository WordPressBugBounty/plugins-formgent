<?php

namespace FormGent\App\Providers;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Contracts\Provider;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class FileUploadServiceProvider implements Provider {
    public function boot() {
        add_action( 'formgent_before_attachment_store', [$this, 'before_attachment_store'], 10, 2 );
    }
    
    public function before_attachment_store( Validator $validator, WP_REST_Request $request ) {
        $context = $request->get_param( 'context' );

        if ( $context === 'file_upload' ) {
            //TODO: Implement file upload validation
            // $validator->validate(
            //     [
            //         'file' => 'file|mimes:jpeg,jpg,png|max:50000'
            //     ]
            // );
        }
    }
}