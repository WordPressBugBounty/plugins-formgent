<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Providers\PostTypeServiceProvider;
use FormGent\WpMVC\App;

/**
 * Plugin Name:       FormGent
 * Description:       Next-Gen AI Form Builder for WordPress with Multi-Step, Quizzes, Payments & More.
 * Version:           1.3.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Tested up to:      6.9
 * Author:            wpWax - Contact Form Plugin & WP Form Builder
 * Author URI:        http://wpwax.com
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       formgent
 * Domain Path:       /languages
 */

require_once __DIR__ . '/vendor/vendor-src/autoload.php';
require_once __DIR__ . '/app/Helpers/helper.php';

define( 'FORMGENT_DEPENDENCY_VERSION', '1.0.0' );

final class FormGent {
    private static ?FormGent $instance = null;

    public static function instance(): FormGent {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load(): void {
        register_activation_hook( __FILE__, [$this, 'on_activation'] );
        register_deactivation_hook( __FILE__, [$this, 'on_deactivation'] );

        $application = App::instance();

        $application->boot( __FILE__, __DIR__ );

        /**
         * Fires once activated plugins have loaded.
         */
        add_action(
            'plugins_loaded', function () use ( $application ): void {

                do_action( 'formgent_before_load' );

                $application->load();

                do_action( 'formgent_after_load' );
            }
        );
    }

    public function on_activation(): void {
        new FormGent\App\Setup\Activation();
        PostTypeServiceProvider::register_post_type();
        flush_rewrite_rules();
        add_option( 'formgent_activation_redirect', true );
    }

    public function on_deactivation(): void {
        flush_rewrite_rules();
    }
}

FormGent::instance()->load();
