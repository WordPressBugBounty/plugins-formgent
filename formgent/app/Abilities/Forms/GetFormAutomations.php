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
 * Reads all non-secret automation resources associated with one form.
 */
class GetFormAutomations extends AbstractAbility {
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
        return 'formgent/get-form-automations';
    }

    public function get_label(): string {
        return esc_html__( 'Get FormGent form automations', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns non-secret email, PDF, registration, and licensed automation configuration for a form. Credentials and PDF passwords are never returned.', 'formgent' );
    }

    public function get_input_schema(): array {
        return [
            'type'                 => 'object',
            'properties'           => ['form_id' => ['type' => 'integer', 'minimum' => 1]],
            'additionalProperties' => false,
            'required'             => ['form_id'],
        ];
    }

    public function get_output_schema(): array {
        return [
            'type'                 => 'object',
            'properties'           => [
                'schema_version' => ['type' => 'string'],
                'form_id'        => ['type' => 'integer'],
                'automations'    => $this->schema->output(),
            ],
            'additionalProperties' => false,
            'required'             => ['schema_version', 'form_id', 'automations'],
        ];
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return [Capabilities::READ_AUTOMATIONS];
    }

    public function execute( array $input ) {
        $form_id     = absint( $input['form_id'] ?? 0 );
        $automations = $this->automations->get( $form_id );

        if ( is_wp_error( $automations ) ) {
            return $automations;
        }

        return [
            'schema_version' => '1.0',
            'form_id'        => $form_id,
            'automations'    => $automations,
        ];
    }
}
