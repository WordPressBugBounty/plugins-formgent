<?php

namespace FormGent\App\Integrations\Google;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\SpreadsheetHeaderDTO;
use FormGent\App\Integrations\Google\Spreadsheet;

class SpreadsheetHeader {
    private Spreadsheet $spreadsheet_service;

    private SpreadsheetHeaderDTO $dto;

    public function __construct( Spreadsheet $spreadsheet_service ) {
        $this->spreadsheet_service = $spreadsheet_service;
    }

    public function update( SpreadsheetHeaderDTO $dto ) {
        $this->dto = $dto;

        if ( 'auto' === $dto->get_field_mapping_type() ) {
            $headers = $this->auto();
        } else {
            $headers = $this->manual();
        }

        $spreadsheet = $this->dto->get_spreadsheet();

        try {
            $this->spreadsheet_service->update_header( $spreadsheet->spreadsheet_id, $spreadsheet->sheet_id, $headers['labels'], $headers['last_column'] );
        } catch ( \Throwable $th ) {
        }

        return $headers['columns'];
    }

    public function auto() {
        $fields      = formgent_get_form_fields( $this->dto->get_form(), 'id' );
        $spreadsheet = $this->dto->get_spreadsheet();
        $columns     = array_flip( json_decode( $this->dto->get_spreadsheet()->columns, true ) );

        if ( empty( $columns ) || ! $this->spreadsheet_service->has_value( $spreadsheet->spreadsheet_id, $spreadsheet->sheet_id ) ) {
            $columns     = [];
            $labels      = [];
            $next_column = "A";
            foreach ( $fields as $field_id => $field ) {
                if ( in_array( $field['field_type'], ['gdpr', 'page-break'], true ) ) {
                    continue;   
                }
                $columns[$next_column] = $field_id;
                $labels[]              = $field['label'];
                $next_column++;
            }

            return [
                'labels'      => $labels,
                'columns'     => $columns,
                'last_column' => array_key_last( $columns )
            ];
        }

        $last_column = end( $columns );

        foreach ( $fields as $field_id => $field ) {
            if ( 'gdpr' === $field['field_type'] ) {
                continue;   
            }
            if ( ! isset( $columns[$field_id] ) ) {
                $last_column++;
                $columns[$field_id] = $last_column;
            }
        }

        $labels = [];

        foreach ( $columns as $field_id => $column ) {
            if ( isset( $fields[$field_id] ) ) {
                $labels[] = $fields[$field_id]['label'];
            } else {
                $labels[] = '';
            }
        }

        return [
            'labels'      => $labels,
            'columns'     => array_flip( $columns ),
            'last_column' => $last_column
        ];
    }

    public function manual() {
        $labels      = [];
        $last_column = array_key_last( $this->dto->get_field_mapping() );

        for ( $column = 'A';; $column++ ) {
            $labels[$column] = '';
            if ( $column == $last_column ) {
                break;
            }
        }

        $fields = formgent_get_form_fields( $this->dto->get_form(), 'id' );

        $columns = $labels;

        foreach ( $this->dto->get_field_mapping() as $column => $field_id ) {
            if ( isset( $fields[$field_id] ) ) {
                $labels[$column]  = $fields[$field_id]['label'];
                $columns[$column] = $fields[$field_id]['id'];
            }
        }

        $last_column = array_key_last( $labels );

        return [
            'labels'      => array_values( $labels ),
            'columns'     => $columns,
            'last_column' => $last_column
        ];
    }
}