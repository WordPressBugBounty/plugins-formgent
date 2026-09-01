<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

/**
 * Defines the closed, non-secret contract for form automation resources.
 */
class FormAutomationSchemaService {
    /** @return array<string,mixed> */
    public function input(): array {
        return $this->automation_object( true );
    }

    /** @return array<string,mixed> */
    public function output(): array {
        return $this->automation_object( false );
    }

    /** @return array<string,mixed> */
    private function automation_object( bool $input ): array {
        $properties = [
            'email_notifications' => [
                'type'     => 'array',
                'maxItems' => 50,
                'items'    => $this->email_notification( $input ),
            ],
            'pdf_templates'       => [
                'type'     => 'array',
                'maxItems' => 50,
                'items'    => $this->pdf_template( $input ),
            ],
            'user_registrations'  => [
                'type'     => 'array',
                'maxItems' => 20,
                'items'    => $this->user_registration( $input ),
            ],
        ];

        /**
         * Adds licensed non-secret automation collections such as CPT feeds.
         * Extensions must return closed schemas and must not expose credentials.
         *
         * @param array<string,mixed> $properties Automation properties.
         * @param bool                $input      Whether the schema accepts writes.
         */
        $properties = apply_filters( 'formgent_mcp_form_automations_schema', $properties, $input );
        $properties = is_array( $properties ) ? $properties : [];

        return [
            'type'                 => 'object',
            'properties'           => $properties,
            'additionalProperties' => false,
            'required'             => $input ? [] : array_keys( $properties ),
        ];
    }

    /** @return array<string,mixed> */
    private function email_notification( bool $input ): array {
        $properties = [
            'id'               => ['type' => 'integer', 'minimum' => 1],
            'name'             => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'send_to'          => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500],
            'subject'          => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'body'             => ['type' => 'string', 'maxLength' => 100000],
            'cc'               => ['type' => 'string', 'maxLength' => 500],
            'bcc'              => ['type' => 'string', 'maxLength' => 500],
            'reply_to'         => ['type' => 'string', 'maxLength' => 500],
            'from_name'        => ['type' => 'string', 'maxLength' => 255],
            'from_email'       => ['type' => 'string', 'maxLength' => 500],
            'status'           => ['type' => 'string', 'enum' => ['publish', 'draft']],
            'condition_status' => ['type' => 'integer', 'enum' => [0, 1]],
            'condition_type'   => ['type' => 'string', 'enum' => ['and', 'or']],
            'conditions'       => $this->conditions(),
        ];

        return [
            'type'                 => 'object',
            'properties'           => $properties,
            'additionalProperties' => false,
            'required'             => $input
                ? ['name', 'send_to', 'subject', 'body', 'status']
                : array_keys( $properties ),
        ];
    }

    /** @return array<string,mixed> */
    private function pdf_template( bool $input ): array {
        $properties = [
            'id'                 => ['type' => 'integer', 'minimum' => 1],
            'template_name'      => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
            'template_type'      => ['type' => 'string', 'maxLength' => 255],
            'content'            => ['type' => 'string', 'maxLength' => 200000],
            'paper_size'         => ['type' => 'string', 'maxLength' => 50],
            'orientation'        => ['type' => 'string', 'enum' => ['P', 'L', 'portrait', 'landscape']],
            'direction'          => ['type' => 'string', 'enum' => ['ltr', 'rtl']],
            'password_protected' => ['type' => 'boolean'],
        ];

        if ( $input ) {
            unset( $properties['password_protected'] );
        }

        return [
            'type'                 => 'object',
            'properties'           => $properties,
            'additionalProperties' => false,
            'required'             => $input
                ? ['template_name', 'content']
                : array_keys( $properties ),
        ];
    }

    /** @return array<string,mixed> */
    private function user_registration( bool $input ): array {
        return [
            'type'                 => 'object',
            'properties'           => [
                'id'                                 => ['type' => 'integer', 'minimum' => 1],
                'name'                               => ['type' => 'string', 'minLength' => 1, 'maxLength' => 255],
                'field_mapping'                      => [
                    'type'                 => 'object',
                    'additionalProperties' => ['type' => 'string', 'maxLength' => 255],
                ],
                'user_role'                          => ['type' => 'string', 'maxLength' => 100],
                'custom_role'                        => ['type' => 'string', 'maxLength' => 100],
                'custom_meta'                        => [
                    'type'     => 'array',
                    'maxItems' => 50,
                    'items'    => [
                        'type'                 => 'object',
                        'properties'           => [
                            'meta_key' => ['type' => 'string', 'maxLength' => 191],
                            'field'    => ['type' => 'string', 'maxLength' => 255],
                        ],
                        'additionalProperties' => false,
                        'required'             => ['meta_key', 'field'],
                    ],
                ],
                'auto_login'                         => ['type' => 'boolean'],
                'verification_method'                => ['type' => 'string', 'enum' => ['user_email', 'manual']],
                'verification_email_template'        => ['type' => 'string', 'maxLength' => 100000],
                'verification_confirmation_page'     => ['type' => 'integer', 'minimum' => 0],
                'hide_for_logged_in_message'         => ['type' => 'string', 'maxLength' => 100000],
                'send_registration_email'            => ['type' => 'boolean'],
                'registration_email_notification_id' => ['type' => 'integer', 'minimum' => 0],
            ],
            'additionalProperties' => false,
            'required'             => array_merge(
                $input ? [] : ['id', 'registration_email_notification_id'],
                [
                    'name',
                    'field_mapping',
                    'user_role',
                    'custom_role',
                    'custom_meta',
                    'auto_login',
                    'verification_method',
                    'verification_email_template',
                    'verification_confirmation_page',
                    'hide_for_logged_in_message',
                    'send_registration_email',
                ]
            ),
        ];
    }

    /** @return array<string,mixed> */
    private function conditions(): array {
        return [
            'type'     => 'array',
            'maxItems' => 100,
            'items'    => [
                'type'                 => 'object',
                'properties'           => [
                    'field'    => ['type' => 'string', 'maxLength' => 255],
                    'operator' => [
                        'type' => 'string',
                        'enum' => ['is', 'is_not', 'contains', 'not_contains', 'greater_than', 'less_than', 'starts_with', 'ends_with', 'is_empty', 'is_not_empty', 'regex'],
                    ],
                    'value'    => ['type' => ['string', 'number', 'integer', 'boolean', 'null']],
                ],
                'additionalProperties' => false,
                'required'             => ['field', 'operator', 'value'],
            ],
        ];
    }
}
