<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Fields\FileUpload\FileUpload;
use FormGent\WpMVC\Contracts\Provider;

class BlockServiceProvider implements Provider {
    public function boot() {
        add_action( 'init', [ $this, 'action_init' ] );
        add_filter( 'formgent_pagination_summery', [$this, 'get_summary'], 10, 2 );
    }

    public function get_summary( $answers, $field ) {
        if ( FileUpload::get_key() === $field['field_type'] ) {
            $answers = array_map(
                function( $answer ) {
                    $answer->value = array_map(
                        function( $value ) {
                            return WP_CONTENT_URL . '/uploads/' . $value;
                        }, json_decode( $answer->value )
                    );
                    return $answer;
                }, $answers
            );
        }
        return $answers;
    }

    /**
     * Fires after WordPress has finished loading but before any headers are sent.
     */
    public function action_init() : void {
        foreach ( formgent_config( 'blocks' ) as $block_name => $block_data ) {
            $name = ltrim( $block_name, 'formgent' );

            wp_enqueue_block_style(
                $block_name, [
                    'handle' => 'formgent/blocks-frontend',
                    'src'    => formgent_url( 'assets/build/css/blocks-frontend.css' )
                ]
            );
            register_block_type( $block_data['dir'] . '/' . $name );
        }

        //add_filter( 'render_block_core/paragraph', [$this, 'add_custom_attributes'], 10, 2 );
        add_filter( 'render_block_core/heading', [$this, 'add_custom_attributes'], 10, 2 );
    }

    public function add_custom_attributes( $block_content, $block ) {
        $post = get_post();

        if ( ! $post || "formgent_form" !== $post->post_type || empty( $block_content ) ) {
            return $block_content;
        }

        $processor = new \WP_HTML_Tag_Processor( $block_content );

        if ( $processor->next_tag( $this->get_html_tag_name( $block_content ) ) ) {
            $processor->add_class( 'display-none' );
            $processor->set_attribute( 'data-wp-bind--hidden', 'state.hideField' );

            if ( ! empty( $block["attrs"]["name"] ) ) {
                $name = $block["attrs"]["name"];
                $processor->set_attribute( 'data-wp-context', '{ "name": "' . $name . '" }' );
            }
        }

        return $processor->get_updated_html();
    }

    private function get_html_tag_name( $html ) {
        $dom = new \DOMDocument;
        libxml_use_internal_errors( true ); // Suppress warnings for malformed HTML
        $dom->loadHTML( $html );

        // Find the first child of the <body> element
        $body = $dom->getElementsByTagName( 'body' )->item( 0 );
        //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        $first_element = $body->firstChild;

        // Get the tag name
        //phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
        $tag_name = $first_element->nodeName;
        return $tag_name;
    }
}