<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\FormReadDTO;
use FormGent\App\Repositories\FormRepository;
use FormGent\App\Services\Mcp\McpInputValidator;
use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Block_Type_Registry;
use WP_Error;
use WP_Post;

/**
 * Provides bounded, normalized form reads without exposing post content or meta.
 */
class FormReadService {
    private FormRepository $repository;

    private SafeFormSettingsService $safe_settings;

    private FormFieldAttributeService $field_attributes;

    private FormLayoutService $layout;

    public function __construct( FormRepository $repository, SafeFormSettingsService $safe_settings, FormFieldAttributeService $field_attributes, FormLayoutService $layout ) {
        $this->repository       = $repository;
        $this->safe_settings    = $safe_settings;
        $this->field_attributes = $field_attributes;
        $this->layout           = $layout;
    }

    /**
     * @param array<string,mixed> $input Validated list filters.
     * @return array<string,mixed>|WP_Error
     */
    public function list( array $input ) {
        $date_frame = McpInputValidator::date_frame( $input['date_type'] ?? 'all', $input['date_frame'] ?? [], true );

        if ( is_wp_error( $date_frame ) ) {
            return $date_frame;
        }

        $pagination = McpInputValidator::pagination( $input['page'] ?? 1, $input['per_page'] ?? 20 );
        $dto        = new FormReadDTO();
        $dto->set_page( $pagination['page'] );
        $dto->set_per_page( $pagination['per_page'] );
        $dto->set_search( sanitize_text_field( $input['search'] ?? '' ) );
        $dto->set_sort_by( $input['sort_by'] ?? 'last_modified' );
        $dto->set_date_type( $input['date_type'] ?? 'all' );
        $dto->set_date_frame( $date_frame );
        $dto->set_type( $input['type'] ?? 'all' );
        $dto->set_status( $input['status'] ?? 'all' );

        $result = $this->repository->get_for_mcp( $dto );
        $forms  = [];

        foreach ( $result['forms'] as $form ) {
            $forms[] = [
                'id'                     => absint( $form->id ),
                'title'                  => (string) $form->title,
                'status'                 => (string) $form->status,
                'type'                   => (string) ( $form->type ?: 'general' ),
                'created_at'             => mysql2date( 'c', (string) $form->created_at, false ),
                'updated_at'             => mysql2date( 'c', (string) $form->updated_at, false ),
                'total_responses'        => absint( $form->total_responses ),
                'total_unread_responses' => absint( $form->total_unread_responses ),
            ];
        }

        $total = absint( $result['total'] );

        return [
            'forms'      => $forms,
            'pagination' => [
                'page'        => $dto->get_page(),
                'per_page'    => $dto->get_per_page(),
                'total_items' => $total,
                'total_pages' => (int) ceil( $total / $dto->get_per_page() ),
            ],
        ];
    }

    /** @return array<string,mixed>|WP_Error */
    public function get( int $form_id ) {
        $post = $this->find( $form_id );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        return [
            'id'              => $post->ID,
            'title'           => get_the_title( $post ),
            'status'          => $post->post_status,
            'type'            => (string) ( get_post_meta( $post->ID, '_formgent_type', true ) ?: 'general' ),
            'fields'          => $this->normalize_blocks( parse_blocks( $post->post_content ) ),
            'layout'          => $this->layout->read( $post->post_content ),
            'layout_complete' => $this->layout->is_complete( $post->post_content ),
            'settings'        => $this->safe_settings->get( $post->ID ),
            'urls'            => $this->urls( $post ),
            'embed'           => $this->embed( $post->ID ),
        ];
    }

    /** @return array<string,mixed>|WP_Error */
    public function get_embed( int $form_id ) {
        $post = $this->find( $form_id );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        return array_merge(
            $this->embed( $post->ID ),
            [
                'public_url' => $this->public_url( $post ),
            ]
        );
    }

    /** @return WP_Post|WP_Error */
    private function find( int $form_id ) {
        if ( 1 > $form_id ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A positive form ID is required.', 'formgent' ) );
        }

        $post = get_post( $form_id );

        if ( ! $post instanceof WP_Post || formgent_post_type() !== $post->post_type || ! in_array( $post->post_status, ['publish', 'draft'], true ) ) {
            return McpErrorFactory::form_not_found();
        }

        return $post;
    }

