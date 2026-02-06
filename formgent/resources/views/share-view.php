<?php

defined( 'ABSPATH' ) || exit;

$design_settings   = formgent_form_repository()->get_setting_by_key( get_post()->ID, 'design' );
$form_type         = formgent_get_form_type( get_post()->ID );
$is_conversational = ( 'conversational' === $form_type );

if ( empty( $design_settings ) ) {
    return;
}

do_action( 'formgent_before_load_share_view' );

$focal_point = isset( $design_settings['cover']['focalPoint'] ) ?
( $design_settings['cover']['focalPoint']['x'] * 100 ) . '% ' . ( $design_settings['cover']['focalPoint']['y'] * 100 ) . '%' : 'center';

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?> style="height: 100vh; background: #f3f4f6;">
        <div class="formgent-form formgent-form-<?php echo esc_attr( $form_type ); ?>">
            <div
                class="formgent-form__inner <?php echo ( isset( $design_settings['is_cover_bg'] ) && $design_settings['is_cover_bg'] ) ? 'formgent-form__inner--cover-bg' : ''; ?>"
                style="<?php
                echo ! $is_conversational ? '--formgent-form-width: ' . esc_attr( $design_settings['width'] ) . '; ' : '';
                ?>"
            >
                <?php if ( ! $is_conversational && isset( $design_settings['show_cover'] ) && $design_settings['show_cover'] ) : ?>
                    <div
                        class="formgent-form-cover <?php echo $design_settings['cover_type'] === 'color' ? 'formgent-form-cover--color' : ''; ?>"
                        style="<?php
                        echo '--formgent-form-cover-color: ' . ( ! empty( $design_settings['cover']['color'] )
                        ? esc_attr( $design_settings['cover']['color'] )
                        : '#d2d6db' ) . '; ';
                               echo isset( $design_settings['cover']['gradient'] )
                               ? '--formgent-form-cover-gradient: ' . esc_attr( $design_settings['cover']['gradient'] ) . ';'
                               : '';
                                ?>"
                    >
                        <?php if ( ! empty( $design_settings['cover']['url'] ) ) : ?>
                            <img
                                src="<?php echo esc_url( $design_settings['cover']['url'] ); ?>"
                                alt=""
                                style="
                                    display: block;
                                    margin: 0 auto;
                                    width: 100%;
                                    height: 100%;
                                    object-fit: cover;
                                    object-position: <?php echo esc_attr( $focal_point ) ?>;
                                "
                            >
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div
                    class="formgent-form-wrapper
                    <?php echo ( ! $is_conversational && ! empty( $design_settings['show_cover'] ) ) ? 'formgent-form-wrapper--has-cover' : ''; ?>
                    <?php echo ( ! $is_conversational && ( ! empty( $design_settings['show_title'] ) || ! empty( $design_settings['show_logo'] ) ) ) ? 'formgent-form-wrapper--has-header' : ''; ?>"
                >
                    <?php if ( ! $is_conversational && ( ! empty( $design_settings['show_logo'] ) || ! empty( $design_settings['show_title'] ) ) ) : ?>
                        <div class="formgent-form-header">
                            <?php if ( ! empty( $design_settings['show_logo'] ) && ! empty( $design_settings['logo']['url'] ) ) : ?>
                                <div class="formgent-form-logo">
                                    <?php //phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
                                    <img src="<?php echo esc_url( $design_settings['logo']['url'] ); ?>" alt="">
                                </div>
                            <?php endif; ?>
                            <?php if ( ! empty( $design_settings['show_title'] ) ) : ?>
                                <h1 class="formgent-form-title"><?php echo esc_html( get_the_title() ); ?></h1>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Render form -->
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
        <?php wp_footer(); ?>
    </body>
</html>
