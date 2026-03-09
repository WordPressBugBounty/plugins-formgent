<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class ResponseLogDTO extends DTO {
    private int $id;

    private int $response_id;

    private string $action = 'entry_edited';

    private ?int $created_by = null;

    private ?string $meta = null;

    /**
     * Get the value of id
     *
     * @return int
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id
     *
     * @return self
     */
    public function set_id( int $id ) {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of response_id
     *
     * @return int
     */
    public function get_response_id() {
        return $this->response_id;
    }

    /**
     * Set the value of response_id
     *
     * @param int $response_id
     *
     * @return self
     */
    public function set_response_id( int $response_id ) {
        $this->response_id = $response_id;

        return $this;
    }

    /**
     * Get the value of action
     *
     * @return string
     */
    public function get_action() {
        return $this->action;
    }

    /**
     * Set the value of action
     *
     * @param string $action
     *
     * @return self
     */
    public function set_action( string $action ) {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the value of created_by
     *
     * @return int|null
     */
    public function get_created_by() {
        return $this->created_by;
    }

    /**
     * Set the value of created_by
     *
     * @param int|null $created_by
     *
     * @return self
     */
    public function set_created_by( ?int $created_by ) {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * Get the value of meta
     *
     * @return string|null
     */
    public function get_meta() {
        return $this->meta;
    }

    /**
     * Set the value of meta
     *
     * @param string|null $meta
     *
     * @return self
     */
    public function set_meta( ?string $meta ) {
        $this->meta = $meta;

        return $this;
    }
}
