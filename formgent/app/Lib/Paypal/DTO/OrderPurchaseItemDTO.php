<?php

namespace FormGent\App\Lib\Paypal\DTO;

defined( "ABSPATH" ) || exit;

class OrderPurchaseItemDTO extends DTO {
    private string $name;

    private float $amount;

    private int $quantity = 1;

    /**
     * Get the value of name
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }
    
    /**
     * Set the value of name
     *
     * @param string $name
     * @return self
     */
    public function set_name( string $name ): self {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get the value of amount
     *
     * @return float
     */
    public function get_amount(): float {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @param float $amount
     * @return self
     */
    public function set_amount( float $amount ): self {
        $this->amount = $amount;

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
     * @return self
     */
    public function set_quantity( int $quantity ): self {
        $this->quantity = $quantity;
    
        return $this;
    }
}