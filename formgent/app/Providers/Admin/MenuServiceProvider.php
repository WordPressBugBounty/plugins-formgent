<?php

namespace FormGent\App\Providers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Contracts\Provider;

class MenuServiceProvider implements Provider
{
    public function boot() {
        add_action( 'admin_menu', [$this, 'action_admin_menu'] );
        add_action( 'admin_head', [ $this, 'action_admin_head' ] );
        add_filter( 'plugin_action_links_formgent/formgent.php', [$this, 'plugin_action_links'] );
    }

    public function plugin_action_links( array $links ) {
        $all_forms = '<a href="' . esc_url( admin_url( 'admin.php?page=formgent' ) ) . '" title="' . esc_attr( __( 'All Forms', 'formgent' ) ) . '">' . esc_html( __( 'All Forms', 'formgent' ) ) . '</a>';
        $pro_url   = '<a href="https://formgent.com" target="_blank" title="' . esc_attr( __( 'Go Pro', 'formgent' ) ) . '" style="color: #f06060;font-weight:700">' . esc_html( __( 'Go Pro', 'formgent' ) ) . '</a>';

        array_unshift( $links, $all_forms );

        if ( ! function_exists( 'formgent_pro' ) ) {
            $links[] = $pro_url;
        }

        return $links;
    }

    /**
     * Loading menu activation js code only helpgent admin page
     */
    public function action_admin_head() : void {
        ?>
        <style>
            .wp-submenu-wrap a[href="https://formgent.com"] {
                color: #f06060 !important;
                font-weight: bold;
            }
        </style>
        <?php
    }

    public function action_admin_menu() {
        $page_url = admin_url( 'admin.php?page=formgent' );
        $icon_dir = formgent_dir( 'assets/svg/sidebar-icon.svg' );
        //phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $icon = file_get_contents( $icon_dir );
        $icon = 'data:image/svg+xml;base64,' . base64_encode( $icon );

        add_menu_page( esc_html__( 'FormGent', 'formgent' ), esc_html__( 'FormGent', 'formgent' ), 'manage_options', 'formgent-menu', function () { }, $icon, 5 );
        add_submenu_page( 'formgent-menu', esc_html__( 'All Forms', 'formgent' ), esc_html__( 'All Forms', 'formgent' ), 'manage_options', 'formgent', [$this, 'content'] );
        add_submenu_page( 'formgent-menu', esc_html__( 'Settings', 'formgent' ), esc_html__( 'Settings', 'formgent' ), 'manage_options', $page_url . '#/settings' );

        if ( ! function_exists( 'formgent_pro' ) ) {
            add_submenu_page( 'formgent-menu', esc_html__( 'Go Pro', 'formgent' ), esc_html__( 'Go Pro', 'formgent' ), 'manage_options', 'https://formgent.com' );
        }

        remove_submenu_page( 'formgent-menu', 'formgent-menu' );
    }

    public function content() {
        echo '<div class="formgent-root"></div>';
    }
}