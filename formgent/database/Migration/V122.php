<?php

namespace FormGent\Database\Migration;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Contracts\Migration;
use FormGent\App\Models\EmailNotification;
use FormGent\WpMVC\Database\Schema\Schema;
use FormGent\WpMVC\Database\Schema\Blueprint;

class V122 implements Migration {
    protected $migration_version = '1.2.2';

    public function more_than_version() {
        return $this->migration_version;
    }

    public function execute(): bool {
        Schema::alter(
            EmailNotification::get_table_name(), function ( Blueprint $table ) {
                $table->string( 'from_name' )->nullable();
                $table->string( 'from_email' )->nullable();
            }
        );

        return true;
    }
}
