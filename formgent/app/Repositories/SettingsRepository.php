<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

class SettingsRepository {
    protected array $default_settings = [
        "disable_ip_logging"         => "no",
        "enable_honeypot_protection" => "yes",
        "validation_messages"        => [
            "required"        => "This field is required",
            "email"           => "This field must contain a valid email",
            "number"          => "This field must contain numeric value",
            "min"             => "This value is below the minimum {limit}",
            "max"             => "This value exceeds the maximum {limit}",
            "confirm"         => "Field values do not match",
            "url"             => "Please enter a valid URL",
            "input_mask"      => "Please fill out the field in required format",
            "gdpr"            => "You must agree to proceed",
            "character_limit" => "Limit is {limit} characters",
        ],
        "google_sheet"               => [
            "status" => false,
        ],
        "mailchimp"                  => [
            "status"    => false,
            "connected" => false,
            "data"      => null,
        ],
        "license_key"                => "",
        "captcha_keys"               => [
            "recaptcha_site_key"   => "",
            "recaptcha_secret_key" => "",
            "hcaptcha_site_key"    => "",
            "hcaptcha_secret_key"  => "",
            "turnstile_site_key"   => "",
            "turnstile_secret_key" => "",
        ],
        "payment"                    => [
            "status"          => false,
            "currency"        => 'USD',
            "symbol_position" => "left",
            "paypal"          => [
                "status"     => false,
                "test_mode"  => false,
                "client_id"  => "",
                "secret_key" => "",
            ],
            "stripe"          => [
                "status"          => false,
                "test_mode"       => false,
                "publishable_key" => "",
                "secret_key"      => "",
            ],
            "mollie"          => [
                "status"  => false,
                "api_key" => "",
            ],
        ],
        "login_registration"         => [
            "login"        => [
                "status" => "0",
                "page"   => "",
            ],
            "registration" => [
                "status" => "0",
                "page"   => "",
            ],
        ],
    ];

    public function save( array $settings ) {
        $update = update_option( 'formgent_settings', map_deep( $settings, 'sanitize_text_field' ) );
        wp_cache_delete( 'settings', 'formgent' );
        return $update;
    }

    /**
     * Return the stored option without creating defaults or tokens.
     *
     * @return array<string,mixed>
     */
    public function get_stored(): array {
        $settings = get_option( 'formgent_settings', [] );

        return is_array( $settings ) ? $settings : [];
    }

    /**
     * Persist an already validated shallow patch while preserving excluded values.
     *
     * @param array<string,mixed> $changes Validated top-level settings.
     */
    public function update_preserving( array $changes ): bool {
        $settings = array_merge( $this->get_stored(), $changes );
        $updated  = update_option( 'formgent_settings', $settings );

        wp_cache_delete( 'settings', 'formgent' );

        return $updated;
    }

    public function get() {
        $settings_cache = wp_cache_get( 'settings', 'formgent' );

        if ( is_array( $settings_cache ) ) {
            return $settings_cache;
        }

        $settings = array_merge( $this->default_settings, get_option( 'formgent_settings', [] ) );

        if ( empty( $settings["zapier_token"] ) ) {
            $settings["zapier_token"] = formgent_generate_token();
            $this->save( $settings );
        }

        wp_cache_add( 'settings', $settings, 'formgent', 3600 );

        return $settings;
    }

    public function get_by_key( string $key, $default = null ) {
        $settings = $this->get();
        return isset( $settings[$key] ) ? $settings[$key] : $default;
    }

    public function update_setting( string $key, $value ) {
        if ( is_array( $value ) ) {
            $value = map_deep( $value, 'sanitize_text_field' );
        } else {
            $value = sanitize_text_field( $value );
        }

        $key            = sanitize_text_field( $key );
        $settings       = $this->get();
        $settings[$key] = $value;

        return $this->save( $settings );
    }
}
