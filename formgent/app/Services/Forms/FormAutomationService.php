<?php

namespace FormGent\App\Services\Forms;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\EmailNotificationDTO;
use FormGent\App\DTO\PdfDTO;
use FormGent\App\Repositories\EmailNotificationRepository;
use FormGent\App\Repositories\PdfRepository;
use FormGent\App\Services\Mcp\McpErrorFactory;
use FormGent\App\Utils\Capabilities;
use Throwable;
use WP_Error;
use WP_Post;

/**
 * Reads and replaces non-secret, per-form automation resources.
 *
 * Passwords, API keys, webhook URLs, payment configuration, integration
 * credentials, and executable custom scripts never enter this contract.
 */
class FormAutomationService {
    private EmailNotificationRepository $emails;

    private PdfRepository $pdfs;

    public function __construct( EmailNotificationRepository $emails, PdfRepository $pdfs ) {
        $this->emails = $emails;
        $this->pdfs   = $pdfs;
    }

    /** @return array<string,mixed>|WP_Error */
    public function get( int $form_id ) {
        if ( ! $this->form_exists( $form_id ) ) {
            return McpErrorFactory::form_not_found();
        }

        $automations = [
            'email_notifications' => $this->read_emails( $form_id ),
            'pdf_templates'       => $this->read_pdfs( $form_id ),
            'user_registrations'  => $this->read_registrations( $form_id ),
        ];

        /**
         * Adds extension-owned non-secret automation collections.
         *
         * @param array<string,mixed> $automations Current collections.
         * @param int                 $form_id     Form ID.
         */
        $automations = apply_filters( 'formgent_mcp_read_form_automations', $automations, $form_id );

        return is_array( $automations ) ? $automations : McpErrorFactory::internal();
    }

    /**
     * Replaces each supplied collection. Omitted collections remain unchanged.
     *
     * @param array<string,mixed> $patch Validated public patch.
     * @return array<string,mixed>|WP_Error
     */
    public function replace( int $form_id, array $patch ) {
        global $wpdb;

        if ( ! $this->form_exists( $form_id ) ) {
            return McpErrorFactory::form_not_found();
        }

        if ( empty( $patch ) ) {
            return McpErrorFactory::invalid_input( esc_html__( 'At least one automation collection must be supplied.', 'formgent' ) );
        }

        $prepared = $this->prepare( $form_id, $patch );

        if ( is_wp_error( $prepared ) ) {
            return $prepared;
        }

        /**
         * Lets extensions validate and sanitize their automation collections
         * before any database mutation starts.
         *
         * @param array<string,mixed>|WP_Error $prepared Prepared collections.
         * @param array<string,mixed>          $patch    Validated public patch.
         * @param int                          $form_id  Form ID.
         */
        $prepared = apply_filters( 'formgent_mcp_prepare_form_automations', $prepared, $patch, $form_id );

        if ( is_wp_error( $prepared ) ) {
            return $prepared;
        }

        if ( ! is_array( $prepared ) ) {
            return McpErrorFactory::internal();
        }

        $wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

        try {
            if ( isset( $prepared['email_notifications'] ) ) {
                $this->replace_emails( $form_id, $prepared['email_notifications'] );
            }

            if ( isset( $prepared['pdf_templates'] ) ) {
                $this->replace_pdfs( $form_id, $prepared['pdf_templates'] );
            }

            if ( isset( $prepared['user_registrations'] ) ) {
                $saved = update_post_meta( $form_id, '_formgent_settings', $prepared['form_settings'] );

                if ( false === $saved && get_post_meta( $form_id, '_formgent_settings', true ) !== $prepared['form_settings'] ) {
                    throw new FormAutomationException( McpErrorFactory::internal() );
                }
            }

            /**
             * Applies extension-owned prepared collections inside the same DB transaction.
             * Returning WP_Error or false rolls the complete replacement back.
             *
             * @param true|WP_Error $result   Current result.
             * @param array         $prepared Prepared collections.
             * @param int           $form_id  Form ID.
             */
            $extension_result = apply_filters( 'formgent_mcp_apply_form_automations', true, $prepared, $form_id );

            if ( is_wp_error( $extension_result ) ) {
                throw new FormAutomationException( $extension_result );
            }

            if ( false === $extension_result ) {
                throw new FormAutomationException( McpErrorFactory::internal() );
            }

            $wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        } catch ( FormAutomationException $exception ) {
            $wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            return $exception->get_wp_error();
        } catch ( Throwable $throwable ) {
            $wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

            try {
                do_action( 'formgent_mcp_internal_exception', 'formgent/update-form-automations', $throwable, ['form_id' => $form_id] );
            } catch ( Throwable $observer_error ) {
                // Error observers must not replace the sanitized MCP failure.
            }

            return McpErrorFactory::internal();
        }

        return $this->get( $form_id );
    }

