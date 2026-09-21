<?php

namespace FormGent\App\Http\Controllers;

defined( "ABSPATH" ) || exit;

use FormGent\App\Utils\UploadFileToken;
use FormGent\App\Repositories\ResponseRepository;
use FormGent\App\Repositories\ResponseTokenRepository;
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
                'form_id'           => 'required|numeric',
                'response_token'    => 'required|string',
                'context'           => 'required|string|accepted:digital_signature,file_upload',
                'field_name'        => 'required|string|max:255',
                'parent_field_name' => 'string|max:255',
                'field_index'       => 'numeric',
                'file'              => 'required|file',
            ]
        );

        do_action( 'formgent_before_attachment_store', $validator, $request );

        $form_id        = absint( $request->get_param( 'form_id' ) );
        $response_token = (string) $request->get_param( 'response_token' );
        $context        = sanitize_key( (string) $request->get_param( 'context' ) );
        $field_name     = sanitize_key( (string) $request->get_param( 'field_name' ) );
        $field_identity = $this->field_identity_from_request( $field_name, $request );
        $form           = formgent_get_form_by_id( $form_id, true );
        $token_data     = formgent_singleton( ResponseTokenRepository::class )->get_by_token( $form_id, $response_token );
        $response       = $token_data
            ? formgent_singleton( ResponseRepository::class )->get_by_id( (int) $token_data->response_id )
            : false;

        if ( ! $form || ! $response || '1' === (string) $response->is_completed ) {
            throw new Exception( esc_html__( 'Form session not found.', 'formgent' ), 404 );
        }

        $file        = $request->get_file_params();
        $file        = $file['file'];
        $field       = $this->validate_upload_for_field( $form, $field_name, $context, $file, $request );
        $upload_key  = 'formgent_upload_' . md5( (int) $response->id . '|' . $field_identity );
        $upload_lock = $this->acquire_upload_lock( $upload_key );

        try {
            $upload_count = (int) get_transient( $upload_key );
            $upload_limit = $this->field_upload_limit( $field, $context );

            if ( $upload_count >= $upload_limit ) {
                throw new Exception( esc_html__( 'The upload limit for this field has been reached.', 'formgent' ), 429 );
            }

            // Set custom upload directory for formgent.
            add_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );

            try {
                $attachment = Helpers::upload_file( $file, false );
            } finally {
                // Never leak the custom directory into later uploads in this request.
                remove_filter( 'upload_dir', [ $this, 'custom_upload_dir' ] );
            }

            $upload_dir    = wp_upload_dir( null, false );
            $uploads_base  = trailingslashit( wp_normalize_path( $upload_dir['basedir'] ) );
            $uploaded_file = wp_normalize_path( $attachment['file'] );
            $relative_path = UploadFileToken::normalize_relative_path( str_replace( $uploads_base, '', $uploaded_file ) );

            if ( null === $relative_path ) {
                throw new Exception( esc_html__( 'Invalid file path', 'formgent' ), 400 );
            }

            $expires_at = ! empty( $token_data->expired_at ) ? strtotime( (string) $token_data->expired_at ) : 0;
            $file_token = UploadFileToken::create( $relative_path, $form_id, $context, (int) $response->id, $field_identity, (int) $expires_at );

            if ( '' === $file_token ) {
                wp_delete_file( $uploaded_file );
                throw new Exception( esc_html__( 'The uploaded file could not be secured.', 'formgent' ), 500 );
            }

            $upload_count_ttl = $expires_at > time() ? $expires_at - time() : DAY_IN_SECONDS;
            set_transient( $upload_key, $upload_count + 1, max( MINUTE_IN_SECONDS, $upload_count_ttl ) );
        } finally {
            $this->release_upload_lock( $upload_lock );
        }

        return Response::send(
            [
                "data" => [
                    "file_token" => $file_token
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
                'file_token'        => 'required|string',
                'form_id'           => 'required|numeric',
                'response_token'    => 'required|string',
                'context'           => 'required|string|accepted:digital_signature,file_upload',
                'field_name'        => 'required|string|max:255',
                'parent_field_name' => 'string|max:255',
                'field_index'       => 'numeric',
            ]
        );

        $form_id        = absint( $request->get_param( 'form_id' ) );
        $response_token = (string) $request->get_param( 'response_token' );
        $context        = sanitize_key( (string) $request->get_param( 'context' ) );
        $field_name     = sanitize_key( (string) $request->get_param( 'field_name' ) );
        $field_identity = $this->field_identity_from_request( $field_name, $request );
        $token_data     = formgent_singleton( ResponseTokenRepository::class )->get_by_token( $form_id, $response_token );
        $response       = $token_data
            ? formgent_singleton( ResponseRepository::class )->get_by_id( (int) $token_data->response_id )
            : false;

        if ( ! $response || '1' === (string) $response->is_completed ) {
            throw new Exception( esc_html__( 'Form session not found.', 'formgent' ), 404 );
        }

        $relative_path = UploadFileToken::path_from_token(
            (string) $request->get_param( 'file_token' ),
            $form_id,
            $context,
            (int) $response->id,
            $field_identity
        );

        if ( null === $relative_path ) {
            throw new Exception( esc_html__( 'Invalid file token', 'formgent' ), 400 );
        }

        $upload_key  = 'formgent_upload_' . md5( (int) $response->id . '|' . $field_identity );
        $upload_lock = $this->acquire_upload_lock( $upload_key );

        try {
            $file_path = UploadFileToken::resolve_existing_upload_path( $relative_path );

            if ( null === $file_path ) {
                throw new Exception( esc_html__( 'Invalid file path', 'formgent' ), 400 );
            }

            if ( ! wp_delete_file( $file_path ) ) {
                throw new Exception( esc_html__( 'The uploaded file could not be deleted.', 'formgent' ), 500 );
            }

            $upload_count = (int) get_transient( $upload_key );

            if ( $upload_count <= 1 ) {
                delete_transient( $upload_key );
            } else {
                $expires_at       = ! empty( $token_data->expired_at ) ? strtotime( (string) $token_data->expired_at ) : 0;
                $upload_count_ttl = $expires_at > time() ? $expires_at - time() : DAY_IN_SECONDS;
                set_transient( $upload_key, $upload_count - 1, max( MINUTE_IN_SECONDS, $upload_count_ttl ) );
            }
        } finally {
            $this->release_upload_lock( $upload_lock );
        }

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

    /** Validate MIME and size against the referenced published form field. */
    private function validate_upload_for_field( object $form, string $field_name, string $context, array $file, WP_REST_Request $request ): array {
        $fields = formgent_get_form_fields( $form );
        $field  = $fields[$field_name] ?? null;

        $parent_field_name = sanitize_key( (string) $request->get_param( 'parent_field_name' ) );

        if ( '' !== $parent_field_name ) {
            $parent = $fields[$parent_field_name] ?? null;
            $field  = is_array( $parent ) && 'repeater' === ( $parent['field_type'] ?? '' )
                ? ( $parent['children'][$field_name] ?? null )
                : null;
        }
        $types = 'file_upload' === $context ? ['file-upload'] : ['digital-signature', 'signature'];

        if ( ! is_array( $field ) || ! in_array( $field['field_type'] ?? '', $types, true ) ) {
            throw new Exception( esc_html__( 'The upload field is invalid.', 'formgent' ), 400 );
        }

        $checked      = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
        $actual_mime  = (string) ( $checked['type'] ?? '' );
        $allowed_mime = 'file_upload' === $context
            ? array_values( array_filter( array_map( 'sanitize_mime_type', (array) ( $field['allowed_types'] ?? [] ) ) ) )
            : ['image/png', 'image/jpeg'];

        if ( '' === $actual_mime || empty( $checked['ext'] ) || empty( $allowed_mime ) || ! in_array( $actual_mime, $allowed_mime, true ) ) {
            throw new Exception( esc_html__( 'This file type is not allowed.', 'formgent' ), 415 );
        }

        $max_size = 'file_upload' === $context ? $this->field_max_bytes( $field ) : 5 * MB_IN_BYTES;

        if ( empty( $file['size'] ) || (int) $file['size'] > $max_size ) {
            throw new Exception( esc_html__( 'The uploaded file is too large.', 'formgent' ), 413 );
        }

        return $field;
    }

    /** Build the signed/count identity for a direct field or repeater item. */
    private function field_identity_from_request( string $field_name, WP_REST_Request $request ): string {
        $parent_field_name = sanitize_key( (string) $request->get_param( 'parent_field_name' ) );
        $has_field_index   = $request->has_param( 'field_index' );

        if ( ( '' === $parent_field_name ) !== ( ! $has_field_index ) ) {
            throw new Exception( esc_html__( 'The upload field scope is invalid.', 'formgent' ), 400 );
        }

        if ( '' === $parent_field_name ) {
            return $field_name;
        }

        return sanitize_key( $parent_field_name . '_' . absint( $request->get_param( 'field_index' ) ) . '_' . $field_name );
    }

    /** Acquire a short per-response/field lock for count and file mutations. */
    private function acquire_upload_lock( string $upload_key ) {
        $lock_scope = wp_normalize_path( ABSPATH ) . '|' . get_current_blog_id() . '|' . $upload_key;
        $lock_path  = trailingslashit( get_temp_dir() ) . 'formgent-' . md5( $lock_scope ) . '.lock';
        $handle     = fopen( $lock_path, 'c' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

        if ( false === $handle ) {
            throw new Exception( esc_html__( 'The upload could not be started.', 'formgent' ), 500 );
        }

        for ( $attempt = 0; $attempt < 100; $attempt++ ) {
            if ( flock( $handle, LOCK_EX | LOCK_NB ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_flock
                return $handle;
            }

            usleep( 20000 );
        }

        fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        throw new Exception( esc_html__( 'Another upload operation is in progress. Please try again.', 'formgent' ), 429 );
    }

    /** Release a lock acquired by acquire_upload_lock(). */
    private function release_upload_lock( $handle ): void {
        flock( $handle, LOCK_UN ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_flock
        fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
    }

    /** Convert a field size setting to bytes, capped at WordPress' configured limit. */
    private function field_max_bytes( array $field ): int {
        if ( empty( $field['is_limit_size'] ) ) {
            return (int) wp_max_upload_size();
        }

        $configured  = max( 1, (int) ( $field['limit_size'] ?? 250 ) );
        $unit        = strtoupper( (string) ( $field['size_type'] ?? 'KB' ) );
        $multipliers = [
            'KB' => KB_IN_BYTES,
            'MB' => MB_IN_BYTES,
            'GB' => GB_IN_BYTES,
            'TB' => TB_IN_BYTES,
        ];
        $bytes       = $configured * ( $multipliers[$unit] ?? KB_IN_BYTES );

        return min( $bytes, wp_max_upload_size() );
    }

    /** Return the configured count limit, with a hard public-session ceiling. */
    private function field_upload_limit( array $field, string $context ): int {
        if ( 'file_upload' !== $context ) {
            return 1;
        }

        if ( empty( $field['is_limit_files'] ) ) {
            return 20;
        }

        return max( 1, min( 20, (int) ( $field['limit_files'] ?? 5 ) ) );
    }
}
