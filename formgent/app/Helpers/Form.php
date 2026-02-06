<?php

namespace FormGent\App\Helpers;

defined( 'ABSPATH' ) || exit;

use WP_Block_Type_Registry;

/**
 * Helper class to extract and normalize Gutenberg form block settings.
 */
class Form {
    /**
     * Fetch default attributes for a block from the block registry.
     *
     * @param string $block_name The block name (e.g. "formgent/input").
     * @param array $registered_blocks Array of registered blocks from WordPress.
     * @return array Default attributes with their default values.
     */
    protected function get_default_attributes( string $block_name, array $registered_blocks ): array {
        $defaults = [];

        if ( ! empty( $registered_blocks[$block_name] ) ) {
            foreach ( $registered_blocks[$block_name]->get_attributes() as $key => $attr ) {
                if ( isset( $attr['default'] ) ) {
                    $defaults[$key] = $attr['default'];
                }
            }
        }

        return $defaults;
    }

    /**
     * Sanitize all placeholder-related fields in block attributes.
     *
     * @param array $attributes Reference to attributes to modify in-place.
     * @return void
     */
    protected function sanitize_placeholders( array &$attributes ): void {
        foreach ( [
            'placeholder',
            'date_placeholder',
            'time_placeholder',
            'date_time_placeholder',
            'start_placeholder',
            'end_placeholder',
            'upload_text',
            'limit_text',
            'limit_files_text'
        ] as $key ) {
            if ( isset( $attributes[$key] ) ) {
                $attributes[$key] = esc_attr( $attributes[$key] );
            }
        }
    }

    /**
     * Remove label and visual-only fields from block attributes.
     *
     * @param array $attributes Reference to attributes to modify in-place.
     * @return void
     */
    protected function remove_labels( array &$attributes ): void {
        foreach ( [
            'label',
            'sub_label',
            'description',
            'options',
            'button_text',
            'placeholder',
            'date_placeholder',
            'time_placeholder',
            'date_time_placeholder',
            'start_placeholder',
            'end_placeholder',
            'upload_text',
            'limit_text',
            'limit_files_text',
        ] as $key ) {
            unset( $attributes[$key] );
        }
    }

    /**
     * Parse Gutenberg form blocks into structured field settings for classic forms.
     *
     * @param array $parsed_blocks Parsed Gutenberg blocks.
     * @param bool $remove_label Whether to remove label-related UI fields.
     * @param string $array_key The key to use as the map key in the output array (e.g., "name").
     * @param bool $allowed_core_blocks Whether to allow specific core blocks like heading or paragraph.
     * @return array Array of field settings indexed by $array_key.
     */
    public function get_form_field_settings(
        array $parsed_blocks,
        bool $remove_label = false,
        string $array_key = 'name',
        bool $allowed_core_blocks = false
    ): array {
        $blocks            = formgent_config( 'blocks' );
        $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $settings          = [];

        foreach ( $parsed_blocks as $parsed_block ) {
            if ( empty( $parsed_block['blockName'] ) ) {
                continue;
            }

            $block_name = $parsed_block['blockName'];

            if ( $allowed_core_blocks && in_array( $block_name, ['core/heading'], true ) ) { // core/paragraph
                $attributes               = $parsed_block['attrs'];
                $attributes['field_type'] = explode( '/', $block_name )[1];

                if ( isset( $attributes[$array_key] ) ) {
                    $setting_key            = $attributes[$array_key];
                    $settings[$setting_key] = $attributes;
                }
                continue;
            }

            // Skip blocks not registered or intentionally excluded
            if ( ! isset( $blocks[$block_name] ) || in_array( $block_name, ['formgent/submit-button', 'formgent/next-button', 'formgent/info'], true ) ) {
                continue;
            }

            // Merge default attributes with actual block attributes
            $attributes = array_merge(
                $this->get_default_attributes( $block_name, $registered_blocks ),
                $parsed_block['attrs']
            );

            // Remove UI-only fields if required
            if ( $remove_label ) {
                $this->remove_labels( $attributes );
            } else {
                $this->sanitize_placeholders( $attributes );
            }

            $field_type  = $blocks[$block_name]['field_type'];
            $setting_key = $attributes[$array_key];

            $attributes['field_type'] = $field_type;
            $settings[$setting_key]   = $attributes;

            if ( ! empty( $parsed_block['innerBlocks'] ) ) {
                $settings[$setting_key]['children'] = $this->get_form_field_settings( $parsed_block['innerBlocks'], $remove_label );
            }
        }

        return $settings;
    }

