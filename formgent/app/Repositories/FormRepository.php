<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use Exception;
use FormGent\App\DTO\FormDTO;
use FormGent\App\DTO\FormReadDTO;
use FormGent\App\EnumeratedList\ResponseStatus;
use FormGent\App\Models\Response;
use FormGent\App\Models\Post;
use FormGent\App\Models\PostMeta;
use FormGent\App\Models\User;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\WpMVC\Database\Query\JoinClause;

class FormRepository extends FormSettingsRepository {
    const DEMOMEDIAOPTIONKEY = 'formgent_demo_medias';

    public function get( FormReadDTO $dto ) {
        $posts_query = Post::query( 'post' )->left_join( User::get_table_name() . " as user", "user.ID", "post.post_author" )->left_join(
            PostMeta::get_table_name() . " as form_type_post_meta", function( JoinClause $join ) {
                $join->on_column( "post.ID", "form_type_post_meta.post_id" )->on( "form_type_post_meta.meta_key", "_formgent_type" );
            }
        )->where( 'post.post_type', formgent_post_type() )->where_in( 'post.post_status', ['publish', 'draft'] );
        

        do_action( 'formgent_forms_select_query', $posts_query );

        $type_count_query = clone $posts_query;

        $this->forms_date_query( $posts_query, $dto );
        $this->forms_search_query( $posts_query, $dto );

        $count_query = clone $posts_query;

        if ( ! empty( $dto->get_type() ) && 'all' !== $dto->get_type() ) {
            $posts_query->where( "form_type_post_meta.meta_value", $dto->get_type() );
        }

        do_action( 'formgent_forms_count_query', $count_query, $dto );

        $select_columns   = ['post.ID as id', 'post.post_title as title', 'post.post_status as status', 'form_type_post_meta.meta_value as type', 'post.post_date as created_at', 'post.post_modified as updated_at', 'user.display_name as username', 'COUNT(DISTINCT response.id) as total_responses', 'COUNT(DISTINCT CASE WHEN response.is_read = 0 THEN response.id ELSE NULL END) AS total_unread_responses'];
        $group_by_columns = ['post.ID', 'post.post_title', 'post.post_status', 'user.display_name' ];

        $posts_query->select( $select_columns )->left_join(
            Response::get_table_name() . ' as response', function( JoinClause $join ) {
                $join->on_column( 'post.ID', 'response.form_id' )->on( 'response.status', ResponseStatus::PUBLISH );
            }
        )->group_by( $group_by_columns );

        $this->forms_sort_query( $posts_query, $dto );

        do_action( 'formgent_forms_query', $posts_query, $dto );

        $types = $type_count_query->select( "meta_value as type", 'COUNT(DISTINCT post.ID) as total' )->group_by( ["form_type_post_meta.meta_value" ] )->get();

        $forms = array_map(
            function( $form ) {
                $form->preview_url = get_post_permalink( $form->id );
                return $form;
            }, $posts_query->pagination( $dto->get_page(), $dto->get_per_page() )
        );

        return [
            'types' => $types,
            'total' => $count_query->count(),
            'forms' => $forms
        ];
    }

    private function forms_search_query( Builder $query, FormReadDTO $dto ) {
        $search = $dto->get_search();

        if ( empty( $search ) ) {
            return $query;
        }

        global $wpdb;

        $search       = "%{$search}%";
        $search_query = $wpdb->prepare( "(post.post_title like %s or user.display_name like %s)", $search, $search );
        return $query->where_raw( $search_query );
    }

