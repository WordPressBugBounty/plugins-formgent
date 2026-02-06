<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

trait ReadQueryDTO {
    public int $page = 1;

    public int $per_page = 10;

    public ?string $order_by = null;

    public ?string $order = null;

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
     * @return self
     */
    public function set_order( ?string $order ): self {
        $this->order = $order;
    
        return $this;
    }
}