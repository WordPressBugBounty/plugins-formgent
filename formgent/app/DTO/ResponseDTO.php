<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\App\EnumeratedList\ResponseStatus;
use FormGent\WpMVC\DTO\DTO;

class ResponseDTO extends DTO {
    private int $id;

    private int $form_id;

    private string $status = ResponseStatus::PUBLISH;

    private int $is_read = 0;

    private int $is_completed = 1;

    private ?string $completed_at = null;

    private ?string $ip;

    private ?int $created_by = null;

    private ?string $device = null;

    private ?string $browser = null;

    private ?string $browser_version = null;

    private ?string $created_at;

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
     * Get the value of is_read
     *
     * @return int
     */
    public function get_is_read(): int {
        return $this->is_read;
    }

    /**
     * Set the value of is_read
     *
     * @param int $is_read 
     *
     * @return self
     */
    public function set_is_read( int $is_read ): self {
        $this->is_read = $is_read;

        return $this;
    }

    /**
     * Get the value of is_completed
     *
     * @return int
     */
    public function get_is_completed(): int {
        return $this->is_completed;
    }

    /**
     * Set the value of is_completed
     *
     * @param int $is_completed 
     *
     * @return self
     */
    public function set_is_completed( int $is_completed ): self {
        $this->is_completed = $is_completed;

        return $this;
    }

    /**
     * Get the value of completed_at
     *
     * @return ?string
     */
    public function get_completed_at(): ?string {
        return $this->completed_at;
    }

    /**
     * Set the value of completed_at
     *
     * @param ?string $completed_at 
     *
     * @return self
     */
    public function set_completed_at( ?string $completed_at ): self {
        $this->completed_at = $completed_at;

        return $this;
    }

    /**
     * Get the value of ip
     *
     * @return ?string
     */
    public function get_ip(): ?string {
        return $this->ip;
    }

    /**
     * Set the value of ip
     *
     * @param ?string $ip 
     *
     * @return self
     */
    public function set_ip( ?string $ip ): self {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get the value of created_by
     *
     * @return ?int
     */
    public function get_created_by(): ?int {
        return $this->created_by;
    }

    /**
     * Set the value of created_by
     *
     * @param ?int $created_by 
     *
     * @return self
     */
    public function set_created_by( ?int $created_by ): self {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * Get the value of device
     *
     * @return ?string
     */
    public function get_device(): ?string {
        return $this->device;
    }

    /**
     * Set the value of device
     *
     * @param ?string $device 
     *
     * @return self
     */
    public function set_device( ?string $device ): self {
        $this->device = $device;

        return $this;
    }

    /**
     * Get the value of browser
     *
     * @return ?string
     */
    public function get_browser(): ?string {
        return $this->browser;
    }

    /**
     * Set the value of browser
     *
     * @param ?string $browser 
     *
     * @return self
     */
    public function set_browser( ?string $browser ): self {
        $this->browser = $browser;

        return $this;
    }

    /**
     * Get the value of browser_version
     *
     * @return ?string
     */
    public function get_browser_version(): ?string {
        return $this->browser_version;
    }

    /**
     * Set the value of browser_version
     *
     * @param ?string $browser_version 
     *
     * @return self
     */
    public function set_browser_version( ?string $browser_version ): self {
        $this->browser_version = $browser_version;

        return $this;
    }

    /**
     * Get the value of created_at
     *
     * @return ?string
     */
    public function get_created_at(): ?string {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @param ?string $created_at 
     *
     * @return self
     */
    public function set_created_at( ?string $created_at ): self {
        $this->created_at = $created_at;

        return $this;
    }
}