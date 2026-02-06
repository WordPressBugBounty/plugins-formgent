<?php

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Enqueue\Enqueue;

Enqueue::register_script( 'formgent/modules', 'build/js/modules' );
Enqueue::register_script( 'formgent/components', 'build/js/components' );
Enqueue::register_script( 'formgent/notification', 'build/js/notification' );
Enqueue::register_script( 'formgent/directorist', 'build/js/directorist' );
Enqueue::register_style( 'formgent/style', 'build/css/app', [ 'wp-components'] );

wp_enqueue_script( "moment" );