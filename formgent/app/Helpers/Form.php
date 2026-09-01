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
     * @param bool  $include_choice_scoring Whether choice defaults and scoring metadata may be exposed.
     * @return void
     */
    protected function remove_labels( array &$attributes, bool $include_choice_scoring = true ): void {
        // Keep a minimal representation of choice options for frontend runtime features
        // (e.g. calculations and conditional logic using selected option values).
        if ( isset( $attributes['options'] ) && is_array( $attributes['options'] ) ) {
            $option_keys = [ 'label', 'value', 'is_other' ];

            if ( $include_choice_scoring ) {
                $option_keys = array_merge( $option_keys, [ 'numeric_value', 'price', 'is_default' ] );
            }

            $attributes['options'] = array_map(
                static function( $opt ) use ( $option_keys ) {
                    if ( ! is_array( $opt ) ) {
                        return $opt;
                    }

                    $keep = [];
                    foreach ( $option_keys as $k ) {
                        if ( array_key_exists( $k, $opt ) ) {
                            $keep[ $k ] = $opt[ $k ];
                        }
                    }
                    return $keep;
                },
                $attributes['options']
            );
        }

        foreach ( [
            'label',
            'sub_label',
            'description',
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
            // Strip quiz answer data to prevent cheating via DOM inspection
            'correct_answer',
            'points',
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
     * @param bool $include_choice_scoring Whether choice defaults and scoring metadata may be exposed.
     * @return array Array of field settings indexed by $array_key.
     */
    public function get_form_field_settings(
        array $parsed_blocks,
        bool $remove_label = false,
        string $array_key = 'name',
        bool $allowed_core_blocks = false,
        bool $include_choice_scoring = true
    ): array {
        $blocks            = formgent_config( 'blocks' );
        $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $settings          = [];

        foreach ( $parsed_blocks as $parsed_block ) {
            if ( empty( $parsed_block['blockName'] ) ) {
                continue;
            }

            $block_name = $parsed_block['blockName'];
            $attrs      = $parsed_block['attrs'] ?? [];

            if ( $allowed_core_blocks && in_array( $block_name, ['core/heading'], true ) ) { // core/paragraph
                $attributes               = $attrs;
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
                $attrs
            );

            // Remove UI-only fields if required
            if ( $remove_label ) {
                $this->remove_labels( $attributes, $include_choice_scoring );
            } else {
                $this->sanitize_placeholders( $attributes );
            }

            $field_type  = $blocks[$block_name]['field_type'];
            $setting_key = $attributes[$array_key] ?? null;
            if ( null === $setting_key ) {
                continue;
            }

            $attributes['field_type'] = $field_type;
            $settings[$setting_key]   = $attributes;

            if ( ! empty( $parsed_block['innerBlocks'] ) ) {
                $settings[$setting_key]['children'] = $this->get_form_field_settings(
                    $parsed_block['innerBlocks'],
                    $remove_label,
                    'name',
                    false,
                    $include_choice_scoring
                );
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
     * @param bool $include_choice_scoring Whether choice defaults and scoring metadata may be exposed.
     * @return array Array of field settings indexed by $array_key or step ID.
     */
    public function get_conversational_form_field_settings(
        array $parsed_blocks,
        bool $remove_label = false,
        string $array_key = 'name',
        bool $include_choice_scoring = true
    ): array {
        $blocks            = formgent_config( 'blocks' );
        $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
        $settings          = [];
        $i                 = 0; // Used to determine which step gets "show = true"
        $last_label        = ''; // Used to capture the latest heading (for implied labels)

        foreach ( $parsed_blocks as $parsed_block ) {
            $block_name = $parsed_block['blockName'] ?? null;
            $attrs      = $parsed_block['attrs'] ?? [];

            if ( ! $block_name || in_array( $block_name, ['formgent/submit-button', 'formgent/next-button', 'formgent/info'], true ) ) {
                continue;
            }

            // Capture core/heading as fallback label for next field
            if ( ! isset( $blocks[$block_name] ) ) {
                if ( ! $remove_label && 'core/heading' === $block_name ) {
                    $last_label = wp_strip_all_tags( $parsed_block['innerHTML'] ?? '' );
                }
                continue;
            }

            $attributes = array_merge(
                $this->get_default_attributes( $block_name, $registered_blocks ),
                $attrs
            );

            if ( $remove_label ) {
                $this->remove_labels( $attributes, $include_choice_scoring );
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
                $setting_key = $attributes['id'] ?? null;
                if ( null === $setting_key ) {
                    continue;
                }

                if ( 0 === $i ) {
                    $attributes['show'] = true;
                } else {
                    $attributes['show'] = false;
                }
                $i++;
            } else {
                $setting_key = $attributes[$array_key] ?? null;
                if ( null === $setting_key ) {
                    continue;
                }
            }

            $attributes['field_type'] = $field_type;
            $settings[$setting_key]   = $attributes;

            // Recurse into inner blocks if available
            if ( ! empty( $parsed_block['innerBlocks'] ) ) {
                $settings[$setting_key]['children'] = $this->get_conversational_form_field_settings(
                    $parsed_block['innerBlocks'],
                    $remove_label,
                    $array_key,
                    $include_choice_scoring
                );
            }
        }

        return $settings;
    }

    /**
     * Build the static conversational summary used when interactive bindings
     * are unavailable in a builder preview.
     *
     * @param array $parsed_blocks Parsed form blocks.
     * @return array{question_count:string,time_to_complete:string}
     */
    public function get_conversational_summary( array $parsed_blocks ): array {
        $settings    = $this->get_conversational_form_field_settings( $parsed_blocks, true );
        $total_steps = count(
            array_filter(
                $settings,
                static function ( array $setting ): bool {
                    return 'step' === ( $setting['field_type'] ?? '' );
                }
            )
        );

        $time_in_seconds = (int) round( ( $total_steps * 2 * 60 ) / 8 / 10 ) * 10;
        $minutes         = (int) floor( $time_in_seconds / 60 );
        $seconds         = $time_in_seconds % 60;
        $time_parts      = [];

        if ( $minutes > 0 ) {
            $time_parts[] = sprintf(
                _n( '%s minute', '%s minutes', $minutes, 'formgent' ),
                number_format_i18n( $minutes )
            );
        }

        if ( $seconds > 0 || empty( $time_parts ) ) {
            $time_parts[] = sprintf(
                _n( '%s second', '%s seconds', $seconds, 'formgent' ),
                number_format_i18n( $seconds )
            );
        }

        return [
            'question_count'   => sprintf(
                _n( '%s Question', '%s Questions', $total_steps, 'formgent' ),
                number_format_i18n( $total_steps )
            ),
            'time_to_complete' => implode( ' ', $time_parts ),
        ];
    }

    /**
     * Check if form has inline submit, login, or page-break blocks.
     *
     * @param array $blocks Parsed blocks from post content
     * @return bool True if those blocks exist (floating default submit not needed).
     */
    public function has_inline_submit_button_or_page_break( $blocks ) {
        if ( ! is_array( $blocks ) ) {
            return false;
        }

        foreach ( $blocks as $parsed_block ) {
            if ( empty( $parsed_block['blockName'] ) ) {
                continue;
            }

            $block_name = $parsed_block['blockName'];

            // Submit / login / paging: no floating default submit needed.
            if ( in_array( $block_name, ['formgent/submit-button', 'formgent/page-break', 'formgent/login'], true ) ) {
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

    /**
     * Check if parsed blocks contain a specific block type.
     *
     * @param array $blocks Parsed blocks from post content
     * @param string $block_name Block name to search for
     * @return bool True if block exists
     */
    public function parsed_blocks_contain( $blocks, $block_name ) {
        if ( ! is_array( $blocks ) ) {
            return false;
        }

        foreach ( $blocks as $parsed_block ) {
            if ( empty( $parsed_block['blockName'] ) ) {
                continue;
            }

            if ( $parsed_block['blockName'] === $block_name ) {
                return true;
            }

            // Check inner blocks recursively
            if ( ! empty( $parsed_block['innerBlocks'] ) ) {
                if ( $this->parsed_blocks_contain( $parsed_block['innerBlocks'], $block_name ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Strip inline submit buttons when login form is present.
     *
     * @param string $post_content The post content
     * @return string Modified post content
     */
    public function strip_inline_submit_when_login_form( $post_content ) {
        // Parse blocks
        $blocks = parse_blocks( $post_content );

        // Filter blocks to remove submit buttons outside login blocks
        $filtered_blocks = $this->filter_blocks_remove_submit_outside_login( $blocks );

        // Serialize back to content
        return serialize_blocks( $filtered_blocks );
    }

    /**
     * Recursively filter blocks to remove submit buttons outside login blocks.
     *
     * @param array $blocks Parsed blocks
     * @param bool $inside_login Whether we're currently inside a login block
     * @return array Filtered blocks
     */
    protected function filter_blocks_remove_submit_outside_login( $blocks, $inside_login = false ) {
        $filtered = [];

        foreach ( $blocks as $block ) {
            // Handle HTML content blocks (null blockName)
            if ( empty( $block['blockName'] ) ) {
                $filtered[] = $block;
                continue;
            }

            $block_name = $block['blockName'];

            // Check if we're entering a login block
            $is_login_block = ( $block_name === 'formgent/login' );
            if ( $is_login_block ) {
                $inside_login = true;
            }

            // Remove submit button if we're not inside login block
            if ( $block_name === 'formgent/submit-button' && ! $inside_login ) {
                continue; // Skip this block (don't add to filtered)
            }

            // Process inner blocks recursively
            if ( ! empty( $block['innerBlocks'] ) ) {
                $block['innerBlocks'] = $this->filter_blocks_remove_submit_outside_login(
                    $block['innerBlocks'],
                    $inside_login
                );
            }

            $filtered[] = $block;

            // Reset inside_login after processing login block
            if ( $is_login_block ) {
                $inside_login = false;
            }
        }

        return $filtered;
    }
}
