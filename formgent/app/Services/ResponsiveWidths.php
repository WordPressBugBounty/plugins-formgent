<?php

namespace FormGent\App\Services;

defined( 'ABSPATH' ) || exit;

/**
 * Opt-in widths shared by Free and Pro fields, including nested fields.
 */
class ResponsiveWidths {
    private const WIDTHS = ['100', '75', '67', '50', '33.33', '25'];

    public static function register_attributes( array $args, string $name ): array {
        if ( 0 !== strpos( $name, 'formgent/' ) || ! isset( $args['attributes']['block_width'] ) ) {
            return $args;
        }

        foreach ( ['tablet', 'mobile'] as $device ) {
            $args['attributes'][ 'block_width_' . $device ] = [
                'type'    => 'string',
                'default' => '',
                'enum'    => array_merge( [''], self::WIDTHS ),
            ];
        }

        return $args;
    }

    public static function render( string $content, array $block ): string {
        if ( 0 !== strpos( $block['blockName'] ?? '', 'formgent/' ) ) {
            return $content;
        }

        $classes = [];
        foreach ( ['tablet', 'mobile'] as $device ) {
            $width = $block['attrs'][ 'block_width_' . $device ] ?? '';
            if ( in_array( $width, self::WIDTHS, true ) ) {
                $classes[] = 'formgent-field-width-' . $device . '-' . (int) $width;
            }
        }

        // Leave legacy markup byte-for-byte unchanged without valid overrides.
        if ( empty( $classes ) ) {
            return $content;
        }

        $processor = new \WP_HTML_Tag_Processor( $content );
        // Only style this block's outer field, never a nested child's wrapper.
        if ( $processor->next_tag() && $processor->has_class( 'formgent-field' ) ) {
            foreach ( $classes as $class_name ) {
                $processor->add_class( $class_name );
            }
        }

        return $processor->get_updated_html();
    }
}
