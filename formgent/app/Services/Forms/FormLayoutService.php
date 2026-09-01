<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Block_Type_Registry;
use WP_Error;

/**
 * Lossless, bounded MCP representation of FormGent's Gutenberg form layout.
 *
 * Only registered FormGent blocks and the two text blocks used by FormGent
 * screens are accepted. Attributes are checked against each block's registry
 * definition, so integrations cannot smuggle post metadata or arbitrary blocks.
 */
class FormLayoutService {
    private const CORE_TEXT_BLOCKS = ['core/heading', 'core/paragraph'];

    /**
     * Core text blocks can register after the Abilities API builds its schemas.
     * Keep their bounded block-support attributes available in both lifecycles.
     */
    private const CORE_TEXT_FALLBACK_ATTRIBUTES = [
        'align'           => ['type' => 'string'],
        'anchor'          => ['type' => 'string'],
        'className'       => ['type' => 'string'],
        'style'           => ['type' => 'object'],
        'backgroundColor' => ['type' => 'string'],
        'textColor'       => ['type' => 'string'],
        'gradient'        => ['type' => 'string'],
        'fontSize'        => ['type' => 'string'],
    ];

    private const MAX_BLOCKS = 250;

    private const SENSITIVE_ATTRIBUTE_KEYS = [
        'api_key',
        'secret_key',
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'license_key',
        'private_key',
        'webhook_secret',
    ];

    private int $block_count = 0;

    /** @var array<string,mixed>|null */
    private ?array $attribute_schema = null;

    private string $attribute_schema_signature = '';

    /** @return array<int,array<string,mixed>> */
    public function read( string $content ): array {
        $this->block_count = 0;

        return $this->read_blocks( parse_blocks( $content ), 0 );
    }

    /**
     * Whether every stored block can be represented by the public layout model.
     */
    public function is_complete( string $content ): bool {
        return $this->blocks_are_supported( parse_blocks( $content ) );
    }

