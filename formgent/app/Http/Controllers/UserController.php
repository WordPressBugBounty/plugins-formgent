<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Routing\Response;

class UserController extends Controller
{
    public function index() {}

    public function get_countries() {
        return Response::send(
            [
                'flag_url'  => formgent_url( 'assets/images/flags' ),
                'countries' => formgent_config( 'countries' )
            ]
        );
    }
}