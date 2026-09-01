<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Settings\SafeSettingKeys;

/**
 * Central JSON Schema fragments shared by FormGent abilities.
 */
class FormSchemaService {
    private FormBlockBuilder $builder;

    private FormFieldAttributeService $field_attributes;

    private FormLayoutService $layout;

    public function __construct( FormBlockBuilder $builder, FormFieldAttributeService $field_attributes, FormLayoutService $layout ) {
        $this->builder          = $builder;
        $this->field_attributes = $field_attributes;
        $this->layout           = $layout;
    }

    /**
     * @param array<string,mixed> $properties Object properties.
     * @param array<int,string> $required Required property names.
     * @return array<string,mixed>
     */
    public function object( array $properties, array $required = [] ): array {
        $schema = [
            'type'                 => 'object',
            'properties'           => $properties,
            'additionalProperties' => false,
        ];

        if ( ! empty( $required ) ) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    /**
     * @param array<string,mixed> $properties Output properties.
     * @param array<int,string> $required Required property names.
     * @return array<string,mixed>
     */
    public function output( array $properties = [], array $required = [] ): array {
        $properties = array_merge(
            [
                'schema_version' => [
                    'type' => 'string',
                    'enum' => ['1.0'],
                ],
            ],
            $properties
        );

        array_unshift( $required, 'schema_version' );

        return $this->object( $properties, array_values( array_unique( $required ) ) );
    }

    /** @return array<string,mixed> */
    public function safe_settings(): array {
        $toggle   = [
            'type' => 'string',
            'enum' => ['yes', 'no'],
        ];
        $message  = [
            'type'      => 'string',
            'minLength' => 1,
            'maxLength' => 255,
        ];
        $messages = [];

        foreach ( SafeSettingKeys::VALIDATION_MESSAGES as $key ) {
            $messages[$key] = $message;
        }

        return $this->object(
            [
                'general'            => $this->object( ['disable_ip_logging' => $toggle] ),
                'validation'         => $this->object( $messages ),
                'security'           => $this->object( ['enable_honeypot_protection' => $toggle] ),
                'login_registration' => $this->object(
                    [
                        'login'        => $this->object(
                            [
                                'status' => ['type' => 'string', 'enum' => ['0', '1']],
                                'page'   => ['type' => 'integer', 'minimum' => 0],
                            ]
                        ),
                        'registration' => $this->object(
                            [
                                'status' => ['type' => 'string', 'enum' => ['0', '1']],
                                'page'   => ['type' => 'integer', 'minimum' => 0],
                            ]
                        ),
                    ]
                ),
            ]
        );
    }

    /** @return array<string,mixed> */
    private function normalized_field( int $depth = 0 ): array {
        $icon       = $this->object(
            [
                'name' => ['type' => 'string'],
                'svg'  => ['type' => 'string'],
            ]
        );
        $image      = $this->object(
            [
                'id'        => ['type' => 'integer'],
                'url'       => ['type' => 'string'],
                'alt'       => ['type' => 'string'],
                'thumbnail' => ['type' => 'string'],
            ]
        );
        $option     = $this->object(
            [
                'id'            => ['type' => 'string'],
                'label'         => ['type' => 'string'],
                'value'         => ['type' => 'string'],
                'numeric_value' => ['type' => ['number', 'string']],
                'price'         => ['type' => ['number', 'string']],
                'is_default'    => ['type' => 'boolean'],
                'is_other'      => ['type' => 'boolean'],
                'icon'          => $icon,
                'image'         => $image,
            ]
        );
        $properties = [
            'field_type'    => ['type' => 'string'],
            'block_name'    => ['type' => 'string'],
            'id'            => ['type' => 'string'],
            'name'          => ['type' => 'string'],
            'label'         => ['type' => 'string'],
            'required'      => ['type' => 'boolean'],
            'placeholder'   => ['type' => 'string'],
            'help_text'     => ['type' => 'string'],
            'default_value' => ['type' => 'string'],
            'block_width'   => ['type' => 'string'],
            'group'         => ['type' => 'string'],
            'options'       => [
                'type'     => 'array',
                'items'    => $option,
                'maxItems' => 100,
            ],
            'attributes'    => $this->field_attributes->schema( $this->builder->supported_types() ),
        ];
        $properties = array_merge( $properties, $this->payment_properties() );

        if ( 5 > $depth ) {
            $properties['children'] = [
                'type'     => 'array',
                'items'    => $this->normalized_field( $depth + 1 ),
                'maxItems' => 50,
            ];
        }

        return $this->object( $properties, ['field_type'] );
    }

    /** @return array<string,mixed> */
    private function input_field( int $depth = 0 ): array {
        $icon       = $this->object(
            [
                'name' => ['type' => 'string', 'maxLength' => 100],
                'svg'  => ['type' => 'string', 'maxLength' => 20000],
            ]
        );
        $image      = $this->object(
            [
                'id'        => ['type' => 'integer', 'minimum' => 1],
                'url'       => ['type' => 'string', 'maxLength' => 2048],
                'alt'       => ['type' => 'string', 'maxLength' => 255],
                'thumbnail' => ['type' => 'string', 'maxLength' => 2048],
            ]
        );
        $option     = $this->object(
            [
                'id'            => ['type' => 'string', 'pattern' => '^[A-Za-z0-9_-]{1,64}$'],
                'label'         => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                'value'         => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                'numeric_value' => ['type' => ['number', 'string']],
                'price'         => ['type' => ['number', 'string']],
                'is_default'    => ['type' => 'boolean'],
                'is_other'      => ['type' => 'boolean'],
                'icon'          => $icon,
                'image'         => $image,
            ],
            ['label']
        );
        $properties = [
            'field_type'    => ['type' => 'string', 'enum' => $this->builder->supported_types()],
            'id'            => ['type' => 'string', 'pattern' => '^[A-Za-z0-9_-]{1,64}$'],
            'name'          => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'label'         => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'required'      => ['type' => 'boolean'],
            'placeholder'   => ['type' => 'string', 'maxLength' => 255],
            'help_text'     => ['type' => 'string', 'maxLength' => 1000],
            'default_value' => ['type' => 'string', 'maxLength' => 10000],
            'block_width'   => ['type' => 'string', 'enum' => ['100', '75', '67', '50', '33.33', '25']],
            'group'         => ['type' => 'string', 'maxLength' => 100],
            'options'       => ['type' => 'array', 'items' => $option, 'maxItems' => 100],
            'attributes'    => $this->field_attributes->schema( $this->builder->supported_types() ),
        ];
        $properties = array_merge( $properties, $this->payment_properties() );

        if ( 5 > $depth ) {
            $properties['children'] = ['type' => 'array', 'items' => $this->input_field( $depth + 1 ), 'maxItems' => 50];
        }

        $schema = $this->object( $properties, ['field_type', 'label'] );

        /**
         * Extends the closed MCP field input schema for registered field adapters.
         * Extensions must preserve an object schema with additionalProperties=false.
         *
         * @param array<string,mixed> $schema Closed field schema.
         * @param int $depth Current child depth.
         */
        $schema = apply_filters( 'formgent_mcp_form_field_schema', $schema, $depth );

        if ( ! is_array( $schema ) || 'object' !== ( $schema['type'] ?? '' ) || false !== ( $schema['additionalProperties'] ?? true ) ) {
            return $this->object( $properties, ['field_type', 'label'] );
        }

        return $schema;
    }

    /** @return array<string,mixed> */
    public function input_fields(): array {
        return [
            'type'     => 'array',
            'items'    => $this->input_field(),
            'minItems' => 1,
            'maxItems' => 100,
        ];
    }

    /** @return array<string,mixed> */
    private function layout_block( int $depth = 0 ): array {
        $properties = [
            'block_name' => ['type' => 'string', 'pattern' => '^(?:formgent/[a-z0-9-]+|core/(?:heading|paragraph))$'],
            'attributes' => $this->layout->attributes_schema(),
            'content'    => ['type' => 'string', 'maxLength' => 20000],
        ];

        if ( 8 > $depth ) {
            $properties['inner_blocks'] = [
                'type'     => 'array',
                'items'    => $this->layout_block( $depth + 1 ),
                'maxItems' => 100,
            ];
        }

        return $this->object( $properties, ['block_name'] );
    }

    /** @return array<string,mixed> */
    public function input_layout(): array {
        return [
            'type'     => 'array',
            'items'    => $this->layout_block(),
            'minItems' => 1,
            'maxItems' => 250,
        ];
    }

    /** @return array<string,mixed> */
    public function safe_form_settings(): array {
        $color = [
            'type'    => 'string',
            'pattern' => '^#[A-Fa-f0-9]{6}$',
        ];
        $json  = [
            'type' => ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null'],
        ];

        $schema = $this->object(
            [
                'behavior'     => $this->object(
                    [
                        'save_incompleted_data'  => [
                            'type' => 'string',
                            'enum' => ['yes', 'no'],
                        ],
                        'hide_formgent_branding' => [
                            'type' => 'string',
                            'enum' => ['yes', 'no'],
                        ],
                    ]
                ),
                'confirmation' => $this->object(
                    [
                        'type'             => [
                            'type' => 'string',
                            'enum' => ['message', 'page', 'url'],
                        ],
                        'message'          => ['type' => 'string', 'maxLength' => 10000],
                        'page'             => ['type' => 'integer'],
                        'url'              => ['type' => 'string', 'maxLength' => 2048],
                        'after_submission' => [
                            'type' => 'string',
                            'enum' => ['reset', 'hide'],
                        ],
                    ]
                ),
                'quiz'         => $this->object(
                    [
                        'is_enabled'         => ['type' => 'boolean'],
                        'show_results'       => ['type' => 'boolean'],
                        'is_grading_enabled' => ['type' => 'boolean'],
                        'score_text'         => ['type' => 'string', 'maxLength' => 1000],
                        'grades'             => [
                            'type'     => 'array',
                            'maxItems' => 20,
                            'items'    => $this->object(
                                [
                                    'id'    => ['type' => 'string', 'maxLength' => 64],
                                    'label' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 50],
                                    'min'   => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                    'max'   => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                ]
                            ),
                        ],
                    ]
                ),
                'appearance'   => $this->object(
                    [
                        'use_label_as_placeholder'       => ['type' => 'boolean'],
                        'show_page_break_labels'         => ['type' => 'boolean'],
                        'page_break_progress_indicator'  => [
                            'type' => 'string',
                            'enum' => ['none', 'progress_bar', 'steps'],
                        ],
                        'submit_button_alignment'        => [
                            'type' => 'string',
                            'enum' => ['left', 'middle', 'right', 'block'],
                        ],
                        'submit_button_style'            => [
                            'type' => 'string',
                            'enum' => ['default', 'solid', 'bordered'],
                        ],
                        'next_button_style'              => [
                            'type' => 'string',
                            'enum' => ['default', 'solid', 'bordered'],
                        ],
                        'back_button_style'              => [
                            'type' => 'string',
                            'enum' => ['default', 'solid', 'bordered'],
                        ],
                        'submit_button_background_color' => $color,
                        'submit_button_border_color'     => $color,
                        'submit_button_text_color'       => $color,
                        'next_button_bg_color'           => $color,
                        'next_button_border_color'       => $color,
                        'next_button_text_color'         => $color,
                        'back_button_bg_color'           => $color,
                        'back_button_border_color'       => $color,
                        'back_button_text_color'         => $color,
                        'form_background'                => ['type' => 'string', 'enum' => ['color', 'image']],
                        'form_background_color'          => $color,
                        'form_background_image'          => $json,
                        'form_padding'                   => $json,
                        'form_margin'                    => $json,
                        'form_border'                    => $json,
                        'field_vertical_spacing'         => $json,
                        'field_horizontal_spacing'       => $json,
                        'field_colors'                   => $json,
                        'field_border'                   => $json,
                        'submit_button_disabled'         => ['type' => 'boolean'],
                        'submit_button_label'            => ['type' => 'string', 'maxLength' => 255],
                    ]
                ),
                'design'       => $this->object(
                    [
                        'status'      => ['type' => 'boolean'],
                        'show_cover'  => ['type' => 'boolean'],
                        'cover'       => $json,
                        'cover_type'  => ['type' => 'string', 'enum' => ['color', 'media']],
                        'is_cover_bg' => ['type' => 'boolean'],
                        'show_logo'   => ['type' => 'boolean'],
                        'logo'        => $json,
                        'show_title'  => ['type' => 'boolean'],
                        'title'       => ['type' => 'string', 'maxLength' => 255],
                        'width'       => ['type' => 'string', 'maxLength' => 50],
                    ]
                ),
                'custom_code'  => $this->object(
                    [
                        'css' => ['type' => 'string', 'maxLength' => 100000],
                        'js'  => ['type' => 'string', 'maxLength' => 100000],
                    ]
                ),
            ]
        );

        /**
         * Extends the non-secret per-form settings contract for Pro/add-ons.
         * Extensions must keep the root object closed.
         *
         * @param array<string,mixed> $schema Closed settings schema.
         */
        $core_schema = $schema;
        $schema      = apply_filters( 'formgent_mcp_form_settings_schema', $schema );

        return is_array( $schema ) && 'object' === ( $schema['type'] ?? '' ) && false === ( $schema['additionalProperties'] ?? true )
            ? $schema
            : $core_schema;
    }

