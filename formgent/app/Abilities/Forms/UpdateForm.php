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
 * Atomically validates and updates a FormGent form's desired state.
 */
class UpdateForm extends AbstractAbility {
    private FormCommandService $commands;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, FormCommandService $commands, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->commands = $commands;
        $this->schema   = $schema;
    }

    public function get_id(): string {
        return 'formgent/update-form';
    }

    public function get_label(): string {
        return esc_html__( 'Update a FormGent form', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Updates a form after full validation. Supplying fields or layout replaces the complete structure; use layout to preserve screens, buttons, and nested blocks.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'form_id'  => ['type' => 'integer', 'minimum' => 1],
                'title'    => ['type' => 'string', 'minLength' => 5, 'maxLength' => 255],
                'type'     => ['type' => 'string', 'enum' => ['general', 'conversational']],
                'fields'   => $this->schema->input_fields(),
                'layout'   => $this->schema->input_layout(),
                'settings' => $this->schema->safe_form_settings(),
                'status'   => ['type' => 'string', 'enum' => ['draft', 'publish']],
            ],
            ['form_id']
        );
    }

    public function get_output_schema(): array {
        return $this->schema->output(
            [
                'form'     => $this->schema->form(),
                'changed'  => ['type' => 'array', 'items' => ['type' => 'string']],
                'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            ['form', 'changed', 'warnings']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER, AccessGroup::WRITE];
    }

    public function get_required_capabilities( array $input = [] ): array {
        $properties   = array_intersect( array_keys( $input ), ['title', 'type', 'fields', 'layout', 'settings'] );
        $capabilities = [];

        if ( ! empty( $properties ) || ! isset( $input['status'] ) ) {
            $capabilities[] = 'formgent_edit_forms';
        }

        if ( isset( $input['status'] ) ) {
            $capabilities[] = 'formgent_publish_forms';
        }

        if ( isset( $input['settings']['custom_code'] ) ) {
            $capabilities[] = 'unfiltered_html';
        }

        return array_values( array_unique( $capabilities ) );
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
        $updated = $this->commands->update( $input );

        if ( is_wp_error( $updated ) ) {
            return $updated;
        }

        return array_merge( ['schema_version' => '1.0'], $updated );
    }
}
