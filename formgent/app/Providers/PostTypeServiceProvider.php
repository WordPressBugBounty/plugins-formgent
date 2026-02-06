<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\View\View;
use FormGent\WpMVC\Contracts\Provider;
use WP_Post;

class PostTypeServiceProvider implements Provider {
    public function boot() {
        add_action( 'init', [self::class, 'register_post_type'] );
        add_filter( 'allowed_block_types_all', [$this, 'allow_blocks_for_formgent_form'], 10, 2 );
        add_filter( 'the_content', [$this, 'filter_the_content'] );
        add_filter( 'block_categories_all', [$this, 'filter_block_categories_all'] );
        add_action( 'admin_init', [$this, 'redirect_to_forms_page'] );
        add_action( 'template_redirect', [$this, 'restrict_formgent_permalink_access'] );
        add_filter( 'template_include', [ $this, 'filter_template_include' ], 999999999 );
        add_action( 'admin_init', [$this, 'handle_plugin_activation_redirect'] );
        add_action( 'send_headers', [$this, 'embed_header_compatibility'], 999999999 );
        add_action( 'post_updated', [$this, 'remove_form_cache'] );
        add_filter( 'display_post_states', [$this, 'page_post_states'], 10, 2 );
    }

    public function page_post_states( $post_states, $post ) {
        if ( 'page' === $post->post_type && formgent_is_payment_enabled() ) {
            $settings = formgent_get_setting( "payment" );
            if ( $settings['success_page'] == $post->ID ) {
                $post_states['formgent_payment_success'] = __( 'FormGent Payment Success', 'formgent' );
            } elseif ( $settings['failed_page'] == $post->ID ) {
                $post_states['formgent_payment_failed'] = __( 'FormGent Payment Failed', 'formgent' );
            }
        }
        return $post_states;
    }

    public function remove_form_cache( $form_id ) : void {
        wp_cache_delete( "form_{$form_id}_fields", "formgent" );
    }

    public function embed_header_compatibility() {
        global $post;

        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! $post instanceof WP_Post || $post->post_type !==  formgent_post_type()  || ! isset( $_GET['embed'] ) ) {
            return;
        }

