<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Error;

/**
 * Converts FormGent block layouts while preserving field block attributes.
 *
 * The public MCP field contract intentionally exposes only a safe subset of block
 * attributes. Type conversion therefore operates on the stored block tree rather
 * than rebuilding it from that lossy public representation.
 */
class FormTypeConversionService {
    /** @return array{content:string,warnings:array<int,string>}|WP_Error */
    public function convert( string $content, string $from, string $to ) {
        if ( $from === $to ) {
            return [
                'content'  => $content,
                'warnings' => [],
            ];
        }

        if ( ! in_array( $from, [ 'general', 'conversational' ], true ) || ! in_array( $to, [ 'general', 'conversational' ], true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The form type conversion is invalid.', 'formgent' ) );
        }

        $blocks = parse_blocks( $content );

        if ( empty( $blocks ) || $this->contains_raw_content( $blocks ) ) {
            return $this->unsafe_conversion();
        }

        $converted = 'conversational' === $to
            ? $this->to_conversational( $blocks )
            : $this->to_general( $blocks );

        if ( is_wp_error( $converted ) ) {
            return $converted;
        }

        $converted  = $this->strip_logic( $converted );
        $serialized = serialize_blocks( $converted );

        if ( '' === trim( $serialized ) || count( parse_blocks( $serialized ) ) !== count( $converted ) ) {
            return McpErrorFactory::internal();
        }

        return [
            'content'  => $serialized,
            'warnings' => [
                esc_html__( 'Conditional logic was removed because general and conversational forms use different rule structures.', 'formgent' ),
            ],
        ];
    }

    /** @param array<int,array<string,mixed>> $blocks @return array<int,array<string,mixed>>|WP_Error */
    private function to_conversational( array $blocks ) {
        $stash = $this->stash_data( $blocks );
        $login = array_values(
            array_filter(
                $blocks,
                static function ( array $block ): bool {
                    return 'formgent/login' === ( $block['blockName'] ?? '' );
                }
            )
        );

        // Login is a self-contained form and is already valid in either layout.
        if ( ! empty( $login ) ) {
            foreach ( $blocks as $block ) {
                $name = (string) ( $block['blockName'] ?? '' );

                if ( ! $this->is_stash( $block ) && ! in_array( $name, [ 'formgent/login', 'formgent/submit-button' ], true ) ) {
                    return $this->unsafe_conversion();
                }
            }

            return $login;
        }

        $steps       = [];
        $heading     = null;
        $description = null;
        $step_index  = 0;

        foreach ( $blocks as $block ) {
            $name = (string) ( $block['blockName'] ?? '' );

            if ( $this->is_stash( $block ) || in_array( $name, [ 'formgent/submit-button', 'formgent/page-break' ], true ) ) {
                continue;
            }

            if ( 'core/heading' === $name ) {
                $heading = $block;
                continue;
            }

            if ( 'core/paragraph' === $name ) {
                $description = $block;
                continue;
            }

            if ( ! $this->is_field( $name ) ) {
                return $this->unsafe_conversion();
            }

            $field                   = $block;
            $field_label             = sanitize_text_field( $field['attrs']['label'] ?? '' );
            $field['attrs']['label'] = '';
            $inner                   = [
                $heading ?: $this->text_block( 'core/heading', 'h2', $field_label ?: esc_html__( 'Question', 'formgent' ) ),
                $description ?: $this->text_block( 'core/paragraph', 'p', '' ),
                $field,
                $this->block( 'formgent/next-button', [ 'id' => $this->id() ] ),
            ];
            $step_attrs              = [
                'id'    => $this->id(),
                'media' => [
                    'type'       => 'image',
                    'url'        => formgent_url( 'assets/images/dummy.webp' ),
                    'focalPoint' => [ 'x' => 0.5, 'y' => 0.5 ],
                ],
            ];
            $saved_meta              = $stash['meta'][ $step_index ] ?? null;

            if ( is_array( $saved_meta ) ) {
                foreach ( [ 'media', 'layout', 'media_brightness', 'brightness_color' ] as $key ) {
                    if ( array_key_exists( $key, $saved_meta ) && null !== $saved_meta[ $key ] ) {
                        $step_attrs[ $key ] = $saved_meta[ $key ];
                    }
                }
            }

            $steps[]     = $this->block(
                'formgent/step',
                $step_attrs,
                $inner
            );
            $heading     = null;
            $description = null;
            $step_index++;
        }

        if ( empty( $steps ) || null !== $heading || null !== $description ) {
            return $this->unsafe_conversion();
        }

        $welcome = $stash['welcome'] ?? $this->welcome_block();
        $end     = $stash['end'] ?? $this->end_block();

        return array_merge( [ $welcome ], $steps, [ $end ] );
    }

    /** @param array<int,array<string,mixed>> $blocks @return array<int,array<string,mixed>>|WP_Error */
    private function to_general( array $blocks ) {
        $general   = [];
        $preserved = [];
        $step_meta = [];

        foreach ( $blocks as $block ) {
            $name = (string) ( $block['blockName'] ?? '' );

            if ( $this->is_stash( $block ) ) {
                continue;
            }

            if ( in_array( $name, [ 'formgent/welcome', 'formgent/end' ], true ) ) {
                $preserved[] = $this->strip_logic( [ $block ] )[0];
                continue;
            }

            if ( 'formgent/step' !== $name ) {
                if ( $this->is_field( $name ) || 'formgent/login' === $name ) {
                    $general[] = $block;
                    continue;
                }

                return $this->unsafe_conversion();
            }

            $inner       = is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [];
            $fields      = [];
            $heading     = null;
            $description = null;
            $attributes  = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : [];
            $step_meta[] = array_intersect_key( $attributes, array_flip( [ 'media', 'layout', 'media_brightness', 'brightness_color' ] ) );

            foreach ( $inner as $inner_block ) {
                $inner_name = (string) ( $inner_block['blockName'] ?? '' );

                if ( 'core/heading' === $inner_name && null === $heading ) {
                    $heading = $inner_block;
                    continue;
                }

                if ( 'core/paragraph' === $inner_name && null === $description ) {
                    $description = $inner_block;
                    continue;
                }

                if ( 'formgent/next-button' === $inner_name ) {
                    continue;
                }

                if ( ! $this->is_field( $inner_name ) ) {
                    return $this->unsafe_conversion();
                }

                $fields[] = $inner_block;
            }

            if ( empty( $fields ) ) {
                return $this->unsafe_conversion();
            }

            foreach ( $fields as $index => $field ) {
                $field['attrs'] = is_array( $field['attrs'] ?? null ) ? $field['attrs'] : [];

                if ( 0 === $index && null !== $heading ) {
                    $label = $this->text_content( $heading );

                    if ( '' !== $label ) {
                        $field['attrs']['label'] = $label;
                    }
                }

                if ( null !== $description ) {
                    $help = $this->text_content( $description );

                    if ( '' !== $help ) {
                        $field['attrs']['sub_label'] = $help;
                    }
                }

                $general[] = $field;
            }
        }

        if ( empty( $general ) ) {
            return $this->unsafe_conversion();
        }

        if ( ! empty( $preserved ) || ! empty( $step_meta ) ) {
            $general[] = $this->stash_block( $preserved, $step_meta );
        }

        if ( ! $this->contains_block( $general, 'formgent/submit-button' ) && ! $this->contains_block( $general, 'formgent/login' ) ) {
            $general[] = $this->block( 'formgent/submit-button', [ 'id' => $this->id() ] );
        }

        return $general;
    }

    /** @param array<int,array<string,mixed>> $blocks @return array<int,array<string,mixed>> */
    private function strip_logic( array $blocks ): array {
        foreach ( $blocks as &$block ) {
            if ( isset( $block['attrs']['logics'] ) ) {
                unset( $block['attrs']['logics'] );
            }

            if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
                $block['innerBlocks']  = $this->strip_logic( $block['innerBlocks'] );
                $block['innerContent'] = array_fill( 0, count( $block['innerBlocks'] ), null );
            }
        }
        unset( $block );

        return $blocks;
    }

