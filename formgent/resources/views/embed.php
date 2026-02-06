<?php defined( 'ABSPATH' ) || exit; 

do_action( 'formgent_before_form_embed' );
do_action( 'formgent_before_load_share_view' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>
       <?php the_content() ?>
        <?php wp_footer(); ?>
    </body>
</html>