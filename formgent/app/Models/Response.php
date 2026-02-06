<?php

namespace FormGent\App\Models;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\App;
use FormGent\WpMVC\Database\Resolver;
use FormGent\WpMVC\Database\Eloquent\Model;
use FormGent\WpMVC\Database\Eloquent\Relations\HasMany;
use FormGent\WpMVC\Database\Eloquent\Relations\HasOne;

class Response extends Model {
    public static function get_table_name():string {
        return 'formgent_responses';
    }

    public function answers(): HasMany {
        return $this->has_many( Answer::class, 'response_id', 'id' );
    }

    public function order(): HasOne {
        return $this->has_one( Order::class, "response_id", "id" );
    }

    public function user():HasOne {
        return $this->has_one( User::class, 'ID', 'created_by' );
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}