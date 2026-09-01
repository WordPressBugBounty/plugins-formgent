<?php

namespace FormGent\App\Abilities;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\AbilityAccessService;
use FormGent\App\Services\Mcp\AbilityAuditService;
use FormGent\App\Services\Mcp\AbilityRateLimiter;
use FormGent\App\Services\Mcp\McpErrorFactory;
use Throwable;
use WP_Error;

/**
 * Applies the common security and execution pipeline to every FormGent ability.
 */
abstract class AbstractAbility {
    protected AbilityAccessService $access;

    private AbilityRateLimiter $rate_limiter;

    private AbilityAuditService $audit;

    public function __construct( AbilityAccessService $access, AbilityRateLimiter $rate_limiter, AbilityAuditService $audit ) {
        $this->access       = $access;
        $this->rate_limiter = $rate_limiter;
        $this->audit        = $audit;
    }

    abstract public function get_id(): string;

    abstract public function get_label(): string;

    abstract public function get_description(): string;

    /** @return array<string,mixed> */
    abstract public function get_input_schema(): array;

    /** @return array<string,mixed> */
    abstract public function get_output_schema(): array;

    /** @return array<int,string> */
    abstract public function get_access_groups(): array;

    /** @param array<string,mixed> $input @return array<int,string> */
    abstract public function get_required_capabilities( array $input = [] ): array;

    /** @param array<string,mixed> $input @return array<string,mixed>|WP_Error */
    abstract public function execute( array $input );

    public function get_annotations(): array {
        return [
            'readonly'      => true,
            'destructive'   => false,
            'idempotent'    => true,
            'openWorldHint' => false,
        ];
    }

    public function get_rate_class(): string {
        return 'read';
    }

    public function is_discoverable(): bool {
        return true === $this->access->check_groups( $this->get_access_groups() );
    }

    /**
     * @param mixed $input Ability input supplied by WordPress.
     * @return true|WP_Error
     */
    public function permission_callback( $input = [] ) {
        $input = is_array( $input ) ? $input : [];

        return $this->access->authorize( $this->get_access_groups(), $this->get_required_capabilities( $input ) );
    }

    /**
     * Execute with gates rechecked at call time, including direct invocations.
     *
     * @param mixed $input Validated input supplied by WordPress.
     * @return array<string,mixed>|WP_Error
     */
    public function execute_wrapper( $input = [] ) {
        $input = is_array( $input ) ? $input : [];

        if ( ! function_exists( 'wp_register_ability' ) || ! function_exists( 'rest_validate_value_from_schema' ) || ! function_exists( 'rest_sanitize_value_from_schema' ) ) {
            return McpErrorFactory::dependency_missing( esc_html__( 'The WordPress Abilities API is unavailable.', 'formgent' ) );
        }

        $validation = rest_validate_value_from_schema( $input, $this->get_input_schema(), 'input' );

        if ( is_wp_error( $validation ) ) {
            return McpErrorFactory::invalid_input(
                esc_html__( 'The ability input does not match the declared schema.', 'formgent' ),
                $this->validation_details( $validation )
            );
        }

        $input = rest_sanitize_value_from_schema( $input, $this->get_input_schema(), 'input' );

        if ( is_wp_error( $input ) || ! is_array( $input ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'The ability input could not be sanitized.', 'formgent' ) );
        }

        $permission = $this->permission_callback( $input );

        if ( is_wp_error( $permission ) ) {
            return $permission;
        }

        try {
            $rate = $this->rate_limiter->consume( $this->get_id(), $this->get_rate_class() );
        } catch ( Throwable $throwable ) {
            $this->report_exception( $throwable );

            return McpErrorFactory::internal();
        }

        if ( is_wp_error( $rate ) ) {
            return $rate;
        }

        $annotations = $this->get_annotations();
        $context     = $this->audit->start( $this->get_id(), $input, ! empty( $annotations['destructive'] ) );

        try {
            $result = $this->execute( $input );
        } catch ( Throwable $throwable ) {
            $this->report_exception( $throwable, $context );
            $result = McpErrorFactory::internal();
        }

        if ( ! is_array( $result ) && ! is_wp_error( $result ) ) {
            $result = McpErrorFactory::internal();
        }

        if ( is_array( $result ) ) {
            $validation = rest_validate_value_from_schema( $result, $this->get_output_schema(), 'output' );

            if ( is_wp_error( $validation ) ) {
                try {
                    do_action( 'formgent_mcp_invalid_ability_output', $this->get_id(), $validation->get_error_code(), $context );
                } catch ( Throwable $throwable ) {
                    $this->report_exception( $throwable, $context );
                }

                $result = McpErrorFactory::internal();
            }
        }

        $this->audit->finish( $context, $result );

        return $result;
    }

    /** @return array<int,array<string,string>> */
    private function validation_details( WP_Error $error ): array {
        $details = [];

        foreach ( array_slice( $error->get_error_codes(), 0, 20 ) as $code ) {
            $data = $error->get_error_data( $code );
            $path = is_array( $data ) && isset( $data['param'] ) ? sanitize_text_field( (string) $data['param'] ) : 'input';

            $details[] = [
                'path'    => substr( $path, 0, 255 ),
                'code'    => sanitize_key( (string) $code ),
                'message' => esc_html__( 'The value is invalid for this field.', 'formgent' ),
            ];
        }

        return $details;
    }

    /** @param array<string,mixed> $context Privacy-safe audit context. */
    private function report_exception( Throwable $throwable, array $context = [] ): void {
        try {
            do_action( 'formgent_mcp_internal_exception', $this->get_id(), $throwable, $context );
        } catch ( Throwable $observer_error ) {
            // Error observers must not leak their own exception through the ability API.
        }
    }

    public function register(): bool {
        if ( ! function_exists( 'wp_register_ability' ) ) {
            return false;
        }

        $input_schema  = $this->get_input_schema();
        $output_schema = $this->get_output_schema();

        if ( ! $this->closed_schema( $input_schema ) || ! $this->closed_schema( $output_schema ) ) {
            return false;
        }

        $ability = wp_register_ability(
            $this->get_id(),
            [
                'label'               => $this->get_label(),
                'description'         => $this->get_description(),
                'category'            => 'formgent',
                'input_schema'        => $input_schema,
                'output_schema'       => $output_schema,
                'permission_callback' => [$this, 'permission_callback'],
                'execute_callback'    => [$this, 'execute_wrapper'],
                'ability_class'       => FormGentAbility::class,
                'meta'                => [
                    'show_in_rest' => true,
                    'annotations'  => $this->get_annotations(),
                    'mcp'          => [
                        'public' => false,
                    ],
                ],
            ]
        );

        return null !== $ability;
    }

    /** @param array<string,mixed> $schema JSON Schema. */
    private function closed_schema( array $schema ): bool {
        return 'object' === ( $schema['type'] ?? '' ) && false === ( $schema['additionalProperties'] ?? true );
    }
}
