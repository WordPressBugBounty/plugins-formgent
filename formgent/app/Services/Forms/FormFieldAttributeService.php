<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Block_Type_Registry;
use WP_Error;

/**
 * Exposes registered, non-secret block attributes through the MCP field model.
 *
 * Identity and common field values remain first-class MCP properties. Everything
 * else is validated against the registered block.json attribute definition so
 * detailed field controls can round-trip without maintaining a second model.
 */
class FormFieldAttributeService {
    private const MANAGED_KEYS = [
        'id',
        'name',
        'label',
        'required',
        'placeholder',
        'sub_label',
        'value',
        'block_width',
        'options',
        'is_preview',
    ];

    /** @var array<string,array<string,array<string,mixed>>> */
    private array $definitions = [];

    /**
     * Build a closed union of detailed attributes supported by registered fields.
     *
     * @param array<int,string> $field_types Supported normalized field types.
     * @return array<string,mixed>
     */
    public function schema( array $field_types ): array {
        $properties = [];

        foreach ( $field_types as $field_type ) {
            foreach ( $this->definitions( $field_type ) as $key => $definition ) {
                $fragment = $this->schema_fragment( $definition );

                if ( isset( $properties[$key] ) ) {
                    $fragment = $this->merge_schema( $properties[$key], $fragment );
                }

                $properties[$key] = $fragment;
            }
        }

        ksort( $properties );

        return [
            'type'                 => 'object',
            'description'          => esc_html__( 'Detailed settings accepted by the selected registered FormGent field type.', 'formgent' ),
            'properties'           => $properties,
            'additionalProperties' => false,
        ];
    }

