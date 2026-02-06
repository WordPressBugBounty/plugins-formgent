<?php

namespace FormGent\App\DTO\GoogleSpreadSheet;

defined( "ABSPATH" ) || exit;

class DTO extends \FormGent\WpMVC\DTO\DTO {
    private int $id;

    private int $form_id;

    private string $title;

    private string $spreadsheet_id;

    private string $sheet_title;

    private string $sheet_id;

    private string $status;

    private string $field_mapping_type;

    private array $field_mapping;

    private int $is_columns_updated;

    private array $columns;

    private string $condition_type;

    private int $condition_status;

    private array $conditions;

    /**
     * Get the value of id
     *
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id 
     *
     * @return self
     */
    public function set_id( int $id ): self {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of form_id
     *
     * @return int
     */
    public function get_form_id(): int {
        return $this->form_id;
    }

    /**
     * Set the value of form_id
     *
     * @param int $form_id 
     *
     * @return self
     */
    public function set_form_id( int $form_id ): self {
        $this->form_id = $form_id;

        return $this;
    }

    /**
     * Get the value of title
     *
     * @return string
     */
    public function get_title(): string {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param string $title 
     *
     * @return self
     */
    public function set_title( string $title ): self {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of spreadsheet_id
     *
     * @return string
     */
    public function get_spreadsheet_id(): string {
        return $this->spreadsheet_id;
    }

    /**
     * Set the value of spreadsheet_id
     *
     * @param string $spreadsheet_id 
     *
     * @return self
     */
    public function set_spreadsheet_id( string $spreadsheet_id ): self {
        $this->spreadsheet_id = $spreadsheet_id;

        return $this;
    }

    /**
     * Get the value of sheet_title
     *
     * @return string
     */
    public function get_sheet_title(): string {
        return $this->sheet_title;
    }

    /**
     * Set the value of sheet_title
     *
     * @param string $sheet_title 
     *
     * @return self
     */
    public function set_sheet_title( string $sheet_title ): self {
        $this->sheet_title = $sheet_title;

        return $this;
    }

    /**
     * Get the value of sheet_id
     *
     * @return string
     */
    public function get_sheet_id(): string {
        return $this->sheet_id;
    }

    /**
     * Set the value of sheet_id
     *
     * @param string $sheet_id 
     *
     * @return self
     */
    public function set_sheet_id( string $sheet_id ): self {
        $this->sheet_id = $sheet_id;

        return $this;
    }

    /**
     * Get the value of status
     *
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param string $status 
     *
     * @return self
     */
    public function set_status( string $status ): self {
        $this->status = $status;

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

    /**
     * Get the value of is_columns_updated
     *
     * @return int
     */
    public function get_is_columns_updated(): int {
        return $this->is_columns_updated;
    }

    /**
     * Set the value of is_columns_updated
     *
     * @param int $is_columns_updated 
     *
     * @return self
     */
    public function set_is_columns_updated( int $is_columns_updated ): self {
        $this->is_columns_updated = $is_columns_updated;

        return $this;
    }

    /**
     * Get the value of columns
     *
     * @return array
     */
    public function get_columns(): array {
        return $this->columns;
    }

    /**
     * Set the value of columns
     *
     * @param array $columns 
     *
     * @return self
     */
    public function set_columns( array $columns ): self {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get the value of condition_type
     *
     * @return string
     */
    public function get_condition_type(): string {
        return $this->condition_type;
    }

    /**
     * Set the value of condition_type
     *
     * @param string $condition_type 
     *
     * @return self
     */
    public function set_condition_type( string $condition_type ): self {
        $this->condition_type = $condition_type;

        return $this;
    }

    /**
     * Get the value of condition_status
     *
     * @return int
     */
    public function get_condition_status(): int {
        return $this->condition_status;
    }

    /**
     * Set the value of condition_status
     *
     * @param int $condition_status 
     *
     * @return self
     */
    public function set_condition_status( int $condition_status ): self {
        $this->condition_status = $condition_status;

        return $this;
    }

    /**
     * Get the value of conditions
     *
     * @return array
     */
    public function get_conditions(): array {
        return $this->conditions;
    }

    /**
     * Set the value of conditions
     *
     * @param array $conditions 
     *
     * @return self
     */
    public function set_conditions( array $conditions ): self {
        $this->conditions = $conditions;

        return $this;
    }
}