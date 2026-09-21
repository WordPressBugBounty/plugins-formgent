<?php

namespace FormGent\App\Services\Security;

defined( 'ABSPATH' ) || exit;

use Exception;

/**
 * Downloads trusted media through WordPress' SSRF-safe HTTP and sideload APIs.
 */
final class RemoteMediaImporter {
    private const MAX_FILE_SIZE = 10 * MB_IN_BYTES;

    /**
     * @return array{id:int,url:string}
     */
    public function import( string $url ): array {
        if ( ! wp_http_validate_url( $url ) ) {
            throw new Exception( esc_html__( 'The media URL is invalid.', 'formgent' ), 400 );
        }

        if ( ! function_exists( 'media_handle_sideload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $temporary_file = wp_tempnam( $url );

        if ( ! is_string( $temporary_file ) || '' === $temporary_file ) {
            throw new Exception( esc_html__( 'A temporary media file could not be created.', 'formgent' ), 500 );
        }

        $response = wp_safe_remote_get(
            $url,
            [
                'timeout'             => 30,
                'redirection'         => 3,
                'stream'              => true,
                'filename'            => $temporary_file,
                'limit_response_size' => self::MAX_FILE_SIZE + 1,
            ]
        );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            wp_delete_file( $temporary_file );
            $message = is_wp_error( $response ) ? $response->get_error_message() : esc_html__( 'The remote media server returned an error.', 'formgent' );
            throw new Exception( esc_html( $message ), 400 );
        }

        try {
            $size = filesize( $temporary_file );

            if ( false === $size || self::MAX_FILE_SIZE < $size ) {
                throw new Exception( esc_html__( 'The remote media file is too large.', 'formgent' ), 413 );
            }

            $path      = (string) wp_parse_url( $url, PHP_URL_PATH );
            $file_name = sanitize_file_name( wp_basename( $path ) );
            $mime      = wp_get_image_mime( $temporary_file );
            $allowed   = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
            ];

            if ( ! isset( $allowed[$mime] ) ) {
                throw new Exception( esc_html__( 'Only JPEG, PNG, GIF, and WebP images may be imported.', 'formgent' ), 415 );
            }

            $expected_extension = $allowed[$mime];
            $actual_extension   = strtolower( (string) pathinfo( $file_name, PATHINFO_EXTENSION ) );
            $jpeg_extensions    = ['jpg', 'jpeg'];

            if ( '' === $actual_extension ) {
                $base_name        = sanitize_file_name( (string) pathinfo( $file_name, PATHINFO_FILENAME ) );
                $file_name        = ( '' !== $base_name ? $base_name : 'remote-media' ) . '.' . $expected_extension;
                $actual_extension = $expected_extension;
            }

            $extension_matches = 'image/jpeg' === $mime
                ? in_array( $actual_extension, $jpeg_extensions, true )
                : $expected_extension === $actual_extension;

            if ( ! $extension_matches ) {
                throw new Exception( esc_html__( 'The remote media type does not match its filename.', 'formgent' ), 415 );
            }

            $attachment_id = media_handle_sideload(
                [
                    'name'     => $file_name,
                    'tmp_name' => $temporary_file,
                ],
                0
            );

            if ( is_wp_error( $attachment_id ) ) {
                throw new Exception( esc_html( $attachment_id->get_error_message() ), 400 );
            }

            $attachment_url = wp_get_attachment_url( $attachment_id );

            if ( ! is_string( $attachment_url ) || '' === $attachment_url ) {
                wp_delete_attachment( $attachment_id, true );
                throw new Exception( esc_html__( 'The imported media URL could not be resolved.', 'formgent' ), 500 );
            }

            // media_handle_sideload() moved the temporary file on success.
            $temporary_file = '';

            return [
                'id'  => (int) $attachment_id,
                'url' => $attachment_url,
            ];
        } finally {
            if ( '' !== $temporary_file && is_file( $temporary_file ) ) {
                wp_delete_file( $temporary_file );
            }
        }
    }
}