    /**
     * Parse Gutenberg form blocks into settings for conversational forms (chat-style layout).
     *
     * Adds dynamic label fallback using previous core/heading and sets visibility per step.
     *
     * @param array $parsed_blocks Parsed Gutenberg blocks.
     * @param bool $remove_label Whether to remove label and placeholders.
     * @param string $array_key Key to identify field (e.g., "name").
     * @return array Array of field settings indexed by $array_key or step ID.
     */
    public function get_conversational_form_field_settings(
        array $parsed_blocks,
        bool $remove_label = false,
        string $array_key = 'name'
    ): array {
        $blocks            = formgent_config( 'blocks' );
        $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $settings          = [];
        $i                 = 0; // Used to determine which step gets "show = true"
        $last_label        = ''; // Used to capture the latest heading (for implied labels)

        foreach ( $parsed_blocks as $parsed_block ) {
            $block_name = $parsed_block['blockName'] ?? null;

            if ( ! $block_name || in_array( $block_name, ['formgent/submit-button', 'formgent/next-button', 'formgent/info'], true ) ) {
                continue;
            }

            // Capture core/heading as fallback label for next field
            if ( ! isset( $blocks[$block_name] ) ) {
                if ( ! $remove_label && 'core/heading' === $block_name ) {
                    $last_label = wp_strip_all_tags( $parsed_block['innerHTML'] );
                }
                continue;
            }

            $attributes = array_merge(
                $this->get_default_attributes( $block_name, $registered_blocks ),
                $parsed_block['attrs']
            );

            if ( $remove_label ) {
                $this->remove_labels( $attributes );
            } else {
                // Fallback label assignment if label is missing
                if ( empty( $attributes['label'] ) && ! empty( $attributes['name'] ) ) {
                    $attributes['label'] = $last_label ?: str_replace( '-', ' ', $attributes['name'] );
                }
                $last_label = '';
                $this->sanitize_placeholders( $attributes );
            }

            $field_type = $blocks[$block_name]['field_type'];

            // Ensure only first step is visible by default
            if ( in_array( $field_type, ['step', 'welcome', 'end'], true ) ) {
                $setting_key = $attributes['id'];

                if ( 0 === $i ) {
                    $attributes['show'] = true;
                } else {
                    $attributes['show'] = false;
                }
                $i++;
            } else {
                $setting_key = $attributes[$array_key];
            }

            $attributes['field_type'] = $field_type;
            $settings[$setting_key]   = $attributes;

            // Recurse into inner blocks if available
            if ( ! empty( $parsed_block['innerBlocks'] ) ) {
                $settings[$setting_key]['children'] = $this->get_conversational_form_field_settings( $parsed_block['innerBlocks'], $remove_label, $array_key );
            }
        }

        return $settings;
    }

    /**
     * Check if form has inline submit button or page-break blocks
     *
     * @param array $blocks Parsed blocks from post content
     * @return bool True if inline submit button or page-break blocks exist
     */
    public function has_inline_submit_button_or_page_break( $blocks ) {
        foreach ( $blocks as $parsed_block ) {
            $block_name = $parsed_block['blockName'];

            // Check for submit button or page-break blocks
            if ( in_array( $block_name, ['formgent/submit-button', 'formgent/page-break'], true ) ) {
                return true;
            }

            // Check inner blocks recursively
            if ( ! empty( $parsed_block['innerBlocks'] ) ) {
                if ( $this->has_inline_submit_button_or_page_break( $parsed_block['innerBlocks'] ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}
