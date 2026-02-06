<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

class OrderDTO extends DTO {
    private int $id;

    private int $response_id;

    private float $amount;

    private string $currency;

    private float $final_amount;

    private string $status = 'pending';

    private string $hash;

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
     * Get the value of response_id
     *
     * @return int
     */
    public function get_response_id(): int {
        return $this->response_id;
    }

    /**
     * Set the value of response_id
     *
     * @param int $response_id 
     *
     * @return self
     */
    public function set_response_id( int $response_id ): self {
        $this->response_id = $response_id;

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
     *
     * @return self
     */
    public function set_amount( float $amount ): self {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of currency
     *
     * @return string
     */
    public function get_currency(): string {
        return $this->currency;
    }

    /**
     * Set the value of currency
     *
     * @param string $currency 
     *
     * @return self
     */
    public function set_currency( string $currency ): self {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get the value of final_amount
     *
     * @return float
     */
    public function get_final_amount(): float {
        return $this->final_amount;
    }

    /**
     * Set the value of final_amount
     *
     * @param float $final_amount 
     *
     * @return self
     */
    public function set_final_amount( float $final_amount ): self {
        $this->final_amount = $final_amount;

        return $this;
    }

    /**
     * Get the value of status
     *
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param string $status 
     *
     * @return self
     */
    public function set_status( string $status ): self {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of hash
     *
     * @return string
     */
    public function get_hash(): string {
        return $this->hash;
    }

    /**
     * Set the value of hash
     *
     * @param string $hash 
     *
     * @return self
     */
    public function set_hash( string $hash ): self {
        $this->hash = $hash;

        return $this;
    }
}
