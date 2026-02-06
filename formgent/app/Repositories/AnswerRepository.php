<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Models\Answer;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\WpMVC\Repositories\Repository;

class AnswerRepository extends Repository {
    public function get_query_builder() : Builder {
        return Answer::query( "answer" );
    }

    public function get( int $response_id ) {
        return $this->get_query_builder()->with( 'children' )->where( 'response_id', $response_id )->where_null( 'parent_id' )->get();
    }

    /**
     * Create multiple items
     */
    public function creates( int $response_id, array $items ) {
        return Answer::query()->insert(
            array_map(
                function( AnswerDTO $field ) use( $response_id ) {
                    return $this->process_values( $field->set_response_id( $response_id )->to_array() );
                }, $items
            )
        );
    }

    /**
     * Create multiple items
     */
    public function creates_from_array( array $array ) {
        return Answer::query()->insert(
            array_map(
                function( $item ) {
                    return $this->process_values( $item );
                }, $array
            ) 
        );
    }

    public function get_by_field_by_name( int $response_id, string $field_name, ?int $parent_id = null ) {
        $field = Answer::query()->where( 'response_id', $response_id )->where( 'field_name', $field_name );

        if ( is_int( $parent_id ) ) {
            $field->where( 'parent_id', $parent_id );
        } else {
            $field->where_null( 'parent_id' );
        }

        return $field->first();
    }
}