<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

class ResponseSingleDTO extends ResponseReadDTO {
    private array $columns = ['response.*', 'user.display_name as username', 'user.user_email'];

    private ?int $id = null;

    /**
     * Get the value of columns
     *
     * @return array
     */
    public function get_columns() {
        return $this->columns;
    }

    /**
     * Set the value of columns
     *
     * @param array $columns
     *
     * @return self
     */
    public function set_columns( array $columns ) {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get the value of id
     *
     * @return ?int
     */
    public function get_id(): ?int {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param ?int $id
     *
     * @return self
     */
    public function set_id( ?int $id ): self {
        $this->id = $id;

        return $this;
    }
}