<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\FormDTO;
use FormGent\App\Services\Mcp\McpErrorFactory;
use Throwable;
use WP_Error;
use WP_Post;
use WP_REST_Request;

/**
 * Coordinates validated FormGent form mutations and compensation.
 */
class FormCommandService {
    private FormBlockBuilder $builder;

    private SafeFormSettingsService $safe_settings;

    private FormCacheService $cache;

    private FormReadService $reader;

    private FormTypeConversionService $converter;

    private FormLayoutService $layout;

    public function __construct(
        FormBlockBuilder $builder,
        SafeFormSettingsService $safe_settings,
        FormCacheService $cache,
        FormReadService $reader,
        FormTypeConversionService $converter,
        FormLayoutService $layout
    ) {
        $this->builder       = $builder;
        $this->safe_settings = $safe_settings;
        $this->cache         = $cache;
        $this->reader        = $reader;
        $this->converter     = $converter;
        $this->layout        = $layout;
    }

    /**
     * @param array<string,mixed> $input Validated create input.
     * @return array<string,mixed>|WP_Error
     */
    public function create( array $input ) {
        $title = $this->prepare_title( $input['title'] ?? '' );

        if ( is_wp_error( $title ) ) {
            return $title;
        }

        $type     = $input['type'] ?? 'general';
        $status   = $input['status'] ?? 'draft';
        $warnings = [];

        if ( 'publish' === $status && ! current_user_can( 'formgent_publish_forms' ) ) {
            $status     = 'draft';
            $warnings[] = esc_html__( 'The form was created as a draft because the current user cannot publish forms.', 'formgent' );
        }
        if ( isset( $input['layout'] ) === isset( $input['fields'] ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Provide exactly one of fields or layout when creating a form.', 'formgent' ) );
        }

        $content = isset( $input['layout'] )
            ? $this->layout->build( $input['layout'] )
            : $this->builder->build( $input['fields'], $type );

        if ( is_wp_error( $content ) ) {
            return $content;
        }

        $settings = $this->safe_settings->prepare( 0, $input['settings'] ?? [] );

        if ( is_wp_error( $settings ) ) {
            return $settings;
        }

        $dto = ( new FormDTO() )
            ->set_title( $title )
            ->set_status( $status )
            ->set_content( $content['content'] )
            ->set_type( $type )
            ->set_fields( $content['fields'] ?? [] )
            ->set_settings( $settings['settings'] )
            ->set_form_settings( $settings['form_settings'] );

        $form = $this->persist_new_form(
            $dto,
            $settings,
            'formgent/create-form',
            'formgent_before_create_form',
            'formgent_after_create_form'
        );

        if ( is_wp_error( $form ) ) {
            return $form;
        }

        return [
            'form'     => $form,
            'warnings' => array_merge( $warnings, $settings['warnings'] ),
        ];
    }

    /**
     * @param array<string,mixed> $input Validated update input.
     * @return array<string,mixed>|WP_Error
     */
    public function update( array $input ) {
        $form_id = absint( $input['form_id'] ?? 0 );
        $post    = $this->active_form( $form_id );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $change_keys = array_intersect( array_keys( $input ), ['title', 'type', 'fields', 'layout', 'settings', 'status'] );

        if ( empty( $change_keys ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'At least one form property must be changed.', 'formgent' ) );
        }

        $title = $this->prepare_title( $input['title'] ?? $post->post_title );

        if ( is_wp_error( $title ) ) {
            return $title;
        }

        $current_type = (string) ( get_post_meta( $form_id, '_formgent_type', true ) ?: 'general' );
        $target_type  = $input['type'] ?? $current_type;
        $content      = $post->post_content;
        $warnings     = [];

        if ( isset( $input['fields'], $input['layout'] ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'Fields and layout cannot be replaced in the same update.', 'formgent' ) );
        }

        if ( isset( $input['layout'] ) ) {
            if ( ! $this->layout->is_complete( $post->post_content ) ) {
                return McpErrorFactory::conflict( esc_html__( 'This form contains internal or unsupported blocks that cannot be safely replaced through the layout contract.', 'formgent' ) );
            }

            $built = $this->layout->build( $input['layout'] );

            if ( is_wp_error( $built ) ) {
                return $built;
            }

            $content = $built['content'];
        } elseif ( isset( $input['fields'] ) ) {
            $built = $this->builder->build( $input['fields'], $target_type );

            if ( is_wp_error( $built ) ) {
                return $built;
            }

            $content = $built['content'];
        } elseif ( $target_type !== $current_type ) {
            $converted = $this->converter->convert( $post->post_content, $current_type, $target_type );

            if ( is_wp_error( $converted ) ) {
                return $converted;
            }

            $content  = $converted['content'];
            $warnings = $converted['warnings'];
        }

        $settings = isset( $input['settings'] ) ? $this->safe_settings->prepare( $form_id, $input['settings'] ) : null;

        if ( null !== $settings && is_wp_error( $settings ) ) {
            return $settings;
        }

