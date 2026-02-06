<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class NoteReadDTO extends DTO {
    private int $response_id;

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
}