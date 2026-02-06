<?php

namespace FormGent\App\Summary;

defined( 'ABSPATH' ) || exit;

use stdClass;
use FormGent\App\EnumeratedList\SummaryType;

trait Custom {

    use Summary;

    public static function summary_type(): string {
        return SummaryType::CUSTOM;
    }

    protected function get_answers( stdClass $form, array $field ) {
        return $this->query( $form->ID, $field['name'], $field['field_type'] )->select( 'value' )->get();
    }

    protected function get_field_summary( stdClass $form, array $field ) {
        return $this->get_answers( $form, $field );
    }

    public function get_summary( stdClass $form, array $field, int $page = 0, int $per_page = 0 ) {
        return apply_filters( "formgent_custom_summary", $this->get_field_summary( $form, $field ), $field, $form );
    }
}

