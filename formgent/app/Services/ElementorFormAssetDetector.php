<?php

namespace FormGent\App\Services;

defined( 'ABSPATH' ) || exit;

use WP_Post;

class ElementorFormAssetDetector {
    private const THEME_TEMPLATE_TYPES = [ 'header', 'footer', 'single', 'archive' ];

    private $published_theme_templates_contain_form = null;

    public function current_page_needs_frontend_assets() : bool {
        return ! empty( $this->current_page_form_ids() );
    }

    /**
     * Return every FormGent form that can render during the current request.
     *
     * @return int[]
     */
    public function current_page_form_ids() : array {
        $form_ids = $this->current_singular_post_form_ids();

        return $this->normalize_form_ids(
            array_merge( $form_ids, $this->matching_theme_document_form_ids() )
        );
    }

    public function theme_location_needs_frontend_assets( string $location, $locations_manager ) : bool {
        return ! empty( $this->theme_location_form_ids( $location, $locations_manager ) );
    }

    /**
     * Return FormGent form IDs used by Elementor documents for a theme location.
     *
     * @return int[]
     */
    public function theme_location_form_ids( string $location, $locations_manager ) : array {
        if ( ! is_object( $locations_manager ) || ! method_exists( $locations_manager, 'get_documents_for_location' ) ) {
            return [];
        }

        $form_ids     = [];
        $document_ids = $locations_manager->get_documents_for_location( $location );

        foreach ( $document_ids as $document_id ) {
            $post = get_post( $document_id );

            if ( $post instanceof WP_Post ) {
                $form_ids = array_merge( $form_ids, $this->post_form_ids( $post ) );
            }
        }

        return $this->normalize_form_ids( $form_ids );
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
            INNER JOIN {$wpdb->postmeta} conditions_meta
                ON conditions_meta.post_id = p.ID
                AND conditions_meta.meta_key = '_elementor_conditions'
                AND conditions_meta.meta_value NOT IN ('', 'a:0:{}')
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
        return ! empty( $this->post_form_ids( $post ) );
    }

    /**
     * Find FormGent form IDs in post content, block attributes and Elementor data.
     *
     * @return int[]
     */
    public function post_form_ids( WP_Post $post ) : array {
        if ( formgent_post_type() === $post->post_type ) {
            return [ (int) $post->ID ];
        }

        $form_ids = array_merge(
            $this->shortcode_form_ids( $post->post_content ),
            $this->block_form_ids( parse_blocks( $post->post_content ) )
        );

        $elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
        if ( is_string( $elementor_data ) && '' !== $elementor_data ) {
            $form_ids = array_merge( $form_ids, $this->elementor_data_form_ids( $elementor_data ) );
        }

        return $this->normalize_form_ids( $form_ids );
    }

    private function current_singular_post_form_ids() : array {
        if ( ! is_singular() ) {
            return [];
        }

        $post = get_post();

        return $post instanceof WP_Post ? $this->post_form_ids( $post ) : [];
    }

    private function matching_theme_document_form_ids() : array {
        if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
            return [];
        }

        $theme_builder = \ElementorPro\Modules\ThemeBuilder\Module::instance();

        if ( ! method_exists( $theme_builder, 'get_conditions_manager' ) ) {
            return [];
        }

        $conditions_manager = $theme_builder->get_conditions_manager();
        if ( ! method_exists( $conditions_manager, 'get_documents_for_location' ) ) {
            return [];
        }

        $form_ids = [];

        foreach ( self::THEME_TEMPLATE_TYPES as $location ) {
            $documents = $conditions_manager->get_documents_for_location( $location );

            foreach ( $documents as $document ) {
                $post_id = $this->document_post_id( $document );
                $post    = $post_id ? get_post( $post_id ) : null;

                if ( $post instanceof WP_Post ) {
                    $form_ids = array_merge( $form_ids, $this->post_form_ids( $post ) );
                }
            }
        }

        return $this->normalize_form_ids( $form_ids );
    }

    private function document_post_id( $document ) : int {
        if ( ! is_object( $document ) ) {
            return 0;
        }

        if ( method_exists( $document, 'get_main_id' ) ) {
            return (int) $document->get_main_id();
        }

        return method_exists( $document, 'get_id' ) ? (int) $document->get_id() : 0;
    }

    /**
     * @return int[]
     */
    private function shortcode_form_ids( string $content ) : array {
        if ( false === strpos( $content, '[formgent' ) ) {
            return [];
        }

        $form_ids = [];
        $pattern  = get_shortcode_regex( [ 'formgent' ] );

        if ( preg_match_all( "/{$pattern}/s", $content, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                if ( 'formgent' !== $match[2] ) {
                    continue;
                }

                $attributes = shortcode_parse_atts( $match[3] );
                if ( is_array( $attributes ) && ! empty( $attributes['id'] ) ) {
                    $form_ids[] = (int) $attributes['id'];
                }
            }
        }

        return $form_ids;
    }

    /**
     * @param array[] $blocks Parsed WordPress blocks.
     * @return int[]
     */
    private function block_form_ids( array $blocks ) : array {
        $form_ids = [];

        foreach ( $blocks as $block ) {
            if ( 'formgent/form' === ( $block['blockName'] ?? '' ) && ! empty( $block['attrs']['formId'] ) ) {
                $form_ids[] = (int) $block['attrs']['formId'];
            }

            if ( ! empty( $block['innerBlocks'] ) ) {
                $form_ids = array_merge( $form_ids, $this->block_form_ids( $block['innerBlocks'] ) );
            }
        }

        return $form_ids;
    }

    /**
     * @return int[]
     */
    private function elementor_data_form_ids( string $elementor_data ) : array {
        $form_ids = $this->shortcode_form_ids( $elementor_data );
        $elements = json_decode( $elementor_data, true );

        if ( ! is_array( $elements ) ) {
            return $form_ids;
        }

        return array_merge( $form_ids, $this->elementor_element_form_ids( $elements ) );
    }

    /**
     * @return int[]
     */
    private function elementor_element_form_ids( array $elements ) : array {
        $form_ids = [];

        foreach ( $elements as $element ) {
            if ( ! is_array( $element ) ) {
                continue;
            }

            if ( 'formgent_forms' === ( $element['widgetType'] ?? '' ) && ! empty( $element['settings']['form_id'] ) ) {
                $form_ids[] = (int) $element['settings']['form_id'];
            }

            foreach ( $element['settings'] ?? [] as $value ) {
                if ( is_string( $value ) ) {
                    $form_ids = array_merge( $form_ids, $this->shortcode_form_ids( $value ) );
                }
            }

            if ( ! empty( $element['elements'] ) ) {
                $form_ids = array_merge( $form_ids, $this->elementor_element_form_ids( $element['elements'] ) );
            }
        }

        return $form_ids;
    }

    /**
     * @param mixed[] $form_ids
     * @return int[]
     */
    private function normalize_form_ids( array $form_ids ) : array {
        $form_ids = array_map( 'intval', $form_ids );
        $form_ids = array_filter( $form_ids );

        return array_values( array_unique( $form_ids ) );
    }
}