        $old_meta = $this->capture_meta(
            $form_id,
            [ '_formgent_type', '_formgent_settings', '_formgent_form_settings', '_formgent_pending_type_migration' ]
        );
        $request  = new WP_REST_Request( 'PATCH' );

        foreach ( $input as $key => $value ) {
            $request->set_param( $key, $value );
        }

        try {
            if ( isset( $input['title'] ) ) {
                do_action( 'formgent_before_update_form_title', $request );
            }

            if ( isset( $input['status'] ) ) {
                do_action( 'formgent_before_update_form_status', $request );
            }
        } catch ( Throwable $throwable ) {
            $this->report_exception( 'formgent/update-form', $throwable, [ 'form_id' => $form_id ] );
            return McpErrorFactory::internal();
        }

        $post_update = [
            'ID'           => $form_id,
            'post_title'   => $title,
            'post_status'  => $input['status'] ?? $post->post_status,
            'post_content' => $content,
            'post_type'    => formgent_post_type(),
        ];
        $updated     = wp_update_post( $post_update, true );

        if ( is_wp_error( $updated ) ) {
            return McpErrorFactory::internal();
        }

        $meta_saved = true;

        if ( $target_type !== $current_type ) {
            $meta_saved = $this->save_meta( $form_id, '_formgent_type', $target_type )
                && $this->save_meta( $form_id, '_formgent_pending_type_migration', '' );
        }

        if ( null !== $settings ) {
            $meta_saved = $meta_saved && $this->safe_settings->save( $form_id, $settings );
        }

        if ( ! $meta_saved ) {
            $this->restore( $post, $old_meta );
            return McpErrorFactory::internal();
        }

        try {
            if ( isset( $input['title'] ) ) {
                do_action( 'formgent_after_update_form_title', $request );
            }

            if ( isset( $input['status'] ) ) {
                do_action( 'formgent_after_update_form_status', $request );
            }
        } catch ( Throwable $throwable ) {
            $this->restore( $post, $old_meta );
            $this->report_exception( 'formgent/update-form', $throwable, [ 'form_id' => $form_id ] );
            return McpErrorFactory::internal();
        }

        $this->cache->forget_fields( $form_id );
        $form = $this->reader->get( $form_id );

        if ( is_wp_error( $form ) ) {
            $this->restore( $post, $old_meta );
            return McpErrorFactory::internal();
        }

