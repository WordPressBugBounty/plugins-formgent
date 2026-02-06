<?php

namespace FormGent\App\Http\Controllers;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\Helpers\Helpers;
use FormGent\App\Http\Controllers\Controller;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Routing\Response;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

class AttachmentController extends Controller {
    /**
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function store( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                'form_id'        => 'required|numeric',
                'response_token' => 'required|string',
                'context'        => 'required|string|accepted:digital_signature,file_upload',
                'file'           => 'required|file',
            ]
        );

        do_action( 'formgent_before_attachment_store', $validator, $request );

        $file = $request->get_file_params();
        $file = $file['file'];

        // Set custom upload directory for formgent
        add_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );

        $attachment = Helpers::upload_file( $file, false );

        // Remove the filter after upload
        remove_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );

        $trimmed_path = preg_replace( '/^.*uploads\//', '', $attachment['file'] );

        return Response::send(
            [
                "data" => [
                    "file_token" => base64_encode( $trimmed_path )
                ]
            ], 201
        );
    }

    /**
     * @param Validator $validator Instance of the Validator.
     * @param WP_REST_Request $request The REST request instance.
     * @return array
     */
    public function delete( Validator $validator, WP_REST_Request $request ): array {
        $validator->validate(
            [
                "file_token" => "required|string"
            ]
        );

        $file_path = ABSPATH . 'wp-content/uploads/' . base64_decode( $request->get_param( "file_token" ) );

        $read_file_path = realpath( $file_path );
        $real_base_path = realpath( ABSPATH . 'wp-content/uploads/formgent' ) . DIRECTORY_SEPARATOR;

        if ( $read_file_path === false || strpos( $read_file_path, $real_base_path ) !== 0 ) {
            throw new Exception( "Invalid file path", 400 );
        }

        if ( ! file_exists( $file_path ) ) {
            throw new Exception( "File not found", 404 );
        }

        //delete the file
        wp_delete_file( $file_path );

        return Response::send( [], 204 );
    }

    /**
     * Custom upload directory for formgent files
     *
     * @param array $upload_dir WordPress upload directory array
     * @return array Modified upload directory array
     */
    public function custom_upload_dir( array $upload_dir ): array {
        $custom_dir = '/formgent/' . gmdate( 'Y/m' );

        $upload_dir['path']   = $upload_dir['basedir'] . $custom_dir;
        $upload_dir['url']    = $upload_dir['baseurl'] . $custom_dir;
        $upload_dir['subdir'] = $custom_dir;

        // Create directory if it doesn't exist
        if ( ! file_exists( $upload_dir['path'] ) ) {
            wp_mkdir_p( $upload_dir['path'] );
        }

        return $upload_dir;
    }
}