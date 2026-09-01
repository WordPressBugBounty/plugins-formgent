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
 * Lists bounded FormGent form summaries.
 */
class ListForms extends AbstractAbility {
    private FormReadService $forms;

    private FormSchemaService $schema;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit, FormReadService $forms, FormSchemaService $schema ) {
        parent::__construct( $access, $rate_limiter, $audit );
        $this->forms  = $forms;
        $this->schema = $schema;
    }

    public function get_id(): string {
        return 'formgent/list-forms';
    }

    public function get_label(): string {
        return esc_html__( 'List FormGent forms', 'formgent' );
    }

    public function get_description(): string {
        return esc_html__( 'Returns a bounded, permission-filtered list of form summaries without form content.', 'formgent' );
    }

    public function get_input_schema(): array {
        return $this->schema->object(
            [
                'status'     => ['type' => 'string', 'enum' => ['all', 'publish', 'draft']],
                'search'     => ['type' => 'string', 'maxLength' => 255],
                'type'       => ['type' => 'string', 'enum' => ['all', 'general', 'conversational']],
                'page'       => ['type' => 'integer', 'minimum' => 1],
                'per_page'   => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                'sort_by'    => ['type' => 'string', 'enum' => ['last_modified', 'date_created', 'alphabetical', 'last_submission', 'unread', 'draft', 'publish']],
                'date_type'  => ['type' => 'string', 'enum' => ['all', 'today', 'yesterday', 'last_week', 'last_month', 'date_frame']],
                'date_frame' => $this->schema->object(
                    [
                        'from' => ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-\\d{2}$'],
                        'to'   => ['type' => 'string', 'pattern' => '^\\d{4}-\\d{2}-\\d{2}$'],
                    ],
                    ['from', 'to']
                ),
            ]
        );
    }

    public function get_output_schema(): array {
        $summary    = $this->schema->object(
            [
                'id'                     => ['type' => 'integer'],
                'title'                  => ['type' => 'string'],
                'status'                 => ['type' => 'string'],
                'type'                   => ['type' => 'string'],
                'created_at'             => ['type' => 'string'],
                'updated_at'             => ['type' => 'string'],
                'total_responses'        => ['type' => 'integer'],
                'total_unread_responses' => ['type' => 'integer'],
            ],
            ['id', 'title', 'status', 'type', 'created_at', 'updated_at', 'total_responses', 'total_unread_responses']
        );
        $pagination = $this->schema->object(
            [
                'page'        => ['type' => 'integer'],
                'per_page'    => ['type' => 'integer'],
                'total_items' => ['type' => 'integer'],
                'total_pages' => ['type' => 'integer'],
            ],
            ['page', 'per_page', 'total_items', 'total_pages']
        );

        return $this->schema->output(
            [
                'forms'      => ['type' => 'array', 'items' => $summary],
                'pagination' => $pagination,
            ],
            ['forms', 'pagination']
        );
    }

    public function get_access_groups(): array {
        return [AccessGroup::MASTER];
    }

    public function get_required_capabilities( array $input = [] ): array {
        return ['formgent_read_forms'];
    }

    public function execute( array $input ) {
        $forms = $this->forms->list( $input );

        if ( is_wp_error( $forms ) ) {
            return $forms;
        }

        return array_merge( ['schema_version' => '1.0'], $forms );
    }
}
