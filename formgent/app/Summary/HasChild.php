<?php

namespace FormGent\App\Summary;

defined( 'ABSPATH' ) || exit;

use FormGent\App\Models\Answer;
use FormGent\App\EnumeratedList\SummaryType;
use stdClass;

trait HasChild {

    use Summary;

    public static function summary_type(): string {
        return SummaryType::HASCHILD;
    }

    protected function get_answers( stdClass $form, array $field, int $page, int $per_page ) {
        $parents = $this->query( $form->ID, $field['name'], $field['field_type'] )->select( 'id' )->pagination( $page, $per_page );

        if ( empty( $parents ) ) {
            return [];
        }

        $ids = array_column( $parents, 'id' );

        $children = Answer::query()
            ->select( 
                [ 
                    'parent_id',
                    "GROUP_CONCAT( CONCAT( field_name, '__FGP__', value ) SEPARATOR '__FGS__' ) as value" 
                ] 
            )
            ->where_in( 'parent_id', $ids )
            ->group_by( 'parent_id' )
            ->get();

        
        if ( empty( $children ) ) {
            return [];
        }

        return array_map(
            function( $item ) {
                $child_data = [];
                $child_rows = explode( '__FGS__', $item->value );

                foreach ( $child_rows as $child_row_item ) {
                      $key_value = explode( '__FGP__', $child_row_item );
                      $filed_key = $key_value[0];

                      $child_data[ $filed_key ] = wp_kses_post( $key_value[1] );
                }

                $item->value = $child_data;

                return $item;
            }, $children 
        );
    }

    protected function get_field_summary( stdClass $form, array $field, int $page, int $per_page ) {
        return $this->get_answers( $form, $field, $page, $per_page );
    }

    public function get_summary( stdClass $form, array $field, int $page = 0, int $per_page = 0 ) {
        return apply_filters( "formgent_has_one_summary", $this->get_field_summary( $form, $field, $page, $per_page ), $field, $form );
    }
}