    /**
     * @param array<int,mixed> $layout Public layout.
     * @return array{content:string,layout:array<int,array<string,mixed>>}|WP_Error
     */
    public function build( array $layout ) {
        if ( empty( $layout ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A form layout cannot be empty.', 'formgent' ) );
        }

        $this->block_count = 0;
        $blocks            = $this->prepare_blocks( $layout, 0 );

        if ( is_wp_error( $blocks ) ) {
            return $blocks;
        }

        if ( ! $this->contains_input_field( $blocks ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A form layout must contain at least one input field.', 'formgent' ) );
        }

        $content = serialize_blocks( $blocks );

        if ( '' === $content || empty( parse_blocks( $content ) ) ) {
            return McpErrorFactory::internal();
        }

        return [
            'content' => $content,
            'layout'  => $this->read( $content ),
        ];
    }

    /** @return array<string,mixed> */
    public function attributes_schema(): array {
        $blocks    = $this->registered_blocks();
        $signature = md5( (string) wp_json_encode( $blocks ) );

        if ( null !== $this->attribute_schema && $signature === $this->attribute_schema_signature ) {
            return $this->attribute_schema;
        }

        $properties = [];

        foreach ( $blocks as $block_name => $definitions ) {
            foreach ( $definitions as $key => $definition ) {
                if ( 'is_preview' === $key || in_array( $key, self::SENSITIVE_ATTRIBUTE_KEYS, true ) || ! is_array( $definition ) ) {
                    continue;
                }

                $candidate = $this->schema_for_definition( $definition );

                if ( ! isset( $properties[$key] ) ) {
                    $properties[$key] = $candidate;
                    continue;
                }

                $left  = (array) ( $properties[$key]['type'] ?? [] );
                $right = (array) ( $candidate['type'] ?? [] );
                $types = array_values( array_unique( array_merge( $left, $right ) ) );

                $properties[$key] = ['type' => 1 === count( $types ) ? $types[0] : $types];
            }
        }

        $this->attribute_schema           = [
            'type'                 => 'object',
            'properties'           => $properties,
            'additionalProperties' => false,
        ];
        $this->attribute_schema_signature = $signature;

        return $this->attribute_schema;
    }

    /** @param array<int,array<string,mixed>> $blocks @return array<int,array<string,mixed>> */
    private function read_blocks( array $blocks, int $depth ): array {
        if ( 8 < $depth ) {
            return [];
        }

        $safe = [];

        foreach ( $blocks as $block ) {
            if ( ! is_array( $block ) || self::MAX_BLOCKS <= $this->block_count ) {
                continue;
            }

            $name = (string) ( $block['blockName'] ?? '' );

            if ( ! $this->is_allowed_block( $name ) || $this->is_stash( $block ) ) {
                continue;
            }

            $this->block_count++;
            $item = [
                'block_name'   => $name,
                'attributes'   => $this->sanitize_attributes( $name, is_array( $block['attrs'] ?? null ) ? $block['attrs'] : [] ),
                'inner_blocks' => $this->read_blocks( is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [], $depth + 1 ),
            ];

            if ( in_array( $name, self::CORE_TEXT_BLOCKS, true ) ) {
                $item['content'] = $this->read_text_content( $block );
            }

            $safe[] = $item;
        }

        return $safe;
    }

    /** @param array<int,mixed> $layout @return array<int,array<string,mixed>>|WP_Error */
    private function prepare_blocks( array $layout, int $depth ) {
        if ( 8 < $depth || self::MAX_BLOCKS < $this->block_count + count( $layout ) ) {
            return McpErrorFactory::limit_exceeded( esc_html__( 'The form layout is too deeply nested or contains too many blocks.', 'formgent' ) );
        }

        $blocks = [];

        foreach ( $layout as $item ) {
            if ( ! is_array( $item ) || ! empty( array_diff( array_keys( $item ), ['block_name', 'attributes', 'content', 'inner_blocks'] ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A form layout block is invalid.', 'formgent' ) );
            }

            $name = is_string( $item['block_name'] ?? null ) ? $item['block_name'] : '';

            if ( ! $this->is_allowed_block( $name ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The form layout contains an unsupported block.', 'formgent' ) );
            }

            $attributes = $item['attributes'] ?? [];

            if ( ! is_array( $attributes ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Form layout block attributes must be an object.', 'formgent' ) );
            }

            $definitions = $this->definitions_for( $name );
            $unknown     = array_diff( array_keys( $attributes ), array_keys( $definitions ) );

            if ( ! empty( $unknown ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A form layout block contains an unknown attribute.', 'formgent' ) );
            }

            $attributes = $this->sanitize_attributes( $name, $attributes );
            $inner      = $item['inner_blocks'] ?? [];

            if ( ! is_array( $inner ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A form layout block has invalid children.', 'formgent' ) );
            }

            $this->block_count++;
            $children = $this->prepare_blocks( $inner, $depth + 1 );

            if ( is_wp_error( $children ) ) {
                return $children;
            }

            $html = '';

            if ( in_array( $name, self::CORE_TEXT_BLOCKS, true ) ) {
                if ( ! is_string( $item['content'] ?? null ) || 20000 < strlen( $item['content'] ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'A form screen text block requires bounded text content.', 'formgent' ) );
                }

                $html = $this->text_html( $name, $attributes, $item['content'] );
            } elseif ( array_key_exists( 'content', $item ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Only heading and paragraph blocks accept direct content.', 'formgent' ) );
            }

            $blocks[] = [
                'blockName'    => $name,
                'attrs'        => $attributes,
                'innerBlocks'  => $children,
                'innerHTML'    => $html,
                'innerContent' => empty( $children ) ? [$html] : array_fill( 0, count( $children ), null ),
            ];
        }

        return $blocks;
    }

    /** @return array<string,array<string,mixed>> */
    private function registered_blocks(): array {
        $blocks   = [];
        $registry = WP_Block_Type_Registry::get_instance();

        foreach ( $registry->get_all_registered() as $name => $block_type ) {
            if ( 0 === strpos( $name, 'formgent/' ) || in_array( $name, self::CORE_TEXT_BLOCKS, true ) ) {
                $definitions   = $block_type->get_attributes();
                $blocks[$name] = in_array( $name, self::CORE_TEXT_BLOCKS, true )
                    ? array_merge( self::CORE_TEXT_FALLBACK_ATTRIBUTES, is_array( $definitions ) ? $definitions : [] )
                    : $definitions;
            }
        }

        foreach ( self::CORE_TEXT_BLOCKS as $name ) {
            if ( ! isset( $blocks[$name] ) ) {
                $blocks[$name] = self::CORE_TEXT_FALLBACK_ATTRIBUTES;
            }
        }

        return $blocks;
    }

    /** @return array<string,mixed> */
    private function definitions_for( string $name ): array {
        $block = WP_Block_Type_Registry::get_instance()->get_registered( $name );

        if ( null === $block || ! is_array( $block->get_attributes() ) ) {
            return [];
        }

        $definitions = $block->get_attributes();
        $definitions = is_array( $definitions ) ? $definitions : [];

        if ( in_array( $name, self::CORE_TEXT_BLOCKS, true ) ) {
            $definitions = array_merge( self::CORE_TEXT_FALLBACK_ATTRIBUTES, $definitions );
        }

        return array_diff_key( $definitions, array_flip( array_merge( ['is_preview'], self::SENSITIVE_ATTRIBUTE_KEYS ) ) );
    }

    private function is_allowed_block( string $name ): bool {
        return ( 0 === strpos( $name, 'formgent/' ) || in_array( $name, self::CORE_TEXT_BLOCKS, true ) )
            && null !== WP_Block_Type_Registry::get_instance()->get_registered( $name );
    }

    /** @param array<string,mixed> $attributes @return array<string,mixed> */
    private function sanitize_attributes( string $name, array $attributes ): array {
        $definitions = $this->definitions_for( $name );
        $safe        = [];

        foreach ( $attributes as $key => $value ) {
            if ( 'is_preview' === $key || ! isset( $definitions[$key] ) || ! is_array( $definitions[$key] ) ) {
                continue;
            }

            $safe[$key] = $this->sanitize_value( $value, $definitions[$key], $key );
        }

        return $safe;
    }

    /** @param mixed $value @param array<string,mixed> $definition @return mixed */
    private function sanitize_value( $value, array $definition, string $key, int $depth = 0 ) {
        $type = $definition['type'] ?? '';

        if ( 'boolean' === $type ) {
            return (bool) $value;
        }
        if ( 'integer' === $type ) {
            return (int) $value;
        }
        if ( 'number' === $type ) {
            return is_numeric( $value ) ? (float) $value : 0;
        }
        if ( 'array' === $type ) {
            if ( ! is_array( $value ) || 8 < $depth ) {
                return [];
            }
            return array_map(
                function ( $item ) use ( $key, $depth ) {
                    return $this->sanitize_untyped( $item, $key, $depth + 1 );
                }, array_slice( $value, 0, 100 ) 
            );
        }
        if ( 'object' === $type ) {
            return is_array( $value ) ? $this->sanitize_untyped( $value, $key, $depth + 1 ) : [];
        }

        $value = is_scalar( $value ) ? substr( (string) $value, 0, 20000 ) : '';

        if ( preg_match( '/(?:html|content|message|description)$/i', $key ) ) {
            return wp_kses_post( $value );
        }
        if ( preg_match( '/(?:url|src)$/i', $key ) && '' !== $value ) {
            return esc_url_raw( $value );
        }

        return sanitize_text_field( $value );
    }

    /** @param mixed $value @return mixed */
    private function sanitize_untyped( $value, string $key, int $depth ) {
        if ( 8 < $depth ) {
            return null;
        }
        if ( is_array( $value ) ) {
            $safe = [];
            foreach ( array_slice( $value, 0, 100, true ) as $child_key => $child ) {
                $safe[is_int( $child_key ) ? $child_key : sanitize_key( (string) $child_key )] = $this->sanitize_untyped( $child, (string) $child_key, $depth + 1 );
            }
            return $safe;
        }
        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) || null === $value ) {
            return $value;
        }

        return $this->sanitize_value( $value, ['type' => 'string'], $key, $depth );
    }

    /** @param array<string,mixed> $definition @return array<string,mixed> */
    private function schema_for_definition( array $definition ): array {
        $type = $definition['type'] ?? 'string';

        if ( ! in_array( $type, ['string', 'boolean', 'integer', 'number', 'array', 'object'], true ) ) {
            $type = 'string';
        }

        $schema = ['type' => $type];

        if ( 'array' === $type ) {
            $schema['items']    = ['type' => ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null']];
            $schema['maxItems'] = 100;
        } elseif ( 'object' === $type ) {
            $schema['additionalProperties'] = true;
        } elseif ( 'string' === $type ) {
            $schema['maxLength'] = 20000;
        }

        return $schema;
    }

    /** @param array<string,mixed> $block */
    private function read_text_content( array $block ): string {
        $html = (string) ( $block['innerHTML'] ?? '' );
        $html = preg_replace( '/^\s*<(?:p|h[1-6])[^>]*>(.*)<\/(?:p|h[1-6])>\s*$/is', '$1', $html );

        return substr( wp_kses( (string) $html, ['a' => ['href' => true, 'target' => true, 'rel' => true], 'br' => [], 'strong' => [], 'em' => [], 'code' => []] ), 0, 20000 );
    }

    /** @param array<string,mixed> $attributes */
    private function text_html( string $name, array $attributes, string $content ): string {
        $content = wp_kses( $content, ['a' => ['href' => true, 'target' => true, 'rel' => true], 'br' => [], 'strong' => [], 'em' => [], 'code' => []] );
        $tag     = 'core/paragraph' === $name ? 'p' : 'h' . min( 6, max( 1, absint( $attributes['level'] ?? 2 ) ) );

        return sprintf( '<%1$s>%2$s</%1$s>', tag_escape( $tag ), $content );
    }

    /** @param array<int,array<string,mixed>> $blocks */
    private function contains_input_field( array $blocks ): bool {
        $non_inputs = ['formgent/form', 'formgent/welcome', 'formgent/end', 'formgent/step', 'formgent/page-break', 'formgent/submit-button', 'formgent/next-button', 'formgent/info'];

        foreach ( $blocks as $block ) {
            $name = $block['blockName'] ?? '';
            if ( 0 === strpos( $name, 'formgent/' ) && ! in_array( $name, $non_inputs, true ) ) {
                return true;
            }
            if ( $this->contains_input_field( $block['innerBlocks'] ?? [] ) ) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string,mixed> $block */
    private function is_stash( array $block ): bool {
        if ( ! in_array( $block['blockName'] ?? '', ['core/html', 'core/missing'], true ) ) {
            return false;
        }
        $content = $block['attrs']['content'] ?? $block['attrs']['originalContent'] ?? $block['innerHTML'] ?? '';
        return is_string( $content ) && false !== strpos( $content, 'data-formgent-stash="true"' );
    }

    /** @param array<int,array<string,mixed>> $blocks */
    private function blocks_are_supported( array $blocks ): bool {
        foreach ( $blocks as $block ) {
            if ( ! is_array( $block ) ) {
                return false;
            }

            $name = (string) ( $block['blockName'] ?? '' );

            // parse_blocks() represents whitespace between block comments as a
            // nameless freeform node; it carries no editable form state.
            if ( '' === $name && '' === trim( (string) ( $block['innerHTML'] ?? '' ) ) ) {
                continue;
            }

            if ( ! $this->is_allowed_block( $name ) || $this->is_stash( $block ) ) {
                return false;
            }

            if ( ! $this->blocks_are_supported( is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [] ) ) {
                return false;
            }
        }

        return true;
    }
}
