<?php

namespace FormGent\App\Summary;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Answer;
use FormGent\WpMVC\Database\Query\Builder;

trait Summary {
    public function query( int $form_id, string $field_name, string $field_type, bool $field_type_like = false ):Builder {
        $query = Answer::query()->where( 'form_id', $form_id )->where( 'field_name', $field_name );
        
        if ( $field_type_like ) {
            return $query->where( 'field_type', 'like', "%{$field_type}%" );
        }

        return $query->where( 'field_type', $field_type );
    }
}