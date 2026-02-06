<?php

namespace FormGent\App\DTO;

defined( 'ABSPATH' ) || exit;

use FormGent\WpMVC\DTO\DTO;

/**
 * Data Transfer Object for Payments
 */
class PaymentDTO extends DTO {
    private int $id;

    private int $order_id;

    private float $amount;

    private string $currency;

    private string $status;

    private ?string $transaction_id;

    private string $method;

    private ?string $billing_email;

    private ?string $billing_name;

    private ?string $billing_country;

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
     * @return int
     */
    public function get_order_id(): int {
        return $this->order_id;
    }

    /**
     * Set the value of order_id
     *
     * @param int $order_id 
     *
     * @return self
     */
    public function set_order_id( int $order_id ): self {
        $this->order_id = $order_id;

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
     * Get the value of transaction_id
     *
     * @return ?string
     */
    public function get_transaction_id(): ?string {
        return $this->transaction_id;
    }

    /**
     * Set the value of transaction_id
     *
     * @param ?string $transaction_id 
     *
     * @return self
     */
    public function set_transaction_id( ?string $transaction_id ): self {
        $this->transaction_id = $transaction_id;

        return $this;
    }

    /**
     * Get the value of method
     *
     * @return string
     */
    public function get_method(): string {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @param string $method 
     *
     * @return self
     */
    public function set_method( string $method ): self {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of billing_email
     *
     * @return ?string
     */
    public function get_billing_email(): ?string {
        return $this->billing_email;
    }

    /**
     * Set the value of billing_email
     *
     * @param ?string $billing_email 
     *
     * @return self
     */
    public function set_billing_email( ?string $billing_email ): self {
        $this->billing_email = $billing_email;

        return $this;
    }

    /**
     * Get the value of billing_name
     *
     * @return ?string
     */
    public function get_billing_name(): ?string {
        return $this->billing_name;
    }

    /**
     * Set the value of billing_name
     *
     * @param ?string $billing_name 
     *
     * @return self
     */
    public function set_billing_name( ?string $billing_name ): self {
        $this->billing_name = $billing_name;

        return $this;
    }

    /**
     * Get the value of billing_country
     *
     * @return ?string
     */
    public function get_billing_country(): ?string {
        return $this->billing_country;
    }

    /**
     * Set the value of billing_country
     *
     * @param ?string $billing_country 
     *
     * @return self
     */
    public function set_billing_country( ?string $billing_country ): self {
        $this->billing_country = $billing_country;

        return $this;
    }
}
