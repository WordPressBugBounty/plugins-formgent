<?php

namespace FormGent\App\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\WpMVC\DTO\DTO;

class PayDTO extends DTO {
    public OrderDTO $order;

    public PaymentDTO $payment;

    /**
     * @var OrderItemDTO[]
     */
    public array $order_items;

    /**
     * Get the value of order
     *
     * @return OrderDTO
     */
    public function get_order(): OrderDTO {
        return $this->order;
    }

    /**
     * Set the value of order
     *
     * @param OrderDTO $order 
     *
     * @return self
     */
    public function set_order( OrderDTO $order ): self {
        $this->order = $order;

        return $this;
    }

    /**
     * Get the value of payment
     *
     * @return PaymentDTO
     */
    public function get_payment(): PaymentDTO {
        return $this->payment;
    }

    /**
     * Set the value of payment
     *
     * @param PaymentDTO $payment 
     *
     * @return self
     */
    public function set_payment( PaymentDTO $payment ): self {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get the value of order_items
     *
     * @return OrderItemDTO[]
     */
    public function get_order_items(): array {
        return $this->order_items;
    }

    /**
     * Set the value of order_items
     *
     * @param OrderItemDTO[] $order_items 
     *
     * @return self
     */
    public function set_order_items( array $order_items ): self {
        $this->order_items = $order_items;

        return $this;
    }
}