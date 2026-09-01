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
 * Permanently deletes up to 50 explicitly confirmed responses.
 */
class DeleteResponse extends AbstractAbility {
    private ResponseCommandService $commands;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, ResponseCommandService $commands, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->commands = $commands;
        $this->schema   = $schema;
    }

    public function get_id(): string {
        return 'formgent/delete-response';
    }

    public function get_label(): string {
        return esc_html__( 'Delete FormGent responses', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Permanently deletes responses. This is irreversible and requires confirm=true.', 'formgent' );
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
                'confirm'      => ['type' => 'boolean'],
            ],
            ['response_ids', 'confirm']
        );
    }

    public function get_output_schema(): array {
        $result = $this->schema->object(
            [
                'id'      => ['type' => 'integer'],
                'deleted' => ['type' => 'boolean'],
            ],
            ['id', 'deleted']
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
        return [AccessGroup::MASTER, AccessGroup::RESPONSE_DATA, AccessGroup::DELETE];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return [Capabilities::DELETE_RESPONSES];
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
        $result = $this->commands->delete( $input['response_ids'] ?? [], true === ( $input['confirm'] ?? false ) );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return array_merge( ['schema_version' => '1.0'], $result );
    }
}
