<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class AnswerDTO extends DTO {
    private int $id;

    private int $response_id = 0;

    private int $form_id;

    private string $field_name;

    private string $field_type;

    private ?string $parent_id = null;

    /**
     * @var null|string|array
     */
    private $value = null;

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
     * Get the value of response_id
     *
     * @return int
     */
    public function get_response_id(): int {
        return $this->response_id;
    }

    /**
     * Set the value of response_id
     *
     * @param int $response_id 
     *
     * @return self
     */
    public function set_response_id( int $response_id ): self {
        $this->response_id = $response_id;

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
     * Get the value of field_name
     *
     * @return string
     */
    public function get_field_name(): string {
        return $this->field_name;
    }

    /**
     * Set the value of field_name
     *
     * @param string $field_name 
     *
     * @return self
     */
    public function set_field_name( string $field_name ): self {
        $this->field_name = $field_name;

        return $this;
    }

    /**
     * Get the value of field_type
     *
     * @return string
     */
    public function get_field_type(): string {
        return $this->field_type;
    }

    /**
     * Set the value of field_type
     *
     * @param string $field_type 
     *
     * @return self
     */
    public function set_field_type( string $field_type ): self {
        $this->field_type = $field_type;

        return $this;
    }

    /**
     * Get the value of parent_id
     *
     * @return ?string
     */
    public function get_parent_id(): ?string {
        return $this->parent_id;
    }

    /**
     * Set the value of parent_id
     *
     * @param ?string $parent_id 
     *
     * @return self
     */
    public function set_parent_id( ?string $parent_id ): self {
        $this->parent_id = $parent_id;

        return $this;
    }

    /**
     * Get the value of value
     *
     * @return ?string|array
     */
    public function get_value() {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @param ?string|array $value 
     *
     * @return self
     */
    public function set_value( $value ): self {
        $this->value = $value;

        return $this;
    }
}