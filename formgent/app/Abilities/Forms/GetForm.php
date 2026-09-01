<?php

namespace FormGent\App\Abilities\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Forms\FormReadService;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;

/**
 * Gets one normalized FormGent form.
 */
class GetForm extends AbstractAbility {
    private FormReadService $forms;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, FormReadService $forms, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->forms  = $forms;
        $this->schema = $schema;
    }

    public function get_id(): string {
        return 'formgent/get-form';
    }

    public function get_label(): string {
        return esc_html__( 'Get a FormGent form', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns normalized fields plus the complete safe Gutenberg layout. Use layout when an exact structural round trip is required.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object( ['form_id' => ['type' => 'integer', 'minimum' => 1]], ['form_id'] );
    }

    public function get_output_schema(): array {
        return $this->schema->output( ['form' => $this->schema->form()], ['form'] );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return ['formgent_read_forms'];
    }

    public function execute( array $input ) {
        $form = $this->forms->get( absint( $input['form_id'] ?? 0 ) );

        if ( is_wp_error( $form ) ) {
            return $form;
        }

        return [
            'schema_version' => '1.0',
            'form'           => $form,
        ];
    }
}
