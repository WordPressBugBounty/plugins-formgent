<?php

namespace FormGent\App\Widgets;

defined( 'ABSPATH' ) || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use FormGent\WpMVC\View\View;

class Form extends Widget_Base {
    public static function get_type() {
        return 'widget';
    }

    public function get_name() {
        return 'formgent_forms';
    }

    public function get_title() {
        return 'FormGent';
    }

    public function get_keywords() {
        return [ 'form', 'forms', 'formgent'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __( 'General', 'formgent' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label'       => esc_html__( 'Select Form', 'formgent' ),
                'label_block' => true,
                'type'        => 'formgent-select',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( '0' === $settings['form_id'] ) {
            return;
        }

        $form_id = apply_filters( 'formgent_form_id', absint( $settings['form_id'] ) );
        $form    = formgent_get_form_post( $form_id, true );

        if ( empty( $form ) ) {
            View::render( 'form-unavailable' );
            return;
        }

        View::render(
            'form', [
                'form' => $form
            ]
        );
    }
}
