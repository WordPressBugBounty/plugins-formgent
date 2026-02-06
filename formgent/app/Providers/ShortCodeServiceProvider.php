<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Post;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\WpMVC\View\View;

class ShortCodeServiceProvider implements Provider {
    public function boot() {
        add_action( 'init', [ $this, 'action_init' ] );
    }

    /**
     * Fires after WordPress has finished loading but before any headers are sent.
     */
    public function action_init() : void {
        add_shortcode( 'formgent', [$this, 'content'] );
    }

    public function content( array $attributes ) {
        if ( empty( $attributes['id'] ) ) {
            return 'id is required.';
        }

        $form_id = apply_filters( 'formgent_form_id',  intval( $attributes['id'] ) );
        $form    = get_post( $form_id );

        if ( empty( $form ) ) {
            return;
        }

        unset( $attributes['id'] );
        $external_data = $attributes;

        return View::get(
            'form', [
                'form'          => $form,
                'css_class'     => 'formgent-shortcode',
                'external_data' => $external_data
            ]
        );
    }
}