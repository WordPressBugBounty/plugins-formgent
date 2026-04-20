<?php

namespace FormGent\Database\Migration;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Contracts\Migration;
use FormGent\WpMVC\Database\Schema\Schema;
use FormGent\WpMVC\Database\Schema\Blueprint;

class V124 implements Migration {
    protected $migration_version = '1.2.4';

    public function more_than_version() {
        return $this->migration_version;
    }

    public function execute(): bool {
        $prefix = 'formgent_';

        Schema::create(
            "{$prefix}pdfs", function ( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->string( 'template_name' );
                $table->string( 'template_type' )->nullable();
                $table->long_text( 'content' );
                $table->string( 'paper_size' )->nullable();
                $table->string( 'orientation' )->nullable();
                $table->string( 'direction' )->nullable();
                $table->text( 'password' )->nullable();
                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
            }
        );

        return true;
    }
}