        return [
            'form'     => $form,
            'changed'  => array_values( $change_keys ),
            'warnings' => array_merge( $warnings, null !== $settings ? $settings['warnings'] : [] ),
        ];
    }

    /**
     * @param array<string,mixed> $input Validated duplicate input.
     * @return array<string,mixed>|WP_Error
     */
    public function duplicate( array $input ) {
        $source_id = absint( $input['form_id'] ?? 0 );
        $source    = $this->active_form( $source_id );

        if ( is_wp_error( $source ) ) {
            return $source;
        }

        $title = $this->prepare_title( $input['title'] ?? $source->post_title . ' - copy' );

        if ( is_wp_error( $title ) ) {
            return $title;
        }

        $settings = $this->safe_settings->prepare( 0, $this->safe_settings->get( $source_id ) );

        if ( is_wp_error( $settings ) ) {
            return $settings;
        }

        $type = (string) ( get_post_meta( $source_id, '_formgent_type', true ) ?: 'general' );
        $dto  = ( new FormDTO() )
            ->set_title( $title )
            ->set_status( 'draft' )
            ->set_content( $source->post_content )
            ->set_type( $type )
            ->set_settings( $settings['settings'] )
            ->set_form_settings( $settings['form_settings'] );

        $form = $this->persist_new_form(
            $dto,
            $settings,
            'formgent/duplicate-form',
            'formgent_before_duplicate_form',
            'formgent_after_duplicate_form',
            [ 'form_id' => $source_id ]
        );

        if ( is_wp_error( $form ) ) {
            return $form;
        }

        return [
            'form'     => $form,
            'warnings' => $settings['warnings'],
        ];
    }

    /** @return array<string,mixed>|WP_Error */
    public function delete( int $form_id, bool $force ) {
        $post = get_post( $form_id );

        if ( ! $post instanceof WP_Post || formgent_post_type() !== $post->post_type ) {
            return McpErrorFactory::form_not_found();
        }

        $previous_status = $post->post_status;

        try {
            do_action( 'formgent_before_delete_form', $form_id, $post );
        } catch ( Throwable $throwable ) {
            $this->report_exception( 'formgent/delete-form', $throwable, [ 'form_id' => $form_id ] );
            return McpErrorFactory::internal();
        }

        $deleted = $force ? wp_delete_post( $form_id, true ) : wp_trash_post( $form_id );

        if ( ! $deleted ) {
            return McpErrorFactory::conflict( esc_html__( 'The form could not be deleted.', 'formgent' ) );
        }

        $this->cache->forget_fields( $form_id );

        try {
            do_action( 'formgent_after_delete_form', $form_id, $post );
        } catch ( Throwable $throwable ) {
            // Deletion already succeeded; returning an error could cause a destructive retry.
            $this->report_exception( 'formgent/delete-form', $throwable, [ 'form_id' => $form_id ] );
        }

        return [
            'id'              => $form_id,
            'previous_status' => $previous_status,
            'mode'            => $force ? 'permanent' : 'trash',
        ];
    }

    /**
     * Run the shared, compensated transaction for new forms.
     *
     * @param array<string,mixed> $settings Prepared safe settings.
     * @param array<string,mixed> $context Privacy-safe context for pre-insert failures.
     * @return array<string,mixed>|WP_Error
     */
    private function persist_new_form( FormDTO $dto, array $settings, string $ability, string $before_hook, string $after_hook, array $context = [] ) {
        $post_data = [
            'post_title'   => $dto->get_title(),
            'post_status'  => $dto->get_status(),
            'post_content' => $dto->get_content(),
            'post_type'    => formgent_post_type(),
            'meta_input'   => [
                '_formgent_type'          => $dto->get_type(),
                '_formgent_fields'        => [],
                '_formgent_settings'      => $settings['settings'],
                '_formgent_form_settings' => $settings['form_settings'],
            ],
        ];

        try {
            do_action( $before_hook, $dto, null );
        } catch ( Throwable $throwable ) {
            $this->report_exception( $ability, $throwable, $context );
            return McpErrorFactory::internal();
        }

        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) || ! $this->created_meta_matches( $post_id, $dto->get_type(), $settings ) ) {
            if ( ! is_wp_error( $post_id ) ) {
                wp_delete_post( $post_id, true );
            }

            return McpErrorFactory::internal();
        }

        $dto->set_id( $post_id );

        try {
            do_action( $after_hook, $dto, null );
        } catch ( Throwable $throwable ) {
            wp_delete_post( $post_id, true );
            $this->report_exception( $ability, $throwable, [ 'form_id' => $post_id ] );
            return McpErrorFactory::internal();
        }

        $this->cache->forget_fields( $post_id );
        $form = $this->reader->get( $post_id );

        if ( is_wp_error( $form ) ) {
            wp_delete_post( $post_id, true );
            return McpErrorFactory::internal();
        }

        return $form;
    }

    /** @return string|WP_Error */
    private function prepare_title( $title ) {
        $title = sanitize_text_field( $title );

        return 5 <= strlen( $title ) && 255 >= strlen( $title )
            ? $title
            : McpErrorFactory::invalid_input( esc_html__( 'A form title must contain between 5 and 255 characters.', 'formgent' ) );
    }

    /** @return WP_Post|WP_Error */
    private function active_form( int $form_id ) {
        $post = get_post( $form_id );

        return $post instanceof WP_Post && formgent_post_type() === $post->post_type && in_array( $post->post_status, ['draft', 'publish'], true )
            ? $post
            : McpErrorFactory::form_not_found();
    }

    /** @param array<string,mixed> $settings */
    private function created_meta_matches( int $form_id, string $type, array $settings ): bool {
        return $this->meta_matches( $form_id, '_formgent_type', $type )
            && $this->meta_matches( $form_id, '_formgent_fields', [] )
            && $this->meta_matches( $form_id, '_formgent_settings', $settings['settings'] ?? [] )
            && $this->meta_matches( $form_id, '_formgent_form_settings', $settings['form_settings'] ?? [] );
    }

    /** @param mixed $value */
    private function meta_matches( int $form_id, string $key, $value ): bool {
        return metadata_exists( 'post', $form_id, $key ) && get_post_meta( $form_id, $key, true ) === $value;
    }

    /** @param mixed $value */
    private function save_meta( int $form_id, string $key, $value ): bool {
        if ( get_post_meta( $form_id, $key, true ) === $value ) {
            return true;
        }

        return false !== update_post_meta( $form_id, $key, $value );
    }

    /** @param array<int,string> $keys @return array<string,array{exists:bool,value:mixed}> */
    private function capture_meta( int $form_id, array $keys ): array {
        $captured = [];

        foreach ( $keys as $key ) {
            $captured[ $key ] = [
                'exists' => metadata_exists( 'post', $form_id, $key ),
                'value'  => get_post_meta( $form_id, $key, true ),
            ];
        }

        return $captured;
    }

    /** @param array<string,array{exists:bool,value:mixed}> $meta */
    private function restore( WP_Post $post, array $meta ): void {
        wp_update_post(
            [
                'ID'           => $post->ID,
                'post_title'   => $post->post_title,
                'post_status'  => $post->post_status,
                'post_content' => $post->post_content,
                'post_type'    => $post->post_type,
            ]
        );

        foreach ( $meta as $key => $state ) {
            if ( $state['exists'] ) {
                update_post_meta( $post->ID, $key, $state['value'] );
            } else {
                delete_post_meta( $post->ID, $key );
            }
        }

        $this->cache->forget_fields( $post->ID );
    }

    /** @param array<string,mixed> $context */
    private function report_exception( string $ability, Throwable $throwable, array $context = [] ): void {
        try {
            do_action( 'formgent_mcp_internal_exception', $ability, $throwable, $context );
        } catch ( Throwable $observer_error ) {
            // Error observers must not replace the sanitized MCP failure.
        }
    }
}
