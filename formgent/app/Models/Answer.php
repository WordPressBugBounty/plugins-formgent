<?php

namespace FormGent\App\Models;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\App;
use FormGent\WpMVC\Database\Eloquent\Relations\HasMany;
use FormGent\WpMVC\Database\Resolver;
use FormGent\WpMVC\Database\Eloquent\Model;

class Answer extends Model {
    public static function get_table_name():string {
        return 'formgent_answers';
    }

    public function children(): HasMany {
        return $this->has_many( Answer::class, 'parent_id', 'id' );
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}