<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class FormReadDTO extends DTO {
    private int $page;

    private int $per_page;

    private ?string $search = null;

    private ?string $sort_by = null;

    private ?string $date_type = null;

    private array $date_frame = [];

    private ?string $type = null;

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
     * Get the value of search
     *
     * @return ?string
     */
    public function get_search(): ?string {
        return $this->search;
    }

    /**
     * Set the value of search
     *
     * @param ?string $search 
     *
     * @return self
     */
    public function set_search( ?string $search ): self {
        $this->search = $search;

        return $this;
    }

    /**
     * Get the value of sort_by
     *
     * @return ?string
     */
    public function get_sort_by(): ?string {
        return $this->sort_by;
    }

    /**
     * Set the value of sort_by
     *
     * @param ?string $sort_by 
     *
     * @return self
     */
    public function set_sort_by( ?string $sort_by ): self {
        $this->sort_by = $sort_by;

        return $this;
    }

    /**
     * Get the value of date_type
     *
     * @return ?string
     */
    public function get_date_type(): ?string {
        return $this->date_type;
    }

    /**
     * Set the value of date_type
     *
     * @param ?string $date_type 
     *
     * @return self
     */
    public function set_date_type( ?string $date_type ): self {
        $this->date_type = $date_type;

        return $this;
    }

    /**
     * Get the value of date_frame
     *
     * @return array
     */
    public function get_date_frame(): array {
        return $this->date_frame;
    }

    /**
     * Set the value of date_frame
     *
     * @param array $date_frame 
     *
     * @return self
     */
    public function set_date_frame( array $date_frame ): self {
        $this->date_frame = $date_frame;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return ?string
     */
    public function get_type(): ?string {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param ?string $type 
     *
     * @return self
     */
    public function set_type( ?string $type ): self {
        $this->type = $type;

        return $this;
    }
}