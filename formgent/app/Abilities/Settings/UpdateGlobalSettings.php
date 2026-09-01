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
 * Atomically changes only allowlisted, non-secret global settings.
 */
class UpdateGlobalSettings extends AbstractAbility {
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
        return 'formgent/update-global-settings';
    }

    public function get_label(): string {
        return esc_html__( 'Update FormGent global settings', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Updates an allowlisted FormGent setting subset. Confirm the requested settings change before execution.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object( ['settings' => $this->schema->safe_settings()], ['settings'] );
    }

    public function get_output_schema(): array {
        return $this->schema->output(
            [
                'category' => [
                    'type' => 'string',
                    'enum' => ['general', 'validation', 'security', 'login_registration'],
                ],
                'settings' => $this->schema->safe_settings(),
                'changed'  => [
                    'type'  => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            ['category', 'settings', 'changed']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER, AccessGroup::WRITE];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return [Capabilities::EDIT_SETTINGS];
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
        $settings = $this->settings->update( $input['settings'] ?? [] );

        if ( is_wp_error( $settings ) ) {
            return $settings;
        }

        $category = array_key_first( $input['settings'] );

        return [
            'schema_version' => '1.0',
            'category'       => $category,
            'settings'       => $settings,
            'changed'        => array_keys( $input['settings'][$category] ),
        ];
    }
}
