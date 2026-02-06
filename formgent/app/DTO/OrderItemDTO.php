<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

/**
 * Data Transfer Object for Order Items
 */
class OrderItemDTO extends DTO {
    private int $id;

    private ?int $order_id = null;

    private ?string $title = null;

    private int $quantity = 1;

    private float $unit_amount = 0.00;

    private float $total_amount = 0.00;

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
     * Get the value of order_id
     *
     * @return ?int
     */
    public function get_order_id(): ?int {
        return $this->order_id;
    }

    /**
     * Set the value of order_id
     *
     * @param ?int $order_id 
     *
     * @return self
     */
    public function set_order_id( ?int $order_id ): self {
        $this->order_id = $order_id;

        return $this;
    }

    /**
     * Get the value of title
     *
     * @return ?string
     */
    public function get_title(): ?string {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param ?string $title 
     *
     * @return self
     */
    public function set_title( ?string $title ): self {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of quantity
     *
     * @return int
     */
    public function get_quantity(): int {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @param int $quantity 
     *
     * @return self
     */
    public function set_quantity( int $quantity ): self {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get the value of unit_amount
     *
     * @return float
     */
    public function get_unit_amount(): float {
        return $this->unit_amount;
    }

    /**
     * Set the value of unit_amount
     *
     * @param float $unit_amount 
     *
     * @return self
     */
    public function set_unit_amount( float $unit_amount ): self {
        $this->unit_amount = $unit_amount;

        return $this;
    }

    /**
     * Get the value of total_amount
     *
     * @return float
     */
    public function get_total_amount(): float {
        return $this->total_amount;
    }

    /**
     * Set the value of total_amount
     *
     * @param float $total_amount 
     *
     * @return self
     */
    public function set_total_amount( float $total_amount ): self {
        $this->total_amount = $total_amount;

        return $this;
    }
}
