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
 * Gets one mandatory-redacted response.
 */
class GetResponse extends AbstractAbility {
    private ResponseReadService $responses;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, ResponseReadService $responses, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->responses = $responses;
        $this->schema    = $schema;
    }

    public function get_id(): string {
        return 'formgent/get-response';
    }

    public function get_label(): string {
        return esc_html__( 'Get a FormGent response', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns one bounded, permission-filtered response with sensitive answer types redacted.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object( ['response_id' => ['type' => 'integer', 'minimum' => 1]], ['response_id'] );
    }

    public function get_output_schema(): array {
        return $this->schema->output( ['response' => $this->schema->response()], ['response'] );
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
        $response = $this->responses->get( absint( $input['response_id'] ?? 0 ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return [
            'schema_version' => '1.0',
            'response'       => $response,
        ];
    }
}
