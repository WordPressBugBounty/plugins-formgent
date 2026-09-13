<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Support\Routing\RewriteBase;

/**
 * FormGent adapter for the reusable rewrite-base contract.
 */
final class FormPermalinkService {
    public const SETTING_KEY = 'form_permalink_base';

    public const DEFAULT_BASE = 'form';

    private const REWRITE_SCHEMA_VERSION = '3';

    private const REWRITE_VERSION_OPTION = 'formgent_form_rewrite_rules_version';

    /**
     * Return extra bases that integrations have marked as unavailable.
     *
     * @return array<int,string>
     */
    public static function reserved_bases(): array {
        $reserved = apply_filters( 'formgent_reserved_form_permalink_bases', [] );

        return is_array( $reserved ) ? $reserved : [];
    }

    /**
     * @param mixed $value Candidate setting value.
     * @return string|\WP_Error
     */
    public static function validate( $value ) {
        return RewriteBase::validate(
            $value,
            self::reserved_bases(),
            self::validation_messages()
        );
    }

    /**
     * Resolve a base from a settings payload.
     *
     * @param array<string,mixed> $settings Global settings.
     */
    public static function from_settings( array $settings ): string {
        return RewriteBase::normalize(
            $settings[self::SETTING_KEY] ?? self::DEFAULT_BASE,
            self::DEFAULT_BASE,
            self::reserved_bases()
        );
    }

    public static function current_base(): string {
        $base = RewriteBase::normalize(
            formgent_get_setting( self::SETTING_KEY, self::DEFAULT_BASE ),
            self::DEFAULT_BASE,
            self::reserved_bases()
        );

        return RewriteBase::normalize(
            apply_filters( 'formgent_form_permalink_base', $base ),
            self::DEFAULT_BASE,
            self::reserved_bases()
        );
    }

    /**
     * Force the next fully initialized request to rebuild rewrite rules.
     */
    public static function mark_rewrite_rules_stale(): void {
        delete_option( self::REWRITE_VERSION_OPTION );
    }

    /**
     * Refresh once after an upgrade or permalink-base change.
     */
    public static function maybe_refresh_rewrite_rules(): void {
        $signature = self::REWRITE_SCHEMA_VERSION . ':' . self::current_base();

        if ( $signature === get_option( self::REWRITE_VERSION_OPTION ) ) {
            return;
        }

        flush_rewrite_rules( false );
        update_option( self::REWRITE_VERSION_OPTION, $signature, false );
    }

    /**
     * Keep product translation outside the portable rewrite-base utility.
     *
     * @return array<string,string>
     */
    private static function validation_messages(): array {
        return [
            'empty_rewrite_base'    => esc_html__( 'The form URL base cannot be empty.', 'formgent' ),
            'invalid_rewrite_base'  => esc_html__( 'Enter a form URL base containing letters or numbers.', 'formgent' ),
            'nested_rewrite_base'   => esc_html__( 'Use one form URL segment without additional slashes.', 'formgent' ),
            'reserved_rewrite_base' => esc_html__(
                'That form URL base is reserved by WordPress. Choose another value.',
                'formgent'
            ),
            'rewrite_base_too_long' => esc_html__( 'The form URL base must be 64 characters or fewer.', 'formgent' ),
        ];
    }
}
