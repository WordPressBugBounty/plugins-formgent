<?php

namespace FormGent\App\Abilities\Responses;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;
use FormGent\App\Services\Responses\ResponseReadService;
use FormGent\App\Utils\Capabilities;

/**
 * Gets up to 50 redacted responses in requested order.
 */
class BulkGetResponses extends AbstractAbility {
    private ResponseReadService $responses;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, ResponseReadService $responses, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->responses = $responses;
        $this->schema    = $schema;
    }

    public function get_id(): string {
        return 'formgent/bulk-get-responses';
    }

    public function get_label(): string {
        return esc_html__( 'Bulk get FormGent responses', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns up to 50 ordered, permission-filtered responses with mandatory sensitive-data redaction.', 'formgent' );
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
            ],
            ['response_ids']
        );
    }

    public function get_output_schema(): array {
        return $this->schema->output(
            [
                'requested' => ['type' => 'integer'],
                'succeeded' => ['type' => 'integer'],
                'failed'    => ['type' => 'integer'],
                'responses' => ['type' => 'array', 'items' => $this->schema->response(), 'maxItems' => 50],
                'errors'    => ['type' => 'array', 'items' => $this->schema->response_error(), 'maxItems' => 50],
            ],
            ['requested', 'succeeded', 'failed', 'responses', 'errors']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER, AccessGroup::RESPONSE_DATA];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return [Capabilities::READ_RESPONSES];
    }

    public function get_rate_class(): string {
        return 'response';
    }

    public function execute( array $input ) {
        $responses = $this->responses->bulk( $input['response_ids'] ?? [] );

        if ( is_wp_error( $responses ) ) {
            return $responses;
        }

        return array_merge( ['schema_version' => '1.0'], $responses );
    }
}
