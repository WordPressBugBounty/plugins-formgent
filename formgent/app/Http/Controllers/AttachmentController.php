<?php

namespace FormGent\App\Http\Controllers;

defined( "ABSPATH" ) || exit;

use FormGent\App\Utils\UploadFileToken;
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

        $upload_dir    = wp_upload_dir( null, false );
        $uploads_base  = trailingslashit( wp_normalize_path( $upload_dir['basedir'] ) );
        $uploaded_file = wp_normalize_path( $attachment['file'] );
        $relative_path = UploadFileToken::normalize_relative_path( str_replace( $uploads_base, '', $uploaded_file ) );

        if ( null === $relative_path ) {
            throw new Exception( esc_html__( 'Invalid file path', 'formgent' ), 400 );
        }

        return Response::send(
            [
                "data" => [
                    "file_token" => UploadFileToken::create( $relative_path )
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

        $relative_path = UploadFileToken::path_from_token( (string) $request->get_param( "file_token" ) );

        if ( null === $relative_path ) {
            throw new Exception( esc_html__( 'Invalid file token', 'formgent' ), 400 );
        }

        $file_path = UploadFileToken::resolve_existing_upload_path( $relative_path );

        if ( null === $file_path ) {
            throw new Exception( esc_html__( 'Invalid file path', 'formgent' ), 400 );
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
