<?php

namespace FormGent\App\Abilities;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Services\Mcp\McpErrorFactory;
use WP_Ability;
use WP_Error;

/**
 * Keeps WordPress Abilities API schema failures inside FormGent's stable
 * public MCP error contract.
 *
 * WordPress validates an ability before its execute callback runs. Without
 * this adapter, those early failures bypass AbstractAbility::execute_wrapper()
 * and surface as generic `ability_*` errors without HTTP status metadata.
 */
class FormGentAbility extends WP_Ability {
    /** @var bool|WP_Error|null Last permission result from the core execution pipeline. */
    private $permission_result;

    /**
     * @param mixed $input Raw ability input.
     * @return true|WP_Error
     */
    public function validate_input( $input = null ) {
        $validation = parent::validate_input( $input );

        if ( ! is_wp_error( $validation ) ) {
            return true;
        }

        if ( 0 === strpos( (string) $validation->get_error_code(), 'formgent_mcp_' ) ) {
            return $validation;
        }

        return McpErrorFactory::invalid_input(
            esc_html__( 'The ability input does not match the declared schema.', 'formgent' )
        );
    }

    /**
     * Retain the denial so execute() can restore FormGent's public error after
     * WordPress replaces it with `ability_invalid_permissions`.
     *
     * @param mixed $input Validated ability input.
     * @return bool|WP_Error
     */
    public function check_permissions( $input = null ) {
        $this->permission_result = parent::check_permissions( $input );

        return $this->permission_result;
    }

    /**
     * @param mixed $input Raw ability input.
     * @return mixed|WP_Error
     */
    public function execute( $input = null ) {
        $this->permission_result = null;
        $result                  = parent::execute( $input );

        if ( ! is_wp_error( $result ) || 'ability_invalid_permissions' !== $result->get_error_code() ) {
            return $result;
        }

        if ( is_wp_error( $this->permission_result ) && 0 === strpos( (string) $this->permission_result->get_error_code(), 'formgent_mcp_' ) ) {
            return $this->permission_result;
        }

        return McpErrorFactory::forbidden();
    }
}
