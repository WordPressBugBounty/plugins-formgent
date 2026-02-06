<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use WP_REST_Request;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Routing\Response;

class TemplateController {
    const DEMOMEDIAOPTIONKEY = 'formgent_demo_medias';

    public function insert_media( Validator $validator, WP_REST_Request $request ) {
        $validator->validate(
            [
                'attachment_url' => 'required|string',
            ]
        );

        $attachment_url   = $request->get_param( 'attachment_url' );
        $demo_attachments = $this->get_demo_attachments();

        /**
         * Return attachment if from cache if exists
         */
        if ( ! empty( $demo_attachments[$attachment_url] ) ) {
            $id         = $demo_attachments[$attachment_url];
            $attachment = wp_get_attachment_url( $id );

            if ( is_string( $attachment ) ) {
                return Response::send(
                    [
                        'id'  => $id,
                        'url' => $attachment
                    ]
                );
            }
        }

        $response = wp_remote_get(
            $attachment_url, [
                [
                    'timeout' => 30
                ]
            ]
        );

        if ( is_wp_error( $response ) ) {
            $error_code    = $response->get_error_code();
            $response_code = 500;

            if ( is_string( $error_code ) ) {
                if ( 'http_request_failed' === $error_code ) {
                    $response_code = 495;
                }
            } else {
                $response_code = $error_code;
            }
            throw new Exception( esc_html( $response->get_error_message() ), esc_attr( $response_code ) );
        }

        $response_code = intval( wp_remote_retrieve_response_code( $response ) );

        if ( 200 !== $response_code ) {
            throw new Exception( esc_html( wp_remote_retrieve_response_message( $response ) ), esc_attr( $response_code ) );
        }

        $file_name = wp_basename( $attachment_url );
        $upload    = wp_upload_bits( $file_name, null, wp_remote_retrieve_body( $response ) );

        if ( ! empty( $upload['error'] ) ) {
            throw new Exception( esc_html( $upload['error'] ), 500 );
        }

        $attachment = [
            'post_title'     => $file_name,
            'post_type'      => 'attachment',
            'post_mime_type' => $upload['type'],
            'guid'           => $upload['url']
        ];

        $id = wp_insert_attachment( $attachment, $upload['file'] );

        if ( is_wp_error( $id ) ) {
            throw new Exception( esc_html( $id->get_error_message() ), esc_attr( $id->get_error_code() ) );
        }

        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            include_once ABSPATH . 'wp-admin/includes/image.php';
        }

        if ( ! function_exists( 'wp_read_video_metadata' ) ) {
            include_once ABSPATH . 'wp-admin/includes/media.php';
        }

        wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

        /**
         * Caching the inserted attachment url and id
         */
        $demo_attachments[$attachment_url] = $id;

        $this->update_demo_attachments( $demo_attachments );

        return Response::send(
            [
                'id'  => $id,
                'url' => $upload['url']
            ]
        );
    }

    private function get_demo_attachments():array {
        return get_option( self::DEMOMEDIAOPTIONKEY, [] );
    }

    private function update_demo_attachments( array $demo_attachments ) {
        return update_option( self::DEMOMEDIAOPTIONKEY, $demo_attachments );
    }
}