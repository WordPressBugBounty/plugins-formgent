<?php

namespace FormGent\App\Providers;


defined( "ABSPATH" ) || exit;

use WP_REST_Request;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\WpMVC\Database\Query\Builder;

class WPMLCompatibilityProvider implements Provider {
    public function boot() {
        if ( ! formgent_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
            return;
        }
        
        add_action( 'formgent_before_rest_request', [ $this, 'before_rest_request' ] );
        add_filter( 'formgent_http_headers', [ $this, 'add_language_to_http_headers' ] );
        add_filter( 'formgent_form_id', [ $this, 'get_tanslation_form_id' ] );
        add_action( 'formgent_forms_select_query', [ $this, 'filter_forms_by_language' ] );
    }

    public function before_rest_request( WP_REST_Request $wp_rest_request ) {
        $current_language = $wp_rest_request->get_header( 'x_fg_language' );

        if ( empty( $current_language ) ) {
            return;
        }
        
        add_filter(
            'wpml_current_language', function () use( $current_language ): string {
                return $current_language;
            } 
        );
    }

    public function add_language_to_http_headers( array $headers ): array {
        $headers[ 'X-FG-Language' ] = apply_filters( 'wpml_current_language', 'en' );

        return $headers;
    }

    public function get_tanslation_form_id( int $form_id ): int {
        return apply_filters( 'wpml_object_id', $form_id, formgent_config( 'app.post_type' ), true, apply_filters( 'wpml_current_language', 'en' ) );
    }

    public function filter_forms_by_language( Builder $query ) {
        $current_language = apply_filters( 'wpml_current_language', 'en' );

        if ( ! empty( $current_language ) && $current_language !== 'all' ) {
            $query->left_join( "icl_translations as wpml_translation",  'wpml_translation.element_id', 'post.ID' )
                ->where( "wpml_translation.language_code",  $current_language );
        }
    }
}