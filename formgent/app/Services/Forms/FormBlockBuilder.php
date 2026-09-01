<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Error;

/**
 * Validates normalized fields and builds editor-compatible Gutenberg content.
 */
class FormBlockBuilder {
    private const CORE_TYPES = [
        'address',
        'html',
        'captcha',
        'text',
        'textarea',
        'hidden',
        'name',
        'email',
        'number',
        'range-slider',
        'gdpr',
        'phone-number',
        'website',
        'single-choice',
        'multiple-choice',
        'dropdown',
        'input-masking',
        'file-upload',
        'google-map',
        'repeater',
        'rating',
        'date-picker',
        'login',
        'password',
        'register',
        'payment',
    ];

    private const CHOICE_TYPES = ['single-choice', 'multiple-choice', 'dropdown'];

    private const CHILD_TYPES = ['name', 'address', 'repeater'];

    private const PAYMENT_KEYS = [
        'payment_type',
        'payment_methods',
        'amount_type',
        'fixed_label',
        'fixed_price',
        'amount_fields',
        'quantity_enabled',
        'quantity_apply_to',
        'quantity_min',
        'quantity_max',
        'quantity_label',
        'subscription_plan_name',
        'subscription_amount_type',
        'subscription_fixed_label',
        'subscription_fixed_price',
        'subscription_amount_fields',
        'billing_interval',
        'total_billing_times',
        'free_trial_enabled',
        'trial_days',
        'customer_name_field',
        'customer_email_field',
        'info_text',
    ];

    /** @var array<string,bool> */
    private array $names = [];

    /** @var array<string,bool> */
    private array $ids = [];

    /** @var array<string,string> */
    private array $field_types = [];

    /** @var array<int,string>|null */
    private ?array $supported_types = null;

    private int $field_count = 0;

    private FormFieldAttributeService $field_attributes;

    public function __construct( FormFieldAttributeService $field_attributes ) {
        $this->field_attributes = $field_attributes;
    }

