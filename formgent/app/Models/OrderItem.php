<?php

namespace FormGent\App\Models;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\App;
use FormGent\WpMVC\Database\Resolver;
use FormGent\WpMVC\Database\Eloquent\Model;

class OrderItem extends Model {
    public static function get_table_name():string {
        return "formgent_order_items";
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}