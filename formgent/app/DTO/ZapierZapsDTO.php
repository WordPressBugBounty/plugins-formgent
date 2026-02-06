<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;

class ZapierZapsDTO extends DTO {
    private int $id;

    private int $zap_id;

    private int $form_id;

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
     * Get the value of zap_id
     *
     * @return int
     */
    public function get_zap_id(): int {
        return $this->zap_id;
    }

    /**
     * Set the value of zap_id
     *
     * @param int $zap_id 
     *
     * @return self
     */
    public function set_zap_id( int $zap_id ): self {
        $this->zap_id = $zap_id;

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
}