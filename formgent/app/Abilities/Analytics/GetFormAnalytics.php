<?php

namespace FormGent\App\Abilities\Analytics;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Analytics\McpAnalyticsService;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;

/**
 * Returns bounded analytics for an inclusive date range.
 */
class GetFormAnalytics extends AbstractAbility {
    private McpAnalyticsService $analytics;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, McpAnalyticsService $analytics, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->analytics = $analytics;
        $this->schema    = $schema;
    }

    public function get_id(): string {
        return 'formgent/get-form-analytics';
    }

    public function get_label(): string {
        return esc_html__( 'Get FormGent analytics', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns bounded, permission-filtered aggregates. Response rows and answers are never returned.', 'formgent' );
    }

    public function get_input_schema(): array {
        $date = ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-\\d{2}$'];

        return $this->schema->object(
            [
                'form_id'   => ['type' => 'integer', 'minimum' => 1],
                'date_from' => $date,
                'date_to'   => $date,
            ]
        );
    }

    public function get_output_schema(): array {
        return $this->schema->output( ['analytics' => $this->schema->analytics()], ['analytics'] );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return ['formgent_read_forms'];
    }

    public function execute( array $input ) {
        $analytics = $this->analytics->get( $input );

        if ( is_wp_error( $analytics ) ) {
            return $analytics;
        }

        return [
            'schema_version' => '1.0',
            'analytics'      => $analytics,
        ];
    }
}
