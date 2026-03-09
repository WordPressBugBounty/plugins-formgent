<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class ResponseLogReadDTO extends DTO {
    private int $response_id;

    private int $page = 1;

    private int $per_page = 10;

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
     * Get the value of page
     *
     * @return int
     */
    public function get_page() {
        return $this->page;
    }

    /**
     * Set the value of page
     *
     * @param int $page
     *
     * @return self
     */
    public function set_page( int $page ) {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the value of per_page
     *
     * @return int
     */
    public function get_per_page() {
        return $this->per_page;
    }

    /**
     * Set the value of per_page
     *
     * @param int $per_page
     *
     * @return self
     */
    public function set_per_page( int $per_page ) {
        $this->per_page = $per_page;

        return $this;
    }
}
