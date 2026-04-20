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
        $validator->validate( $this->get_validation_rules() );

        $form_error = $this->resolve_form_or_fail( $request );
        if ( $form_error !== null ) {
            return $form_error;
        }

        $default_zip_path = 'https://github.com/sovware/formgent-pdf-lib/releases/download/v1.0.0/pdf-resources.zip';

        // Allow filtering the zip path before validation so the filter result is also validated.
        $zip_path = (string) apply_filters( 'formgent_pdf_resources_zip_path', $request->get_param( 'zip_path' ) ?: $default_zip_path );

        // Reject local paths that escape ABSPATH; remote URLs are always allowed.
        if ( ! empty( $zip_path ) && ! wp_http_validate_url( $zip_path ) && ! $this->is_valid_zip_path( $zip_path ) ) {
            $zip_path = '';
        }

        $is_remote_zip = function_exists( 'wp_http_validate_url' ) && wp_http_validate_url( $zip_path );
        $zip_file      = $this->resolve_zip_file( $zip_path );
        if ( empty( $zip_file ) || ! is_readable( $zip_file ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'PDF resources zip not found or could not be downloaded. Enter a valid local path above, or ensure the remote URL is reachable.', 'formgent' ),
                ],
                400
            );
        }

        $base_dir = trailingslashit( WP_PLUGIN_DIR ) . 'formgent-libraries/pdf';
        $base_dir = apply_filters( 'formgent_pdf_library_base_dir', $base_dir );

        if ( ! wp_mkdir_p( $base_dir ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Could not create formgent-libraries/pdf directory.', 'formgent' ),
                ],
                500
            );
        }

        $extracted = $this->unzip_to( $zip_file, $base_dir );
        if ( $is_remote_zip && is_file( $zip_file ) ) {
            if ( is_writable( $zip_file ) ) {
                unlink( $zip_file );
            }
        }
        if ( ! $extracted ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Failed to extract PDF resources archive.', 'formgent' ),
                ],
                500
            );
        }

        $library_root = $this->find_library_root( $base_dir );
        if ( empty( $library_root ) ) {
            return Response::send(
                [
                    'message' => esc_html__( 'Verification failed: vendor/autoload.php not found after extraction.', 'formgent' ),
                ],
                500
            );
        }

        formgent_set_pdf_library_path( $library_root );

        return Response::send(
            [
                'success'        => true,
                'message'        => esc_html__( 'PDF resources installed successfully.', 'formgent' ),
                'pdf_generation' => [
                    'is_library_exist' => true,
                ],
            ]
        );
    }

    /**
     * Remove PDF library: clear global pdf_library_path option and delete the extracted directory.
     *
     * @param WP_REST_Request $request
     * @param Validator       $validator
     * @return array
     */
    public function delete( WP_REST_Request $request, Validator $validator ) {
        $validator->validate( $this->get_validation_rules() );

        $form_error = $this->resolve_form_or_fail( $request );
        if ( $form_error !== null ) {
            return $form_error;
        }

        $base_dir = trailingslashit( WP_PLUGIN_DIR ) . 'formgent-libraries/pdf';
        $base_dir = apply_filters( 'formgent_pdf_library_base_dir', $base_dir );

        $plugins_dir = wp_normalize_path( WP_PLUGIN_DIR );
        $real        = realpath( $base_dir );
        if ( $real !== false && strpos( wp_normalize_path( $real ), $plugins_dir ) === 0 && is_dir( $base_dir ) ) {
            $this->delete_directory( $base_dir );
        }

        formgent_set_pdf_library_path( '' );

        return Response::send(
            [
                'success'        => true,
                'message'        => esc_html__( 'PDF library removed successfully.', 'formgent' ),
                'pdf_generation' => [
                    'is_library_exist' => false,
                ],
            ]
        );
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
    private function delete_directory( $dir ) {
        if ( ! is_dir( $dir ) ) {
            return true;
        }
        $items = array_diff( scandir( $dir ), [ '.', '..' ] );
        foreach ( $items as $item ) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if ( is_dir( $path ) ) {
                $this->delete_directory( $path );
            } else {
                if ( is_file( $path ) && is_writable( $path ) ) {
                    unlink( $path );
                }
            }
        }
        if ( is_writable( $dir ) ) {
            return rmdir( $dir );
        }
        return false;
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

    /**
     * Resolve path to the actual zip file: if path is a file, return it; if it's a folder, return folder/pdf-resources.zip.
     *
     * @param string $path Path to zip file or folder containing pdf-resources.zip.
     * @return string Empty if not found, else absolute path to the zip file.
     */
    private function resolve_zip_file( $path ) {
        $path = wp_normalize_path( trim( $path ) );
        if ( empty( $path ) ) {
            return '';
        }
        // If path is a URL, download to a temp file.
        if ( function_exists( 'wp_http_validate_url' ) && wp_http_validate_url( $path ) ) {
            if ( ! function_exists( 'download_url' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            $tmp = download_url( $path );
            if ( is_wp_error( $tmp ) ) {
                return '';
            }
            return $tmp;
        }
        // If path is a file and readable, use it.
        if ( is_file( $path ) && is_readable( $path ) ) {
            return $path;
        }
        // If path is a directory, look for pdf-resources.zip inside.
        if ( is_dir( $path ) ) {
            $file = trailingslashit( $path ) . 'pdf-resources.zip';
            return is_readable( $file ) ? $file : '';
        }
        return '';
    }

    /**
     * Check that path is under ABSPATH (allowed for security).
     *
     * @param string $path Path to zip or folder.
     * @return bool
     */
    private function is_valid_zip_path( $path ) {
        $path = wp_normalize_path( trim( $path ) );
        if ( empty( $path ) ) {
            return false;
        }
        $abs       = wp_normalize_path( ABSPATH );
        $real_path = realpath( $path );
        $real_dir  = is_dir( $path ) ? $real_path : realpath( dirname( $path ) );
        if ( $real_path === false ) {
            $real_path = realpath( dirname( $path ) );
            if ( $real_path !== false ) {
                $real_path = trailingslashit( $real_path ) . basename( $path );
            }
        }
        $base = $real_path !== false ? $real_path : $path;
        return strpos( wp_normalize_path( $base ), $abs ) === 0;
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