    /** @return array<string,array<string,mixed>> */
    private function payment_properties(): array {
        $gateways    = function_exists( 'formgent_get_payment_gateways' ) ? array_keys( formgent_get_payment_gateways() ) : ['stripe', 'paypal', 'mollie'];
        $gateways    = array_values( array_unique( array_filter( array_map( 'sanitize_key', $gateways ) ) ) );
        $field_names = [
            'type'        => 'array',
            'items'       => [
                'type'      => 'string',
                'minLength' => 1,
                'maxLength' => 255,
                'pattern'   => '^[a-z0-9_-]+$',
            ],
            'maxItems'    => 50,
            'uniqueItems' => true,
        ];

        return [
            'payment_type'               => ['type' => 'string', 'enum' => ['one_time', 'subscription']],
            'payment_methods'            => [
                'type'        => 'array',
                'items'       => ['type' => 'string', 'enum' => $gateways],
                'minItems'    => 1,
                'maxItems'    => 10,
                'uniqueItems' => true,
            ],
            'amount_type'                => ['type' => 'string', 'enum' => ['fixed', 'from_fields']],
            'fixed_label'                => ['type' => 'string', 'maxLength' => 255],
            'fixed_price'                => ['type' => 'number', 'minimum' => 0, 'maximum' => 1000000000],
            'amount_fields'              => $field_names,
            'quantity_enabled'           => ['type' => 'boolean'],
            'quantity_apply_to'          => $field_names,
            'quantity_min'               => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000000],
            'quantity_max'               => ['type' => 'integer', 'minimum' => 1, 'maximum' => 1000000],
            'quantity_label'             => ['type' => 'string', 'maxLength' => 255],
            'subscription_plan_name'     => ['type' => 'string', 'maxLength' => 255],
            'subscription_amount_type'   => ['type' => 'string', 'enum' => ['fixed', 'from_fields']],
            'subscription_fixed_label'   => ['type' => 'string', 'maxLength' => 255],
            'subscription_fixed_price'   => ['type' => 'number', 'minimum' => 0, 'maximum' => 1000000000],
            'subscription_amount_fields' => $field_names,
            'billing_interval'           => ['type' => 'string', 'enum' => ['daily', 'weekly', 'monthly', 'yearly']],
            'total_billing_times'        => ['type' => 'integer', 'minimum' => 0, 'maximum' => 1000000],
            'free_trial_enabled'         => ['type' => 'boolean'],
            'trial_days'                 => ['type' => 'integer', 'minimum' => 0, 'maximum' => 3650],
            'customer_name_field'        => ['type' => 'string', 'maxLength' => 255, 'pattern' => '^[a-z0-9_-]*$'],
            'customer_email_field'       => ['type' => 'string', 'maxLength' => 255, 'pattern' => '^[a-z0-9_-]*$'],
            'info_text'                  => ['type' => 'string', 'maxLength' => 500],
        ];
    }

    /** @return array<string,mixed> */
    public function urls(): array {
        return $this->object(
            [
                'edit'      => ['type' => 'string'],
                'preview'   => ['type' => 'string'],
                'permalink' => ['type' => 'string'],
            ],
            ['edit', 'preview', 'permalink']
        );
    }

    /** @return array<string,mixed> */
    public function embed(): array {
        return $this->object(
            [
                'shortcode' => ['type' => 'string'],
                'block'     => ['type' => 'string'],
            ],
            ['shortcode', 'block']
        );
    }

    /** @return array<string,mixed> */
    public function form(): array {
        return $this->object(
            [
                'id'              => ['type' => 'integer'],
                'title'           => ['type' => 'string'],
                'status'          => [
                    'type' => 'string',
                    'enum' => ['draft', 'publish'],
                ],
                'type'            => [
                    'type' => 'string',
                    'enum' => ['general', 'conversational'],
                ],
                'fields'          => [
                    'type'     => 'array',
                    'items'    => $this->normalized_field(),
                    'maxItems' => 100,
                ],
                'layout'          => [
                    'type'     => 'array',
                    'items'    => $this->layout_block(),
                    'maxItems' => 250,
                ],
                'layout_complete' => ['type' => 'boolean'],
                'settings'        => $this->safe_form_settings(),
                'urls'            => $this->urls(),
                'embed'           => $this->embed(),
            ],
            ['id', 'title', 'status', 'type', 'fields', 'layout', 'layout_complete', 'settings', 'urls', 'embed']
        );
    }

    /** @return array<string,mixed> */
    public function analytics(): array {
        return $this->object(
            [
                'form_id'                    => ['type' => 'integer'],
                'views'                      => ['type' => 'integer'],
                'starts'                     => ['type' => 'integer'],
                'completions'                => ['type' => 'integer'],
                'unread_responses'           => ['type' => 'integer'],
                'completion_rate'            => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                'average_completion_seconds' => ['type' => 'integer'],
                'range'                      => $this->object(
                    [
                        'from'        => ['type' => 'string'],
                        'to'          => ['type' => 'string'],
                        'views_scope' => ['type' => 'string', 'enum' => ['lifetime']],
                    ],
                    ['from', 'to', 'views_scope']
                ),
            ],
            ['form_id', 'views', 'starts', 'completions', 'unread_responses', 'completion_rate', 'average_completion_seconds', 'range']
        );
    }

    /** @return array<string,mixed> */
    public function response_summary(): array {
        return $this->object(
            [
                'id'           => ['type' => 'integer'],
                'form_id'      => ['type' => 'integer'],
                'form_title'   => ['type' => 'string'],
                'created_at'   => ['type' => 'string'],
                'is_completed' => ['type' => 'boolean'],
                'is_read'      => ['type' => 'boolean'],
                'is_starred'   => ['type' => 'boolean'],
            ],
            ['id', 'form_id', 'form_title', 'created_at', 'is_completed', 'is_read', 'is_starred']
        );
    }

    /** @return array<string,mixed> */
    public function response_answer( int $depth = 0 ): array {
        $properties = [
            'label'      => ['type' => 'string'],
            'name'       => ['type' => 'string'],
            'field_type' => ['type' => 'string'],
            'value'      => ['type' => ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null']],
        ];

        if ( 5 > $depth ) {
            $properties['children'] = [
                'type'     => 'array',
                'items'    => $this->response_answer( $depth + 1 ),
                'maxItems' => 50,
            ];
        }

        return $this->object( $properties, ['label', 'name', 'field_type', 'value'] );
    }

    /** @return array<string,mixed> */
    public function response(): array {
        return $this->object(
            [
                'id'           => ['type' => 'integer'],
                'form_id'      => ['type' => 'integer'],
                'form_title'   => ['type' => 'string'],
                'created_at'   => ['type' => 'string'],
                'completed_at' => ['type' => 'string'],
                'is_completed' => ['type' => 'boolean'],
                'is_read'      => ['type' => 'boolean'],
                'is_starred'   => ['type' => 'boolean'],
                'answers'      => [
                    'type'     => 'array',
                    'items'    => $this->response_answer(),
                    'maxItems' => 200,
                ],
            ],
            ['id', 'form_id', 'form_title', 'created_at', 'completed_at', 'is_completed', 'is_read', 'is_starred', 'answers']
        );
    }

    /** @return array<string,mixed> */
    public function pagination(): array {
        return $this->object(
            [
                'page'        => ['type' => 'integer'],
                'per_page'    => ['type' => 'integer'],
                'total_items' => ['type' => 'integer'],
                'total_pages' => ['type' => 'integer'],
            ],
            ['page', 'per_page', 'total_items', 'total_pages']
        );
    }

    /** @return array<string,mixed> */
    public function response_error(): array {
        return $this->object(
            [
                'id'      => ['type' => 'integer'],
                'code'    => ['type' => 'string'],
                'message' => ['type' => 'string'],
            ],
            ['id', 'code', 'message']
        );
    }
}