    /**
     * @param array<int,array<string,mixed>> $blocks Parsed Gutenberg blocks.
     * @return array<int,array<string,mixed>>
     */
    private function normalize_blocks( array $blocks ): array {
        $normalized  = [];
        $group_start = 0;
        $config      = formgent_config( 'blocks' );
        $registry    = WP_Block_Type_Registry::get_instance();
        $skip        = [
            'formgent/submit-button',
            'formgent/next-button',
            'formgent/info',
            'core/heading',
            'core/paragraph',
        ];
        $wrappers    = ['formgent/form', 'formgent/step', 'formgent/welcome', 'formgent/end'];

        foreach ( $blocks as $block ) {
            $name  = $block['blockName'] ?? '';
            $inner = isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : [];

            if ( 'formgent/page-break' === $name ) {
                $group = sanitize_text_field( $block['attrs']['step_text'] ?? '' );

                if ( '' !== $group ) {
                    for ( $index = $group_start; $index < count( $normalized ); $index++ ) {
                        $normalized[$index]['group'] = $group;
                    }
                }

                $group_start = count( $normalized );
                continue;
            }

            if ( '' === $name || in_array( $name, $skip, true ) || $this->is_stash( $block ) ) {
                continue;
            }

            if ( 'formgent/step' === $name ) {
                $normalized = array_merge( $normalized, $this->normalize_step( $inner ) );
                continue;
            }

            if ( in_array( $name, $wrappers, true ) ) {
                $normalized = array_merge( $normalized, $this->normalize_blocks( $inner ) );
                continue;
            }

            if ( ! isset( $config[$name] ) ) {
                $normalized[] = [
                    'field_type' => 'unsupported',
                    'block_name' => sanitize_text_field( $name ),
                ];
                continue;
            }

            $field_type = $config[$name]['field_type'] ?? '';

            if ( '' === $field_type ) {
                continue;
            }

            $block_type = $registry->get_registered( $name );
            $defaults   = [];

            if ( null !== $block_type ) {
                foreach ( $block_type->get_attributes() as $key => $attribute ) {
                    if ( array_key_exists( 'default', $attribute ) ) {
                        $defaults[$key] = $attribute['default'];
                    }
                }
            }

            $attributes   = array_merge( $defaults, $block['attrs'] ?? [] );
            $normalized[] = $this->normalize_field( (string) $field_type, $name, $attributes, $inner );
        }

        return $normalized;
    }

    /** @param array<int,array<string,mixed>> $blocks Conversational step children. @return array<int,array<string,mixed>> */
    private function normalize_step( array $blocks ): array {
        $label  = '';
        $help   = '';
        $fields = $this->normalize_blocks( $blocks );

        foreach ( $blocks as $block ) {
            if ( '' === $label && 'core/heading' === ( $block['blockName'] ?? '' ) ) {
                $label = $this->block_text( $block );
            }

            if ( '' === $help && 'core/paragraph' === ( $block['blockName'] ?? '' ) ) {
                $help = $this->block_text( $block );
            }
        }

        if ( ! empty( $fields ) ) {
            if ( '' === ( $fields[0]['label'] ?? '' ) ) {
                $fields[0]['label'] = $label;
            }

            if ( '' === ( $fields[0]['help_text'] ?? '' ) ) {
                $fields[0]['help_text'] = $help;
            }
        }

        return $fields;
    }

