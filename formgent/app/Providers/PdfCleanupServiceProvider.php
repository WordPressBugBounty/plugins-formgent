<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Contracts\Provider;

class PdfCleanupServiceProvider implements Provider {
    const CRON_HOOK = 'formgent_cleanup_old_pdfs';

    public function boot() {
        add_action( self::CRON_HOOK, [ $this, 'delete_old_pdf_files' ] );

        // Clean up generated PDF files when responses are deleted.
        add_action( 'formgent_before_delete_responses', [ $this, 'on_responses_deleted' ], 10, 1 );
        add_action( 'formgent_before_delete_all_responses', [ $this, 'on_responses_deleted' ], 10, 1 );
        add_action( 'init', [ $this, 'schedule_cleanup' ] );
    }

    public function schedule_cleanup(): void {
        // Schedule cron only on admin requests to avoid per-request DB queries on frontend.
        if ( is_admin() && ! wp_next_scheduled( self::CRON_HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::CRON_HOOK );
        }
    }

    /**
     * Delete generated PDF files older than 7 days.
     */
    public function delete_old_pdf_files(): void {
        $uploads = wp_upload_dir();
        if ( ! empty( $uploads['error'] ) ) {
            return;
        }

        $pdf_dir = trailingslashit( $uploads['basedir'] ) . 'formgent/pdfs';
        if ( ! is_dir( $pdf_dir ) ) {
            return;
        }

        $max_age = apply_filters( 'formgent_pdf_max_age_seconds', 7 * DAY_IN_SECONDS );
        $now     = time();

        $files = glob( trailingslashit( $pdf_dir ) . '*.pdf' );
        if ( ! is_array( $files ) ) {
            return;
        }

        foreach ( $files as $file ) {
            if ( ! is_file( $file ) ) {
                continue;
            }

            $file_age = $now - filemtime( $file );
            if ( $file_age > $max_age && is_writable( $file ) ) {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
                unlink( $file );
            }
        }
    }

    /**
     * When responses are deleted, remove any generated PDF files
     * associated with those responses' forms.
     *
     * Note: Generated PDFs on disk are not directly linked to response IDs
     * in the database. The filenames contain random hashes. Since we cannot
     * map a specific file to a specific response, we delete all PDF template
     * configurations (DB rows) for orphaned forms and let the cron handle
     * the generated file cleanup based on age.
     *
     * @param array $response_ids
     */
    public function on_responses_deleted( $response_ids ): void {
        if ( empty( $response_ids ) || ! is_array( $response_ids ) ) {
            return;
        }

        // The formgent_pdfs table has CASCADE on form_id → posts.ID,
        // so when a form (post) is deleted, its PDF templates are auto-deleted.
        // For response deletions, we don't need to delete PDF templates since
        // they belong to the form, not the response.
        // However, we should clean up any transient PDF links stored for payments.
        foreach ( $response_ids as $response_id ) {
            $response_id = absint( $response_id );
            if ( $response_id <= 0 ) {
                continue;
            }

            $order = formgent_order_repository()->first_by_response_id( $response_id );
            if ( $order && ! empty( $order->hash ) ) {
                delete_transient( 'formgent_payment_pdf_links_' . $order->hash );
            }
        }
    }
}
