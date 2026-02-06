<?php

namespace FormGent\App\Models;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\App;
use FormGent\WpMVC\Database\Resolver;
use FormGent\WpMVC\Database\Eloquent\Model;

class ResponseToken extends Model {
    public static function get_table_name():string {
        return 'formgent_response_token';
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}