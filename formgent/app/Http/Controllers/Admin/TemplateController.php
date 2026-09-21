<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use WP_REST_Request;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\Routing\Response;
use FormGent\App\Services\Security\RemoteMediaImporter;

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

        $import = ( new RemoteMediaImporter() )->import( $attachment_url );

        /**
         * Caching the inserted attachment url and id
         */
        $demo_attachments[$attachment_url] = $import['id'];

        $this->update_demo_attachments( $demo_attachments );

        return Response::send( $import );
    }

    private function get_demo_attachments():array {
        return get_option( self::DEMOMEDIAOPTIONKEY, [] );
    }

    private function update_demo_attachments( array $demo_attachments ) {
        return update_option( self::DEMOMEDIAOPTIONKEY, $demo_attachments );
    }
}
