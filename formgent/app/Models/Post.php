<?php

namespace FormGent\App\Models;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\App;
use FormGent\WpMVC\Database\Eloquent\Model;
use FormGent\WpMVC\Database\Eloquent\Relations\HasMany;
use FormGent\WpMVC\Database\Resolver;

class Post extends Model {
    public static function get_table_name():string {
        return 'posts';
    }

    public function meta(): HasMany {
        return $this->has_many( PostMeta::class, 'post_id', 'id' );
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}