<?php

namespace FormGent\App\Repositories;

defined( "ABSPATH" ) || exit;

class FormSettingsRepository {
    protected array $default_settings = [
        "save_incompleted_data"  => "no",
        "hide_formgent_branding" => "no",
        "confirmation"           => [
            "type"             => "message", // message | page | url
            "page"             => 0,
            "url"              => "https://google.com",
            "after_submission" => "reset" // hide | reset
        ],
        "quiz"                   => [
            "is_enabled" => false,
            "grades"     => [],
        ],
        "design"                 => [
            "status"     => false,
            "show_cover" => true,
            "cover"      => [],
            "cover_type" => 'color',
            "show_logo"  => true,
            "logo"       => [],
            "show_title" => true,
            "width"      => '740px',
        ],
    ];

    public function __construct() {
        $this->default_settings['confirmation']['message'] = $this->get_message();
    }

    private function get_message() {
        $image_url = formgent_url( 'assets/images/success.gif' );
        //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
        return "<h3 style='text-align: center; font-size: 32px; font-weight: bold;'>Thank You!</h3> <p style='text-align: center;'>Your submission has been received successfully. <br /> We truly appreciate your time and effort.</p>";
    }

    public function get_settings( int $form_id ) {
        $settings = get_post_meta( $form_id, '_formgent_settings', true );
        return is_array( $settings ) ? array_merge( $this->default_settings, $settings ) : $this->default_settings;
    }

    public function save_settings( int $form_id, array $settings ) {
        return update_post_meta( $form_id, '_formgent_settings', $settings );
    }

    public function get_setting_by_key( int $form_id, string $key, $default = null ) {
        $settings = $this->get_settings( $form_id );
        return isset( $settings[$key] ) ? $settings[$key] : $default;
    }

    public function update_setting( int $form_id, string $key, $value ) {
        $key            = sanitize_text_field( $key );
        $settings       = $this->get_settings( $form_id );
        $settings[$key] = $value;

        return $this->save_settings( $form_id, $settings );
    }
}