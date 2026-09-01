<?php

namespace FormGent\App\Abilities\Settings;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;
use FormGent\App\Services\Settings\SafeGlobalSettingsService;
use FormGent\App\Utils\Capabilities;

/**
 * Returns only the allowlisted, non-secret FormGent settings.
 */
class GetGlobalSettings extends AbstractAbility {
    private SafeGlobalSettingsService $settings;

    private FormSchemaService $schema;

    public function __construct(
        AbilityAccessService $access,
        AbilityRateLimiter $rate_limiter,
        AbilityAuditService $audit,
        SafeGlobalSettingsService $settings,
        FormSchemaService $schema
    ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->settings = $settings;
        $this->schema   = $schema;
    }

    public function get_id(): string {
        return 'formgent/get-global-settings';
    }

    public function get_label(): string {
        return esc_html__( 'Get FormGent global settings', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns a bounded, permission-filtered subset of non-secret FormGent settings.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'categories' => [
                    'type'        => 'array',
                    'items'       => [
                        'type' => 'string',
                        'enum' => ['general', 'validation', 'security', 'login_registration'],
                    ],
                    'uniqueItems' => true,
                    'minItems'    => 1,
                    'maxItems'    => 4,
                ],
            ]
        );
    }

    public function get_output_schema(): array {
        return $this->schema->output( ['settings' => $this->schema->safe_settings()], ['settings'] );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return [Capabilities::READ_SETTINGS];
    }

    public function execute( array $input ) {
        $settings = $this->settings->get( $input['categories'] ?? [] );

        if ( is_wp_error( $settings ) ) {
            return $settings;
        }

        return [
            'schema_version' => '1.0',
            'settings'       => $settings,
        ];
    }
}
