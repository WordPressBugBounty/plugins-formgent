<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Widgets\Form;
use FormGent\App\Widgets\Controls\Select;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\WpMVC\Enqueue\Enqueue;

class ElementorServiceProvider implements Provider {
    public function boot() {
        add_action( 'init', [ $this, 'action_init' ] );
    }

    /**
     * Fires after WordPress has finished loading but before any headers are sent.
     *
     */
    public function action_init() : void {
        add_action( 'elementor/widgets/register', [$this, 'register_widgets'] );
        add_action( 'elementor/controls/register', [$this, 'register_controls'] );
        add_action( 'elementor/editor/before_enqueue_scripts', [$this, 'enqueue_scripts'] );
    }

    /**
     * Register Elementor widgets.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     * @return void
     */
    public function register_widgets( $widgets_manager ) {
        $widgets_manager->register( new Form );
    }

    /**
     * Register Currency Control.
     *
     * Include control file and register control class.
     *
     * @since 1.0.0
     * @param \Elementor\Controls_Manager $controls_manager Elementor controls manager.
     * @return void
     */
    public function register_controls( $controls_manager ) {
        $controls_manager->register( new Select );
    }

    public function enqueue_scripts() {
        Enqueue::script( 'formgent/admin/elementor', 'build/js/elementor', [], true );
    }
}