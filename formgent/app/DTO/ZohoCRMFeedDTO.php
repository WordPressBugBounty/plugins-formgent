<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;

class ZohoCRMFeedDTO extends DTO {
    private int $id;

    private int $form_id;

    private string $module;

    private array $fields;

    private int $status;

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
     * Get the value of module
     *
     * @return string
     */
    public function get_module(): string {
        return $this->module;
    }

    /**
     * Set the value of module
     *
     * @param string $module 
     *
     * @return self
     */
    public function set_module( string $module ): self {
        $this->module = $module;

        return $this;
    }

    /**
     * Get the value of fields
     *
     * @return array
     */
    public function get_fields(): array {
        return $this->fields;
    }

    /**
     * Set the value of fields
     *
     * @param array $fields 
     *
     * @return self
     */
    public function set_fields( array $fields ): self {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get the value of status
     *
     * @return int
     */
    public function get_status(): int {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param int $status 
     *
     * @return self
     */
    public function set_status( int $status ): self {
        $this->status = $status;

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