<?php

namespace FormGent\App\Abilities\Responses;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;
use FormGent\App\Services\Responses\ResponseCommandService;
use FormGent\App\Utils\Capabilities;

/**
 * Applies a read or starred state to up to 50 responses.
 */
class UpdateResponseState extends AbstractAbility {
    private ResponseCommandService $commands;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, ResponseCommandService $commands, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->commands = $commands;
        $this->schema   = $schema;
    }

    public function get_id(): string {
        return 'formgent/update-response-state';
    }

    public function get_label(): string {
        return esc_html__( 'Update FormGent response state', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Marks responses read or unread, or stars or unstars them. Confirm the requested state before execution.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'response_ids' => [
                    'type'        => 'array',
                    'items'       => ['type' => 'integer', 'minimum' => 1],
                    'minItems'    => 1,
                    'maxItems'    => 50,
                    'uniqueItems' => true,
                ],
                'operation'    => ['type' => 'string', 'enum' => ['mark_read', 'mark_unread', 'star', 'unstar']],
            ],
            ['response_ids', 'operation']
        );
    }

    public function get_output_schema(): array {
        $result = $this->schema->object(
            [
                'id'    => ['type' => 'integer'],
                'state' => ['type' => 'string', 'enum' => ['read', 'unread', 'starred', 'unstarred']],
            ],
            ['id', 'state']
        );

        return $this->schema->output(
            [
                'requested' => ['type' => 'integer'],
                'succeeded' => ['type' => 'integer'],
                'failed'    => ['type' => 'integer'],
                'results'   => ['type' => 'array', 'items' => $result, 'maxItems' => 50],
                'errors'    => ['type' => 'array', 'items' => $this->schema->response_error(), 'maxItems' => 50],
            ],
            ['requested', 'succeeded', 'failed', 'results', 'errors']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER, AccessGroup::RESPONSE_DATA, AccessGroup::WRITE];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return [Capabilities::EDIT_RESPONSES];
    }

    public function get_annotations(): array {
        return [
            'readonly'      => false,
            'destructive'   => false,
            'idempotent'    => true,
            'openWorldHint' => false,
        ];
    }

    public function get_rate_class(): string {
        return 'write';
    }

    public function execute( array $input ) {
        $result = $this->commands->update_state( $input['response_ids'] ?? [], $input['operation'] ?? '' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return array_merge( ['schema_version' => '1.0'], $result );
    }
}