    private function forms_date_query( Builder $query, FormReadDTO $dto ) {
        if ( empty( $dto->get_date_type() ) || 'all' === $dto->get_date_type() ) {
            return $query;
        }

        $now              = formgent_now();
        $from_date_format = "Y-m-d 00:00:01";
        $to_date_format   = "Y-m-d 23:59:59";

        $date_type  = $dto->get_date_type();
        $date_frame = $dto->get_date_frame();

        if ( 'today' === $date_type ) {
            $today = $now->format( $from_date_format );
            return $query->where_raw( "(post.post_modified > '{$today}' or post.post_date > '{$today}')" );
        }

        if ( 'date_frame' === $date_type ) {
            /**
             * Checking if the date is valid and the date format is correct.
             */
            if ( empty( $date_frame['from'] ) ||
                ! is_string( $date_frame['from'] ) ||
                empty( $date_frame['to'] ) || 
                ! is_string( $date_frame['to'] ) ||
                ! formgent_is_valid_date( $date_frame['from'], $from_date_format ) ||
                ! formgent_is_valid_date( $date_frame['to'], $to_date_format )
            ) {
                return $query;
            }

            $form = sanitize_text_field( $date_frame['from'] );
            $to   = sanitize_text_field( $date_frame['to'] );
        } else {
            switch ( $date_type ) {
                case 'yesterday':
                    $date = $now->sub_days( 1 );
                    break;
                case 'last_week':
                    $date = $now->sub_days( 6 );
                    break;
                case 'last_month':
                    $date = $now->sub_days( 30 );
            }
            $form = $date->format( $from_date_format );
            $to   = formgent_now()->format( $to_date_format );
        }

        return $query->where_raw( "((post.post_modified > '{$form}' and post.post_modified < '{$to}') or (post.post_date > '{$form}' and post.post_date < '{$to}'))" );
    }

    private function forms_sort_query( Builder $query, FormReadDTO $dto ) {
        $sort_by = $dto->get_sort_by();

        if ( empty( $sort_by ) ) {
            return $query->order_by_desc( 'post.ID' );
        }

        switch ( $sort_by ) {
            case 'last_modified':
                return $query->order_by_desc( 'post.post_modified' );
            case 'alphabetical':
                return $query->order_by_raw( 'post.post_title asc, post.ID desc' );
            case 'last_submission':
                return $query->left_join(
                    Response::get_table_name() . ' as max_response', function( JoinClause $join ) {
                        $join->select( 'max_response.form_id', 'MAX(max_response.id) AS max_id' )->on_column( 'response.form_id', 'max_response.form_id' )->on_column( 'response.id', 'max_response.max_id' );
                    }
                )->order_by_desc( 'response.id' );
            case 'unread':
                return $query->left_join(
                    Response::get_table_name() . ' as unread_old_response', function( JoinClause $join ) {
                        $join->select( 'unread_old_response.form_id', 'MIN(unread_old_response.created_at) AS old_created_at' )->on_column( 'response.form_id', 'unread_old_response.form_id' )->on_column( 'response.created_at', 'unread_old_response.old_created_at' );
                    }
                )->order_by_raw(
                    "CASE
                    WHEN response.is_read = 0 THEN 1
                    WHEN response.is_read = 1 THEN 2
                    ELSE 3
                END, post.ID desc"
                );
            case 'publish':
                return $query->order_by_desc( 'post.post_status' );
            case 'draft':
                return $query->order_by( 'post.post_status' );
            case 'date_created':
            default:
                return $query->order_by_desc( 'post.ID' );
        }
    } 

    public function create( FormDTO $dto ) {
        $post_id = wp_insert_post(
            [
                'post_title'   => $dto->get_title(),
                'post_status'  => $dto->get_status(),
                'post_content' => $dto->get_content(),
                'post_type'    => formgent_post_type()
            ]
        );

        if ( $post_id instanceof WP_Error ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped	
            throw new Exception( $post_id->get_error_message(), $post_id->get_error_code() );
        }

        add_post_meta( $post_id, '_formgent_type', $dto->get_type() );
        add_post_meta( $post_id, '_formgent_fields', $dto->get_fields() );

        if ( $dto->is_initialized( 'settings' ) ) {
            add_post_meta( $post_id, '_formgent_settings', $dto->get_settings() );
        }

        if ( $dto->is_initialized( 'form_settings' ) ) {
            add_post_meta( $post_id, '_formgent_form_settings', $dto->get_form_settings() );
        }

        return $post_id;
    }

