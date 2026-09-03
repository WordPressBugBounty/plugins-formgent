<?php

namespace FormGent\App\Services;

defined( 'ABSPATH' ) || exit;

use WP_Block_Type_Registry;
use WP_Post;

class FrontendAssetManager {
    private const CACHE_GROUP = 'formgent';

    /**
     * CSS from the legacy consolidated frontend stylesheet that is required by
     * specific block families in Elementor's dependency-managed path.
     */
    private const BLOCK_STYLE_DEPENDENCIES = [
        'formgent/address'         => [ 'dropdown' ],
        'formgent/dropdown'        => [ 'dropdown' ],
        'formgent/single-choice'   => [ 'choice' ],
        'formgent/multiple-choice' => [ 'choice' ],
        'formgent/gdpr'            => [ 'gdpr' ],
        'formgent/phone-number'    => [ 'phone-number' ],
        'formgent/range-slider'    => [ 'range-slider' ],
        'formgent/step'            => [ 'conversational' ],
    ];

    private ElementorFormAssetDetector $detector;

    public function __construct( ElementorFormAssetDetector $detector ) {
        $this->detector = $detector;
    }

    public function enqueue_current_page_assets() : void {
        if ( is_admin() || formgent_is_elementor_preview() ) {
            return;
        }

        $form_ids      = $this->detector->current_page_form_ids();
        $compatibility = $this->elementor_compatibility_required();

        if ( $compatibility ) {
            $this->dequeue_globally_loaded_formgent_styles();
        }

        if ( ! empty( $form_ids ) ) {
            $this->enqueue_form_assets( $form_ids, $compatibility );
        } elseif ( $this->detector->published_theme_templates_contain_form() ) {
            wp_enqueue_script_module( 'formgent/interactivity-importmap' );
        }
    }

    /**
     * Preload the complete frontend runtime for the Elementor preview document.
     */
    public function enqueue_elementor_preview_assets() : void {
        if ( ! formgent_is_elementor_preview() ) {
            return;
        }

        $this->enqueue_core_block_library();
        $this->enqueue_full_frontend_style();
        $this->enqueue_frontend_scripts();

        // Let extensions preload their frontend assets before dynamic widgets render.
        do_action( 'formgent_frontend_form_assets', [], [], false );
    }

    /**
     * Enqueue assets for forms rendered from an Elementor theme location.
     */
    public function enqueue_theme_location_assets( string $location, $locations_manager ) : void {
        $form_ids = $this->detector->theme_location_form_ids( $location, $locations_manager );

        if ( ! empty( $form_ids ) ) {
            $this->enqueue_form_assets( $form_ids, $this->elementor_compatibility_required() );
        }
    }

    /**
     * Render-time safety net for dynamic/AJAX content that was not discoverable in wp_head.
     */
    public function enqueue_rendered_form_assets( $form ) : void {
        if ( $form instanceof WP_Post ) {
            $this->enqueue_form_assets( [ (int) $form->ID ], $this->elementor_compatibility_required() );
        }
    }

    /**
     * @param int[] $form_ids Form post IDs.
     */
    public function enqueue_form_assets( array $form_ids, bool $compatibility = false ) : void {
        $form_ids  = array_values( array_unique( array_filter( array_map( 'intval', $form_ids ) ) ) );
        $manifests = [];

        foreach ( $form_ids as $form_id ) {
            $manifest = $this->form_asset_manifest( $form_id );

            if ( ! empty( $manifest['blocks'] ) ) {
                $manifests[] = $manifest;
            }
        }

        if ( empty( $manifests ) ) {
            return;
        }

        if ( $compatibility ) {
            $this->enqueue_compatibility_styles( $manifests );
        } else {
            $this->enqueue_full_frontend_style();
        }

        $this->enqueue_frontend_scripts();

        do_action( 'formgent_frontend_form_assets', $form_ids, $manifests, $compatibility );
    }

    public function clear_form_asset_cache( int $form_id ) : void {
        wp_cache_delete( $this->manifest_cache_key( $form_id ), self::CACHE_GROUP );
    }

    public function elementor_compatibility_required() : bool {
        if ( is_admin() || is_singular( formgent_post_type() ) || formgent_is_elementor_preview() ) {
            return false;
        }

        return class_exists( '\Elementor\Plugin' );
    }

    private function enqueue_full_frontend_style() : void {
        wp_enqueue_style(
            'formgent/blocks-frontend',
            formgent_url( 'assets/build/css/blocks-frontend.css' ),
            [],
            formgent_version()
        );
    }

    private function enqueue_frontend_scripts() : void {
        wp_enqueue_script( 'lodash' );
        wp_enqueue_script( 'wp-api-fetch' );
        wp_enqueue_script( 'formgent/jquery-input-mask' );
        wp_enqueue_script_module( 'formgent/blocks-frontend' );
    }