    /**
     * @param array<int,mixed> $fields Normalized public fields.
     * @return array<string,mixed>|WP_Error
     */
    public function build( array $fields, string $form_type ) {
        if ( ! in_array( $form_type, ['general', 'conversational'], true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The form type is invalid.', 'formgent' ) );
        }

        if ( empty( $fields ) || 100 < count( $fields ) ) {
            return McpErrorFactory::limit_exceeded( esc_html__( 'A form must contain between 1 and 100 fields.', 'formgent' ) );
        }

        $this->names       = [];
        $this->ids         = [];
        $this->field_types = [];
        $this->field_count = 0;
        $prepared          = [];

        foreach ( $fields as $field ) {
            $field = $this->prepare_field( $field, 0 );

            if ( is_wp_error( $field ) ) {
                return $field;
            }

            $prepared[] = $field;
        }

        $payment_validation = $this->validate_payment_fields( $prepared );

        if ( is_wp_error( $payment_validation ) ) {
            return $payment_validation;
        }

        $blocks  = 'conversational' === $form_type ? $this->conversational_blocks( $prepared ) : $this->general_blocks( $prepared );
        $content = serialize_blocks( $blocks );
        $parsed  = parse_blocks( $content );

        if ( empty( $content ) || count( $parsed ) !== count( $blocks ) || ! $this->valid_block_tree( $parsed ) ) {
            return McpErrorFactory::internal();
        }

        return [
            'content' => $content,
            'fields'  => $prepared,
        ];
    }

    /** @return array<int,string> */
    public function supported_types(): array {
        if ( null !== $this->supported_types ) {
            return $this->supported_types;
        }

        $types = apply_filters( 'formgent_mcp_form_field_types', self::CORE_TYPES );

        if ( ! is_array( $types ) ) {
            $types = self::CORE_TYPES;
        }

        $registry = class_exists( '\\WP_Block_Type_Registry' ) ? \WP_Block_Type_Registry::get_instance() : null;
        $types    = array_filter(
            $types,
            static function ( $type ) use ( $registry ): bool {
                return is_string( $type )
                    && '' !== $type
                    && sanitize_key( $type ) === $type
                    && ( ! $registry || $registry->is_registered( 'formgent/' . $type ) );
            }
        );

        $this->supported_types = array_values( array_unique( $types ) );

        return $this->supported_types;
    }

    /**
     * @param mixed $field Candidate field.
     * @return array<string,mixed>|WP_Error
     */
    private function prepare_field( $field, int $depth ) {
        $this->field_count++;

        if ( ! is_array( $field ) || 5 < $depth || 100 < $this->field_count ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A form field has an invalid nested structure.', 'formgent' ) );
        }

        $field_type = sanitize_key( $field['field_type'] ?? '' );

        if ( ! in_array( $field_type, $this->supported_types(), true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A form field type is not supported.', 'formgent' ) );
        }

        $label = sanitize_text_field( $field['label'] ?? '' );

        if ( '' === $label || 255 < strlen( $label ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Every form field requires a label of at most 255 characters.', 'formgent' ) );
        }

        if ( isset( $field['name'] ) ) {
            $name = sanitize_title( $field['name'] );

            if ( '' === $name || isset( $this->names[$name] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Supplied field names must be non-empty and unique.', 'formgent' ) );
            }

            $this->names[$name] = true;
        } else {
            $name = $this->unique_name( sanitize_title( $label ) ?: $field_type );
        }

        $this->field_types[$name] = $field_type;

        if ( isset( $field['id'] ) ) {
            $id = $field['id'];

            if ( ! is_string( $id ) || ! preg_match( '/^[A-Za-z0-9_-]{1,64}$/', $id ) || isset( $this->ids[$id] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Supplied field IDs must be safe and unique.', 'formgent' ) );
            }

            $this->ids[$id] = true;
        } else {
            $id = $this->id();
        }

        $default_value = $field['default_value'] ?? '';

        $prepared = [
            'field_type'    => $field_type,
            'id'            => $id,
            'name'          => $name,
            'label'         => $label,
            'required'      => ! empty( $field['required'] ),
            'placeholder'   => sanitize_text_field( $field['placeholder'] ?? '' ),
            'help_text'     => sanitize_text_field( $field['help_text'] ?? '' ),
            'default_value' => is_scalar( $default_value ) ? sanitize_text_field( (string) $default_value ) : '',
            'block_width'   => $field['block_width'] ?? '100',
            'options'       => [],
        ];

        if ( ! in_array( $prepared['block_width'], ['100', '75', '67', '50', '33.33', '25'], true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A form field has an invalid block width.', 'formgent' ) );
        }

        $options = $this->prepare_options( $field['options'] ?? [] );

        if ( is_wp_error( $options ) ) {
            return $options;
        }

        if ( ! empty( $options ) && ! in_array( $field_type, self::CHOICE_TYPES, true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Options are allowed only for choice fields.', 'formgent' ) );
        }

        $prepared['options'] = $options;

        $attributes = $this->field_attributes->prepare( $field_type, $field['attributes'] ?? [] );

        if ( is_wp_error( $attributes ) ) {
            return $attributes;
        }

        $prepared['attributes'] = $attributes;

        if ( isset( $field['group'] ) ) {
            $prepared['group'] = sanitize_text_field( $field['group'] );
        }

        if ( isset( $field['children'] ) ) {
            if ( ! in_array( $field_type, self::CHILD_TYPES, true ) || ! is_array( $field['children'] ) || 50 < count( $field['children'] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'This field does not accept the supplied children.', 'formgent' ) );
            }

            $prepared['children'] = [];

            foreach ( $field['children'] as $child ) {
                $child = $this->prepare_field( $child, $depth + 1 );

                if ( is_wp_error( $child ) ) {
                    return $child;
                }

                $prepared['children'][] = $child;
            }
        }

        if ( 'payment' === $field_type ) {
            $payment = $this->prepare_payment( $field );

            if ( is_wp_error( $payment ) ) {
                return $payment;
            }

            $prepared = array_merge( $prepared, $payment );
        } elseif ( ! empty( array_intersect( array_keys( $field ), self::PAYMENT_KEYS ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Payment settings are accepted only by the payment field.', 'formgent' ) );
        }

        /**
         * Filters a validated normalized field. Returning invalid data fails closed later.
         *
         * @param array<string,mixed> $prepared Validated field.
         * @param array<string,mixed> $field Original field.
         */
        $prepared = apply_filters( 'formgent_mcp_normalize_form_field', $prepared, $field, 'write' );

        return is_array( $prepared ) ? $prepared : McpErrorFactory::internal();
    }

    /** @param mixed $options Candidate options. @return array<int,array<string,mixed>>|WP_Error */
    private function prepare_options( $options ) {
        if ( ! is_array( $options ) || 100 < count( $options ) ) {
            return McpErrorFactory::limit_exceeded( esc_html__( 'A field can contain at most 100 options.', 'formgent' ) );
        }

        $prepared = [];
        $values   = [];

        foreach ( $options as $option ) {
            if ( ! is_array( $option ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A field option is invalid.', 'formgent' ) );
            }

            $label = sanitize_text_field( $option['label'] ?? '' );
            $value = sanitize_text_field( $option['value'] ?? sanitize_title( $label ) );

            if ( '' === $label || '' === $value || 255 < strlen( $label ) || 255 < strlen( $value ) || isset( $values[$value] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Field option labels and values must be non-empty and unique.', 'formgent' ) );
            }

            if ( isset( $option['id'] ) ) {
                $id = $option['id'];

                if ( ! is_string( $id ) || ! preg_match( '/^[A-Za-z0-9_-]{1,64}$/', $id ) || isset( $this->ids[$id] ) ) {
                    return McpErrorFactory::invalid_input( esc_html__( 'Supplied option IDs must be safe and unique.', 'formgent' ) );
                }

                $this->ids[$id] = true;
            } else {
                $id = $this->id();
            }

            $values[$value] = true;
            $item           = [
                'id'    => $id,
                'label' => $label,
                'value' => $value,
            ];

            foreach ( ['numeric_value', 'price'] as $numeric_key ) {
                if ( array_key_exists( $numeric_key, $option ) ) {
                    if ( ! is_numeric( $option[$numeric_key] ) ) {
                        return McpErrorFactory::invalid_input( esc_html__( 'Choice option numeric values must be valid numbers.', 'formgent' ) );
                    }

                    $item[$numeric_key] = is_string( $option[$numeric_key] )
                        ? sanitize_text_field( $option[$numeric_key] )
                        : (float) $option[$numeric_key];
                }
            }

            foreach ( ['is_default', 'is_other'] as $boolean_key ) {
                if ( array_key_exists( $boolean_key, $option ) ) {
                    $item[$boolean_key] = (bool) $option[$boolean_key];
                }
            }

            if ( function_exists( 'formgent_sanitize_choice_options' ) ) {
                $media = formgent_sanitize_choice_options( [$option] );

                foreach ( ['icon', 'image'] as $media_key ) {
                    if ( isset( $media[0][$media_key] ) ) {
                        $item[$media_key] = $media[0][$media_key];
                    }
                }
            }

            $prepared[] = $item;
        }

        return $prepared;
    }

    /** @param array<int,array<string,mixed>> $fields Prepared fields. @return array<int,array<string,mixed>> */
    private function general_blocks( array $fields ): array {
        $blocks     = [];
        $groups     = [];
        $has_groups = false;

        foreach ( $fields as $field ) {
            $group                                                        = $field['group'] ?? '';
            $has_groups                                                   = $has_groups || '' !== $group;
            $groups[$group ?: esc_html__( 'Default Step', 'formgent' )][] = $field;
        }

        if ( ! $has_groups ) {
            foreach ( $fields as $field ) {
                $blocks[] = $this->field_block( $field );
            }

            if ( ! $this->contains_login( $fields ) ) {
                $blocks[] = $this->block( 'formgent/submit-button', ['id' => $this->id()] );
            }

            return $blocks;
        }

        $last = count( $groups ) - 1;
        $i    = 0;

        foreach ( $groups as $label => $group_fields ) {
            foreach ( $group_fields as $field ) {
                $blocks[] = $this->field_block( $field );
            }

            $blocks[] = $this->block(
                'formgent/page-break',
                [
                    'id'               => $this->id(),
                    'name'             => $this->unique_name( 'page-break' ),
                    'step_text'        => $label,
                    'next_button_text' => $i === $last ? esc_html__( 'Submit', 'formgent' ) : esc_html__( 'Next', 'formgent' ),
                    'back_button_text' => esc_html__( 'Back', 'formgent' ),
                ]
            );
            $i++;
        }

        return $blocks;
    }

    /** @param array<int,array<string,mixed>> $fields Prepared fields. @return array<int,array<string,mixed>> */
    private function conversational_blocks( array $fields ): array {
        $blocks = [
            $this->block(
                'formgent/welcome',
                [ 'id' => $this->id() ],
                [
                    $this->core_text_block( 'core/heading', 'h2', esc_html__( 'Welcome', 'formgent' ) ),
                    $this->core_text_block( 'core/paragraph', 'p', esc_html__( 'Hi there, please fill out and submit this form.', 'formgent' ) ),
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
            ),
        ];

        foreach ( $fields as $field ) {
            $field_block                   = $this->field_block( $field );
            $field_block['attrs']['label'] = '';
            $inner                         = [
                $this->core_text_block( 'core/heading', 'h2', $field['label'] ),
                $this->core_text_block( 'core/paragraph', 'p', $field['help_text'] ),
                $field_block,
                $this->block( 'formgent/next-button', ['id' => $this->id()] ),
            ];
            $blocks[]                      = $this->block( 'formgent/step', ['id' => $this->id()], $inner );
        }

        $blocks[] = $this->block(
            'formgent/end',
            [ 'id' => $this->id() ],
            [
                $this->core_text_block( 'core/heading', 'h2', esc_html__( 'Thank you', 'formgent' ) ),
                $this->core_text_block( 'core/paragraph', 'p', esc_html__( 'Your submission has been received!', 'formgent' ) ),
            ]
        );

        return $blocks;
    }

    /** @param array<string,mixed> $field Prepared field. @return array<string,mixed> */
    private function field_block( array $field ): array {
        $type  = $field['field_type'];
        $attrs = [
            'id'          => $field['id'],
            'name'        => $field['name'],
            'label'       => $field['label'],
            'required'    => $field['required'],
            'placeholder' => $field['placeholder'],
            'sub_label'   => $field['help_text'],
            'value'       => $field['default_value'],
            'block_width' => $field['block_width'],
        ];

        if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
            $attrs = array_merge( $attrs, $field['attributes'] );
        }

        if ( ! empty( $field['options'] ) ) {
            $attrs['options'] = $field['options'];
        }

        if ( 'payment' === $type ) {
            $attrs = array_merge( $attrs, array_intersect_key( $field, array_flip( self::PAYMENT_KEYS ) ) );
        }

        if ( 'gdpr' === $type ) {
            $attrs['description'] = $field['label'];
        }

        $inner = [];

        if ( ! empty( $field['children'] ) ) {
            foreach ( $field['children'] as $child ) {
                $inner[] = $this->field_block( $child );
            }
        } elseif ( 'name' === $type ) {
            $inner = $this->name_blocks();
        } elseif ( 'address' === $type ) {
            $inner = $this->address_blocks();
        } elseif ( 'repeater' === $type ) {
            $inner = $this->repeater_blocks();
        } elseif ( 'login' === $type ) {
            $inner = $this->login_blocks();
        } elseif ( 'register' === $type ) {
            $inner = $this->register_blocks();
        }

        $block = $this->block( 'formgent/' . $type, $attrs, $inner );

        $block = apply_filters( 'formgent_mcp_build_form_field_block', $block, $field );

        if ( ! is_array( $block ) || 'formgent/' . $type !== ( $block['blockName'] ?? '' ) || ! is_array( $block['attrs'] ?? null ) ) {
            return $this->block( 'formgent/' . $type, $attrs, $inner );
        }

        return $block;
    }

    /** @return array<int,array<string,mixed>> */
    private function name_blocks(): array {
        return [
            $this->simple_child( 'text', 'First Name', 'first-name', '33.33' ),
            $this->simple_child( 'text', 'Middle Name', 'middle-name', '33.33' ),
            $this->simple_child( 'text', 'Last Name', 'last-name', '33.33' ),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function address_blocks(): array {
        $countries = [];

        foreach ( formgent_config( 'countries' ) as $code => $country ) {
            $countries[] = [
                'id'    => strtoupper( $code ),
                'label' => $country['name'],
                'value' => strtoupper( $code ),
            ];
        }

        return [
            $this->simple_child( 'text', 'Address line 1', 'address-line-one', '100' ),
            $this->simple_child( 'text', 'Address line 2', 'address-line-two', '100' ),
            $this->simple_child( 'text', 'City', 'city', '50' ),
            $this->simple_child( 'text', 'State / Province', 'state', '50' ),
            $this->simple_child( 'text', 'Postal code / Zip Code', 'zip-code', '50' ),
            $this->block(
                'formgent/dropdown',
                [
                    'id'          => $this->id(),
                    'name'        => $this->unique_name( 'country' ),
                    'label'       => 'Country',
                    'placeholder' => 'Select a Country',
                    'block_width' => '50',
                    'options'     => $countries,
                ]
            ),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function repeater_blocks(): array {
        return [
            $this->simple_child( 'text', 'Text', 'text', '50' ),
            $this->simple_child( 'number', 'Number', 'number', '50' ),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function login_blocks(): array {
        return [
            $this->simple_child( 'login-username', 'Username or email', 'login-username', '100', true ),
            $this->simple_child( 'login-password', 'Password', 'login-password', '100', true, ['enable_password_visibility_toggle' => true] ),
            $this->simple_child( 'login-remember-me', 'Remember Me', 'login-remember', '50' ),
            $this->block( 'formgent/login-forgot-password', ['name' => $this->unique_name( 'login-forgot-password' ), 'link_text' => 'Forgot Password?', 'text_alignment' => 'right', 'block_width' => '50'] ),
            $this->block( 'formgent/submit-button', ['id' => $this->id(), 'name' => $this->unique_name( 'login-button' ), 'button_text' => 'Sign In', 'button_alignment' => 'block'] ),
            $this->block( 'formgent/login-signup-link', ['name' => $this->unique_name( 'login-signup-link' ), 'prefix_text' => "Don't have an account?", 'link_text' => 'Sign Up', 'text_alignment' => 'center', 'block_width' => '100'] ),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function register_blocks(): array {
        return [
            $this->simple_child( 'text', 'First Name', 'first-name', '33.33' ),
            $this->simple_child( 'text', 'Middle Name', 'middle-name', '33.33' ),
            $this->simple_child( 'text', 'Last Name', 'last-name', '33.33' ),
            $this->simple_child( 'text', 'Username', 'username', '100' ),
            $this->simple_child( 'email', 'Email', 'email', '100', true ),
            $this->simple_child( 'password', 'Password', 'password', '100', true, ['enable_confirmation_field' => true, 'confirm_label' => 'Confirm Password'] ),
            $this->block( 'formgent/register-signin-link', ['name' => $this->unique_name( 'register-signin-link' ), 'prefix_text' => 'Already have an account?', 'link_text' => 'Sign In', 'block_width' => '100'] ),
        ];
    }

    /** @param array<string,mixed> $extra Extra attributes. @return array<string,mixed> */
    private function simple_child( string $type, string $label, string $name, string $width, bool $required = false, array $extra = [] ): array {
        return $this->block(
            'formgent/' . $type,
            array_merge(
                [
                    'id'          => $this->id(),
                    'name'        => $this->unique_name( $name ),
                    'label'       => $label,
                    'required'    => $required,
                    'block_width' => $width,
                ],
                $extra
            )
        );
    }

    /** @param array<string,mixed> $attrs Block attributes. @param array<int,array<string,mixed>> $inner Inner blocks. @return array<string,mixed> */
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
    private function core_text_block( string $name, string $tag, string $content ): array {
        $html = sprintf( '<%1$s>%2$s</%1$s>', tag_escape( $tag ), esc_html( $content ) );

        return [
            'blockName'    => $name,
            'attrs'        => [],
            'innerBlocks'  => [],
            'innerHTML'    => $html,
            'innerContent' => [$html],
        ];
    }

    private function unique_name( string $candidate ): string {
        $base = sanitize_title( $candidate ) ?: 'field';
        $name = $base;
        $i    = 2;

        while ( isset( $this->names[$name] ) ) {
            $name = $base . '-' . $i;
            $i++;
        }

        $this->names[$name] = true;

        return $name;
    }

    private function id(): string {
        do {
            $id = substr( str_replace( '-', '', wp_generate_uuid4() ), 0, 12 );
        } while ( isset( $this->ids[$id] ) );

        $this->ids[$id] = true;

        return $id;
    }

    /** @param array<int,array<string,mixed>> $fields Prepared fields. */
    private function contains_login( array $fields ): bool {
        foreach ( $fields as $field ) {
            if ( 'login' === $field['field_type'] ) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string,mixed> $field Raw payment field. @return array<string,mixed>|WP_Error */
    private function prepare_payment( array $field ) {
        $payment_type = $field['payment_type'] ?? 'one_time';
        $amount_type  = $field['amount_type'] ?? 'fixed';

        if ( ! in_array( $payment_type, ['one_time', 'subscription'], true ) || ! in_array( $amount_type, ['fixed', 'from_fields'], true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The payment type or amount type is invalid.', 'formgent' ) );
        }

        if ( 'subscription' === $payment_type && ! function_exists( 'formgent_pro' ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Subscription payments require FormGent Pro to be active.', 'formgent' ) );
        }

        $configured_methods = function_exists( 'formgent_get_payment_gateways' ) ? array_keys( formgent_get_payment_gateways() ) : ['stripe', 'paypal', 'mollie'];
        $payment_methods    = $this->string_list( $field['payment_methods'] ?? ['stripe'], 10 );

        if ( is_wp_error( $payment_methods ) || empty( $payment_methods ) || ! empty( array_diff( $payment_methods, $configured_methods ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'At least one registered payment method is required.', 'formgent' ) );
        }

        if ( 'subscription' === $payment_type ) {
            $allowed_methods = function_exists( 'formgent_get_subscription_payment_gateways' )
                ? formgent_get_subscription_payment_gateways( $field )
                : ['stripe'];

            if ( ! empty( array_diff( $payment_methods, $allowed_methods ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A selected payment method does not support subscriptions.', 'formgent' ) );
            }
        }

        $amount_fields              = $this->string_list( $field['amount_fields'] ?? [], 50 );
        $quantity_apply_to          = $this->string_list( $field['quantity_apply_to'] ?? [], 50 );
        $subscription_amount_fields = $this->string_list( $field['subscription_amount_fields'] ?? [], 50 );

        if ( is_wp_error( $amount_fields ) || is_wp_error( $quantity_apply_to ) || is_wp_error( $subscription_amount_fields ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Payment field mappings must contain unique field names.', 'formgent' ) );
        }

        $fixed_price              = $this->price( $field['fixed_price'] ?? 0 );
        $subscription_fixed_price = $this->price( $field['subscription_fixed_price'] ?? 0 );
        $quantity_enabled         = $field['quantity_enabled'] ?? false;
        $free_trial_enabled       = $field['free_trial_enabled'] ?? false;
        $quantity_min             = $field['quantity_min'] ?? 1;
        $quantity_max             = $field['quantity_max'] ?? 10;
        $total_billing_times      = $field['total_billing_times'] ?? 0;
        $trial_days               = $field['trial_days'] ?? 0;

        if ( null === $fixed_price || null === $subscription_fixed_price
            || ! is_bool( $quantity_enabled ) || ! is_bool( $free_trial_enabled )
            || ! is_int( $quantity_min ) || ! is_int( $quantity_max ) || 1 > $quantity_min || $quantity_min > $quantity_max || 1000000 < $quantity_max
            || ! is_int( $total_billing_times ) || 0 > $total_billing_times || 1000000 < $total_billing_times
            || ! is_int( $trial_days ) || 0 > $trial_days || 3650 < $trial_days ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The payment amounts, quantity, billing count, or trial duration is invalid.', 'formgent' ) );
        }

        $subscription_amount_type = $field['subscription_amount_type'] ?? 'fixed';
        $billing_interval         = $field['billing_interval'] ?? 'monthly';

        if ( ! in_array( $subscription_amount_type, ['fixed', 'from_fields'], true ) || ! in_array( $billing_interval, ['daily', 'weekly', 'monthly', 'yearly'], true ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The subscription amount type or billing interval is invalid.', 'formgent' ) );
        }

        return [
            'payment_type'               => $payment_type,
            'payment_methods'            => $payment_methods,
            'amount_type'                => $amount_type,
            'fixed_label'                => $this->plain_text( $field['fixed_label'] ?? '', 255 ),
            'fixed_price'                => $fixed_price,
            'amount_fields'              => $amount_fields,
            'quantity_enabled'           => $quantity_enabled,
            'quantity_apply_to'          => $quantity_apply_to,
            'quantity_min'               => $quantity_min,
            'quantity_max'               => $quantity_max,
            'quantity_label'             => $this->plain_text( $field['quantity_label'] ?? '', 255 ),
            'subscription_plan_name'     => $this->plain_text( $field['subscription_plan_name'] ?? '', 255 ),
            'subscription_amount_type'   => $subscription_amount_type,
            'subscription_fixed_label'   => $this->plain_text( $field['subscription_fixed_label'] ?? '', 255 ),
            'subscription_fixed_price'   => $subscription_fixed_price,
            'subscription_amount_fields' => $subscription_amount_fields,
            'billing_interval'           => $billing_interval,
            'total_billing_times'        => $total_billing_times,
            'free_trial_enabled'         => $free_trial_enabled,
            'trial_days'                 => $trial_days,
            'customer_name_field'        => sanitize_key( $field['customer_name_field'] ?? '' ),
            'customer_email_field'       => sanitize_key( $field['customer_email_field'] ?? '' ),
            'info_text'                  => $this->plain_text( $field['info_text'] ?? '', 500 ),
        ];
    }

    /** @param mixed $values Raw names. @return array<int,string>|WP_Error */
    private function string_list( $values, int $maximum ) {
        if ( ! is_array( $values ) || $maximum < count( $values ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A payment field mapping is invalid.', 'formgent' ) );
        }

        $prepared = [];

        foreach ( $values as $value ) {
            if ( ! is_string( $value ) || '' === $value || sanitize_key( $value ) !== $value || in_array( $value, $prepared, true ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Payment field mappings must contain unique field names.', 'formgent' ) );
            }

            $prepared[] = $value;
        }

        return $prepared;
    }

    /** @param mixed $value Candidate price. */
    private function price( $value ): ?float {
        if ( ! is_int( $value ) && ! is_float( $value ) ) {
            return null;
        }

        $value = (float) $value;

        return is_finite( $value ) && 0 <= $value && 1000000000 >= $value ? round( $value, 2 ) : null;
    }

    /** @param mixed $value Candidate string. */
    private function plain_text( $value, int $maximum ): string {
        if ( ! is_string( $value ) ) {
            return '';
        }

        return substr( sanitize_text_field( $value ), 0, $maximum );
    }

    /** @param array<int,array<string,mixed>> $fields Prepared fields. @return true|WP_Error */
    private function validate_payment_fields( array $fields ) {
        $all_fields = $this->flatten_fields( $fields );
        $payments   = array_values(
            array_filter(
                $all_fields,
                static function ( array $field ): bool {
                    return 'payment' === $field['field_type'];
                }
            )
        );

        if ( 1 < count( $payments ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'A form can contain only one payment field.', 'formgent' ) );
        }

        if ( empty( $payments ) ) {
            return true;
        }

        if ( 1 !== count(
            array_filter(
                $fields,
                static function ( array $field ): bool {
                    return 'payment' === $field['field_type'];
                }
            )
        ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The payment field must be at the top level of the form.', 'formgent' ) );
        }

        $payment      = $payments[0];
        $amount_types = ['single-choice', 'multiple-choice', 'dropdown', 'number'];

        foreach ( array_merge( $payment['amount_fields'], $payment['subscription_amount_fields'] ) as $name ) {
            if ( ! isset( $this->field_types[$name] ) || ! in_array( $this->field_types[$name], $amount_types, true ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A payment amount mapping references an incompatible or missing field.', 'formgent' ) );
            }
        }

        if ( 'one_time' === $payment['payment_type'] && 'from_fields' === $payment['amount_type'] && empty( $payment['amount_fields'] ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'An amount-from-fields payment requires at least one amount field.', 'formgent' ) );
        }

        if ( $payment['quantity_enabled'] && ( empty( $payment['quantity_apply_to'] ) || ! empty( array_diff( $payment['quantity_apply_to'], $payment['amount_fields'] ) ) ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Quantity mappings must reference selected one-time amount fields.', 'formgent' ) );
        }

        if ( 'subscription' === $payment['payment_type'] ) {
            if ( 'from_fields' === $payment['subscription_amount_type'] && empty( $payment['subscription_amount_fields'] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'An amount-from-fields subscription requires at least one amount field.', 'formgent' ) );
            }

            if ( '' === $payment['customer_name_field'] || ! isset(
                $this->field_types[$payment['customer_name_field']]
            ) || ! in_array( $this->field_types[$payment['customer_name_field']], ['name', 'text'], true )
                || '' === $payment['customer_email_field'] || 'email' !== ( $this->field_types[$payment['customer_email_field']] ?? '' ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A subscription requires mapped customer name and email fields.', 'formgent' ) );
            }
        }

        return true;
    }

    /** @param array<int,array<string,mixed>> $fields Prepared fields. @return array<int,array<string,mixed>> */
    private function flatten_fields( array $fields ): array {
        $flat = [];

        foreach ( $fields as $field ) {
            $flat[] = $field;

            if ( ! empty( $field['children'] ) && is_array( $field['children'] ) ) {
                $flat = array_merge( $flat, $this->flatten_fields( $field['children'] ) );
            }
        }

        return $flat;
    }

    /** @param array<int,array<string,mixed>> $blocks Parsed block tree. */
    private function valid_block_tree( array $blocks, int $depth = 0 ): bool {
        if ( 10 < $depth ) {
            return false;
        }

        $registry = class_exists( '\\WP_Block_Type_Registry' ) ? \WP_Block_Type_Registry::get_instance() : null;

        foreach ( $blocks as $block ) {
            $name = $block['blockName'] ?? '';

            if ( ! is_string( $name ) || '' === $name || ( $registry && ! $registry->is_registered( $name ) ) ) {
                return false;
            }

            $inner = $block['innerBlocks'] ?? [];

            if ( ! is_array( $inner ) || ! $this->valid_block_tree( $inner, $depth + 1 ) ) {
                return false;
            }
        }

        return true;
    }
}
