<?php defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\View\View; 

if ( 0 >= $attributes['formId'] ) {
    View::render( 'form-unavailable' );
    return;
}

$form_id = apply_filters( 'formgent_form_id', absint( $attributes['formId'] ) );
$post    = formgent_get_form_post( $form_id, true );

if ( empty( $post ) ) {
    View::render( 'form-unavailable' );
    return;
}

View::render(
    'form', [
        'form'     => $post,
        'is_block' => true
    ]
);
