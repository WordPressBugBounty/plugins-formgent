<?php

namespace FormGent\App\Multisite;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Utils\Capabilities;
use FormGent\Database\Setup;
use WP_Error;
use WP_Site;

/**
 * Owns all site-scoped lifecycle work required by WordPress multisite.
 *
 * FormGent stores form definitions in each site's posts tables and operational
 * data in tables that use that site's database prefix. Consequently, every
 * site needs an independently installed and versioned schema.
 */
final class SiteLifecycle {
    public const SCHEMA_VERSION = '1.11.0-multisite-1';

    private const SCHEMA_VERSION_OPTION = 'formgent_schema_version';
    private const SCHEMA_CHECKED_OPTION = 'formgent_schema_checked_at';
    private const SCHEMA_ERROR_OPTION   = 'formgent_schema_error';
    private const REWRITE_FLUSH_OPTION  = 'formgent_flush_rewrite_rules';
    private const NETWORK_ERROR_OPTION  = 'formgent_multisite_install_errors';

    /**
     * Historical migrations already represented by the current base schema.
     */
    private const INCLUDED_MIGRATIONS = ['1.2.2', '1.2.3', '1.2.4'];

    /**
     * All site-scoped custom tables owned by FormGent.
     *
     * Keep child tables before their foreign-key parents. WordPress drops the
     * tables in the order returned by wpmu_drop_tables and does not disable
     * foreign-key checks while doing so.
     */
    private const TABLES = [
        'formgent_response_meta',
        'formgent_answers',
        'formgent_notes',
        'formgent_response_token',
        'formgent_zapier_processed_responses',
        'formgent_order_items',
        'formgent_payments',
        'formgent_queues',
        'formgent_response_logs',
        'formgent_orders',
        'formgent_email_notifications',
        'formgent_google_spreadsheets',
        'formgent_mailchimp_feeds',
        'formgent_zapier_zaps',
        'formgent_zohocrm_feed',
        'formgent_pdfs',
        'formgent_responses',
    ];

    /**
     * Columns required for the dashboard and frontend submission flow.
     */
    private const REQUIRED_COLUMNS = [
        'formgent_responses'      => ['id', 'form_id', 'status', 'is_read', 'is_completed'],
        'formgent_answers'        => ['id', 'response_id', 'form_id', 'field_name', 'field_type', 'value'],
        'formgent_response_token' => ['id', 'form_id', 'response_id', 'token'],
    ];

    private string $plugin_file;

    /**
     * Prevent duplicate installation attempts during the same request.
     *
     * @var array<int, bool>
     */
    private static array $installation_attempted = [];

    /**
     * Request-local schema readiness cache, keyed by blog ID.
     *
     * @var array<int, bool>
     */
    private static array $schema_ready = [];

    public function __construct( string $plugin_file ) {
        $this->plugin_file = $plugin_file;
    }

    /**
     * Register runtime lifecycle hooks and repair the current site when needed.
     */
    public function boot(): void {
        add_action( 'wp_initialize_site', [$this, 'initialize_site'], 200, 2 );
        add_filter( 'wpmu_drop_tables', [$this, 'filter_site_drop_tables'], 10, 2 );
        add_action( 'init', [$this, 'maybe_flush_rewrite_rules'], 100 );
        add_action( 'admin_notices', [$this, 'render_schema_notice'] );
        add_action( 'network_admin_notices', [$this, 'render_network_notice'] );
        add_filter( 'formgent_pdf_library_base_dir', [$this, 'filter_pdf_library_base_dir'] );

        if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\\WP_CLI' ) ) {
            \WP_CLI::add_command( 'formgent multisite audit', [$this, 'audit'] );
            \WP_CLI::add_command( 'formgent multisite repair', [$this, 'repair'] );
        }

