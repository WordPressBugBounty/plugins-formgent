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
 * Lists bounded response metadata without loading answer values.
 */
class ListResponses extends AbstractAbility {
    private ResponseReadService $responses;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, ResponseReadService $responses, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->responses = $responses;
        $this->schema    = $schema;
    }

    public function get_id(): string {
        return 'formgent/list-responses';
    }

    public function get_label(): string {
        return esc_html__( 'List FormGent responses', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns bounded, permission-filtered response summaries without answer data.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'form_id'      => ['type' => 'integer', 'minimum' => 1],
                'is_read'      => ['type' => 'boolean'],
                'is_starred'   => ['type' => 'boolean'],
                'is_completed' => ['type' => 'boolean'],
                'search'       => ['type' => 'string', 'maxLength' => 255],
                'date_type'    => ['type' => 'string', 'enum' => ['all', 'today', 'yesterday', 'last_week', 'last_month', 'date_frame']],
                'date_frame'   => $this->schema->object(
                    [
                        'from' => ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-\\d{2}$'],
                        'to'   => ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-\\d{2}$'],
                    ],
                    ['from', 'to']
                ),
                'sort_by'      => ['type' => 'string', 'enum' => ['alphabetical', 'date_created', 'read', 'unread', 'complete', 'incomplete', 'starred']],
                'order'        => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'page'         => ['type' => 'integer', 'minimum' => 1],
                'per_page'     => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
            ]
        );
    }

    public function get_output_schema(): array {
        return $this->schema->output(
            [
                'responses'  => ['type' => 'array', 'items' => $this->schema->response_summary(), 'maxItems' => 100],
                'pagination' => $this->schema->pagination(),
            ],
            ['responses', 'pagination']
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
        $responses = $this->responses->list( $input );

        if ( is_wp_error( $responses ) ) {
            return $responses;
        }

        return array_merge( ['schema_version' => '1.0'], $responses );
    }
}