    public function insert_media( string $attachment_url ) {
        $demo_attachments = $this->get_demo_attachments();

        /**
         * Return attachment if from cache if exists
         */
        if ( ! empty( $demo_attachments[$attachment_url] ) ) {
            $id         = $demo_attachments[$attachment_url];
            $attachment = wp_get_attachment_url( $id, );

            if ( is_string( $attachment_url ) ) {
                return [
                    'id'  => $id,
                    'url' => $attachment_url
                ];
            }
        }

        $response = wp_remote_get(
            $attachment_url, [
                [
                    'timeout' => 30
                ]
            ]
        );

        if ( is_wp_error( $response ) ) {
            $error_code    = $response->get_error_code();
            $response_code = 500;

            if ( is_string( $error_code ) ) {
                if ( 'http_request_failed' === $error_code ) {
                    $response_code = 495;
                }
            } else {
                $response_code = $error_code;
            }
            //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped	
            throw new Exception( $response->get_error_message(), $response_code );
        }

        $response_code = intval( wp_remote_retrieve_response_code( $response ) );

        if ( 200 !== $response_code ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped	
            throw new Exception( wp_remote_retrieve_response_message( $response ), $response_code );
        }

        $file_name = wp_basename( $attachment_url );
        $upload    = wp_upload_bits( $file_name, null, wp_remote_retrieve_body( $response ) );

        if ( ! empty( $upload['error'] ) ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped	
            throw new Exception( $upload['error'], 500 );
        }

        $attachment = [
            'post_title'     => $file_name,
            'post_type'      => 'attachment',
            'post_mime_type' => $upload['type'],
            'guid'           => $upload['url']
        ];

        $id = wp_insert_attachment( $attachment, $upload['file'] );

        if ( is_wp_error( $id ) ) {
            //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped	
            throw new Exception( $id->get_error_message(), $id->get_error_code() );
        }

        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            include_once ABSPATH . 'wp-admin/includes/image.php';
        }

        if ( ! function_exists( 'wp_read_video_metadata' ) ) {
            include_once ABSPATH . 'wp-admin/includes/media.php';
        }

        wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

        /**
         * Caching the inserted attachment url and id
         */
        $demo_attachments[$attachment_url] = $id;

        $this->update_demo_attachments( $demo_attachments );

        return [
            'id'  => $id,
            'url' => $upload['url']
        ];
    }

    public function get_demo_attachments():array {
        return get_option( self::DEMOMEDIAOPTIONKEY, [] );
    }

    public function update_demo_attachments( array $demo_attachments ) {
        return update_option( self::DEMOMEDIAOPTIONKEY, $demo_attachments );
    }

    public function update_status( int $post_id, string $status ) {
        return wp_update_post(
            [
                'ID'          => $post_id,
                'post_status' => $status,
                'post_type'   => formgent_post_type()
            ]
        );
    }

    public function update_title( int $post_id, string $title ) {
        return wp_update_post(
            [
                'ID'         => $post_id,
                'post_title' => $title,
                'post_type'  => formgent_post_type()
            ]
        );
    }

    public function update_bulk_status( array $ids, string $post_status ) {
        $ids = map_deep( $ids, "intval" );

        return Post::query()->where( 'post_type', formgent_post_type() )->where_in( 'ID', $ids )->update(
            [
                'post_status' => $post_status
            ]
        );
    }

    public function get_by_id( int $id, $columns = ['*'] ) {
        return Post::query()->select( $columns )->where( 'ID', $id )->where( 'post_type', formgent_post_type() )->first();
    }

    public function get_by_id_publish( int $id, $columns = ['*'] ) {
        return Post::query()->select( $columns )->where( 'ID', $id )->where( 'post_type', formgent_post_type() )->where( 'post_status', 'publish' )->first();
    }

    public function delete_by_id( int $id ) {
        return wp_delete_post( $id, true );
    }

    public function get_by_ids( array $ids, $columns = ['*'] ) {
        return Post::query()->select( $columns )->where( 'post_type', formgent_post_type() )->where_in( 'ID', $ids )->get();
    }

    public function deletes( array $ids ) {
        $forms = $this->get_by_ids( $ids );

        if ( ! $forms ) {
            throw new Exception( esc_html__( 'Forms not found.', 'formgent' ), 404 );
        }

        foreach ( $ids as $id ) {
            wp_delete_post( $id, true );
        }

        return $forms;
    }

    public function get_form_dto( int $id ): ?FormDTO {
        $form_data = $this->get_by_id( $id );

        if ( ! $form_data ) {
            return null;
        }

        $form_dto = new FormDTO();

        $form_dto->set_id( $form_data->ID )
            ->set_title( $form_data->post_title )
            ->set_status( $form_data->post_status )
            ->set_type( get_post_meta( $form_data->ID, '_formgent_type', true ) )
            ->set_content( $form_data->post_content )
            ->set_save_incomplete_data( formgent_is_save_incompleted_data( $form_data->ID ) );

        return $form_dto;
    }
}