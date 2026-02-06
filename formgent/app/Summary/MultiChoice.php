<?php

namespace FormGent\App\Summary;

defined( 'ABSPATH' ) || exit;

use FormGent\App\EnumeratedList\SummaryType;
use stdClass;

trait MultiChoice {

    use Summary;

    public static function summary_type(): string {
        return SummaryType::MULTICHOICE;
    }

    protected function get_answers( stdClass $form, array $field ) {
        $query   = $this->query( $form->ID, $field['name'], $field['field_type'] );
        $answers = [];

        $options = $field['options'];

        foreach ( $options as $option ) {
            $option_query = clone $query;
            $answers[]    = [
                'label' => $option['label'],
                'total' => $option_query->where_raw( 'JSON_CONTAINS(value, \'"' . $option['value'] . '"\')' )->count()
            ];
        }

        return $answers;
    }

    protected function get_field_summary( stdClass $form, array $field ) {
        return $this->get_answers( $form, $field );
    }

    public function get_summary( stdClass $form, array $field, int $page = 0, int $per_page = 0 ) {
        return apply_filters( "formgent_multi_select_summery", $this->get_field_summary( $form, $field ), $field, $form );
    }
}