<?php

namespace FormGent\Database\Migration;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Contracts\Migration;
use FormGent\WpMVC\Database\Schema\Schema;
use FormGent\WpMVC\Database\Schema\Blueprint;

class V123 implements Migration {
    protected $migration_version = '1.2.3';

    public function more_than_version() {
        return $this->migration_version;
    }

    public function execute(): bool {
        $prefix = 'formgent_';

        Schema::create(
            "{$prefix}response_logs", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->string( 'action', 50 )->default( 'entry_edited' );
                $table->unsigned_big_integer( 'created_by' )->nullable();
                $table->long_text( 'meta' )->nullable();
                $table->timestamp( 'created_at' )->use_current();

                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );

        return true;
    }
}
