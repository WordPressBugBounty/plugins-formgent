<?php

namespace FormGent\App\Abilities\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Forms\FormCommandService;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;

/**
 * Trashes a form by default and permanently deletes only when requested.
 */
class DeleteForm extends AbstractAbility {
    private FormCommandService $commands;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, FormCommandService $commands, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->commands = $commands;
        $this->schema   = $schema;
    }

    public function get_id(): string {
        return 'formgent/delete-form';
    }

    public function get_label(): string {
        return esc_html__( 'Delete a FormGent form', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Deletes a form. State whether the operation uses trash or irreversible permanent deletion before execution.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'form_id' => ['type' => 'integer', 'minimum' => 1],
                'force'   => ['type' => 'boolean', 'default' => false],
            ],
            ['form_id']
        );
    }

    public function get_output_schema(): array {
        return $this->schema->output(
            [
                'id'              => ['type' => 'integer'],
                'previous_status' => ['type' => 'string'],
                'mode'            => ['type' => 'string', 'enum' => ['trash', 'permanent']],
            ],
            ['id', 'previous_status', 'mode']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER, AccessGroup::DELETE];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return ['formgent_delete_forms'];
    }

    public function get_annotations(): array {
        return [
            'readonly'      => false,
            'destructive'   => true,
            'idempotent'    => false,
            'openWorldHint' => false,
        ];
    }

    public function get_rate_class(): string {
        return 'destructive';
    }

    public function execute( array $input ) {
        $deleted = $this->commands->delete( absint( $input['form_id'] ?? 0 ), ! empty( $input['force'] ) );

        if ( is_wp_error( $deleted ) ) {
            return $deleted;
        }

        return array_merge( ['schema_version' => '1.0'], $deleted );
    }
}
