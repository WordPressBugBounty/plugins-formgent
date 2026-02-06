<?php defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\View\View; 

if ( 0 >= $attributes['formId'] ) {
    echo "Form not found";
    return;
}

$post = get_post( $attributes['formId'] );

if ( empty( $post ) ) {
    echo "Form not found";
    return;
}

View::render(
    'form', [
        'form'     => $post,
        'is_block' => true
    ]
);