<?php

namespace FormGent\App\Abilities\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Abilities\AbstractAbility;
use FormGent\App\Abilities\AccessGroup;
use FormGent\App\Services\Forms\FormReadService;
use FormGent\App\Services\Forms\FormSchemaService;
use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;

/**
 * Returns FormGent shortcode and block embed snippets.
 */
class GetEmbedCode extends AbstractAbility {
    private FormReadService $forms;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, FormReadService $forms, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->forms  = $forms;
        $this->schema = $schema;
    }

    public function get_id(): string {
        return 'formgent/get-embed-code';
    }

    public function get_label(): string {
        return esc_html__( 'Get FormGent embed code', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns bounded FormGent shortcode and Gutenberg embed markup after validating the form.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object( ['form_id' => ['type' => 'integer', 'minimum' => 1]], ['form_id'] );
    }

    public function get_output_schema(): array {
        return $this->schema->output(
            [
                'form_id'    => ['type' => 'integer'],
                'shortcode'  => ['type' => 'string'],
                'block'      => ['type' => 'string'],
                'public_url' => ['type' => 'string'],
            ],
            ['form_id', 'shortcode', 'block', 'public_url']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return ['formgent_read_forms'];
    }

    public function execute( array $input ) {
        $form_id = absint( $input['form_id'] ?? 0 );
        $embed   = $this->forms->get_embed( $form_id );

        if ( is_wp_error( $embed ) ) {
            return $embed;
        }

        return array_merge(
            [
                'schema_version' => '1.0',
                'form_id'        => $form_id,
            ],
            $embed
        );
    }
}
