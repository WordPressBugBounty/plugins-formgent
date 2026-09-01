<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\FrontendAssetManager;
use FormGent\WpMVC\Contracts\Provider;

class FrontendAssetServiceProvider implements Provider {
    public function boot() : void {
        $manager = formgent_singleton( FrontendAssetManager::class );

        add_action( 'wp_enqueue_scripts', [ $manager, 'enqueue_current_page_assets' ], 100 );

        // Elementor's optimized Gutenberg loading dequeues core block styles at priority 999.
        add_action( 'wp_enqueue_scripts', [ $manager, 'enqueue_elementor_preview_assets' ], 1000 );
        add_action( 'formgent_before_load_form', [ $manager, 'enqueue_rendered_form_assets' ], 1 );
        add_action( 'formgent_form_asset_cache_clear', [ $manager, 'clear_form_asset_cache' ] );

        foreach ( [ 'header', 'footer', 'single', 'archive' ] as $location ) {
            add_action(
                "elementor/theme/before_do_{$location}",
                static function ( $locations_manager ) use ( $manager, $location ) : void {
                    $manager->enqueue_theme_location_assets( $location, $locations_manager );
                }
            );
        }
    }
}