        self::maybe_install_current_site();
    }

    /**
     * Activation callback. Network activation provisions every existing site.
     */
    public function activate( bool $network_wide = false ): void {
        $errors = [];

        if ( is_multisite() && $network_wide ) {
            $site_ids = get_sites(
                [
                    'fields' => 'ids',
                    'number' => 0,
                ]
            );

            foreach ( $site_ids as $site_id ) {
                $result = self::install_for_site( (int) $site_id );

                if ( is_wp_error( $result ) ) {
                    $errors[(int) $site_id] = $result->get_error_message();
                }
            }

            if ( empty( $errors ) ) {
                delete_site_option( self::NETWORK_ERROR_OPTION );
            } else {
                update_site_option( self::NETWORK_ERROR_OPTION, $errors );
            }

            return;
        }

        $result = self::install_current_site();

        if ( ! is_wp_error( $result ) ) {
            add_option( 'formgent_activation_redirect', true );
        }
    }

    /**
     * Provision a newly initialized site when FormGent is network active.
     *
     * @param WP_Site $new_site Newly initialized site.
     * @param array   $args     Site initialization arguments.
     */
    public function initialize_site( WP_Site $new_site, array $args = [] ): void {
        unset( $args );

        if ( ! $this->is_network_active() ) {
            return;
        }

        $result = self::install_for_site( (int) $new_site->blog_id );

        if ( is_wp_error( $result ) ) {
            $errors                           = (array) get_site_option( self::NETWORK_ERROR_OPTION, [] );
            $errors[(int) $new_site->blog_id] = $result->get_error_message();
            update_site_option( self::NETWORK_ERROR_OPTION, $errors );
        }
    }

    /**
     * Add FormGent tables to WordPress's own site-deletion table list.
     */
    public function filter_site_drop_tables( array $tables, int $site_id ): array {
        // Drop FormGent's tables before wp_posts, which they reference.
        return array_values( array_unique( array_merge( self::get_site_tables( $site_id ), $tables ) ) );
    }

    /**
     * Flush only the current site's rewrite rules, after the CPT is registered.
     */
    public function maybe_flush_rewrite_rules(): void {
        if ( ! get_option( self::REWRITE_FLUSH_OPTION, false ) ) {
            return;
        }

        flush_rewrite_rules( false );
        delete_option( self::REWRITE_FLUSH_OPTION );
    }

    /**
     * Keep multisite PDF resources isolated because the controlling option is
     * site-scoped while WP_PLUGIN_DIR is shared by the whole network.
     */
    public function filter_pdf_library_base_dir( string $base_dir ): string {
        if ( ! is_multisite() ) {
            return $base_dir;
        }

        return trailingslashit( $base_dir ) . 'site-' . get_current_blog_id();
    }

    /**
     * Show a useful error instead of allowing an empty dashboard or fake token.
     */
    public function render_schema_notice(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $error = get_option( self::SCHEMA_ERROR_OPTION, '' );

        if ( empty( $error ) ) {
            return;
        }

        printf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s</p></div>',
            esc_html__( 'FormGent database setup is incomplete.', 'formgent' ),
            esc_html( $error )
        );
    }

    /**
     * Report sites that could not be provisioned during network activation.
     */
    public function render_network_notice(): void {
        if ( ! current_user_can( 'manage_network_options' ) ) {
            return;
        }

        $errors = (array) get_site_option( self::NETWORK_ERROR_OPTION, [] );

        if ( empty( $errors ) ) {
            return;
        }

        $site_ids = implode( ', ', array_map( 'absint', array_keys( $errors ) ) );

        printf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s</p></div>',
            esc_html__( 'FormGent could not provision every network site.', 'formgent' ),
            esc_html( sprintf( __( 'Run `wp formgent multisite repair`. Affected site IDs: %s', 'formgent' ), $site_ids ) )
        );
    }

    /**
     * Install the current site only when its schema version is stale.
     *
     * @return true|WP_Error
     */
    public static function maybe_install_current_site() {
        if ( self::SCHEMA_VERSION !== get_option( self::SCHEMA_VERSION_OPTION ) ) {
            return self::install_current_site();
        }

        $last_checked = (int) get_option( self::SCHEMA_CHECKED_OPTION, 0 );

        if ( $last_checked >= time() - DAY_IN_SECONDS ) {
            return true;
        }

        if ( self::is_current_site_schema_ready( true ) ) {
            update_option( self::SCHEMA_CHECKED_OPTION, time(), false );

            return true;
        }

        return self::install_current_site();
    }

    /**
     * Ensure the current site's schema exists, including recovery from a table
     * being removed after the version option was written.
     *
     * @return true|WP_Error
     */
    public static function ensure_current_site_schema() {
        if ( self::is_current_site_schema_ready() ) {
            return true;
        }

        return self::install_current_site();
    }

    /**
     * Run the full idempotent installer and verify its critical output.
     *
     * @return true|WP_Error
     */
    public static function install_current_site() {
        $blog_id = get_current_blog_id();

        if ( ! empty( self::$installation_attempted[$blog_id] ) ) {
            return self::schema_error_or_true();
        }

        self::$installation_attempted[$blog_id] = true;
        unset( self::$schema_ready[$blog_id] );

        Capabilities::install();
        ( new Setup() )->execute();

        $missing = self::get_current_site_schema_issues();

        if ( ! empty( $missing ) ) {
            $message = sprintf(
                /* translators: %s is a comma-separated list of missing database objects. */
                __( 'Missing database objects: %s', 'formgent' ),
                implode( ', ', $missing )
            );

            update_option( self::SCHEMA_ERROR_OPTION, $message, false );
            self::$schema_ready[$blog_id] = false;

            return new WP_Error( 'formgent_schema_incomplete', $message );
        }

        self::mark_included_migrations_complete();
        update_option( self::SCHEMA_VERSION_OPTION, self::SCHEMA_VERSION, false );
        update_option( self::SCHEMA_CHECKED_OPTION, time(), false );
        update_option( self::REWRITE_FLUSH_OPTION, 1, false );
        delete_option( self::SCHEMA_ERROR_OPTION );
        self::$schema_ready[$blog_id] = true;

        return true;
    }

    /**
     * Install a specific blog without leaking its context into the caller.
     *
     * @return true|WP_Error
     */
    public static function install_for_site( int $site_id ) {
        if ( $site_id <= 0 || ( is_multisite() && ! get_site( $site_id ) ) ) {
            return new WP_Error( 'formgent_invalid_site', __( 'The requested site does not exist.', 'formgent' ) );
        }

        $switched = get_current_blog_id() !== $site_id;

        if ( $switched && ! switch_to_blog( $site_id ) ) {
            return new WP_Error( 'formgent_site_switch_failed', __( 'Could not switch to the requested site.', 'formgent' ) );
        }

        try {
            return self::install_current_site();
        } finally {
            if ( $switched ) {
                restore_current_blog();
            }
        }
    }

    /**
     * Determine whether all required current-site tables and columns exist.
     */
    public static function is_current_site_schema_ready( bool $refresh = false ): bool {
        $blog_id = get_current_blog_id();

        if ( ! $refresh && array_key_exists( $blog_id, self::$schema_ready ) ) {
            return self::$schema_ready[$blog_id];
        }

        self::$schema_ready[$blog_id] = empty( self::get_current_site_schema_issues() );

        return self::$schema_ready[$blog_id];
    }

    /**
     * Return fully prefixed FormGent tables for a site.
     *
     * @return string[]
     */
    public static function get_site_tables( int $site_id ): array {
        global $wpdb;

        $prefix = is_multisite() ? $wpdb->get_blog_prefix( $site_id ) : $wpdb->prefix;

        return array_map(
            static function ( string $table ) use ( $prefix ): string {
                return $prefix . $table;
            },
            self::TABLES
        );
    }

    /**
     * WP-CLI: audit every site's forms and schema without changing data.
     *
     * ## OPTIONS
     *
     * [--site=<id>]
     * : Audit one site instead of the whole network.
     */
    public function audit( array $args, array $assoc_args ): void {
        unset( $args );
        $items = [];

        foreach ( $this->get_cli_site_ids( $assoc_args ) as $site_id ) {
            $items[] = $this->audit_site( $site_id );
        }

        \WP_CLI\Utils\format_items( 'table', $items, ['site_id', 'url', 'forms', 'schema', 'details'] );
    }

    /**
     * WP-CLI: idempotently provision or repair every requested site.
     *
     * ## OPTIONS
     *
     * [--site=<id>]
     * : Repair one site instead of the whole network.
     */
    public function repair( array $args, array $assoc_args ): void {
        unset( $args );
        $failed = [];

        foreach ( $this->get_cli_site_ids( $assoc_args ) as $site_id ) {
            $result = self::install_for_site( $site_id );

            if ( is_wp_error( $result ) ) {
                $failed[$site_id] = $result->get_error_message();
                \WP_CLI::warning( sprintf( 'Site %d: %s', $site_id, $result->get_error_message() ) );
                continue;
            }

            \WP_CLI::log( sprintf( 'Site %d: schema ready.', $site_id ) );
        }

        if ( ! empty( $failed ) ) {
            \WP_CLI::error( sprintf( 'FormGent repair failed for site IDs: %s', implode( ', ', array_keys( $failed ) ) ) );
        }

        delete_site_option( self::NETWORK_ERROR_OPTION );
        \WP_CLI::success( 'FormGent multisite repair completed.' );
    }

    /**
     * @return int[]
     */
    private function get_cli_site_ids( array $assoc_args ): array {
        if ( ! empty( $assoc_args['site'] ) ) {
            $site_id = absint( $assoc_args['site'] );

            if ( $site_id <= 0 || ( is_multisite() && ! get_site( $site_id ) ) ) {
                \WP_CLI::error( 'The requested site does not exist.' );
            }

            return [$site_id];
        }

        if ( ! is_multisite() ) {
            return [get_current_blog_id()];
        }

        return array_map(
            'absint',
            get_sites(
                [
                    'fields' => 'ids',
                    'number' => 0,
                ]
            )
        );
    }

    private function audit_site( int $site_id ): array {
        global $wpdb;

        $url      = is_multisite() ? get_home_url( $site_id ) : home_url();
        $switched = get_current_blog_id() !== $site_id;

        if ( $switched ) {
            switch_to_blog( $site_id );
        }

        try {
            $forms = (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
                    'formgent_form'
                )
            );

            $issues = self::get_current_site_schema_issues();
        } finally {
            if ( $switched ) {
                restore_current_blog();
            }
        }

        return [
            'site_id' => $site_id,
            'url'     => $url,
            'forms'   => $forms,
            'schema'  => empty( $issues ) ? 'ready' : 'incomplete',
            'details' => empty( $issues ) ? '-' : implode( ', ', $issues ),
        ];
    }

    /**
     * @return string[]
     */
    private static function get_current_site_schema_issues(): array {
        global $wpdb;

        $issues     = [];
        $table_like = $wpdb->esc_like( $wpdb->prefix . 'formgent_' ) . '%';
        $found      = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_like ) );

        foreach ( self::TABLES as $table_suffix ) {
            $table = $wpdb->prefix . $table_suffix;

            if ( ! in_array( $table, $found, true ) ) {
                $issues[] = $table;
                continue;
            }

            if ( empty( self::REQUIRED_COLUMNS[$table_suffix] ) ) {
                continue;
            }

            // Table names are assembled only from the trusted constants above.
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $columns = $wpdb->get_col( "SHOW COLUMNS FROM `{$table}`", 0 );

            foreach ( self::REQUIRED_COLUMNS[$table_suffix] as $column ) {
                if ( ! in_array( $column, $columns, true ) ) {
                    $issues[] = $table . '.' . $column;
                }
            }
        }

        return $issues;
    }

    private static function mark_included_migrations_complete(): void {
        $executed = (array) get_option( 'formgent_migrations', [] );
        $executed = array_values( array_unique( array_merge( $executed, self::INCLUDED_MIGRATIONS ) ) );
        update_option( 'formgent_migrations', $executed, false );
    }

    /**
     * @return true|WP_Error
     */
    private static function schema_error_or_true() {
        if ( self::is_current_site_schema_ready( true ) ) {
            return true;
        }

        $message = (string) get_option( self::SCHEMA_ERROR_OPTION, __( 'The FormGent database schema is incomplete.', 'formgent' ) );

        return new WP_Error( 'formgent_schema_incomplete', $message );
    }

    private function is_network_active(): bool {
        if ( ! is_multisite() ) {
            return false;
        }

        $active = (array) get_site_option( 'active_sitewide_plugins', [] );

        return isset( $active[plugin_basename( $this->plugin_file )] );
    }
}
