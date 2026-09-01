<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

/**
 * Invalidates only FormGent's parsed-field caches for one form.
 */
class FormCacheService {
    /**
     * @return array<int,string>
     */
    private function field_cache_keys( int $form_id ): array {
        $array_keys = apply_filters( 'formgent_form_field_cache_array_keys', ['name', 'id'], $form_id );
        $array_keys = is_array( $array_keys ) ? $array_keys : ['name', 'id'];
        $keys       = [];

        foreach ( array_unique( array_map( 'sanitize_key', $array_keys ) ) as $array_key ) {
            if ( '' !== $array_key ) {
                $keys[] = "form_{$form_id}_fields_{$array_key}";
            }
        }

        return $keys;
    }

    public function forget_fields( int $form_id ): void {
        if ( 1 > $form_id ) {
            return;
        }

        foreach ( $this->field_cache_keys( $form_id ) as $key ) {
            wp_cache_delete( $key, 'formgent' );
        }

        do_action( 'formgent_form_field_cache_cleared', $form_id );
    }
}
