<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;
use stdClass;

class SpreadsheetHeaderDTO extends DTO {
    private stdClass $form;

    private stdClass $spreadsheet;

    private string $field_mapping_type;

    private array $field_mapping;

    /**
     * Get the value of form
     *
     * @return stdClass
     */
    public function get_form(): stdClass {
        return $this->form;
    }

    /**
     * Set the value of form
     *
     * @param stdClass $form 
     *
     * @return self
     */
    public function set_form( stdClass $form ): self {
        $this->form = $form;

        return $this;
    }

    /**
     * Get the value of spreadsheet
     *
     * @return stdClass
     */
    public function get_spreadsheet(): stdClass {
        return $this->spreadsheet;
    }

    /**
     * Set the value of spreadsheet
     *
     * @param stdClass $spreadsheet 
     *
     * @return self
     */
    public function set_spreadsheet( stdClass $spreadsheet ): self {
        $this->spreadsheet = $spreadsheet;

        return $this;
    }

    /**
     * Get the value of field_mapping_type
     *
     * @return string
     */
    public function get_field_mapping_type(): string {
        return $this->field_mapping_type;
    }

    /**
     * Set the value of field_mapping_type
     *
     * @param string $field_mapping_type 
     *
     * @return self
     */
    public function set_field_mapping_type( string $field_mapping_type ): self {
        $this->field_mapping_type = $field_mapping_type;

        return $this;
    }

    /**
     * Get the value of field_mapping
     *
     * @return array
     */
    public function get_field_mapping(): array {
        return $this->field_mapping;
    }

    /**
     * Set the value of field_mapping
     *
     * @param array $field_mapping 
     *
     * @return self
     */
    public function set_field_mapping( array $field_mapping ): self {
        $this->field_mapping = $field_mapping;

        return $this;
    }
}