        header_remove( 'X-Frame-Options' );
    }

    public function handle_plugin_activation_redirect() {
        $redirect = apply_filters( 'formgent_redirect_after_activation', get_option( 'formgent_activation_redirect', false ) );

        // Check if the transient exists and the user has the necessary permissions
        if ( $redirect ) {
            // Delete the option to avoid repeated redirects
            delete_option( 'formgent_activation_redirect' );

            //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( ! isset( $_GET['activate-multi'] ) ) { // Prevent redirect if multiple plugins are activated
                wp_safe_redirect( admin_url( 'admin.php?page=formgent' ) );
                exit;
            }
        }
    }

    public function restrict_formgent_permalink_access() {
        if ( ! is_singular( formgent_post_type() ) || current_user_can( 'administrator' ) ) {
            return;
        }

        $form_settings = formgent_form_repository()->get_settings( get_post()->ID );

        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['embed'] ) || formgent_get_nested_value( 'design.status', $form_settings, false ) ) {
            return;
        }

        wp_safe_redirect( wp_login_url( get_permalink() ) );
        exit;
    }

    public function filter_template_include( string $template ) : string {
        if ( ! is_singular( formgent_post_type() ) ) {
            return $template;
        }

        wp_enqueue_script_module( 'formgent/blocks-frontend' );

        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_GET['embed'] ) ) {
            return formgent_dir( 'resources/views/embed.php' );
        }

        return formgent_dir( 'resources/views/share-view.php' );
    }

    public function redirect_to_forms_page() {
        global $pagenow;

        //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( $pagenow !== 'edit.php' || empty( $_GET['post_type'] ) || 'formgent_form' !== $_GET['post_type'] ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=formgent' ) );
        exit();
    }

    /**
     * Filters the default array of categories for block types.
     *
     * @param array[] $block_categories Array of categories for block types.
     * @return array[] Array of categories for block types.
     */
    public function filter_block_categories_all( array $block_categories ) : array {
        $block_categories[] = [
            'slug'  => 'formgent',
            'title' => esc_html__( 'FormGent', 'formgent' ),
            'icon'  => ''
        ];

        $block_categories[] = [
            'slug'  => 'formgent-payment',
            'title' => esc_html__( 'FormGent Payment Fields', 'formgent' ),
            'icon'  => ''
        ];
        return $block_categories;
    }

    public function filter_the_content( string $content ) : string {
        global $post;

        if ( $post->post_type !==  formgent_post_type() ) {
            return $content;
        }

        return View::get(
            'form', [
                'form'   => $post,
                'fields' => $content
            ]
        );
    }

    public static function register_post_type() : void {
        $labels = [
            'name'                  => _x( 'Forms', 'Post type general name', 'formgent' ),
            'singular_name'         => _x( 'Form', 'Post type singular name', 'formgent' ),
            'menu_name'             => _x( 'Formgent Forms', 'Admin Menu text', 'formgent' ),
            'name_admin_bar'        => _x( 'Formgent Form', 'Add New on Toolbar', 'formgent' ),
            'add_new'               => __( 'Add New', 'formgent' ),
            'add_new_item'          => __( 'Add New Form', 'formgent' ),
            'new_item'              => __( 'New Form', 'formgent' ),
            'edit_item'             => __( 'Edit Form', 'formgent' ),
            'view_item'             => __( 'View Form', 'formgent' ),
            'all_items'             => __( 'All Forms', 'formgent' ),
            'search_items'          => __( 'Search Forms', 'formgent' ),
            'parent_item_colon'     => __( 'Parent Forms:', 'formgent' ),
            'not_found'             => __( 'No Forms found.', 'formgent' ),
            'not_found_in_trash'    => __( 'No Forms found in Trash.', 'formgent' ),
            'featured_image'        => _x( 'Form Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'formgent' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'formgent' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'formgent' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'formgent' ),
            'archives'              => _x( 'Form archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'formgent' ),
            'insert_into_item'      => _x( 'Insert into Form', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'formgent' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this Form', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'formgent' ),
            'filter_items_list'     => _x( 'Filter Forms list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'formgent' ),
            'items_list_navigation' => _x( 'Forms list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'formgent' ),
            'items_list'            => _x( 'Forms list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'formgent' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'form' ],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => ['title', 'editor', 'author', 'custom-fields' ],
            'show_in_rest'       => true, // Enables Gutenberg editor support
        ];

        register_post_type( formgent_post_type(), $args );
        // Register the meta field
        register_post_meta(
            'formgent_form', '_formgent_fields', [
                'show_in_rest'  => [
                    'schema' => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object', // Allow object-based array items
                            'properties' => [
                                'type'        => [
                                    'type' => 'string',
                                ],
                                'label'       => [
                                    'type' => 'string',
                                ],
                                'required'    => [
                                    'type' => 'boolean',
                                ],
                                'placeholder' => [
                                    'type' => 'string',
                                ],
                                'options'     => [
                                    'type' => 'array'
                                ],
                                'group'       => [
                                    'type' => 'string'
                                ]
                            ],
                            'required'   => ['type', 'label'], // Fields that must be present in each object
                        ],
                    ],
                ],
                'type'          => 'array', // Define type as array
                'single'        => true, // One value per post
                'default'       => [], // Default is an empty array
                'auth_callback' => function () {
                    return current_user_can( 'edit_posts' );
                },
            ]
        );

        register_post_meta(
            'formgent_form', '_formgent_page_break_settings', [
                'show_in_rest'  => [
                    'schema' => [
                        'type'       => 'object', // Allow object-based array items
                        'properties' => [
                            'next_button_style'        => [
                                'type' => 'string',
                            ],
                            'next_button_bg_color'     => [
                                'type' => 'string',
                            ],
                            'next_button_border_color' => [
                                'type' => 'string',
                            ],
                            'next_button_text_color'   => [
                                'type' => 'string',
                            ],
                            'back_button_style'        => [
                                'type' => 'string'
                            ],
                            'back_button_bg_color'     => [
                                'type' => 'string'
                            ],
                            'back_button_border_color' => [
                                'type' => 'string'
                            ],
                            'back_button_text_color'   => [
                                'type' => 'string'
                            ],
                            'progress_indicator'       => [
                                'type' => 'string'
                            ],
                            'show_labels'              => [
                                'type' => 'boolean'
                            ]
                        ],
                        'required'   => [],
                    ]
                ],
                'type'          => 'object', // Define type as array
                'single'        => true, // One value per post
                'default'       =>
                [
                    "next_button_style"        => "default",
                    "next_button_bg_color"     => "#4cafaa",
                    "next_button_border_color" => "#000000",
                    "next_button_text_color"   => "#000000",
                    "back_button_style"        => "default",
                    "back_button_bg_color"     => "#4cafaa",
                    "back_button_border_color" => "#000000",
                    "back_button_text_color"   => "#000000",
                    "progress_indicator"       => "progress_bar",
                    "show_labels"              => true
                ],
                'auth_callback' => function () {
                    return current_user_can( 'edit_posts' );
                },
            ]
        );
        register_post_meta(
            formgent_post_type(), '_formgent_form_settings', [
                'type'          => 'object',
                'single'        => true,
                'show_in_rest'  => [
                    'schema' => [
                        'type'                 => 'object',
                        'additionalProperties' => true, // this allows dynamic/unstructured data
                    ],
                ],
                'auth_callback' => function() {
                    return current_user_can( 'edit_posts' );
                },
            ]
        );
    }

    /**
     * Filters the allowed block types for all editor types.
     *
     * @since 5.8.0
     *
     * @param bool|string[]           $allowed_block_types  Array of block type slugs, or boolean to enable/disable all.
     *                                                      Default true (all registered block types supported).
     * @param \WP_Block_Editor_Context $block_editor_context The current block editor context.
     */
    public function allow_blocks_for_formgent_form( $allowed_block_types, $editor_context ) {
        if ( empty( $editor_context->post->post_type ) || formgent_post_type() !== $editor_context->post->post_type ) {
            return $allowed_block_types;
        }

        $blocks = formgent_config( 'blocks' );
        unset( $blocks['formgent/form'] );
        $blocks = array_keys( $blocks );

        $blocks[] = 'core/heading';
        //$blocks[] = 'core/paragraph';

        return apply_filters( 'formgent_allowed_blocks',  $blocks );
    }
}