    /** @param array<string,mixed> $block Text block. */
    private function block_text( array $block ): string {
        $content = $block['attrs']['content'] ?? $block['innerHTML'] ?? '';

        return sanitize_text_field( html_entity_decode( wp_strip_all_tags( (string) $content ), ENT_QUOTES, get_bloginfo( 'charset' ) ?: 'UTF-8' ) );
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
     * @param array<string,mixed> $attributes Block attributes.
     * @param array<int,array<string,mixed>> $inner Inner blocks.
     * @return array<string,mixed>
     */
    private function normalize_field( string $field_type, string $block_name, array $attributes, array $inner ): array {
        $default_value = $attributes['value'] ?? '';
        $field         = [
            'field_type'    => $field_type,
            'block_name'    => $block_name,
            'id'            => sanitize_text_field( $attributes['id'] ?? '' ),
            'name'          => sanitize_key( $attributes['name'] ?? '' ),
            'label'         => sanitize_text_field( $attributes['label'] ?? '' ),
            'required'      => ! empty( $attributes['required'] ),
            'placeholder'   => sanitize_text_field( $attributes['placeholder'] ?? '' ),
            'help_text'     => sanitize_text_field( $attributes['sub_label'] ?? '' ),
            'default_value' => is_scalar( $default_value ) ? sanitize_text_field( (string) $default_value ) : '',
            'block_width'   => sanitize_text_field( $attributes['block_width'] ?? '100' ),
            'options'       => $this->normalize_options( $attributes['options'] ?? [] ),
            'attributes'    => $this->field_attributes->read( $field_type, $attributes ),
        ];

        if ( ! empty( $inner ) && in_array( $field_type, ['name', 'address', 'repeater'], true ) ) {
            $children = $this->normalize_blocks( $inner );

            if ( ! empty( $children ) ) {
                $field['children'] = $children;
            }
        }

        if ( 'payment' === $field_type ) {
            $field = array_merge( $field, $this->normalize_payment( $attributes ) );
        }

        /**
         * Lets a registered field adapter expose additional safe attributes.
         *
         * @param array<string,mixed> $field Normalized field.
         * @param array<string,mixed> $attributes Registered block attributes.
         * @param string $context Normalization direction.
         */
        $filtered = apply_filters( 'formgent_mcp_normalize_form_field', $field, $attributes, 'read' );

        return is_array( $filtered ) ? $filtered : $field;
    }

    /** @param array<string,mixed> $attributes Payment block attributes. @return array<string,mixed> */
    private function normalize_payment( array $attributes ): array {
        $lists               = ['payment_methods', 'amount_fields', 'quantity_apply_to', 'subscription_amount_fields'];
        $configured_gateways = function_exists( 'formgent_get_payment_gateways' ) ? array_keys( formgent_get_payment_gateways() ) : ['stripe', 'paypal', 'mollie'];
        $configured_gateways = array_values( array_unique( array_filter( array_map( 'sanitize_key', $configured_gateways ) ) ) );
        $quantity_min        = min( 1000000, max( 1, absint( $attributes['quantity_min'] ?? 1 ) ) );
        $safe                = [
            'payment_type'               => in_array( $attributes['payment_type'] ?? '', ['one_time', 'subscription'], true ) ? $attributes['payment_type'] : 'one_time',
            'payment_methods'            => [],
            'amount_type'                => in_array( $attributes['amount_type'] ?? '', ['fixed', 'from_fields'], true ) ? $attributes['amount_type'] : 'fixed',
            'fixed_label'                => substr( sanitize_text_field( $attributes['fixed_label'] ?? '' ), 0, 255 ),
            'fixed_price'                => min( 1000000000, max( 0, (float) ( $attributes['fixed_price'] ?? 0 ) ) ),
            'amount_fields'              => [],
            'quantity_enabled'           => ! empty( $attributes['quantity_enabled'] ),
            'quantity_apply_to'          => [],
            'quantity_min'               => $quantity_min,
            'quantity_max'               => min( 1000000, max( $quantity_min, absint( $attributes['quantity_max'] ?? 10 ) ) ),
            'quantity_label'             => substr( sanitize_text_field( $attributes['quantity_label'] ?? '' ), 0, 255 ),
            'subscription_plan_name'     => substr( sanitize_text_field( $attributes['subscription_plan_name'] ?? '' ), 0, 255 ),
            'subscription_amount_type'   => in_array( $attributes['subscription_amount_type'] ?? '', ['fixed', 'from_fields'], true ) ? $attributes['subscription_amount_type'] : 'fixed',
            'subscription_fixed_label'   => substr( sanitize_text_field( $attributes['subscription_fixed_label'] ?? '' ), 0, 255 ),
            'subscription_fixed_price'   => min( 1000000000, max( 0, (float) ( $attributes['subscription_fixed_price'] ?? 0 ) ) ),
            'subscription_amount_fields' => [],
            'billing_interval'           => in_array( $attributes['billing_interval'] ?? '', ['daily', 'weekly', 'monthly', 'yearly'], true ) ? $attributes['billing_interval'] : 'monthly',
            'total_billing_times'        => min( 1000000, absint( $attributes['total_billing_times'] ?? 0 ) ),
            'free_trial_enabled'         => ! empty( $attributes['free_trial_enabled'] ),
            'trial_days'                 => min( 3650, absint( $attributes['trial_days'] ?? 0 ) ),
            'customer_name_field'        => substr( sanitize_key( $attributes['customer_name_field'] ?? '' ), 0, 255 ),
            'customer_email_field'       => substr( sanitize_key( $attributes['customer_email_field'] ?? '' ), 0, 255 ),
            'info_text'                  => substr( sanitize_text_field( $attributes['info_text'] ?? '' ), 0, 500 ),
        ];

        foreach ( $lists as $key ) {
            $default    = 'payment_methods' === $key ? ['stripe'] : [];
            $values     = is_array( $attributes[$key] ?? null ) ? $attributes[$key] : $default;
            $values     = array_slice( $values, 0, 'payment_methods' === $key ? 10 : 50 );
            $safe[$key] = array_values( array_unique( array_filter( array_map( 'sanitize_key', $values ) ) ) );

            if ( 'payment_methods' === $key ) {
                $safe[$key] = array_values( array_intersect( $safe[$key], $configured_gateways ) );

                if ( empty( $safe[$key] ) && ! empty( $configured_gateways ) ) {
                    $safe[$key] = [reset( $configured_gateways )];
                }
            }
        }

        return $safe;
    }

    /** @param mixed $options Raw block options. @return array<int,array<string,mixed>> */
    private function normalize_options( $options ): array {
        if ( ! is_array( $options ) ) {
            return [];
        }

        $normalized = [];

        foreach ( array_slice( $options, 0, 100 ) as $option ) {
            if ( ! is_array( $option ) ) {
                continue;
            }

            $safe = array_intersect_key( $option, array_flip( ['id', 'label', 'value', 'numeric_value', 'price', 'is_default', 'is_other', 'icon', 'image'] ) );

            if ( isset( $safe['id'] ) ) {
                $safe['id'] = sanitize_text_field( $safe['id'] );
            }
            if ( isset( $safe['label'] ) ) {
                $safe['label'] = sanitize_text_field( $safe['label'] );
            }
            if ( isset( $safe['value'] ) ) {
                $safe['value'] = sanitize_text_field( $safe['value'] );
            }

            foreach ( ['numeric_value', 'price'] as $numeric_key ) {
                if ( isset( $safe[$numeric_key] ) && ! is_numeric( $safe[$numeric_key] ) ) {
                    unset( $safe[$numeric_key] );
                }
            }

            foreach ( ['is_default', 'is_other'] as $boolean_key ) {
                if ( array_key_exists( $boolean_key, $safe ) ) {
                    $safe[$boolean_key] = ! empty( $safe[$boolean_key] );
                }
            }

            if ( function_exists( 'formgent_sanitize_choice_options' ) ) {
                $sanitized = formgent_sanitize_choice_options( [$safe] );
                $safe      = $sanitized[0] ?? $safe;
            }

            // The editor sanitizer stores switch values as 0/1 strings. The
            // public contract deliberately exposes real JSON booleans.
            foreach ( ['is_default', 'is_other'] as $boolean_key ) {
                if ( array_key_exists( $boolean_key, $safe ) ) {
                    $safe[$boolean_key] = ! empty( $safe[$boolean_key] );
                }
            }

            $normalized[] = $safe;
        }

        return $normalized;
    }

    /** @return array<string,string> */
    private function urls( WP_Post $post ): array {
        $can_edit = current_user_can( 'edit_post', $post->ID );

        return [
            'edit'      => $can_edit ? get_edit_post_link( $post->ID, 'raw' ) : '',
            'preview'   => $can_edit ? get_preview_post_link( $post ) : '',
            'permalink' => $this->public_url( $post ),
        ];
    }

    private function public_url( WP_Post $post ): string {
        if ( 'publish' !== $post->post_status ) {
            return '';
        }

        $settings = get_post_meta( $post->ID, '_formgent_settings', true );
        $settings = is_array( $settings ) ? $settings : [];
        $design   = is_array( $settings['design'] ?? null ) ? $settings['design'] : [];

        return empty( $design['status'] ) ? '' : (string) get_permalink( $post );
    }

    /** @return array<string,string> */
    private function embed( int $form_id ): array {
        return [
            'shortcode' => sprintf( '[formgent id="%d"]', $form_id ),
            'block'     => serialize_block(
                [
                    'blockName'    => 'formgent/form',
                    'attrs'        => ['formId' => $form_id],
                    'innerBlocks'  => [],
                    'innerHTML'    => '',
                    'innerContent' => [],
                ]
            ),
        ];
    }
}
