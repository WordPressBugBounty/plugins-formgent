<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;

class EmailNotificationReadDTO extends DTO {
    private int $page;

    private int $per_page;

    private int $form_id;

    private ?string $sort_by;

    /**
     * Get the value of page
     *
     * @return int
     */
    public function get_page(): int {
        return $this->page;
    }

    /**
     * Set the value of page
     *
     * @param int $page 
     *
     * @return self
     */
    public function set_page( int $page ): self {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the value of per_page
     *
     * @return int
     */
    public function get_per_page(): int {
        return $this->per_page;
    }

    /**
     * Set the value of per_page
     *
     * @param int $per_page 
     *
     * @return self
     */
    public function set_per_page( int $per_page ): self {
        $this->per_page = $per_page;

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
     * Get the value of sort_by
     *
     * @return string
     */
    public function get_sort_by(): ?string {
        return $this->sort_by;
    }
    
    /**
     * Set the value of sort_by
     *
     * @param string $sort_by
     * 
     * @return self
     */
    public function set_sort_by( ?string $sort_by ): self {
        $this->sort_by = $sort_by;
    
        return $this;
    }
}