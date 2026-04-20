<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Pdf;
use FormGent\App\DTO\PdfDTO;

class PdfRepository {
    private const PASSWORD_PREFIX = 'enc_v1:';

    /**
     * Allowed keys for create/update (sanitized).
     *
     * @var string[]
     */
    protected array $allowed_keys = [
        'form_id',
        'template_name',
        'template_type',
        'content',
        'paper_size',
        'orientation',
        'direction',
        'password',
    ];

    /**
     * Create a PDF configuration.
     *
     * @param PdfDTO $dto Must have form_id, template_name, content set.
     * @return int Inserted row ID, or 0 if required fields missing.
     */
    public function create( PdfDTO $dto ): int {
        $data = $this->dto_to_array( $dto );
        $row  = $this->prepare_row( $data, [ 'form_id', 'template_name', 'content' ] );
        if ( empty( $row ) ) {
            return 0;
        }
        return (int) Pdf::query()->insert_get_id( $row );
    }

    /**
     * Update a PDF configuration by id and form_id from DTO.
     *
     * @param PdfDTO $dto Must have id and form_id set; other fields are updated.
     * @return int Number of rows updated.
     */
    public function update( PdfDTO $dto ): int {
        $data = $this->dto_to_array( $dto );
        unset( $data['form_id'] ); // Do not allow changing form_id.
        $row = $this->prepare_row( $data, null );
        if ( empty( $row ) ) {
            return 0;
        }
        return Pdf::query()
            ->where( 'id', $dto->get_id() )
            ->where( 'form_id', $dto->get_form_id() )
            ->update( $row );
    }

    /**
     * Delete a PDF configuration by id.
     *
     * @param int $id PDF row id.
     * @return int Number of rows deleted.
     */
    public function delete_by_id( int $id ): int {
        return Pdf::query()->where( 'id', $id )->delete();
    }

    /**
     * Get a PDF configuration by id.
     *
     * @param int $id PDF row id.
     * @return object|null Row object or null.
     */
    public function get_by_id( int $id ) {
        $pdf = Pdf::query()->where( 'id', $id )->first();
        return $this->hydrate_password( $pdf );
    }

    /**
     * Get a PDF configuration by id and form_id (ownership check).
     *
     * @param int $id      PDF row id.
     * @param int $form_id Form id.
     * @return object|null Row object or null.
     */
    public function get_by_id_and_form( int $id, int $form_id ) {
        $pdf = Pdf::query()
            ->where( 'id', $id )
            ->where( 'form_id', $form_id )
            ->first();
        return $this->hydrate_password( $pdf );
    }

    /**
     * Get a PDF configuration with its decrypted password in a single DB query.
     * Avoids the extra query that get_decrypted_password() would otherwise require.
     *
     * @param int $id      PDF row id.
     * @param int $form_id Form id.
     * @return array{0: object|null, 1: string} [hydrated row or null, decrypted password]
     */
    public function get_by_id_and_form_with_decrypted_password( int $id, int $form_id ): array {
        $pdf = Pdf::query()
            ->where( 'id', $id )
            ->where( 'form_id', $form_id )
            ->first();

        if ( ! is_object( $pdf ) ) {
            return [ null, '' ];
        }

        $decrypted = property_exists( $pdf, 'password' )
            ? $this->decrypt_password( (string) $pdf->password )
            : '';

        return [ $this->hydrate_password( $pdf ), $decrypted ];
    }

    /**
     * Get all PDF configurations for a form.
     *
     * @param int $form_id Form id.
     * @return array List of PDF rows.
     */
    public function get_by_form_id( int $form_id ): array {
        $result = Pdf::query()
            ->where( 'form_id', $form_id )
            ->order_by( 'created_at', 'desc' )
            ->get();
        if ( ! is_array( $result ) ) {
            return [];
        }
        return array_map( [ $this, 'hydrate_password' ], $result );
    }

