<?php

use FormGent\WpMVC\Enqueue\Enqueue;

defined( 'ABSPATH' ) || exit;

$data = [
    'baseUrl'     => false,
    'locale'      => determine_locale(),
    'domainMap'   => [],
    'domainPaths' => [],
];

$lang_dir    = WP_LANG_DIR;
$content_dir = WP_CONTENT_DIR;
$abspath     = ABSPATH;

// Note: str_starts_with() is not used here, as wp-includes/compat.php may not be loaded at this point.
if ( strpos( $lang_dir, $content_dir ) === 0 ) {
    $data['baseUrl'] = content_url( substr( trailingslashit( $lang_dir ), strlen( trailingslashit( $content_dir ) ) ) );
} elseif ( strpos( $lang_dir, $abspath ) === 0 ) {
    $data['baseUrl'] = site_url( substr( trailingslashit( $lang_dir ), strlen( untrailingslashit( $abspath ) ) ) );
}

Enqueue::script( 'formgent/i18n', 'build/js/i18n-loader' );

$data['domainMap']   = (object) $data['domainMap']; // Ensure it becomes a json object.
$data['domainPaths'] = (object) $data['domainPaths']; // Ensure it becomes a json object.

wp_add_inline_script( 'formgent/i18n', 'if (typeof wp.formgent18nLoader === "undefined") { wp.formgent18nLoader = {}; } wp.formgent18nLoader.state = ' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES ) . ';' );
