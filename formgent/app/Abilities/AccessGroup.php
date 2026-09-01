<?php

namespace FormGent\App\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * MCP access groups backed by the dedicated settings option.
 */
final class AccessGroup {
    public const MASTER        = 'master';
    public const RESPONSE_DATA = 'response_data';
    public const WRITE         = 'write';
    public const DELETE        = 'delete';

    private const SETTING_KEYS = [
        self::RESPONSE_DATA => 'response_data',
        self::WRITE         => 'writes',
        self::DELETE        => 'deletes',
    ];

    public static function is_valid( string $group ): bool {
        return self::MASTER === $group || isset( self::SETTING_KEYS[$group] );
    }

    public static function setting_key( string $group ): ?string {
        return self::SETTING_KEYS[$group] ?? null;
    }
}
