<?php

namespace FormGent\App\Services\Responses;

defined( 'ABSPATH' ) || exit;

/**
 * Enforces the non-overridable MCP response denylist and bounded output.
 */
class ResponseRedactor {
    private const MAX_VALUE_NODES = 1000;

    private const DENIED_TYPES = [
        'password',
        'login-password',
        'captcha',
        'stripe',
        'paypal',
        'payment',
        'payment-summary',
    ];

    private const DENIED_NAME_PARTS = [
        'password',
        'credential',
        'secret',
        'token',
        'captcha',
        'authorization',
        'api-key',
        'api_key',
        'card-number',
        'card_number',
        'cvc',
        'cvv',
    ];

    private int $value_nodes = 0;

    /** @return array<string,mixed> */
    public function response( object $response ): array {
        $this->value_nodes = 0;
        $answers           = [];

        foreach ( array_slice( is_array( $response->answers ?? null ) ? $response->answers : [], 0, 200 ) as $answer ) {
            if ( ! is_object( $answer ) ) {
                continue;
            }

            $safe = $this->answer( $answer );

            if ( null !== $safe ) {
                $answers[] = $safe;
            }
        }

        return [
            'id'           => absint( $response->id ),
            'form_id'      => absint( $response->form_id ),
            'form_title'   => sanitize_text_field( $response->form_title ?? '' ),
            'created_at'   => $this->date( $response->created_at ?? '' ),
            'completed_at' => $this->date( $response->completed_at ?? '' ),
            'is_completed' => ! empty( $response->is_completed ),
            'is_read'      => ! empty( $response->is_read ),
            'is_starred'   => ! empty( $response->is_starred ),
            'answers'      => $answers,
        ];
    }

    /** @return array<string,mixed>|null */
    private function answer( object $answer ): ?array {
        $type   = sanitize_key( $answer->field_type ?? '' );
        $name   = sanitize_key( $answer->field_name ?? '' );
        $denied = $this->denied( $type, $name );
        $safe   = [
            'label'      => sanitize_text_field( $answer->label ?? '' ),
            'name'       => $name,
            'field_type' => $type,
            'value'      => $denied ? '[REDACTED]' : $this->value( $answer->value ?? '', $type, 0 ),
        ];

        if ( ! empty( $answer->children ) && is_array( $answer->children ) ) {
            $safe['children'] = [];

            foreach ( array_slice( $answer->children, 0, 50 ) as $child ) {
                if ( is_object( $child ) ) {
                    $child = $this->answer( $child );

                    if ( null !== $child ) {
                        $safe['children'][] = $child;
                    }
                }
            }
        }

        /**
         * Allows extensions to omit or further restrict an already-safe answer.
         * Core-denied fields are always redacted again after this filter.
         *
         * @param array<string,mixed>|false $safe Safe field or false to omit it.
         * @param object $answer Prepared internal answer.
         */
        $filtered = apply_filters( 'formgent_mcp_redact_response_field', $safe, $answer );

        if ( false === $filtered || ! is_array( $filtered ) ) {
            return null;
        }

        $core_value    = $safe['value'];
        $core_children = $safe['children'] ?? null;
        $safe          = array_intersect_key( $filtered, array_flip( ['label', 'name', 'field_type', 'value', 'children'] ) );

        if ( $denied ) {
            $safe['value'] = '[REDACTED]';
            unset( $safe['children'] );
        } else {
            $safe['value'] = '[REDACTED]' === ( $safe['value'] ?? null ) ? '[REDACTED]' : $core_value;

            if ( null !== $core_children && array_key_exists( 'children', $safe ) ) {
                $safe['children'] = $core_children;
            } else {
                unset( $safe['children'] );
            }
        }

        $safe['label']      = sanitize_text_field( $safe['label'] ?? '' );
        $safe['name']       = $name;
        $safe['field_type'] = $type;

        return $safe;
    }

    /** @param mixed $value Prepared answer value. @return mixed */
    private function value( $value, string $type, int $depth ) {
        $this->value_nodes++;

        if ( 5 < $depth || self::MAX_VALUE_NODES < $this->value_nodes ) {
            return '[TRUNCATED]';
        }

        if ( in_array( $type, ['file-upload', 'digital-signature', 'signature'], true ) ) {
            return $this->files( $value );
        }

        if ( is_array( $value ) ) {
            $safe = [];

            foreach ( array_slice( $value, 0, 100, true ) as $key => $item ) {
                $safe_key = is_int( $key ) ? $key : substr( sanitize_text_field( (string) $key ), 0, 128 );
                $key_name = is_int( $key ) ? '' : sanitize_key( (string) $key );

                if ( '' === $safe_key ) {
                    continue;
                }

                if ( '' !== $key_name && $this->denied( '', $key_name ) ) {
                    $safe[$safe_key] = '[REDACTED]';
                    continue;
                }

                if ( is_array( $item ) && isset( $item['field_type'] ) ) {
                    $child_type      = sanitize_key( $item['field_type'] );
                    $child_name      = sanitize_key( $item['field_name'] ?? $key );
                    $safe[$safe_key] = [
                        'label'      => sanitize_text_field( $item['label'] ?? '' ),
                        'name'       => $child_name,
                        'field_type' => $child_type,
                        'value'      => $this->denied( $child_type, $child_name ) ? '[REDACTED]' : $this->value( $item['value'] ?? '', $child_type, $depth + 1 ),
                    ];
                } else {
                    $safe[$safe_key] = $this->value( $item, '', $depth + 1 );
                }
            }

            return $safe;
        }

        if ( is_object( $value ) ) {
            return $this->value( get_object_vars( $value ), $type, $depth + 1 );
        }

        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
            return $value;
        }

        return substr( sanitize_textarea_field( (string) $value ), 0, 10000 );
    }

    /** @param mixed $value File/signature value. @return array<int,array<string,string>> */
    private function files( $value ): array {
        $items        = is_array( $value ) ? $value : [$value];
        $files        = [];
        $content_host = strtolower( (string) wp_parse_url( content_url(), PHP_URL_HOST ) );
        $content_path = trailingslashit( (string) wp_parse_url( content_url(), PHP_URL_PATH ) );

        foreach ( array_slice( $items, 0, 20 ) as $item ) {
            $url = is_array( $item ) ? ( $item['url'] ?? '' ) : $item;

            if ( ! is_string( $url ) || ! wp_http_validate_url( $url ) ) {
                continue;
            }

            $host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
            $path = (string) wp_parse_url( $url, PHP_URL_PATH );

            if ( $content_host !== $host || 0 !== strpos( $path, $content_path ) ) {
                continue;
            }

            $files[] = [
                'name' => sanitize_file_name( wp_basename( $path ) ),
                'url'  => esc_url_raw( $url ),
            ];
        }

        return $files;
    }

    private function denied( string $type, string $name ): bool {
        if ( in_array( $type, self::DENIED_TYPES, true ) ) {
            return true;
        }

        foreach ( self::DENIED_NAME_PARTS as $part ) {
            if ( false !== strpos( $name, $part ) ) {
                return true;
            }
        }

        return false;
    }

    /** @param mixed $date Stored date. */
    private function date( $date ): string {
        return is_string( $date ) && '' !== $date ? mysql2date( 'c', $date, false ) : '';
    }
}