    /** @param array<string,mixed> $patch @return array<string,mixed>|WP_Error */
    private function prepare( int $form_id, array $patch ) {
        $prepared = [];

        if ( isset( $patch['email_notifications'] ) ) {
            $prepared['email_notifications'] = $this->prepare_emails( $form_id, $patch['email_notifications'] );

            if ( is_wp_error( $prepared['email_notifications'] ) ) {
                return $prepared['email_notifications'];
            }
        }

        if ( isset( $patch['pdf_templates'] ) ) {
            $prepared['pdf_templates'] = $this->prepare_pdfs( $form_id, $patch['pdf_templates'] );

            if ( is_wp_error( $prepared['pdf_templates'] ) ) {
                return $prepared['pdf_templates'];
            }
        }

        if ( isset( $patch['user_registrations'] ) ) {
            $settings      = get_post_meta( $form_id, '_formgent_settings', true );
            $settings      = is_array( $settings ) ? $settings : [];
            $stored        = is_array( $settings['user_registrations'] ?? null ) ? $settings['user_registrations'] : [];
            $registrations = $this->prepare_registrations( $patch['user_registrations'], $form_id, $stored, true );

            if ( is_wp_error( $registrations ) ) {
                return $registrations;
            }

            $settings['user_registrations'] = $registrations;
            $prepared['user_registrations'] = $registrations;
            $prepared['form_settings']      = $settings;
        }

        return $prepared;
    }

    /** @return array<int,array<string,mixed>> */
    private function read_emails( int $form_id ): array {
        $rows = $this->emails->get_all_by_form_id( $form_id );
        $safe = [];

        foreach ( $rows as $row ) {
            $conditions = $this->decode_array( $row->conditions ?? [] );
            $safe[]     = [
                'id'               => absint( $row->id ?? 0 ),
                'name'             => sanitize_text_field( (string) ( $row->name ?? '' ) ),
                'send_to'          => sanitize_text_field( (string) ( $row->send_to ?? '' ) ),
                'subject'          => sanitize_text_field( (string) ( $row->subject ?? '' ) ),
                'body'             => wp_kses_post( (string) ( $row->body ?? '' ) ),
                'cc'               => sanitize_text_field( (string) ( $row->cc ?? '' ) ),
                'bcc'              => sanitize_text_field( (string) ( $row->bcc ?? '' ) ),
                'reply_to'         => sanitize_text_field( (string) ( $row->reply_to ?? '' ) ),
                'from_name'        => sanitize_text_field( (string) ( $row->from_name ?? '' ) ),
                'from_email'       => sanitize_text_field( (string) ( $row->from_email ?? '' ) ),
                'status'           => 'publish' === ( $row->status ?? '' ) ? 'publish' : 'draft',
                'condition_status' => empty( $row->condition_status ) ? 0 : 1,
                'condition_type'   => 'and' === ( $row->condition_type ?? '' ) ? 'and' : 'or',
                'conditions'       => $this->sanitize_conditions( $conditions ),
            ];
        }

        return $safe;
    }

    /** @return array<int,array<string,mixed>> */
    private function read_pdfs( int $form_id ): array {
        $safe = [];

        foreach ( $this->pdfs->get_safe_by_form_id( $form_id ) as $row ) {
            $safe[] = [
                'id'                 => absint( $row->id ?? 0 ),
                'template_name'      => sanitize_text_field( (string) ( $row->template_name ?? '' ) ),
                'template_type'      => sanitize_text_field( (string) ( $row->template_type ?? '' ) ),
                'content'            => wp_kses_post( (string) ( $row->content ?? '' ) ),
                'paper_size'         => sanitize_text_field( (string) ( $row->paper_size ?? '' ) ),
                'orientation'        => $this->orientation( $row->orientation ?? '' ),
                'direction'          => 'rtl' === ( $row->direction ?? '' ) ? 'rtl' : 'ltr',
                'password_protected' => ! empty( $row->password_protected ),
            ];
        }

        return $safe;
    }

    /** @return array<int,array<string,mixed>> */
    private function read_registrations( int $form_id ): array {
        $settings      = get_post_meta( $form_id, '_formgent_settings', true );
        $registrations = is_array( $settings ) && is_array( $settings['user_registrations'] ?? null ) ? $settings['user_registrations'] : [];
        $prepared      = $this->prepare_registrations( $registrations, $form_id, $registrations );

        return is_wp_error( $prepared ) ? [] : $prepared;
    }

