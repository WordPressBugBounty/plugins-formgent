<?php

namespace FormGent\App\Models;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\App;
use FormGent\WpMVC\Database\Resolver;
use FormGent\WpMVC\Database\Eloquent\Model;

class EmailNotification extends Model {
    public static function get_table_name():string {
        return 'formgent_email_notifications';
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}