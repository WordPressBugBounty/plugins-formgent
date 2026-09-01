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
     * Return bounded MCP aggregates for one form or the current site.
     *
     * @param int|null $form_id Optional form ID.
     * @param string|null $date_from Inclusive site-local start timestamp.
     * @param string|null $date_to Exclusive site-local end timestamp.
     * @return array<string,int>
     */
    public function mcp_aggregate( ?int $form_id = null, ?string $date_from = null, ?string $date_to = null ): array {
        global $wpdb;

        $response_table = esc_sql( $wpdb->prefix . Response::get_table_name() );
        $conditions     = ['1 = 1'];
        $values         = [];

        if ( null !== $form_id ) {
            $conditions[] = 'form_id = %d';
            $values[]     = $form_id;
        }

        if ( null !== $date_from && null !== $date_to ) {
            $conditions[] = 'created_at >= %s';
            $conditions[] = 'created_at < %s';
            $values[]     = $date_from;
            $values[]     = $date_to;
        }

        $where = implode( ' AND ', $conditions );
        $sql   = "
            SELECT
                COUNT(*) AS starts,
                SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) AS completions,
                SUM(CASE WHEN status = 'publish' AND is_read = 0 THEN 1 ELSE 0 END) AS unread,
                COALESCE(
                    SUM(
                        CASE
                            WHEN is_completed = 1 AND completed_at IS NOT NULL AND created_at IS NOT NULL AND completed_at >= created_at
                            THEN TIMESTAMPDIFF(SECOND, created_at, completed_at)
                            ELSE 0
                        END
                    ),
                    0
                ) AS completion_seconds
            FROM {$response_table}
            WHERE {$where}
        ";

        // Table names are built from the current site's trusted wpdb prefix and model constant.
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        $prepared = empty( $values ) ? $sql : $wpdb->prepare( $sql, $values );
        $result   = $wpdb->get_row( $prepared );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared

        $starts      = $result ? absint( $result->starts ) : 0;
        $completions = $result ? absint( $result->completions ) : 0;
        $unread      = $result ? absint( $result->unread ) : 0;
        $seconds     = $result ? absint( $result->completion_seconds ) : 0;

        return [
            'views'                      => $this->mcp_view_count( $form_id ),
            'starts'                     => $starts,
            'completions'                => $completions,
            'unread_responses'           => $unread,
            'completion_rate'            => 0 < $starts ? (int) round( ( $completions / $starts ) * 100 ) : 0,
            'average_completion_seconds' => 0 < $completions ? (int) round( $seconds / $completions ) : 0,
        ];
    }

    private function mcp_view_count( ?int $form_id ): int {
        global $wpdb;

        $where  = '';
        $values = [formgent_post_type(), '_formgent_views'];

        if ( null !== $form_id ) {
            $where    = ' AND post.ID = %d';
            $values[] = $form_id;
        }

        $sql = "
            SELECT COALESCE(SUM(CAST(meta.meta_value AS UNSIGNED)), 0)
            FROM {$wpdb->posts} AS post
            INNER JOIN {$wpdb->postmeta} AS meta ON meta.post_id = post.ID
            WHERE post.post_type = %s
                AND post.post_status IN ('draft', 'publish')
                AND meta.meta_key = %s
                {$where}
        ";

        // Core table identifiers come from wpdb; every value is prepared.
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
        return absint( $wpdb->get_var( $wpdb->prepare( $sql, $values ) ) );
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
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
