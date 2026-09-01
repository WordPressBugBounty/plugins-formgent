<?php

namespace FormGent\App\Abilities\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Forms\FormAutomationSchemaService;
use FormGent\App\Services\Forms\FormAutomationService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;
use FormGent\App\Utils\Capabilities;

/**
 * Replaces selected non-secret automation collections for one form.
 */
class UpdateFormAutomations extends AbstractAbility {
    private FormAutomationService $automations;

    private FormAutomationSchemaService $schema;

    public function __construct(
        AbilityAccessService $access,
        AbilityRateLimiter $rate_limiter,
        AbilityAuditService $audit,
        FormAutomationService $automations,
        FormAutomationSchemaService $schema
    ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->automations = $automations;
        $this->schema      = $schema;
    }

    public function get_id(): string {
        return 'formgent/update-form-automations';
    }

    public function get_label(): string {
        return esc_html__( 'Update FormGent form automations', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Replaces each supplied automation collection after validation. Omitted collections remain unchanged; credentials, passwords, payment settings, webhooks, and executable scripts are excluded.', 'formgent' );
    }

    public function get_input_schema(): array {
        return [
            'type'                 => 'object',
            'properties'           => [
                'form_id'     => ['type' => 'integer', 'minimum' => 1],
                'automations' => $this->schema->input(),
            ],
            'additionalProperties' => false,
            'required'             => ['form_id', 'automations'],
        ];
    }

    public function get_output_schema(): array {
        return [
            'type'                 => 'object',
            'properties'           => [
                'schema_version' => ['type' => 'string'],
                'form_id'        => ['type' => 'integer'],
                'changed'        => ['type' => 'array', 'items' => ['type' => 'string']],
                'automations'    => $this->schema->output(),
            ],
            'additionalProperties' => false,
            'required'             => ['schema_version', 'form_id', 'changed', 'automations'],
        ];
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER, AccessGroup::WRITE];
    }

    public function get_required_capabilities( array $input = [] ): array {
        $capabilities = [Capabilities::EDIT_FORMS, Capabilities::EDIT_AUTOMATIONS];
        $automations  = is_array( $input['automations'] ?? null ) ? $input['automations'] : [];

        if ( isset( $automations['user_registrations'] ) ) {
            $capabilities[] = Capabilities::MANAGE_REGISTRATION_AUTOMATIONS;
        }

        /**
         * Lets extensions require additional capabilities for their collections.
         * Extensions may only add requirements; the access service still checks
         * every returned capability at execution time.
         *
         * @param array<int,string>   $capabilities Required capabilities.
         * @param array<string,mixed> $automations  Requested collections.
         */
        $base_capabilities = $capabilities;
        $filtered          = apply_filters( 'formgent_mcp_form_automation_capabilities', $capabilities, $automations );
        $capabilities      = is_array( $filtered ) ? array_merge( $base_capabilities, $filtered ) : $base_capabilities;

        return array_values( array_unique( array_filter( array_map( 'sanitize_key', $capabilities ) ) ) );
    }

    public function get_annotations(): array {
        return [
            'readonly'      => false,
            'destructive'   => true,
            'idempotent'    => true,
            'openWorldHint' => false,
        ];
    }

    public function get_rate_class(): string {
        return 'write';
    }

    public function execute( array $input ) {
        $form_id     = absint( $input['form_id'] ?? 0 );
        $patch       = is_array( $input['automations'] ?? null ) ? $input['automations'] : [];
        $automations = $this->automations->replace( $form_id, $patch );

        if ( is_wp_error( $automations ) ) {
            return $automations;
        }

        return [
            'schema_version' => '1.0',
            'form_id'        => $form_id,
            'changed'        => array_keys( $patch ),
            'automations'    => $automations,
        ];
    }
}
