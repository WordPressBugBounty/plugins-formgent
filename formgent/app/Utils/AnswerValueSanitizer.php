<?php

namespace FormGent\App\Utils;

defined( 'ABSPATH' ) || exit;

use stdClass;

final class AnswerValueSanitizer {
    /**
     * Sanitize submitted answer values recursively.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function sanitize( $value ) {
        if ( is_array( $value ) ) {
            return array_map( [ self::class, 'sanitize' ], $value );
        }

        if ( is_object( $value ) && $value instanceof stdClass ) {
            $sanitized = new stdClass();

            foreach ( get_object_vars( $value ) as $key => $item ) {
                $sanitized->{ sanitize_key( (string) $key ) } = self::sanitize( $item );
            }

            return $sanitized;
        }

        if ( is_string( $value ) ) {
            return sanitize_textarea_field( $value );
        }

        return $value;
    }
}
