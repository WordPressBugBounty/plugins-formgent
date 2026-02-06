<?php

namespace FormGent\App\Http\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\Controller;
use FormGent\App\Models\Post;
use FormGent\WpMVC\RequestValidator\Validator;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

class PageController extends Controller {
    public function index( Validator $validator, WP_REST_Request $wp_rest_request ) {
        // $validator->validate(
        //     [
        //         'ids'    => 'array',
        //         'search' => 'string',
        //         'page'   => 'numeric',
        //     ]
        // );

        $pages_query = Post::query()->select( 'ID as value, post_title as label' )->where( 'post_type', 'page' )->where( 'post_status', 'publish' )->order_by_desc( 'id' );
            
        // if ( $wp_rest_request->has_param( 'ids' ) ) {
        //     $ids   = map_deep( $wp_rest_request->get_param( 'ids' ), 'intval' );
        //     $pages = $pages_query->where_in( 'ID', $ids )->get();
        // } else {
        //     if ( $wp_rest_request->has_param( 'search' ) ) {
        //         $pages_query->where_like( 'post_title', 'like', "%{$wp_rest_request->get_param('search')}%" );
        //     }
        //     $pages = $pages_query->pagination( absint( $wp_rest_request->get_param( 'page' ) ) );
        // }

        $pages = $pages_query->get();
    
        return Response::send(
            [
                'items' => $pages
            ] 
        );
    }
}