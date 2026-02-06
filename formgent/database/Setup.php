<?php

namespace FormGent\Database;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\Database\Schema\Schema;
use FormGent\WpMVC\Database\Schema\Blueprint;

class Setup {
    public function execute() {
        $prefix = 'formgent_';

        // -- Table: responses
        Schema::create(
            "{$prefix}responses", function ( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->enum( 'status', ['publish', 'draft'] )->default( 'publish' );
                $table->tiny_integer( 'is_read' )->default( 0 )->comment( 'value: 0/1' );
                $table->tiny_integer( 'is_completed' )->default( 0 )->comment( 'value: 0/1' );
                $table->timestamp( 'completed_at' )->nullable();
                $table->tiny_integer( 'is_starred' )->default( 0 )->comment( 'value: 0/1' );
                $table->string( 'ip', 50 )->nullable();
                $table->string( 'device', 50 )->nullable();
                $table->string( 'browser', 50 )->nullable();
                $table->string( 'browser_version', 50 )->nullable();
                $table->unsigned_big_integer( 'created_by' )->nullable();
                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
            }
        );

        // -- Table: response_meta
        Schema::create(
            "{$prefix}response_meta", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->string( 'meta_key', 255 );
                $table->long_text( 'meta_value' )->nullable();

                $table->foreign( 'response_id' )->references( 'id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );

        // -- Table: answers
        Schema::create(
            "{$prefix}answers", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->unsigned_big_integer( 'parent_id' )->nullable();
                $table->string( 'field_name', 50 );
                $table->string( 'field_type', 50 );
                $table->long_text( 'value' )->nullable();
                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
                $table->foreign( 'parent_id' )->on( "{$prefix}answers" )->on_delete( 'cascade' );
            }
        );

        // -- Table: notes
        Schema::create(
            "{$prefix}notes", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->long_text( 'note' )->nullable();
                $table->timestamps();

                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );

        // -- Table: response_token
        Schema::create(
            "{$prefix}response_token", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->string( 'token' );
                $table->timestamp( 'created_at' )->use_current();
                $table->timestamp( 'expired_at' )->nullable();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );

        // -- Table: email_notifications
        Schema::create(
            "{$prefix}email_notifications", function ( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->string( 'name' );
                $table->string( 'send_to' );
                $table->string( 'subject' );
                $table->long_text( 'body' )->nullable();
                $table->string( 'cc' )->nullable();
                $table->string( 'bcc' )->nullable();
                $table->string( 'reply_to' )->nullable();
                $table->string( 'from_name' )->nullable();
                $table->string( 'from_email' )->nullable();
                $table->enum( 'status', ['publish', 'draft'] )->default( 'publish' );
                $table->enum( 'condition_type', ['or', 'and'] )->default( 'or' );
                $table->tiny_integer( 'condition_status' )->default( 0 )->comment( 'value: 0/1' );
                $table->long_text( 'conditions' )->nullable();
                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
            }
        );

        // -- Table: google_spreadsheets
        Schema::create(
            "{$prefix}google_spreadsheets", function ( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->string( 'title' )->nullable();
                $table->string( 'spreadsheet_id' )->nullable();
                $table->string( 'sheet_title' )->nullable();
                $table->string( 'sheet_id' )->nullable();
                $table->enum( 'status', ['publish', 'draft'] )->default( 'draft' );
                $table->enum( 'field_mapping_type', ['auto', 'manual'] )->default( 'auto' );
                $table->long_text( 'field_mapping' )->nullable();
                $table->long_text( 'columns' )->nullable();
                $table->tiny_integer( 'is_columns_updated' )->default( 1 )->comment( 'value: 0/1' );
                $table->enum( 'condition_type', ['or', 'and'] )->default( 'or' );
                $table->tiny_integer( 'condition_status' )->default( 0 )->comment( 'value: 0/1' );
                $table->long_text( 'conditions' )->nullable();
                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
            }
        );

        // -- Table: mailchimp_feeds
        Schema::create(
            "{$prefix}mailchimp_feeds", function ( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->string( 'title' );
                $table->string( 'list_id' );
                $table->string( 'group_id' )->nullable();
                $table->string( 'group_option_id' )->nullable();
                $table->long_text( 'field_mapping' )->nullable();
                $table->long_text( 'tags' )->nullable();
                $table->tiny_integer( 'double_opt_in' )->default( 0 )->comment( 'value: 0/1' );
                $table->tiny_integer( 'resubscribe_existing_contact' )->default( 0 )->comment( 'value: 0/1' );
                $table->tiny_integer( 'update_existing_contact' )->default( 0 )->comment( 'value: 0/1' );
                $table->tiny_integer( 'mark_as_vip' )->default( 0 )->comment( 'value: 0/1' );
                $table->tiny_integer( 'condition_status' )->default( 0 )->comment( 'value: 0/1' );
                $table->enum( 'condition_type', ['or', 'and'] )->default( 'or' );
                $table->long_text( 'conditions' )->nullable();
                $table->tiny_integer( 'status' )->default( 0 )->comment( 'value: 0/1' );

                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
            }
        );

        // -- Table: zapier_zaps
        Schema::create(
            "{$prefix}zapier_zaps", function ( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->unsigned_big_integer( 'zap_id' );
                $table->timestamp( 'created_at' )->use_current();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
            }
        );

        // -- Table: zapier_processed_responses
        Schema::create(
            "{$prefix}zapier_processed_responses", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'zap_id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->timestamp( 'created_at' )->use_current();

                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );


        // -- Table: zohocrm_feed
        Schema::create(
            "{$prefix}zohocrm_feed", function ( Blueprint $table ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->string( 'module' );
                $table->long_text( 'fields' );
                $table->tiny_integer( 'status' )->default( 1 )->comment( 'value: 0/1' );
                $table->tiny_integer( 'condition_status' )->default( 0 )->comment( 'value: 0/1' );
                $table->enum( 'condition_type', ['or', 'and'] )->default( 'or' );
                $table->long_text( 'conditions' )->nullable();
                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
            }
        );

        // -- Table: orders
        Schema::create(
            "{$prefix}orders", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'response_id' )->nullable();
                $table->decimal( 'amount', 10, 2 );
                $table->string( 'currency', 10 );
                $table->decimal( 'final_amount', 10, 2 )->default( 0.00 );
                $table->enum( 'status', ['pending','paid','failed','cancelled','expired','refunded','unpaid'] )->default( 'pending' );
                $table->string( 'hash' )->comment( "Using for public payment link" );
                $table->timestamps();

                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );

        // -- Table: order_items
        Schema::create(
            "{$prefix}order_items", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'order_id' )->nullable();
                $table->string( 'title', 255 )->nullable();
                $table->decimal( 'unit_amount', 10, 2 )->default( 0.00 );
                $table->integer( 'quantity' );
                $table->decimal( 'total_amount', 10, 2 )->default( 0.00 );
                $table->timestamps();

                $table->foreign( 'order_id' )->on( "{$prefix}orders" )->on_delete( 'cascade' );
            }
        );

        // -- Table: payments
        Schema::create(
            "{$prefix}payments", function ( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'order_id' );
                $table->decimal( 'amount', 10, 2 );
                $table->string( 'currency', 10 );
                $table->enum( 'status', ['pending','paid','failed','cancelled','refunded','unpaid','expired'] )->default( 'pending' );
                $table->string( 'transaction_id', 100 )->nullable();
                $table->string( 'method', 30 )->nullable();
                $table->string( 'billing_email', 255 )->nullable();
                $table->string( 'billing_name', 255 )->nullable();
                $table->string( 'billing_country', 255 )->nullable();
                $table->timestamps();

                $table->foreign( 'order_id' )->references( 'id' )->on( "{$prefix}orders" )->on_delete( 'cascade' );
            }
        );

        // -- Table: queues
        Schema::create(
            "{$prefix}queues", function( Blueprint $table ) use ( $prefix ) {
                $table->big_increments( 'id' );
                $table->unsigned_big_integer( 'form_id' );
                $table->unsigned_big_integer( 'response_id' );
                $table->string( 'task_type' );
                $table->string( 'task_id' );
                $table->enum( 'status', ['in_queue', 'in_progress', 'failed'] )->default( 'in_queue' );
                $table->timestamps();

                $table->foreign( 'form_id' )->references( 'ID' )->on( 'posts' )->on_delete( 'cascade' );
                $table->foreign( 'response_id' )->on( "{$prefix}responses" )->on_delete( 'cascade' );
            }
        );
    }
}