<?php

namespace FormGent\App\Services;

defined( 'ABSPATH' ) || exit;

use WP_Post;

class ElementorFormAssetDetector {
    private const THEME_TEMPLATE_TYPES = [ 'header', 'footer', 'single', 'archive' ];

    private $published_theme_templates_contain_form = null;

    public function current_page_needs_frontend_assets() : bool {
        if ( $this->current_singular_post_needs_frontend_assets() ) {
            return true;
        }

        return $this->matching_theme_documents_need_frontend_assets();
    }

    public function theme_location_needs_frontend_assets( string $location, $locations_manager ) : bool {
        if ( ! is_object( $locations_manager ) || ! method_exists( $locations_manager, 'get_documents_for_location' ) ) {
            return false;
        }

        $document_ids = $locations_manager->get_documents_for_location( $location );

        foreach ( $document_ids as $document_id ) {
            $post = get_post( $document_id );

            if ( $post instanceof WP_Post && $this->post_needs_frontend_assets( $post ) ) {
                return true;
            }
        }

        return false;
    }

    public function published_theme_templates_contain_form() : bool {
        if ( null !== $this->published_theme_templates_contain_form ) {
            return $this->published_theme_templates_contain_form;
        }

        global $wpdb;

        $shortcode_like = '%' . $wpdb->esc_like( '[formgent' ) . '%';
        $widget_like    = '%' . $wpdb->esc_like( '"formgent_forms"' ) . '%';
        $query_args     = array_merge( self::THEME_TEMPLATE_TYPES, [ $shortcode_like, $shortcode_like, $widget_like ] );

        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $query = $wpdb->prepare(
            "SELECT 1
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} template_meta
                ON template_meta.post_id = p.ID
                AND template_meta.meta_key = '_elementor_template_type'
                AND template_meta.meta_value IN (%s, %s, %s, %s)
            LEFT JOIN {$wpdb->postmeta} data_meta
                ON data_meta.post_id = p.ID
                AND data_meta.meta_key = '_elementor_data'
            WHERE p.post_type = 'elementor_library'
                AND p.post_status = 'publish'
                AND (
                    p.post_content LIKE %s
                    OR data_meta.meta_value LIKE %s
                    OR data_meta.meta_value LIKE %s
                )
            LIMIT 1",
            $query_args[0],
            $query_args[1],
            $query_args[2],
            $query_args[3],
            $query_args[4],
            $query_args[5],
            $query_args[6]
        );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $this->published_theme_templates_contain_form = (bool) $wpdb->get_var( $query );

        return $this->published_theme_templates_contain_form;
    }

    public function post_needs_frontend_assets( WP_Post $post ) : bool {
        if ( has_shortcode( $post->post_content, 'formgent' ) ) {
            return true;
        }

        $elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
        if ( ! is_string( $elementor_data ) || '' === $elementor_data ) {
            return false;
        }

        return $this->elementor_data_contains_form( $elementor_data );
    }

    private function current_singular_post_needs_frontend_assets() : bool {
        if ( ! is_singular() ) {
            return false;
        }

        $post = get_post();
        if ( ! $post instanceof WP_Post ) {
            return false;
        }

        return $this->post_needs_frontend_assets( $post );
    }

    private function matching_theme_documents_need_frontend_assets() : bool {
        if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
            return false;
        }

        $theme_builder = \ElementorPro\Modules\ThemeBuilder\Module::instance();

        if ( ! method_exists( $theme_builder, 'get_conditions_manager' ) ) {
            return false;
        }

        $conditions_manager = $theme_builder->get_conditions_manager();

        foreach ( self::THEME_TEMPLATE_TYPES as $location ) {
            if ( ! method_exists( $conditions_manager, 'get_documents_for_location' ) ) {
                continue;
            }

            $documents = $conditions_manager->get_documents_for_location( $location );

            foreach ( $documents as $document ) {
                if ( $this->document_needs_frontend_assets( $document ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    private function document_needs_frontend_assets( $document ) : bool {
        if ( ! is_object( $document ) ) {
            return false;
        }

        if ( method_exists( $document, 'get_main_id' ) ) {
            $post_id = $document->get_main_id();
        } elseif ( method_exists( $document, 'get_id' ) ) {
            $post_id = $document->get_id();
        } else {
            return false;
        }

        $post = get_post( $post_id );

        return $post instanceof WP_Post && $this->post_needs_frontend_assets( $post );
    }

    private function elementor_data_contains_form( string $elementor_data ) : bool {
        if ( has_shortcode( $elementor_data, 'formgent' ) || false !== strpos( $elementor_data, '[formgent' ) ) {
            return true;
        }

        $elements = json_decode( $elementor_data, true );
        if ( ! is_array( $elements ) ) {
            return false !== strpos( $elementor_data, '"formgent_forms"' );
        }

        return $this->elementor_elements_contain_form( $elements );
    }

    private function elementor_elements_contain_form( array $elements ) : bool {
        foreach ( $elements as $key => $value ) {
            if ( 'widgetType' === $key && 'formgent_forms' === $value ) {
                return true;
            }

            if ( is_string( $value ) && ( has_shortcode( $value, 'formgent' ) || false !== strpos( $value, '[formgent' ) ) ) {
                return true;
            }

            if ( is_array( $value ) && $this->elementor_elements_contain_form( $value ) ) {
                return true;
            }
        }

        return false;
    }
}