    /**
     * Get paginated PDF configurations for a form.
     *
     * @param int      $form_id  Form id.
     * @param int      $page     Page number (1-based).
     * @param int      $per_page Items per page.
     * @return array   ['items' => array, 'total' => int]
     */
    public function get_paginated_by_form_id( int $form_id, int $page = 1, int $per_page = 10 ): array {
        // Create separate queries for count and results
        $total_query = Pdf::query()->where( 'form_id', $form_id );
        $total       = (int) $total_query->count();

        $offset = ( $page - 1 ) * $per_page;
        $result = Pdf::query()
            ->where( 'form_id', $form_id )
            ->order_by( 'created_at', 'desc' )
            ->limit( $per_page )
            ->offset( $offset )
            ->get();

        if ( ! is_array( $result ) ) {
            $result = [];
        }

        $items = array_map( [ $this, 'hydrate_password' ], $result );

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * Get total count of PDF configurations for a form.
     *
     * @param int $form_id Form id.
     * @return int Total count.
     */
    public function count_by_form_id( int $form_id ): int {
        return (int) Pdf::query()
            ->where( 'form_id', $form_id )
            ->count();
    }

    /**
     * Get all PDF configurations for a form with decrypted passwords.
     * Avoids N+1 queries when generating PDFs for all templates.
     *
     * @param int $form_id Form id.
     * @return array List of ['pdf' => hydrated row, 'password' => decrypted password].
     */
    public function get_all_with_decrypted_passwords( int $form_id ): array {
        $result = Pdf::query()
            ->where( 'form_id', $form_id )
            ->order_by( 'created_at', 'desc' )
            ->get();

        if ( ! is_array( $result ) || empty( $result ) ) {
            return [];
        }

        $items = [];
        foreach ( $result as $pdf ) {
            $decrypted = property_exists( $pdf, 'password' )
                ? $this->decrypt_password( (string) $pdf->password )
                : '';
            $items[]   = [
                'pdf'      => $this->hydrate_password( $pdf ),
                'password' => $decrypted,
            ];
        }

        return $items;
    }

    /**
     * Convert PdfDTO to array for prepare_row (same keys as allowed_keys).
     *
     * @param PdfDTO $dto
     * @return array
     */
    protected function dto_to_array( PdfDTO $dto ): array {
        return [
            'form_id'       => $dto->get_form_id(),
            'template_name' => $dto->get_template_name(),
            'template_type' => $dto->get_template_type(),
            'content'       => $dto->get_content(),
            'paper_size'    => $dto->get_paper_size(),
            'orientation'   => $dto->get_orientation(),
            'direction'     => $dto->get_direction(),
            'password'      => $dto->get_password(),
        ];
    }

    /**
     * Sanitize and whitelist row data for insert/update.
     *
     * @param array      $data       Raw request data.
     * @param array|null $required   Keys that must be present (for create). Null to skip required check.
     * @return array Sanitized row (only allowed keys).
     */
    protected function prepare_row( array $data, ?array $required = null ): array {
        $row = [];
        foreach ( $this->allowed_keys as $key ) {
            if ( ! array_key_exists( $key, $data ) ) {
                if ( $required && in_array( $key, $required, true ) ) {
                    return []; // Required key missing.
                }
                continue;
            }
            $value = $data[ $key ];
            if ( in_array( $key, [ 'form_id' ], true ) ) {
                $row[ $key ] = absint( $value );
            } elseif ( in_array( $key, [ 'content' ], true ) ) {
                $row[ $key ] = wp_kses_post( $value );
            } elseif ( 'password' === $key ) {
                $password    = sanitize_text_field( (string) $value );
                $row[ $key ] = $this->encrypt_password( $password );
            } else {
                $row[ $key ] = sanitize_text_field( (string) $value );
            }
        }
        return $row;
    }

    /**
     * Hydrate password for API response: decrypt the stored password so admins can view it.
     *
     * @param object|null $pdf Row object from DB.
     * @return object|null
     */
    private function hydrate_password( $pdf ) {
        if ( ! is_object( $pdf ) ) {
            return $pdf;
        }
        if ( ! property_exists( $pdf, 'password' ) ) {
            return $pdf;
        }
        $pdf->password = $this->decrypt_password( (string) $pdf->password );
        return $pdf;
    }

    /**
     * Get decrypted password for server-side use only (e.g. PDF generation). Not for API responses.
     *
     * @param int      $id      PDF row id.
     * @param int|null $form_id Optional form id for ownership check; if provided, password is returned only when pdf belongs to form.
     * @return string Decrypted password or empty string.
     */
    public function get_decrypted_password( int $id, ?int $form_id = null ): string {
        $query = Pdf::query()->where( 'id', $id );
        if ( $form_id !== null ) {
            $query->where( 'form_id', $form_id );
        }
        $row = $query->first();
        if ( ! is_object( $row ) || ! property_exists( $row, 'password' ) ) {
            return '';
        }
        return $this->decrypt_password( (string) $row->password );
    }

    private function get_password_encryption_key(): string {
        $material = '';
        if ( defined( 'AUTH_KEY' ) ) {
            $material .= (string) AUTH_KEY;
        }
        if ( defined( 'SECURE_AUTH_KEY' ) ) {
            $material .= (string) SECURE_AUTH_KEY;
        }
        $material .= wp_salt( 'auth' );
        return hash( 'sha256', $material, true );
    }

    private function encrypt_password( string $plaintext ): string {
        if ( '' === $plaintext ) {
            return '';
        }
        if ( 0 === strpos( $plaintext, self::PASSWORD_PREFIX ) ) {
            return $plaintext;
        }
        if ( ! function_exists( 'openssl_encrypt' ) || ! function_exists( 'random_bytes' ) ) {
            return $plaintext;
        }

        $key = $this->get_password_encryption_key();
        $iv  = random_bytes( 16 );

        $ciphertext = openssl_encrypt( $plaintext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
        if ( false === $ciphertext ) {
            return $plaintext;
        }

        $mac     = hash_hmac( 'sha256', $iv . $ciphertext, $key, true );
        $payload = base64_encode( $iv . $mac . $ciphertext );
        return self::PASSWORD_PREFIX . $payload;
    }

    private function decrypt_password( string $stored ): string {
        if ( '' === $stored ) {
            return '';
        }
        if ( 0 !== strpos( $stored, self::PASSWORD_PREFIX ) ) {
            return $stored; // Backwards compatibility for existing plaintext.
        }
        if ( ! function_exists( 'openssl_decrypt' ) ) {
            return '';
        }

        $encoded = substr( $stored, strlen( self::PASSWORD_PREFIX ) );
        $raw     = base64_decode( $encoded, true );
        if ( false === $raw || strlen( $raw ) < ( 16 + 32 + 1 ) ) {
            return '';
        }

        $iv         = substr( $raw, 0, 16 );
        $mac        = substr( $raw, 16, 32 );
        $ciphertext = substr( $raw, 48 );

        $key      = $this->get_password_encryption_key();
        $calc_mac = hash_hmac( 'sha256', $iv . $ciphertext, $key, true );
        if ( ! hash_equals( $mac, $calc_mac ) ) {
            return '';
        }

        $plaintext = openssl_decrypt( $ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv );
        return false === $plaintext ? '' : (string) $plaintext;
    }
}
