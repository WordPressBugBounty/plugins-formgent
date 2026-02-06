<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;

class PaymentReturnDTO extends DTO {
    private int $order_id;

    private int $payment_id;

    private ?string $transaction_id;

    private ?string $billing_email = null;

    private ?string $billing_name = null;

    private ?string $billing_country = null;

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
     * Get the value of payment_id
     *
     * @return int
     */
    public function get_payment_id(): int {
        return $this->payment_id;
    }

    /**
     * Set the value of payment_id
     *
     * @param int $payment_id 
     *
     * @return self
     */
    public function set_payment_id( int $payment_id ): self {
        $this->payment_id = $payment_id;

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