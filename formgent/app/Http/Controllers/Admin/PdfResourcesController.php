<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( "ABSPATH" ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Repositories\FormRepository;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

/**
 * Handles download, extraction, and activation of PDF resources (composer vendor, fonts).
 * Stores the library path in the global option formgent_pdf_library_path (shared by all forms).
 */
class PdfResourcesController extends Controller {
    private const ARCHIVE_URL = 'https://github.com/sovware/formgent-pdf-lib/releases/download/v1.0.0/pdf-resources.zip';

    private const ARCHIVE_SHA256 = 'd044b04e11b01f488599d7460930393cd0df3b798de217c32826149220356a61';

    /** @var resource|null Process-held filesystem lock. */
    private $library_lock_handle = null;

    private string $library_lock_token = '';

    private string $library_lock_path = '';

    public FormRepository $form_repository;

    public function __construct( FormRepository $form_repository ) {
        $this->form_repository = $form_repository;
    }

    /**
     * Install PDF resources: extract zip, verify vendor/autoload.php, save path to form settings.
     *
     * @param WP_REST_Request $request
     * @param Validator       $validator
     * @return array
     */
    public function install( WP_REST_Request $request, Validator $validator ) {
        if ( ! current_user_can( 'install_plugins' ) ) {
            return Response::send( ['message' => esc_html__( 'You are not allowed to install runtime libraries.', 'formgent' )], 403 );
        }

        $validator->validate( $this->get_validation_rules() );

        $form_error = $this->resolve_form_or_fail( $request );
        if ( $form_error !== null ) {
            return $form_error;
        }

        $base_dir = trailingslashit( WP_PLUGIN_DIR ) . 'formgent-libraries/pdf';
        $base_dir = untrailingslashit( (string) apply_filters( 'formgent_pdf_library_base_dir', $base_dir ) );

        if ( ! $this->is_safe_library_path( $base_dir ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'The PDF resources directory is invalid.', 'formgent' ),
                ],
                400
            );
        }

        $lock_token = $this->acquire_library_lock( $base_dir );
        if ( null === $lock_token ) {
            return Response::send(
                [
                    'message' => esc_html__( 'A PDF resources operation is already in progress.', 'formgent' ),
                ],
                409
            );
        }

        $zip_file = '';
        $staging  = '';

        try {
            $legacy_archive_url = (string) apply_filters( 'formgent_pdf_resources_zip_path', self::ARCHIVE_URL );
            $archive            = apply_filters(
                'formgent_pdf_resources_archive',
                [
                    'url'    => $legacy_archive_url,
                    'sha256' => self::ARCHIVE_SHA256,
                ]
            );
            $zip_file           = $this->download_verified_archive( $archive );

            if ( empty( $zip_file ) || ! is_readable( $zip_file ) ) {
                return Response::send(
                    [
                        'message' => esc_html__( 'The verified PDF resources archive could not be downloaded.', 'formgent' ),
                    ],
                    400
                );
            }

            if ( ! wp_mkdir_p( dirname( $base_dir ) ) ) {
                return Response::send(
                    [
                        'message' => esc_html__( 'Could not create the PDF resources parent directory.', 'formgent' ),
                    ],
                    500
                );
            }

            $suffix  = sanitize_key( wp_generate_uuid4() );
            $staging = $base_dir . '-staging-' . $suffix;
            $backup  = $base_dir . '-backup-' . $suffix;

            if ( ! wp_mkdir_p( $staging ) ) {
                return Response::send(
                    [
                        'message' => esc_html__( 'Could not create the PDF resources staging directory.', 'formgent' ),
                    ],
                    500
                );
            }

            if ( ! $this->unzip_to( $zip_file, $staging ) ) {
                $this->delete_directory( $staging );
                return Response::send(
                    [
                        'message' => esc_html__( 'Failed to extract PDF resources archive.', 'formgent' ),
                    ],
                    500
                );
            }

            $staged_library_root = $this->find_library_root( $staging );
            if ( empty( $staged_library_root ) ) {
                $this->delete_directory( $staging );
                return Response::send(
                    [
                        'message' => esc_html__( 'Verification failed: vendor/autoload.php not found after extraction.', 'formgent' ),
                    ],
                    500
                );
            }

            $relative_root = ltrim( substr( wp_normalize_path( $staged_library_root ), strlen( wp_normalize_path( $staging ) ) ), '/' );
            $had_existing  = is_dir( $base_dir );
            $previous_root = formgent_get_pdf_library_path();

            if ( $had_existing && ! $this->move_directory( $base_dir, $backup ) ) {
                $this->delete_directory( $staging );
                return Response::send(
                    [
                        'message' => esc_html__( 'Could not prepare the existing PDF resources for replacement.', 'formgent' ),
                    ],
                    500
                );
            }

            if ( ! $this->move_directory( $staging, $base_dir ) ) {
                $restored = true;
                if ( $had_existing ) {
                    $restored = $this->restore_library_backup( $backup, $base_dir, $previous_root );
                }
                $this->delete_directory( $staging );
                return Response::send(
                    [
                        'message' => $restored
                            ? esc_html__( 'Could not activate the new PDF resources.', 'formgent' )
                            : esc_html__( 'Could not activate the new PDF resources. The previous library was preserved in its recovery location.', 'formgent' ),
                    ],
                    500
                );
            }

            $library_root = $base_dir . ( '' !== $relative_root ? '/' . $relative_root : '' );
            if ( ! is_readable( $library_root . '/vendor/autoload.php' ) ) {
                $this->delete_directory( $base_dir );
                $restored = true;
                if ( $had_existing ) {
                    $restored = $this->restore_library_backup( $backup, $base_dir, $previous_root );
                }
                return Response::send(
                    [
                        'message' => $restored
                            ? esc_html__( 'The new PDF resources failed final verification.', 'formgent' )
                            : esc_html__( 'The new PDF resources failed final verification. The previous library was preserved in its recovery location.', 'formgent' ),
                    ],
                    500
                );
            }

            if ( ! $this->persist_library_path( $library_root ) ) {
                $this->delete_directory( $base_dir );
                $restored = true;
                if ( $had_existing ) {
                    $restored = $this->restore_library_backup( $backup, $base_dir, $previous_root );
                }

                return Response::send(
                    [
                        'message' => $restored
                            ? esc_html__( 'Could not save the new PDF resources configuration. The previous library was restored.', 'formgent' )
                            : esc_html__( 'Could not save the new PDF resources configuration. The previous library was preserved in its recovery location.', 'formgent' ),
                    ],
                    500
                );
            }

            if ( $had_existing ) {
                $this->delete_directory( $backup );
            }

            return Response::send(
                [
                    'success'        => true,
                    'message'        => esc_html__( 'PDF resources installed successfully.', 'formgent' ),
                    'pdf_generation' => [
                        'is_library_exist' => true,
                    ],
                ]
            );
        } finally {
            if ( '' !== $zip_file && is_file( $zip_file ) ) {
                wp_delete_file( $zip_file );
            }
            if ( '' !== $staging && is_dir( $staging ) ) {
                $this->delete_directory( $staging );
            }
            $this->release_library_lock( $base_dir, $lock_token );
        }
    }

    /**
     * Remove PDF library: clear global pdf_library_path option and delete the extracted directory.
     *
     * @param WP_REST_Request $request
     * @param Validator       $validator
     * @return array
     */
    public function delete( WP_REST_Request $request, Validator $validator ) {
        if ( ! current_user_can( 'install_plugins' ) ) {
            return Response::send( ['message' => esc_html__( 'You are not allowed to remove runtime libraries.', 'formgent' )], 403 );
        }

        $validator->validate( $this->get_validation_rules() );

        $form_error = $this->resolve_form_or_fail( $request );
        if ( $form_error !== null ) {
            return $form_error;
        }

        $base_dir = trailingslashit( WP_PLUGIN_DIR ) . 'formgent-libraries/pdf';
        $base_dir = untrailingslashit( (string) apply_filters( 'formgent_pdf_library_base_dir', $base_dir ) );

        if ( ! $this->is_safe_library_path( $base_dir ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'The PDF resources directory is invalid.', 'formgent' ),
                ],
                400
            );
        }

        $lock_token = $this->acquire_library_lock( $base_dir );
        if ( null === $lock_token ) {
            return Response::send(
                [
                    'message' => esc_html__( 'A PDF resources operation is already in progress.', 'formgent' ),
                ],
                409
            );
        }

        try {
            $previous_root = formgent_get_pdf_library_path();
            $deleting      = '';

            if ( is_dir( $base_dir ) ) {
                $deleting = $base_dir . '-deleting-' . sanitize_key( wp_generate_uuid4() );

                if ( ! $this->move_directory( $base_dir, $deleting ) ) {
                    return Response::send(
                        [
                            'message' => esc_html__( 'Could not prepare the PDF resources directory for removal.', 'formgent' ),
                        ],
                        500
                    );
                }
            }

            if ( ! $this->persist_library_path( '' ) ) {
                $restored = '' === $deleting || $this->move_directory( $deleting, $base_dir );
                if ( $restored ) {
                    $restored = $this->persist_library_path( $previous_root );
                }

                return Response::send(
                    [
                        'message' => $restored
                            ? esc_html__( 'Could not clear the PDF resources configuration. The existing library was restored.', 'formgent' )
                            : esc_html__( 'Could not clear the PDF resources configuration. The existing library was preserved in its recovery location.', 'formgent' ),
                    ],
                    500
                );
            }

            if ( '' !== $deleting && ! $this->delete_directory( $deleting ) ) {
                return Response::send(
                    [
                        'message' => esc_html__( 'The PDF resources were deactivated, but their recovery directory could not be fully removed.', 'formgent' ),
                    ],
                    500
                );
            }

            return Response::send(
                [
                    'success'        => true,
                    'message'        => esc_html__( 'PDF library removed successfully.', 'formgent' ),
                    'pdf_generation' => [
                        'is_library_exist' => false,
                    ],
                ]
            );
        } finally {
            $this->release_library_lock( $base_dir, $lock_token );
        }
    }

    /**
     * Resolve form_id from the request and return a 404 response if the form doesn't exist.
     * Returns null on success (form found), or a Response array on failure.
     *
     * @param WP_REST_Request $request
     * @return array|null Null if form exists, error response array otherwise.
     */
    private function resolve_form_or_fail( WP_REST_Request $request ) {
        $form_id = absint( $request->get_param( 'form_id' ) );
        if ( $form_id === 0 ) {
            $form_id = absint( $request->get_param( 'id' ) );
        }

        $form = $this->form_repository->get_by_id( $form_id );
        if ( ! $form ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Form not found.', 'formgent' ),
                ],
                404
            );
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    protected function get_validation_rules() {
        return [
            'form_id' => 'numeric',
        ];
    }

    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $dir Absolute path to directory.
     * @return bool True on success.
     */
    private function delete_directory( $dir, ?string $root_real = null ) {
        if ( is_link( $dir ) ) {
            return is_writable( dirname( $dir ) ) && unlink( $dir );
        }

        if ( ! is_dir( $dir ) ) {
            return ! file_exists( $dir );
        }

        $current_real = realpath( $dir );
        if ( false === $current_real ) {
            return false;
        }

        $current_real = untrailingslashit( wp_normalize_path( $current_real ) );
        $root_real    = null === $root_real ? $current_real : untrailingslashit( wp_normalize_path( $root_real ) );

        if ( $current_real !== $root_real && 0 !== strpos( $current_real, trailingslashit( $root_real ) ) ) {
            return false;
        }

        $scanned = scandir( $dir );
        if ( false === $scanned ) {
            return false;
        }

        $items = array_diff( $scanned, [ '.', '..' ] );
        foreach ( $items as $item ) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;

            // Inspect the directory entry itself before is_dir(), which follows links.
            if ( false === lstat( $path ) ) {
                return false;
            }

            if ( is_link( $path ) ) {
                if ( ! is_writable( $dir ) || ! unlink( $path ) ) {
                    return false;
                }
                continue;
            }

            if ( is_dir( $path ) ) {
                if ( ! $this->delete_directory( $path, $root_real ) ) {
                    return false;
                }
                continue;
            }

            if ( file_exists( $path ) && ( ! is_writable( $path ) || ! unlink( $path ) ) ) {
                return false;
            }
        }

        if ( is_writable( $dir ) ) {
            return rmdir( $dir );
        }

        return false;
    }

    /** Move a complete library tree without exposing a partially copied replacement. */
    private function move_directory( string $source, string $destination ): bool {
        if ( file_exists( $destination ) ) {
            return false;
        }

        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Rename failure is handled and rolled back below.
        return @rename( $source, $destination );
    }

    /** Acquire an OS-level mutex shared by every site using this physical library. */
    private function acquire_library_lock( string $base_dir ): ?string {
        $token     = wp_generate_uuid4();
        $lock_path = trailingslashit( WP_PLUGIN_DIR ) . '.formgent-pdf-library-' . md5( wp_normalize_path( $base_dir ) ) . '.lock';

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen, WordPress.PHP.NoSilencedErrors.Discouraged -- flock requires a persistent local file handle; failures are handled below.
        $handle = @fopen( $lock_path, 'c' );

        if ( false === $handle || ! flock( $handle, LOCK_EX | LOCK_NB ) ) {
            if ( is_resource( $handle ) ) {
                fclose( $handle );
            }

            return null;
        }

        ftruncate( $handle, 0 );
        fwrite( $handle, $token );
        fflush( $handle );

        $this->library_lock_handle = $handle;
        $this->library_lock_token  = $token;
        $this->library_lock_path   = $lock_path;

        return $token;
    }

    /** Release the mutex only when this request still owns it. */
    private function release_library_lock( string $base_dir, string $token ): void {
        $expected_path = trailingslashit( WP_PLUGIN_DIR ) . '.formgent-pdf-library-' . md5( wp_normalize_path( $base_dir ) ) . '.lock';

        if ( is_resource( $this->library_lock_handle ) && hash_equals( $this->library_lock_token, $token ) && hash_equals( $this->library_lock_path, $expected_path ) ) {
            flock( $this->library_lock_handle, LOCK_UN );
            fclose( $this->library_lock_handle );
        }

        $this->library_lock_handle = null;
        $this->library_lock_token  = '';
        $this->library_lock_path   = '';
    }

    /** Restore the old directory, or repoint the option to its readable backup. */
    private function restore_library_backup( string $backup, string $base_dir, string $previous_root ): bool {
        if ( $this->move_directory( $backup, $base_dir ) ) {
            return $this->persist_library_path( $previous_root );
        }

        $recovery_root = $this->find_library_root( $backup );

        if ( '' !== $previous_root && 0 === strpos( wp_normalize_path( $previous_root ), trailingslashit( wp_normalize_path( $base_dir ) ) ) ) {
            $relative_root = ltrim( substr( wp_normalize_path( $previous_root ), strlen( wp_normalize_path( $base_dir ) ) ), '/' );
            $candidate     = $backup . ( '' !== $relative_root ? '/' . $relative_root : '' );

            if ( is_readable( $candidate . '/vendor/autoload.php' ) ) {
                $recovery_root = $candidate;
            }
        }

        if ( '' !== $recovery_root ) {
            $this->persist_library_path( $recovery_root );
        }

        return false;
    }

    /** Persist the active library path and verify the normalized option readback. */
    private function persist_library_path( string $path ): bool {
        formgent_set_pdf_library_path( $path );

        $raw_stored = get_option( 'formgent_pdf_library_path', '' );
        if ( '' === $path ) {
            return is_string( $raw_stored ) && '' === trim( $raw_stored );
        }

        $expected = realpath( $path );
        $stored   = formgent_get_pdf_library_path();

        return is_string( $raw_stored )
            && '' !== trim( $raw_stored )
            && false !== $expected
            && hash_equals( wp_normalize_path( $expected ), wp_normalize_path( $stored ) );
    }

    /** Ensure filters cannot point destructive operations outside the plugins directory. */
    private function is_safe_library_path( string $path ): bool {
        $plugins_path = trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) );
        $plugins_real = realpath( WP_PLUGIN_DIR );
        $path         = trailingslashit( wp_normalize_path( $path ) );

        if ( false === $plugins_real || $path === $plugins_path || 0 !== strpos( $path, $plugins_path ) || preg_match( '#(?:^|/)\.\.(?:/|$)#', $path ) ) {
            return false;
        }

        // Resolve the nearest existing ancestor so a symlink cannot redirect
        // installation or recursive cleanup outside WP_PLUGIN_DIR.
        $ancestor = untrailingslashit( $path );
        while ( false === realpath( $ancestor ) && dirname( $ancestor ) !== $ancestor ) {
            $ancestor = dirname( $ancestor );
        }

        $real_ancestor = realpath( $ancestor );
        $plugins_real  = trailingslashit( wp_normalize_path( $plugins_real ) );

        return false !== $real_ancestor
            && 0 === strpos( trailingslashit( wp_normalize_path( $real_ancestor ) ), $plugins_real );
    }

    /**
     * Find the directory that contains vendor/autoload.php after extraction.
     * Checks base_dir first, then a single subdirectory (e.g. when zip has a root folder like pdf-resources/).
     *
     * @param string $base_dir Extracted zip base directory.
     * @return string Absolute path to library root, or empty if not found.
     */
    private function find_library_root( $base_dir ) {
        $autoload = $base_dir . '/vendor/autoload.php';
        if ( is_readable( $autoload ) ) {
            return $base_dir;
        }
        $subdirs = glob( $base_dir . '/*', GLOB_ONLYDIR );
        if ( is_array( $subdirs ) ) {
            foreach ( $subdirs as $subdir ) {
                if ( is_readable( $subdir . '/vendor/autoload.php' ) ) {
                    return $subdir;
                }
            }
        }
        return '';
    }

    /** Download an allowlisted archive and verify it before extraction. */
    private function download_verified_archive( $archive ): string {
        if ( ! is_array( $archive ) || empty( $archive['url'] ) || empty( $archive['sha256'] ) ) {
            return '';
        }

        $url    = esc_url_raw( (string) $archive['url'] );
        $sha256 = strtolower( sanitize_text_field( (string) $archive['sha256'] ) );

        if ( ! wp_http_validate_url( $url ) || ! preg_match( '/^[a-f0-9]{64}$/', $sha256 ) ) {
            return '';
        }

        if ( ! function_exists( 'download_url' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $temporary_file = download_url( $url, 60 );

        if ( is_wp_error( $temporary_file ) ) {
            return '';
        }

        $actual_sha256 = hash_file( 'sha256', $temporary_file );

        if ( ! is_string( $actual_sha256 ) || ! hash_equals( $sha256, $actual_sha256 ) ) {
            wp_delete_file( $temporary_file );
            return '';
        }

        return $temporary_file;
    }

    /**
     * Extract zip file to destination directory.
     *
     * @param string $zip_path   Path to the zip file.
     * @param string $dest_dir   Destination directory (must exist).
     * @return bool True on success, false on failure.
     */
    private function unzip_to( $zip_path, $dest_dir ) {
        if ( empty( $zip_path ) || empty( $dest_dir ) ) {
            return false;
        }

        if ( ! function_exists( 'unzip_file' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();

        $result = unzip_file( $zip_path, $dest_dir );
        if ( is_wp_error( $result ) ) {
            return false;
        }

        return true;
    }
}