    /**
     * Validate and sanitize detailed attributes for one field type.
     *
     * @param mixed $attributes Candidate detailed attributes.
     * @return array<string,mixed>|WP_Error
     */
    public function prepare( string $field_type, $attributes ) {
        if ( null === $attributes || [] === $attributes ) {
            return [];
        }

        if ( ! is_array( $attributes ) || array_keys( $attributes ) === range( 0, count( $attributes ) - 1 ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Detailed field attributes must be an object.', 'formgent' ) );
        }

        $definitions = $this->definitions( $field_type );
        $unknown     = array_diff( array_keys( $attributes ), array_keys( $definitions ) );

        if ( ! empty( $unknown ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A detailed setting is not supported by this field type.', 'formgent' ) );
        }

        $prepared = [];

        foreach ( $attributes as $key => $value ) {
            $schema = $this->schema_fragment( $definitions[$key] );

            if ( function_exists( 'rest_validate_value_from_schema' ) ) {
                $valid = rest_validate_value_from_schema( $value, $schema, 'attributes.' . $key );

                if ( is_wp_error( $valid ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'A detailed field setting has an invalid value.', 'formgent' ) );
                }
            }

            $prepared[$key] = $this->sanitize_value( $value, $key, 0 );
        }

        return $prepared;
    }

    /**
     * Return a safe detailed-attribute projection for reads.
     *
     * @param array<string,mixed> $attributes Registered block attributes.
     * @return array<string,mixed>
     */
    public function read( string $field_type, array $attributes ): array {
        $allowed = array_intersect_key( $attributes, $this->definitions( $field_type ) );
        $safe    = [];

        foreach ( $allowed as $key => $value ) {
            $safe[$key] = $this->sanitize_value( $value, $key, 0 );
        }

        return $safe;
    }

    /** @return array<string,array<string,mixed>> */
    private function definitions( string $field_type ): array {
        if ( isset( $this->definitions[$field_type] ) ) {
            return $this->definitions[$field_type];
        }

        $definitions = [];
        $registry    = WP_Block_Type_Registry::get_instance();
        $block       = $registry->get_registered( 'formgent/' . $field_type );

        if ( null !== $block ) {
            $definitions = $block->get_attributes();
        }

        // Ability registration can run before a third-party block is registered.
        // Core blocks retain a deterministic block.json fallback for that case.
        if ( empty( $definitions ) && function_exists( 'formgent_dir' ) ) {
            $path = formgent_dir( 'assets/blocks/' . $field_type . '/block.json' );

            if ( is_readable( $path ) ) {
                $metadata    = json_decode( (string) file_get_contents( $path ), true );
                $definitions = is_array( $metadata['attributes'] ?? null ) ? $metadata['attributes'] : [];
            }
        }

        $definitions = array_diff_key( is_array( $definitions ) ? $definitions : [], array_flip( self::MANAGED_KEYS ) );

        /**
         * Filters detailed registered attributes exposed for one field type.
         * Sensitive or internal attributes must be removed by extensions here.
         *
         * @param array<string,array<string,mixed>> $definitions Attribute definitions.
         * @param string $field_type Normalized field type.
         */
        $definitions = apply_filters( 'formgent_mcp_field_attribute_definitions', $definitions, $field_type );
        $definitions = is_array( $definitions ) ? $definitions : [];

        $this->definitions[$field_type] = $definitions;

        return $definitions;
    }

    /** @param array<string,mixed> $definition @return array<string,mixed> */
    private function schema_fragment( array $definition ): array {
        $allowed = [
            'type',
            'enum',
            'items',
            'properties',
            'additionalProperties',
            'minimum',
            'maximum',
            'minLength',
            'maxLength',
            'minItems',
            'maxItems',
            'uniqueItems',
            'pattern',
            'format',
        ];
        $schema  = array_intersect_key( $definition, array_flip( $allowed ) );

        if ( ! isset( $schema['type'] ) ) {
            $schema['type'] = ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null'];
        }

        if ( 'array' === $schema['type'] && ! isset( $schema['maxItems'] ) ) {
            $schema['maxItems'] = 100;
        }

        return $schema;
    }

    /**
     * Merge definitions for identically named attributes on different fields.
     *
     * @param array<string,mixed> $left Existing definition.
     * @param array<string,mixed> $right New definition.
     * @return array<string,mixed>
     */
    private function merge_schema( array $left, array $right ): array {
        if ( $left === $right ) {
            return $left;
        }

        $left_types  = (array) ( $left['type'] ?? [] );
        $right_types = (array) ( $right['type'] ?? [] );
        $types       = array_values( array_unique( array_merge( $left_types, $right_types ) ) );
        $merged      = ['type' => 1 === count( $types ) ? reset( $types ) : $types];

        if ( isset( $left['enum'], $right['enum'] ) && $left['enum'] === $right['enum'] ) {
            $merged['enum'] = $left['enum'];
        }

        foreach ( ['minLength', 'maxLength', 'minItems', 'maxItems', 'minimum', 'maximum'] as $key ) {
            if ( isset( $left[$key], $right[$key] ) && $left[$key] === $right[$key] ) {
                $merged[$key] = $left[$key];
            }
        }

        return $merged;
    }

    /** @param mixed $value @return mixed */
    private function sanitize_value( $value, string $key, int $depth ) {
        if ( 8 < $depth ) {
            return null;
        }

        if ( is_string( $value ) ) {
            if ( in_array( $key, ['html_content', 'message', 'content'], true ) ) {
                return substr( wp_kses_post( $value ), 0, 100000 );
            }

            return substr( sanitize_text_field( $value ), 0, 10000 );
        }

        if ( is_array( $value ) ) {
            $safe = [];

            foreach ( array_slice( $value, 0, 100, true ) as $child_key => $child_value ) {
                $safe_key        = is_int( $child_key ) ? $child_key : substr( sanitize_key( (string) $child_key ), 0, 100 );
                $safe[$safe_key] = $this->sanitize_value( $child_value, (string) $child_key, $depth + 1 );
            }

            return $safe;
        }

        return is_scalar( $value ) || null === $value ? $value : null;
    }
}