    private function is_field( string $name ): bool {
        $structural = [
            'formgent/form',
            'formgent/step',
            'formgent/welcome',
            'formgent/end',
            'formgent/next-button',
            'formgent/info',
            'formgent/submit-button',
            'formgent/page-break',
            'formgent/html',
        ];
        $config     = formgent_config( 'blocks' );
        $field_type = is_array( $config ) ? ( $config[ $name ]['field_type'] ?? '' ) : '';
        $allowed    = '' !== $field_type && ! in_array( $name, $structural, true );

        return (bool) apply_filters( 'formgent_mcp_conversion_field_block', $allowed, $name );
    }

    /** @param array<string,mixed> $block */
    private function is_stash( array $block ): bool {
        if ( ! in_array( $block['blockName'] ?? '', [ 'core/html', 'core/missing' ], true ) ) {
            return false;
        }

        $content = $block['attrs']['content'] ?? $block['attrs']['originalContent'] ?? $block['innerHTML'] ?? '';

        return is_string( $content ) && false !== strpos( $content, 'data-formgent-stash="true"' );
    }

    /**
     * @param array<int,array<string,mixed>> $blocks
     * @return array{meta:array<int,mixed>,welcome?:array<string,mixed>,end?:array<string,mixed>}
     */
    private function stash_data( array $blocks ): array {
        $data = [ 'meta' => [] ];

        foreach ( $blocks as $block ) {
            if ( ! $this->is_stash( $block ) ) {
                continue;
            }

            $content = (string) ( $block['attrs']['content'] ?? $block['attrs']['originalContent'] ?? $block['innerHTML'] ?? '' );

            if ( preg_match( "/data-formgent-step-meta-list='([^']+)'/", $content, $matches ) ) {
                $decoded = base64_decode( $matches[1], true );
                $meta    = false !== $decoded ? json_decode( $decoded, true ) : null;

                if ( is_array( $meta ) ) {
                    $data['meta'] = $meta;
                }
            }

            $preserved_blocks = is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [];

            foreach ( $preserved_blocks as $preserved ) {
                if ( 'formgent/welcome' === ( $preserved['blockName'] ?? '' ) ) {
                    $data['welcome'] = $preserved;
                }

                if ( 'formgent/end' === ( $preserved['blockName'] ?? '' ) ) {
                    $data['end'] = $preserved;
                }
            }

            if ( preg_match( '/<div[^>]*data-formgent-stash="true"[^>]*>([\s\S]*)<\/div>/', $content, $matches ) ) {
                foreach ( parse_blocks( $matches[1] ) as $preserved ) {
                    if ( 'formgent/welcome' === ( $preserved['blockName'] ?? '' ) ) {
                        $data['welcome'] = $preserved;
                    }

                    if ( 'formgent/end' === ( $preserved['blockName'] ?? '' ) ) {
                        $data['end'] = $preserved;
                    }
                }
            }
        }

        return $data;
    }

