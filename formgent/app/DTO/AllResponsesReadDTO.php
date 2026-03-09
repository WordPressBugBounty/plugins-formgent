<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class AllResponsesReadDTO extends DTO {
    private int $page;

    private int $per_page;

    private ?int $is_read = null;

    private ?int $is_starred = null;

    private ?int $is_completed = null;

    private ?string $search = null;

    private ?string $order_by = null;

    private ?string $order = null;

    private ?string $date_type = null;

    private array $date_frame = [];

    private ?string $sort_by = null;

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
     * Get the value of is_read
     *
     * @return ?int
     */
    public function get_is_read(): ?int {
        return $this->is_read;
    }

    /**
     * Set the value of is_read
     *
     * @param ?int $is_read 
     *
     * @return self
     */
    public function set_is_read( ?int $is_read ): self {
        $this->is_read = $is_read;

        return $this;
    }

    /**
     * Get the value of is_starred
     *
     * @return ?int
     */
    public function get_is_starred(): ?int {
        return $this->is_starred;
    }

    /**
     * Set the value of is_starred
     *
     * @param ?int $is_starred 
     *
     * @return self
     */
    public function set_is_starred( ?int $is_starred ): self {
        $this->is_starred = $is_starred;

        return $this;
    }

    /**
     * Get the value of is_completed
     *
     * @return ?int
     */
    public function get_is_completed(): ?int {
        return $this->is_completed;
    }

    /**
     * Set the value of is_completed
     *
     * @param ?int $is_completed 
     *
     * @return self
     */
    public function set_is_completed( ?int $is_completed ): self {
        $this->is_completed = $is_completed;

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
     * Get the value of order_by
     *
     * @return ?string
     */
    public function get_order_by(): ?string {
        return $this->order_by;
    }

    /**
     * Set the value of order_by
     *
     * @param ?string $order_by 
     *
     * @return self
     */
    public function set_order_by( ?string $order_by ): self {
        $this->order_by = $order_by;

        return $this;
    }

    /**
     * Get the value of order
     *
     * @return ?string
     */
    public function get_order(): ?string {
        return $this->order;
    }

    /**
     * Set the value of order
     *
     * @param ?string $order 
     *
     * @return self
     */
    public function set_order( ?string $order ): self {
        $this->order = $order;

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
}