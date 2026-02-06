<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use ReflectionClass;

abstract class DTO {
    public function is_initialized( string $property ): bool {
        $reflection = new ReflectionClass( $this );

        if ( ! $reflection->hasProperty( $property ) ) {
            return false;
        }
        
        $prop = $reflection->getProperty( $property );
        $prop->setAccessible( true );
        
        return $prop->isInitialized( $this );
    }

    public function to_array( bool $with_id = false, $skip_fields = [ 'created_at' ] ) {
        $values = [];

        $reflection = new ReflectionClass( $this );

        foreach ( $reflection->getProperties() as $property ) {
            $property->setAccessible( true );

            if ( ! $property->isInitialized( $this ) ) {
                continue;
            }

            $property_name = $property->getName();
            
            if ( in_array( $property_name, $skip_fields ) ) {
                continue;
            }

            if ( ! $with_id && 'id' === $property_name ) {
                continue;
            }

            $value = $this->{"get_{$property_name}"}();

            if ( $value instanceof DTO ) {
                $value = $value->to_array( $with_id );
            } else if ( is_array( $value ) && ! empty( $value ) ) {
                $value_items = array_map(
                    function( $item ) use( $with_id ) {

                        if ( $item instanceof DTO ) {
                              return $item->to_array( $with_id );
                        }

                        return $item;
                    }, $value 
                );

                $value = $value_items;
            }

            $values[ $property_name ] = $value;
        }

        return $values;
    }
}