    /**
     * @param array[] $manifests Form asset manifests.
     */
    private function enqueue_compatibility_styles( array $manifests ) : void {
        $block_names        = [];
        $style_dependencies = [];

        foreach ( $manifests as $manifest ) {
            $block_names = array_merge( $block_names, $manifest['blocks'] );
        }

        wp_enqueue_style(
            'formgent/blocks-frontend-core',
            formgent_url( 'assets/build/css/blocks-frontend-core.css' ),
            [],
            formgent_version()
        );

        $block_names = array_unique( $block_names );

        if ( $this->has_core_blocks( $block_names ) ) {
            $this->enqueue_core_block_library();
        }

        foreach ( $block_names as $block_name ) {
            $style_dependencies = array_merge(
                $style_dependencies,
                self::BLOCK_STYLE_DEPENDENCIES[ $block_name ] ?? []
            );
        }

        foreach ( array_unique( $style_dependencies ) as $style_dependency ) {
            wp_enqueue_style(
                "formgent/blocks-frontend-{$style_dependency}",
                formgent_url( "assets/build/css/blocks-frontend-{$style_dependency}.css" ),
                [],
                formgent_version()
            );
        }

        $registry = WP_Block_Type_Registry::get_instance();

        foreach ( $block_names as $block_name ) {
            $block_type = $registry->get_registered( $block_name );

            if ( ! $block_type ) {
                continue;
            }

            foreach ( array_merge( $block_type->style_handles, $block_type->view_style_handles ) as $style_handle ) {
                if ( 'formgent/blocks-frontend' !== $style_handle ) {
                    wp_enqueue_style( $style_handle );
                }
            }
        }
    }

    private function dequeue_globally_loaded_formgent_styles() : void {
        $registry = WP_Block_Type_Registry::get_instance();

        foreach ( $registry->get_all_registered() as $block_name => $block_type ) {
            if ( 0 !== strpos( $block_name, 'formgent/' ) ) {
                continue;
            }

            foreach ( array_merge( $block_type->style_handles, $block_type->view_style_handles ) as $style_handle ) {
                wp_dequeue_style( $style_handle );
            }
        }

        wp_dequeue_style( 'formgent/blocks-frontend' );
        wp_dequeue_style( 'formgent/blocks-frontend-core' );

        foreach ( array_unique( array_merge( ...array_values( self::BLOCK_STYLE_DEPENDENCIES ) ) ) as $style_dependency ) {
            wp_dequeue_style( "formgent/blocks-frontend-{$style_dependency}" );
        }

        wp_dequeue_style( 'formgent-pro/blocks-frontend' );
    }

    /**
     * @return array{blocks:string[]}
     */
    private function form_asset_manifest( int $form_id ) : array {
        $form = formgent_get_form_post( $form_id );

        // Draft/private previews are valid for users who can view the form.
        // Check visibility before consulting the cache so a previously cached
        // manifest cannot make assets available after access is revoked.
        if ( ! $form || ! formgent_is_form_visible( $form ) ) {
            return [ 'blocks' => [] ];
        }

        $cache_key = $this->manifest_cache_key( $form_id );
        $manifest  = wp_cache_get( $cache_key, self::CACHE_GROUP );

        if ( is_array( $manifest ) ) {
            return $manifest;
        }

        $blocks   = $this->collect_block_names( parse_blocks( $form->post_content ) );
        $blocks   = array_values( array_unique( array_merge( [ 'formgent/form' ], $blocks ) ) );
        $manifest = [
            'blocks' => $blocks,
        ];

        wp_cache_set( $cache_key, $manifest, self::CACHE_GROUP );

        return $manifest;
    }

    /**
     * @param array[] $blocks Parsed WordPress blocks.
     * @return string[]
     */
    private function collect_block_names( array $blocks ) : array {
        $block_names = [];

        foreach ( $blocks as $block ) {
            $block_name = $block['blockName'] ?? '';

            if ( '' !== $block_name ) {
                $block_names[] = $block_name;
            }

            if ( ! empty( $block['innerBlocks'] ) ) {
                $block_names = array_merge( $block_names, $this->collect_block_names( $block['innerBlocks'] ) );
            }
        }

        return $block_names;
    }

    private function manifest_cache_key( int $form_id ) : string {
        return "form_{$form_id}_asset_manifest_v2";
    }

    /**
     * @param string[] $block_names Registered block names.
     */
    private function has_core_blocks( array $block_names ) : bool {
        foreach ( $block_names as $block_name ) {
            if ( 0 === strpos( $block_name, 'core/' ) ) {
                return true;
            }
        }

        return false;
    }

    private function enqueue_core_block_library() : void {
        wp_enqueue_style( 'wp-block-library' );
    }
}
