<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class NoteDTO extends DTO {
    private int $id;

    private int $response_id;

    private string $note;

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
     * Get the value of note
     *
     * @return string
     */
    public function get_note() {
        return $this->note;
    }

    /**
     * Set the value of note
     *
     * @param string $note 
     *
     * @return self
     */
    public function set_note( string $note ) {
        $this->note = $note;

        return $this;
    }
}