    /** @param mixed $items @return array<int,array<string,mixed>>|WP_Error */
    private function prepare_emails( int $form_id, $items ) {
        $existing = [];

        foreach ( $this->read_emails( $form_id ) as $email ) {
            $existing[$email['id']] = true;
        }

        $safe = [];
        $ids  = [];

        foreach ( is_array( $items ) ? $items : [] as $item ) {
            $id = absint( $item['id'] ?? 0 );

            if ( $id && ! isset( $existing[$id] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'An email notification does not belong to this form.', 'formgent' ) );
            }

            if ( $id && isset( $ids[$id] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'Email notification IDs must be unique.', 'formgent' ) );
            }

            $ids[$id] = true;
            $safe[]   = $this->sanitize_email_item( $item, $id );
        }

        return $safe;
    }

    /** @param mixed $items @return array<int,array<string,mixed>>|WP_Error */
    private function prepare_pdfs( int $form_id, $items ) {
        $existing = [];

        foreach ( $this->read_pdfs( $form_id ) as $pdf ) {
            $existing[$pdf['id']] = true;
        }

        $safe = [];
        $ids  = [];

        foreach ( is_array( $items ) ? $items : [] as $item ) {
            $id = absint( $item['id'] ?? 0 );

            if ( $id && ! isset( $existing[$id] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A PDF template does not belong to this form.', 'formgent' ) );
            }

            if ( $id && isset( $ids[$id] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'PDF template IDs must be unique.', 'formgent' ) );
            }

            $ids[$id] = true;
            $safe[]   = [
                'id'            => $id,
                'template_name' => substr( sanitize_text_field( (string) ( $item['template_name'] ?? '' ) ), 0, 255 ),
                'template_type' => substr( sanitize_text_field( (string) ( $item['template_type'] ?? '' ) ), 0, 255 ),
                'content'       => wp_kses_post( (string) ( $item['content'] ?? '' ) ),
                'paper_size'    => substr( sanitize_text_field( (string) ( $item['paper_size'] ?? 'A4' ) ), 0, 50 ),
                'orientation'   => $this->orientation( $item['orientation'] ?? 'P' ),
                'direction'     => 'rtl' === ( $item['direction'] ?? '' ) ? 'rtl' : 'ltr',
            ];
        }

        return $safe;
    }

    /** @param mixed $items @return array<int,array<string,mixed>>|WP_Error */
    private function prepare_registrations( $items, int $form_id = 0, array $stored = [], bool $validate_links = false ) {
        $safe  = [];
        $ids   = [];
        $next  = 1;
        $links = [];

        foreach ( $stored as $registration ) {
            if ( ! is_array( $registration ) ) {
                continue;
            }

            $stored_id = absint( $registration['id'] ?? 0 );

            if ( $stored_id ) {
                $links[$stored_id] = absint( $registration['registration_email_notification_id'] ?? 0 );
            }
        }

        foreach ( is_array( $items ) ? $items : [] as $item ) {
            $id = absint( $item['id'] ?? 0 );

            if ( ! $id ) {
                while ( isset( $ids[$next] ) ) {
                    ++$next;
                }
                $id = $next++;
            }

            if ( isset( $ids[$id] ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'User registration IDs must be unique.', 'formgent' ) );
            }

            $ids[$id] = true;
            $auto     = ! empty( $item['auto_login'] );
            $method   = in_array( $item['verification_method'] ?? '', ['user_email', 'manual'], true ) ? $item['verification_method'] : 'user_email';
            $page     = $auto ? 0 : absint( $item['verification_confirmation_page'] ?? 0 );

            if ( ! $auto && ( ! $page || 'page' !== get_post_type( $page ) || 'publish' !== get_post_status( $page ) ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A published verification confirmation page is required when automatic login is disabled.', 'formgent' ) );
            }

            $role = sanitize_key( (string) ( $item['user_role'] ?? 'subscriber' ) );
            if ( 'custom' !== $role && ! Capabilities::is_safe_registration_role( $role ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The selected user role is not permitted for registration automations.', 'formgent' ) );
            }

            $custom_role = sanitize_key( (string) ( $item['custom_role'] ?? '' ) );
            if ( 'custom' === $role && ! Capabilities::is_safe_registration_role( $custom_role ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The selected custom user role is not permitted for registration automations.', 'formgent' ) );
            }

            $notification_id = array_key_exists( 'registration_email_notification_id', $item )
                ? absint( $item['registration_email_notification_id'] )
                : ( $links[$id] ?? 0 );

            if ( $validate_links && $notification_id && ! $this->email_belongs_to_form( $notification_id, $form_id ) ) {
                return McpErrorFactory::invalid_input( esc_html__( 'The registration email notification does not belong to this form.', 'formgent' ) );
            }

            if ( $validate_links && ! empty( $item['send_registration_email'] ) && ! $notification_id ) {
                return McpErrorFactory::invalid_input( esc_html__( 'A registration email notification ID is required when registration email delivery is enabled.', 'formgent' ) );
            }

            $safe[] = [
                'id'                                 => $id,
                'name'                               => substr( sanitize_text_field( (string) ( $item['name'] ?? '' ) ), 0, 255 ),
                'field_mapping'                      => $this->sanitize_string_map( $item['field_mapping'] ?? [] ),
                'user_role'                          => $role,
                'custom_role'                        => $custom_role,
                'custom_meta'                        => $this->sanitize_custom_meta( $item['custom_meta'] ?? [] ),
                'auto_login'                         => $auto,
                'verification_method'                => $auto ? 'user_email' : $method,
                'verification_email_template'        => $auto ? '' : wp_kses_post( (string) ( $item['verification_email_template'] ?? '' ) ),
                'verification_confirmation_page'     => $page,
                'hide_for_logged_in_message'         => wp_kses_post( (string) ( $item['hide_for_logged_in_message'] ?? '' ) ),
                'send_registration_email'            => ! empty( $item['send_registration_email'] ),
                'registration_email_notification_id' => $notification_id,
            ];
        }

        return $safe;
    }

    /** @param array<int,array<string,mixed>> $items */
    private function replace_emails( int $form_id, array $items ): void {
        $keep = [];

        foreach ( $items as $item ) {
            $dto = $this->email_dto( $form_id, $item );

            if ( ! empty( $item['id'] ) ) {
                $dto->set_id( $item['id'] );
                $this->emails->update( $dto );
                $keep[] = $item['id'];
            } else {
                $created = (int) $this->emails->create( $dto );

                if ( 1 > $created ) {
                    throw new FormAutomationException( McpErrorFactory::internal() );
                }

                $keep[] = $created;
            }
        }

        foreach ( $this->read_emails( $form_id ) as $existing ) {
            if ( ! in_array( $existing['id'], $keep, true ) ) {
                $this->emails->delete_by_id( $existing['id'] );
            }
        }
    }

    /** @param array<int,array<string,mixed>> $items */
    private function replace_pdfs( int $form_id, array $items ): void {
        $keep = [];

        foreach ( $items as $item ) {
            $dto = ( new PdfDTO() )
                ->set_form_id( $form_id )
                ->set_template_name( $item['template_name'] )
                ->set_template_type( $item['template_type'] )
                ->set_content( $item['content'] )
                ->set_paper_size( $item['paper_size'] )
                ->set_orientation( $item['orientation'] )
                ->set_direction( $item['direction'] )
                ->set_password( '' );

            if ( ! empty( $item['id'] ) ) {
                $this->pdfs->update_non_secret( $item['id'], $form_id, $item );
                $keep[] = $item['id'];
            } else {
                $created = $this->pdfs->create( $dto );

                if ( 1 > $created ) {
                    throw new FormAutomationException( McpErrorFactory::internal() );
                }

                $keep[] = $created;
            }
        }

        foreach ( $this->read_pdfs( $form_id ) as $existing ) {
            if ( ! in_array( $existing['id'], $keep, true ) ) {
                $this->pdfs->delete_by_id( $existing['id'] );
            }
        }
    }

    private function email_belongs_to_form( int $email_id, int $form_id ): bool {
        $email = $this->emails->get_by_id( $email_id );
        return is_object( $email ) && absint( $email->form_id ?? 0 ) === $form_id;
    }

    /** @param array<string,mixed> $item */
    private function email_dto( int $form_id, array $item ): EmailNotificationDTO {
        return ( new EmailNotificationDTO() )
            ->set_form_id( $form_id )
            ->set_name( $item['name'] )
            ->set_send_to( $item['send_to'] )
            ->set_subject( $item['subject'] )
            ->set_body( $item['body'] )
            ->set_cc( $item['cc'] )
            ->set_bcc( $item['bcc'] )
            ->set_reply_to( $item['reply_to'] )
            ->set_from_name( $item['from_name'] )
            ->set_from_email( $item['from_email'] )
            ->set_status( $item['status'] )
            ->set_condition_status( $item['condition_status'] )
            ->set_condition_type( $item['condition_type'] )
            ->set_conditions( $item['conditions'] );
    }

    /** @param array<string,mixed> $item @return array<string,mixed> */
    private function sanitize_email_item( array $item, int $id ): array {
        return [
            'id'               => $id,
            'name'             => substr( sanitize_text_field( (string) ( $item['name'] ?? '' ) ), 0, 255 ),
            'send_to'          => substr( sanitize_text_field( (string) ( $item['send_to'] ?? '' ) ), 0, 500 ),
            'subject'          => substr( sanitize_text_field( (string) ( $item['subject'] ?? '' ) ), 0, 255 ),
            'body'             => wp_kses_post( (string) ( $item['body'] ?? '' ) ),
            'cc'               => substr( sanitize_text_field( (string) ( $item['cc'] ?? '' ) ), 0, 500 ),
            'bcc'              => substr( sanitize_text_field( (string) ( $item['bcc'] ?? '' ) ), 0, 500 ),
            'reply_to'         => substr( sanitize_text_field( (string) ( $item['reply_to'] ?? '' ) ), 0, 500 ),
            'from_name'        => substr( sanitize_text_field( (string) ( $item['from_name'] ?? '' ) ), 0, 255 ),
            'from_email'       => substr( sanitize_text_field( (string) ( $item['from_email'] ?? '' ) ), 0, 500 ),
            'status'           => 'publish' === ( $item['status'] ?? '' ) ? 'publish' : 'draft',
            'condition_status' => empty( $item['condition_status'] ) ? 0 : 1,
            'condition_type'   => 'and' === ( $item['condition_type'] ?? '' ) ? 'and' : 'or',
            'conditions'       => $this->sanitize_conditions( $item['conditions'] ?? [] ),
        ];
    }

    /** @param mixed $conditions @return array<int,array<string,mixed>> */
    private function sanitize_conditions( $conditions ): array {
        $safe      = [];
        $operators = ['is', 'is_not', 'contains', 'not_contains', 'greater_than', 'less_than', 'starts_with', 'ends_with', 'is_empty', 'is_not_empty', 'regex'];

        foreach ( array_slice( is_array( $conditions ) ? $conditions : [], 0, 100 ) as $condition ) {
            if ( ! is_array( $condition ) ) {
                continue;
            }

            $operator = sanitize_key( (string) ( $condition['operator'] ?? 'is' ) );
            $value    = $condition['value'] ?? '';

            if ( ! is_scalar( $value ) && null !== $value ) {
                $value = '';
            }

            $safe[] = [
                'field'    => substr( sanitize_text_field( (string) ( $condition['field'] ?? '' ) ), 0, 255 ),
                'operator' => in_array( $operator, $operators, true ) ? $operator : 'is',
                'value'    => is_string( $value ) ? sanitize_text_field( $value ) : $value,
            ];
        }

        return $safe;
    }

    /** @param mixed $value @return array<string,string> */
    private function sanitize_string_map( $value ): array {
        $safe = [];

        foreach ( is_array( $value ) ? $value : [] as $key => $item ) {
            $safe[substr( sanitize_key( (string) $key ), 0, 100 )] = substr( sanitize_text_field( (string) $item ), 0, 255 );
        }

        return $safe;
    }

    /** @param mixed $value @return array<int,array<string,string>> */
    private function sanitize_custom_meta( $value ): array {
        $safe = [];

        foreach ( array_slice( is_array( $value ) ? $value : [], 0, 50 ) as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $safe[] = [
                'meta_key' => substr( sanitize_key( (string) ( $row['meta_key'] ?? '' ) ), 0, 191 ),
                'field'    => substr( sanitize_text_field( (string) ( $row['field'] ?? '' ) ), 0, 255 ),
            ];
        }

        return $safe;
    }

    /** @param mixed $value @return array<int,mixed> */
    private function decode_array( $value ): array {
        if ( is_array( $value ) ) {
            return $value;
        }

        $decoded = is_string( $value ) ? json_decode( $value, true ) : [];
        return is_array( $decoded ) ? $decoded : [];
    }

    /** @param mixed $value */
    private function orientation( $value ): string {
        return in_array( $value, ['L', 'landscape'], true ) ? 'L' : 'P';
    }

    private function form_exists( int $form_id ): bool {
        $post = get_post( $form_id );
        return $post instanceof WP_Post && formgent_post_type() === $post->post_type && in_array( $post->post_status, ['draft', 'publish'], true );
    }
}

/**
 * Carries a sanitized WP_Error across a database transaction boundary.
 */
class FormAutomationException extends \RuntimeException {
    private WP_Error $wp_error;

    public function __construct( WP_Error $wp_error ) {
        parent::__construct( $wp_error->get_error_message() );
        $this->wp_error = $wp_error;
    }

    public function get_wp_error(): WP_Error {
        return $this->wp_error;
    }
}
