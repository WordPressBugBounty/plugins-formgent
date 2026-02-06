<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class FormDTO extends DTO {
    private int $id;

    private string $title;

    private string $status = 'draft';

    private string $type;

    private string $content = '';

    private array $fields = [];

    private array $settings;

    private array $form_settings;

    private bool $save_incomplete_data = false;

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
     * Get the value of type
     *
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param string $type 
     *
     * @return self
     */
    public function set_type( string $type ): self {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of content
     *
     * @return string
     */
    public function get_content(): string {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @param string $content 
     *
     * @return self
     */
    public function set_content( string $content ): self {
        $this->content = $content;

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
     * Get the value of settings
     *
     * @return array
     */
    public function get_settings(): array {
        return $this->settings;
    }

    /**
     * Set the value of settings
     *
     * @param array $settings 
     *
     * @return self
     */
    public function set_settings( array $settings ): self {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get the value of form_settings
     *
     * @return array
     */
    public function get_form_settings(): array {
        return $this->form_settings;
    }

    /**
     * Set the value of form_settings
     *
     * @param array $form_settings 
     *
     * @return self
     */
    public function set_form_settings( array $form_settings ): self {
        $this->form_settings = $form_settings;

        return $this;
    }

    /**
     * Get the value of save_incomplete_data
     *
     * @return bool
     */
    public function is_save_incomplete_data(): bool {
        return $this->save_incomplete_data;
    }

    /**
     * Set the value of save_incomplete_data
     *
     * @param bool $save_incomplete_data 
     *
     * @return self
     */
    public function set_save_incomplete_data( bool $save_incomplete_data ): self {
        $this->save_incomplete_data = $save_incomplete_data;

        return $this;
    }
}