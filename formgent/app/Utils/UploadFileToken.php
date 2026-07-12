<?php

namespace FormGent\App\Utils;

defined( 'ABSPATH' ) || exit;

final class UploadFileToken {
    private const VERSION = 'fg1';

    private const SEPARATOR = '.';

    public static function create( string $relative_path ): string {
        $relative_path = self::normalize_relative_path( $relative_path );

        if ( null === $relative_path ) {
            return '';
        }

        $json = wp_json_encode(
            [
                'path' => $relative_path,
            ]
        );

        if ( ! is_string( $json ) ) {
            return '';
        }

        $payload   = self::base64_url_encode( $json );
        $signature = hash_hmac( 'sha256', $payload, self::signing_key() );

        return self::VERSION . self::SEPARATOR . $payload . self::SEPARATOR . $signature;
    }

    public static function path_from_token( string $token ): ?string {
        $token = sanitize_text_field( $token );
        $parts = explode( self::SEPARATOR, $token );

        if ( 3 !== count( $parts ) || self::VERSION !== $parts[0] ) {
            return null;
        }

        [ , $payload, $signature ] = $parts;
        $expected_signature        = hash_hmac( 'sha256', $payload, self::signing_key() );

        if ( ! hash_equals( $expected_signature, $signature ) ) {
            return null;
        }

        $json = self::base64_url_decode( $payload );

        if ( null === $json ) {
            return null;
        }

        $data = json_decode( $json, true );

        if ( ! is_array( $data ) || empty( $data['path'] ) || ! is_string( $data['path'] ) ) {
            return null;
        }

        return self::normalize_relative_path( $data['path'] );
    }

    public static function normalize_relative_path( string $relative_path ): ?string {
        $relative_path = str_replace( "\0", '', $relative_path );
        $relative_path = ltrim( wp_normalize_path( $relative_path ), '/' );
        $segments      = [];

        foreach ( explode( '/', $relative_path ) as $segment ) {
            if ( '' === $segment || '.' === $segment ) {
                continue;
            }

            if ( '..' === $segment ) {
                return null;
            }

            $segments[] = $segment;
        }

        if ( empty( $segments ) || 'formgent' !== $segments[0] ) {
            return null;
        }

        return implode( '/', $segments );
    }

    public static function resolve_existing_upload_path( string $relative_path ): ?string {
        $relative_path = self::normalize_relative_path( $relative_path );

        if ( null === $relative_path ) {
            return null;
        }

        $upload_dir = wp_upload_dir( null, false );

        if ( ! empty( $upload_dir['error'] ) || empty( $upload_dir['basedir'] ) ) {
            return null;
        }

        $uploads_base  = trailingslashit( wp_normalize_path( $upload_dir['basedir'] ) );
        $formgent_base = realpath( $uploads_base . 'formgent' );

        if ( false === $formgent_base ) {
            return null;
        }

        $real_file_path = realpath( $uploads_base . $relative_path );

        if ( false === $real_file_path ) {
            return null;
        }

        $formgent_base  = trailingslashit( wp_normalize_path( $formgent_base ) );
        $real_file_path = wp_normalize_path( $real_file_path );

        if ( 0 !== strpos( $real_file_path, $formgent_base ) ) {
            return null;
        }

        return $real_file_path;
    }

    private static function base64_url_encode( string $value ): string {
        return rtrim( strtr( base64_encode( $value ), '+/', '-_' ), '=' );
    }

    private static function base64_url_decode( string $value ): ?string {
        $padding = strlen( $value ) % 4;

        if ( 1 === $padding ) {
            return null;
        }

        if ( $padding > 0 ) {
            $value .= str_repeat( '=', 4 - $padding );
        }

        $decoded = base64_decode( strtr( $value, '-_', '+/' ), true );

        return false === $decoded ? null : $decoded;
    }

    private static function signing_key(): string {
        return wp_salt( 'auth' );
    }
}
