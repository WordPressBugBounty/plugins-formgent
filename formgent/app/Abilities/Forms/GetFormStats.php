<?php

namespace FormGent\App\Abilities\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Analytics\McpAnalyticsService;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;

/**
 * Returns current bounded form statistics.
 */
class GetFormStats extends AbstractAbility {
    private McpAnalyticsService $analytics;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, McpAnalyticsService $analytics, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->analytics = $analytics;
        $this->schema    = $schema;
    }

    public function get_id(): string {
        return 'formgent/get-form-stats';
    }

    public function get_label(): string {
        return esc_html__( 'Get FormGent form statistics', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns bounded, permission-filtered aggregate statistics without response records.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object( ['form_id' => ['type' => 'integer', 'minimum' => 1]] );
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
