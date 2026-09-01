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
 * Duplicates safe form content into a new draft.
 */
class DuplicateForm extends AbstractAbility {
    private FormCommandService $commands;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, FormCommandService $commands, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->commands = $commands;
        $this->schema   = $schema;
    }

    public function get_id(): string {
        return 'formgent/duplicate-form';
    }

    public function get_label(): string {
        return esc_html__( 'Duplicate a FormGent form', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Creates a new draft from safe form content. Confirm the source and optional title before execution.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'form_id' => ['type' => 'integer', 'minimum' => 1],
                'title'   => ['type' => 'string', 'minLength' => 5, 'maxLength' => 255],
            ],
            ['form_id']
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
        $form_id      = absint( $input['form_id'] ?? 0 );
        $settings     = $form_id ? get_post_meta( $form_id, '_formgent_settings', true ) : [];
        $custom_code  = is_array( $settings ) && is_array( $settings['customScript'] ?? null ) ? $settings['customScript'] : [];

        if ( '' !== trim( (string) ( $custom_code['css'] ?? '' ) ) || '' !== trim( (string) ( $custom_code['js'] ?? '' ) ) ) {
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
        $duplicated = $this->commands->duplicate( $input );

        if ( is_wp_error( $duplicated ) ) {
            return $duplicated;
        }

        return array_merge( ['schema_version' => '1.0'], $duplicated );
    }
}
