<?php

namespace FormGent\App\Repositories;

defined( 'ABSPATH' ) || exit;

use FormGent\App\DTO\AnswerDTO;
use FormGent\App\Models\Answer;
use FormGent\App\Utils\AnswerValueSanitizer;
use FormGent\WpMVC\Database\Query\Builder;
use FormGent\WpMVC\Repositories\Repository;
use FormGent\WpMVC\DTO\DTO;

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
                    return $this->prepare_values( $field->set_response_id( $response_id )->to_array() );
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
                    return $this->prepare_values( $item );
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

    /**
     * Create a single answer.
     *
     * @param AnswerDTO $dto
     * @return bool|int
     */
    public function create( DTO $dto ) {
        // Type check to ensure we have AnswerDTO
        if ( ! $dto instanceof AnswerDTO ) {
            return false;
        }
        
        return Answer::query()->insert_get_id( $this->prepare_values( $dto->to_array() ) );
    }

    public function update( DTO $dto ) {
        // Type check to ensure we have AnswerDTO
        if ( ! $dto instanceof AnswerDTO ) {
            return false;
        }
        
        $data = $this->prepare_values( $dto->to_array( true ) );
        
        // Remove 'id' from update data as it's used in WHERE clause
        unset( $data['id'] );
        
        // Ensure 'value' field is always included in update (even if null)
        // This is critical for updates to work correctly
        if ( ! isset( $data['value'] ) ) {
            $data['value'] = AnswerValueSanitizer::sanitize( $dto->get_value() );
            // Process the value if it's an array or object
            if ( is_array( $data['value'] ) || ( is_object( $data['value'] ) && get_class( $data['value'] ) === 'stdClass' ) ) {
                $data['value'] = wp_json_encode( $data['value'] );
            }
        }
        
        // Ensure we have data to update
        if ( empty( $data ) ) {
            return false;
        }
        
        // Ensure required fields are present
        if ( ! isset( $data['response_id'] ) || ! isset( $data['form_id'] ) || ! isset( $data['field_name'] ) || ! isset( $data['field_type'] ) ) {
            return false;
        }
        
        return Answer::query()
            ->where( 'id', $dto->get_id() )
            ->update( $data );
    }

    private function prepare_values( array $values ): array {
        if ( array_key_exists( 'value', $values ) ) {
            $values['value'] = AnswerValueSanitizer::sanitize( $values['value'] );
        }

        return $this->process_values( $values );
    }
}
