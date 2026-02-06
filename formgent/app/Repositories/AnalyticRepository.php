<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Response;
use FormGent\App\Models\PostMeta;

use stdClass;
use Exception;

class AnalyticRepository {
    public function form_summary( int $form_id ) {
        $data = [
            'total_stared'            => 0,
            'total_finished'          => 0,
            'total_views'             => $this->form_view_count( $form_id ),
            'average_completion_time' => 0,
        ];

        $response_summary = $this->response_summary( $form_id );

        if ( $response_summary ) {
            $data = array_merge( $data, $response_summary );
        }

        return $data;
    }

    public function form_view_count( int $form_id ): int {
        $data = PostMeta::query()
            ->where( 'post_id', $form_id )
            ->where( 'meta_key', '_formgent_views' ) //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            ->first();

        return $data ? absint( $data->meta_value ) : 0;
    }

    public function response_summary( int $form_id ): ?array {
        global $wpdb;
        
        // Table name is safe - constructed from wpdb->prefix and constant class method
        $table_name = esc_sql( $wpdb->prefix . Response::get_table_name() );
        
        // Use TIMESTAMPDIFF for accurate time calculation in seconds
        // Handle timezone-corrupted records (5-7 hour offset) with 5-second fallback
        // Exclude invalid records where completed_at < created_at (outside timezone offset range)
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        $query = $wpdb->prepare(
            "
            SELECT 
                COUNT( form_id ) AS total_stared,
                SUM( CASE WHEN is_completed = 1 THEN 1 ELSE 0 END ) AS total_finished,
                COALESCE(
                    SUM(
                        CASE
                            WHEN is_completed = 1 
                                AND completed_at IS NOT NULL 
                                AND created_at IS NOT NULL
                            THEN 
                                CASE
                                    WHEN completed_at >= created_at
                                    THEN TIMESTAMPDIFF( SECOND, created_at, completed_at )
                                    WHEN ABS( TIMESTAMPDIFF( SECOND, created_at, completed_at ) ) BETWEEN 18000 AND 25200
                                    THEN 5
                                    ELSE 0
                                END
                            ELSE 0
                        END
                    ),
                    0
                ) AS total_completion_time
            FROM {$table_name}
            WHERE form_id = %d
            GROUP BY form_id
            ",
            $form_id
        );
        
        $result = $wpdb->get_row( $query, OBJECT );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        
        if ( ! $result ) {
            return null;
        }
        
        return $this->transform_response_summary_item_data( $result );
    }

    private function transform_response_summary_item_data( stdClass $item ) {
        // Calculate average completion time
        $average_completion_time = absint( $item->total_finished ) > 0 
            ? absint( $item->total_completion_time ) / absint( $item->total_finished ) 
            : 0;

        $item->average_completion_time = round( $average_completion_time );
        $item->total_stared            = absint( $item->total_stared );
        $item->total_finished          = absint( $item->total_finished );

        unset( $item->total_completion_time );

        return ( array ) $item;
    }

    /**
     * @throws Exception
     */
    public function update_form_view_count( int $form_id, int $count, $type = '=' ): int {
        $old_count_meta = PostMeta::query()
            ->where( 'post_id', $form_id )
            ->where( 'meta_key', '_formgent_views' ) //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            ->first();

        if ( ! $old_count_meta ) {
            $count = $count < 0 || '-' === $type ? 0 : $count;
            $id    = PostMeta::query()->insert_get_id(
                [
                    'post_id'    => $form_id,
                    'meta_key'   => '_formgent_views', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'meta_value' => $count, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
                ]
            );

            if ( $id ) {
                return $count;
            }

            throw new Exception( esc_html__( 'Could not update the view count.', 'formgent' ), 403 );
        }

        $type = in_array( $type, [ '=', '+', '-' ] ) ? $type : '=';

        switch ( $type ) {
            case '+':
                $count = absint( $old_count_meta->meta_value ) + $count;
                break;
            case '-':
                $count = absint( $old_count_meta->meta_value ) - $count;
                $count = $count < 0 ? 0 : $count;
                break;
        }

        $status = PostMeta::query()
            ->where( 'post_id', $form_id )
            ->where( 'meta_key', '_formgent_views' ) //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            ->update( [  'meta_value' => $count ] ); //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value

        if ( false === $status ) {
            throw new Exception( esc_html__( 'Could not update the view count.', 'formgent' ), 403 );
        }

        return $count;
    }
}