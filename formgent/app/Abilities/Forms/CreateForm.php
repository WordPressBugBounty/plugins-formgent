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
 * Creates a complete FormGent form from normalized fields.
 */
class CreateForm extends AbstractAbility {
    private FormCommandService $commands;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, FormCommandService $commands, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->commands = $commands;
        $this->schema   = $schema;
    }

    public function get_id(): string {
        return 'formgent/create-form';
    }

    public function get_label(): string {
        return esc_html__( 'Create a FormGent form', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Creates a complete form from either normalized fields or an exact Gutenberg layout. Provide exactly one representation.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'title'    => ['type' => 'string', 'minLength' => 5, 'maxLength' => 255],
                'type'     => ['type' => 'string', 'enum' => ['general', 'conversational']],
                'fields'   => $this->schema->input_fields(),
                'layout'   => $this->schema->input_layout(),
                'settings' => $this->schema->safe_form_settings(),
                'status'   => ['type' => 'string', 'enum' => ['draft', 'publish'], 'default' => 'draft'],
            ],
            ['title', 'type']
        );
    }

    public function get_output_schema(): array {
        return $this->schema->output(
            [
                'form'     => $this->schema->form(),
                'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            ['form', 'warnings']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER, AccessGroup::WRITE];
    }

    public function get_required_capabilities( array $input = [] ): array {
        $capabilities = ['formgent_create_forms'];

        if ( isset( $input['settings']['custom_code'] ) ) {
            $capabilities[] = 'unfiltered_html';
        }

        return $capabilities;
    }

    public function get_annotations(): array {
        return [
            'readonly'      => false,
            'destructive'   => false,
            'idempotent'    => false,
            'openWorldHint' => false,
        ];
    }

    public function get_rate_class(): string {
        return 'write';
    }

    public function execute( array $input ) {
        $created = $this->commands->create( $input );

        if ( is_wp_error( $created ) ) {
            return $created;
        }

        return array_merge( ['schema_version' => '1.0'], $created );
    }
}
