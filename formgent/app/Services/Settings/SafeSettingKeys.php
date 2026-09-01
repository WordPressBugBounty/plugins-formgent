<?php

namespace FormGent\App\Services\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Public allowlist shared by global-settings validation and schemas.
 */
final class SafeSettingKeys {
    public const VALIDATION_MESSAGES = [
        'required',
        'email',
        'number',
        'min',
        'max',
        'confirm',
        'url',
        'input_mask',
        'gdpr',
        'character_limit',
    ];
}