    /** @param array<int,array<string,mixed>> $preserved @param array<int,array<string,mixed>> $meta @return array<string,mixed> */
    private function stash_block( array $preserved, array $meta ): array {
        $encoded = base64_encode( (string) wp_json_encode( $meta ) );
        $html    = sprintf(
            "\n<!-- formgent-stash -->\n<div style=\"display:none;\" data-formgent-stash=\"true\"><div data-formgent-step-meta-list='%s'></div>%s</div>\n<!-- /formgent-stash -->\n",
            esc_attr( $encoded ),
            serialize_blocks( $preserved )
        );

        return [
            'blockName'    => 'core/html',
            'attrs'        => [],
            'innerBlocks'  => [],
            'innerHTML'    => $html,
            'innerContent' => [ $html ],
        ];
    }

    /** @param array<int,array<string,mixed>> $blocks */
    private function contains_raw_content( array $blocks ): bool {
        foreach ( $blocks as $block ) {
            if ( empty( $block['blockName'] ) && '' !== trim( (string) ( $block['innerHTML'] ?? '' ) ) ) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int,array<string,mixed>> $blocks */
    private function contains_block( array $blocks, string $name ): bool {
        foreach ( $blocks as $block ) {
            if ( $name === ( $block['blockName'] ?? '' ) ) {
                return true;
            }

            if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) && $this->contains_block( $block['innerBlocks'], $name ) ) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string,mixed> */
    private function welcome_block(): array {
        return $this->block(
            'formgent/welcome',
            [ 'id' => $this->id() ],
            [
                $this->text_block( 'core/heading', 'h2', esc_html__( 'Welcome', 'formgent' ) ),
                $this->text_block( 'core/paragraph', 'p', esc_html__( 'Hi there, please fill out and submit this form.', 'formgent' ) ),
                $this->block( 'formgent/info' ),
                $this->block(
                    'formgent/next-button',
                    [
                        'id'               => $this->id(),
                        'button_text'      => esc_html__( 'Start', 'formgent' ),
                        'skip_button'      => false,
                        'button_alignment' => 'middle',
                    ]
                ),
            ]
        );
    }

    /** @return array<string,mixed> */
    private function end_block(): array {
        return $this->block(
            'formgent/end',
            [ 'id' => $this->id() ],
            [
                $this->text_block( 'core/heading', 'h2', esc_html__( 'Thank you', 'formgent' ) ),
                $this->text_block( 'core/paragraph', 'p', esc_html__( 'Your submission has been received!', 'formgent' ) ),
            ]
        );
    }

    /** @param array<string,mixed> $attrs @param array<int,array<string,mixed>> $inner @return array<string,mixed> */
    private function block( string $name, array $attrs = [], array $inner = [] ): array {
        return [
            'blockName'    => $name,
            'attrs'        => $attrs,
            'innerBlocks'  => $inner,
            'innerHTML'    => '',
            'innerContent' => array_fill( 0, count( $inner ), null ),
        ];
    }

    /** @return array<string,mixed> */
    private function text_block( string $name, string $tag, string $content ): array {
        $html = sprintf( '<%1$s>%2$s</%1$s>', tag_escape( $tag ), esc_html( $content ) );

        return [
            'blockName'    => $name,
            'attrs'        => [ 'content' => $content ],
            'innerBlocks'  => [],
            'innerHTML'    => $html,
            'innerContent' => [ $html ],
        ];
    }

    /** @param array<string,mixed> $block */
    private function text_content( array $block ): string {
        $content = $block['attrs']['content'] ?? $block['innerHTML'] ?? '';

        return sanitize_text_field( wp_strip_all_tags( (string) $content ) );
    }

    private function id(): string {
        return substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 12 );
    }

    private function unsafe_conversion(): WP_Error {
        return McpErrorFactory::conflict( esc_html__( 'This form contains content that cannot be converted safely without data loss.', 'formgent' ) );
    }
}
