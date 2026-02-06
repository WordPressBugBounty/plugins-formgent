<?php

namespace FormGent\App\Models;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\App;
use FormGent\WpMVC\Database\Resolver;
use FormGent\WpMVC\Database\Eloquent\Model;
use FormGent\WpMVC\Database\Eloquent\Relations\HasMany;
use FormGent\WpMVC\Database\Eloquent\Relations\BelongsToOne;

class Order extends Model {
    public static function get_table_name():string {
        return "formgent_orders";
    }

    public function payment(): BelongsToOne {
        return $this->belongs_to_one( Payment::class, "order_id", "id" );
    }

    public function items(): HasMany {
        return $this->has_many( OrderItem::class, "order_id", "id" );
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}