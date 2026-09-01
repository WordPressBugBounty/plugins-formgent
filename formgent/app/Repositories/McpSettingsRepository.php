<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use InvalidArgumentException;
use RuntimeException;

/**
 * Stores MCP access controls separately from FormGent's product settings.
 */
class McpSettingsRepository {
    public const OPTION_KEY = 'formgent_mcp_settings';

    private const DEFAULTS = [
        'enabled'       => false,
        'response_data' => false,
        'writes'        => false,
        'deletes'       => false,
        'server'        => false,
    ];

    /** @var array<string,bool>|null */
    private ?array $settings = null;

    /**
     * Return the normalized settings for the current site.
     *
     * @return array<string,bool>
     */
    public function get(): array {
        if ( null !== $this->settings ) {
            return $this->settings;
        }

        $saved = get_option( self::OPTION_KEY, [] );
        $saved = is_array( $saved ) ? $saved : [];

        $this->settings = self::normalize( array_merge( self::DEFAULTS, $saved ) );

        return $this->settings;
    }

    /**
     * Merge and persist a validated partial settings update.
     *
     * @param array<string,mixed> $changes Settings supplied by an administrator.
     * @return array<string,bool>
     */
    public function update( array $changes ): array {
        self::assert_valid_patch( $changes );

        $settings = array_merge( $this->get(), $changes );
        $settings = self::normalize( $settings );

        // Passing false keeps this security-sensitive option out of autoloaded options.
        $updated = update_option( self::OPTION_KEY, $settings, false );

        if ( ! $updated && self::normalize( (array) get_option( self::OPTION_KEY, [] ) ) !== $settings ) {
            throw new RuntimeException( 'MCP settings could not be persisted.' );
        }

        $this->settings = $settings;

        return $settings;
    }

    public function enabled( string $key ): bool {
        $settings = $this->get();

        return ! empty( $settings['enabled'] ) && ! empty( $settings[$key] );
    }

    public function master_enabled(): bool {
        $settings = $this->get();

        return $settings['enabled'];
    }

    /**
     * Normalize values read from older/manual option writes.
     *
     * @param array<string,mixed> $settings Raw settings.
     * @return array<string,bool>
     */
    public static function normalize( array $settings ): array {
        $normalized = self::DEFAULTS;

        foreach ( self::DEFAULTS as $key => $default ) {
            if ( array_key_exists( $key, $settings ) ) {
                $normalized[$key] = true === $settings[$key] || 1 === $settings[$key] || '1' === $settings[$key];
            }
        }

        return $normalized;
    }

    /**
     * @param array<string,mixed> $changes Settings patch.
     */
    private static function assert_valid_patch( array $changes ): void {
        if ( empty( $changes ) ) {
            throw new InvalidArgumentException( 'At least one MCP setting is required.' );
        }

        foreach ( $changes as $key => $value ) {
            if ( ! array_key_exists( $key, self::DEFAULTS ) || ! is_bool( $value ) ) {
                throw new InvalidArgumentException( 'MCP settings contain an unknown key or non-boolean value.' );
            }
        }
    }
}
