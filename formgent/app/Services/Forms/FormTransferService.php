<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use Exception;
use FormGent\App\DTO\FormDTO;
use FormGent\App\Repositories\FormRepository;
use Throwable;
use WP_REST_Request;

/**
 * Creates and consumes portable FormGent form export payloads.
 */
class FormTransferService {
    private const EXPORT_TYPE = 'formgent_form_export';

    private const EXPORT_VERSION = '1.0.0';

    private const MAX_PAYLOAD_BYTES = 5242880;

    private FormRepository $forms;

    public function __construct( FormRepository $forms ) {
        $this->forms = $forms;
    }

    /**
     * Build a portable export payload for a form.
     *
     * @return array<string,mixed>
     */
    public function export( int $form_id ): array {
        $form = $this->forms->get_by_id( $form_id );

        if ( ! $form ) {
            throw new Exception( esc_html__( 'Form not found.', 'formgent' ), 404 );
        }

        $payload = [
            'type'        => self::EXPORT_TYPE,
            'version'     => self::EXPORT_VERSION,
            'exported_at' => gmdate( 'c' ),
            'source'      => [
                'site_url'       => get_site_url(),
                'plugin_version' => defined( 'FORMGENT_VERSION' ) ? FORMGENT_VERSION : formgent_app_config( 'version' ),
            ],
            'form'        => [
                'title'   => (string) ( $form->post_title ?? '' ),
                'status'  => (string) ( $form->post_status ?? 'draft' ),
                'type'    => $this->normalize_form_type( get_post_meta( $form_id, '_formgent_type', true ) ),
                'content' => (string) ( $form->post_content ?? '' ),
            ],
            'settings'    => [
                'general'    => $this->get_array_meta( $form_id, '_formgent_settings' ),
                'appearance' => $this->get_array_meta( $form_id, '_formgent_form_settings' ),
                'page_break' => $this->get_array_meta( $form_id, '_formgent_page_break_settings' ),
                'fields'     => $this->get_array_meta( $form_id, '_formgent_fields' ),
            ],
            'excluded'    => [
                'responses',
                'analytics',
                'integration_credentials',
                'integration_records',
                'uploaded_files',
            ],
        ];

        /**
         * Filters a FormGent form export payload before it is returned.
         *
         * @param array<string,mixed> $payload Export payload.
         * @param object              $form Form database record.
         * @param int                 $form_id Form ID.
         */
        $payload = apply_filters( 'formgent_form_export_payload', $payload, $form, $form_id );

        if ( ! is_array( $payload ) ) {
            throw new Exception( esc_html__( 'The form could not be exported.', 'formgent' ), 500 );
        }

        return $payload;
    }

    /**
     * Import a FormGent export payload as a draft.
     */
    public function import( array $payload, ?WP_REST_Request $request = null ): int {
        /**
         * Filters a FormGent form export payload before it is validated and imported.
         *
         * @param array<string,mixed> $payload Import payload.
         */
        $payload = apply_filters( 'formgent_form_import_payload', $payload );

        if ( ! is_array( $payload ) || self::MAX_PAYLOAD_BYTES < strlen( (string) wp_json_encode( $payload ) ) ) {
            throw new Exception( esc_html__( 'The selected form export file is too large.', 'formgent' ), 413 );
        }

        $normalized = $this->normalize_import_payload( $payload );
        $form       = $normalized['form'];
        $settings   = $normalized['settings'];
        $title      = $this->get_import_title( $form['title'] ?? '' );
        $content    = $this->normalize_content( $form['content'] ?? '' );
        $type       = $this->normalize_form_type( $form['type'] ?? 'general' );
        $general    = FormSecurityPolicy::sanitize_settings( $settings['general'], true );
        $appearance = $settings['appearance'];
        $page_break = $settings['page_break'];
        $fields     = $settings['fields'];
        $form_id    = 0;
        $dto        = ( new FormDTO() )
            ->set_title( $title )
            ->set_status( 'draft' )
            ->set_content( $content )
            ->set_type( $type )
            ->set_fields( $fields )
            ->set_settings( $general )
            ->set_form_settings( $appearance );

        try {
            do_action( 'formgent_before_import_form', $dto, $payload, $request );
            do_action( 'formgent_before_create_form', $dto, $request );

            $form_id = $this->forms->create( $dto );
            $dto->set_id( $form_id );

            if ( ! empty( $page_break ) ) {
                $saved = update_post_meta( $form_id, '_formgent_page_break_settings', $page_break );

                if ( false === $saved && get_post_meta( $form_id, '_formgent_page_break_settings', true ) !== $page_break ) {
                    throw new Exception( esc_html__( 'The imported form settings could not be saved.', 'formgent' ), 500 );
                }
            }

            do_action( 'formgent_after_create_form', $dto, $request );
            do_action( 'formgent_after_import_form', $dto, $payload, $request );
        } catch ( Throwable $throwable ) {
            if ( 0 < $form_id ) {
                wp_delete_post( $form_id, true );
            }

            throw $throwable;
        }

        return $form_id;
    }

    /**
     * @return array{form:array<string,mixed>,settings:array{general:array,appearance:array,page_break:array,fields:array}}
     */
    protected function normalize_import_payload( array $payload ): array {
        if ( self::EXPORT_TYPE !== ( $payload['type'] ?? '' ) || empty( $payload['form'] ) || ! is_array( $payload['form'] ) ) {
            throw new Exception( esc_html__( 'Please select a valid FormGent form export file.', 'formgent' ), 422 );
        }

        $version = (string) ( $payload['version'] ?? '' );

        if ( 1 !== (int) strtok( $version, '.' ) ) {
            throw new Exception( esc_html__( 'This FormGent export version is not supported.', 'formgent' ), 422 );
        }

        $settings = isset( $payload['settings'] ) && is_array( $payload['settings'] ) ? $payload['settings'] : [];

        return [
            'form'     => $payload['form'],
            'settings' => [
                'general'    => $this->normalize_array( $settings['general'] ?? [] ),
                'appearance' => $this->normalize_array( $settings['appearance'] ?? [] ),
                'page_break' => $this->normalize_array( $settings['page_break'] ?? [] ),
                'fields'     => $this->normalize_array( $settings['fields'] ?? [] ),
            ],
        ];
    }

    protected function normalize_content( $content ): string {
        if ( ! is_string( $content ) || '' === trim( $content ) || self::MAX_PAYLOAD_BYTES < strlen( $content ) ) {
            throw new Exception( esc_html__( 'The selected file does not contain valid form content.', 'formgent' ), 422 );
        }

        if ( ! current_user_can( 'unfiltered_html' ) ) {
            $content = wp_kses_post( $content );
        }

        return $content;
    }

    protected function get_import_title( $title ): string {
        $title = trim( sanitize_text_field( (string) $title ) );

        if ( '' === $title ) {
            $title = esc_html__( 'Imported form', 'formgent' );
        } else {
            /* translators: %s: imported form title. */
            $title = sprintf( esc_html__( '%s imported', 'formgent' ), $title );
        }

        return substr( $title, 0, 255 );
    }

    protected function normalize_form_type( $type ): string {
        return 'conversational' === $type ? 'conversational' : 'general';
    }

    /**
     * @return array<mixed>
     */
    protected function get_array_meta( int $form_id, string $key ): array {
        return $this->normalize_array( get_post_meta( $form_id, $key, true ) );
    }

    /**
     * @return array<mixed>
     */
    protected function normalize_array( $value ): array {
        return is_array( $value ) ? $value : [];
    }
}
