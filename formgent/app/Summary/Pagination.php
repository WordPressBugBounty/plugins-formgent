<?php

namespace FormGent\App\Summary;

defined( 'ABSPATH' ) || exit;

use stdClass;
use FormGent\App\EnumeratedList\SummaryType;

trait Pagination {

    use Summary;

    public static function summary_type(): string {
        return SummaryType::PAGINATION;
    }

    protected function get_answers( stdClass $form, array $field, int $page, int $per_page ) {
        return $this->query( $form->ID, $field['name'], $field['field_type'] )->select( 'id, value, created_at' )->pagination( $page, $per_page, 1, 100 );
    }

    protected function get_field_summary( stdClass $form, array $field, int $page, int $per_page ) {
        return $this->get_answers( $form, $field, $page, $per_page );
    }

    public function get_summary( stdClass $form, array $field, int $page = 0, int $per_page = 0 ) {
        return apply_filters( "formgent_pagination_summery", $this->get_field_summary( $form, $field, $page, $per_page ), $field, $form );
    }
}

