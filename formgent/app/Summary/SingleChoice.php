<?php

namespace FormGent\App\Summary;

defined( 'ABSPATH' ) || exit;

use FormGent\App\EnumeratedList\SummaryType;
use stdClass;

trait SingleChoice {

    use Summary;

    public static function summary_type(): string {
        return SummaryType::SINGLECHOICE;
    }

    protected function get_answers( stdClass $form, array $field ) {
        $query   = $this->query( $form->ID, $field['name'], $field['field_type'] );
        $answers = [];

        $options = $field['options'];

        foreach ( $options as $option ) {
            $option_query = clone $query;
            $total        = ! empty( $field['allow_multi_select'] )
                ? $option_query->where_raw(
                    $GLOBALS['wpdb']->prepare(
                        'JSON_CONTAINS(value, %s)',
                        wp_json_encode( (string) $option['value'] )
                    )
                )->count()
                : $option_query->where( 'value', $option['value'] )->count();

            $answers[] = array_merge(
                [
                    'label' => $option['label'],
                    'total' => $total,
                ],
                formgent_get_choice_option_media( $option )
            );
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
