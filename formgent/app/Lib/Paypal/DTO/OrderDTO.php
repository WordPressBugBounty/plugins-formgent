<?php

namespace FormGent\App\Lib\Paypal\DTO;

defined( "ABSPATH" ) || exit;

use FormGent\App\Lib\Paypal\EnumList\CheckoutPaymentIntent;

class OrderDTO extends DTO {
    private string $intent = CheckoutPaymentIntent::CAPTURE;

    private string $currency;

    private float $amount;

    private float $final_amount;

    private array $purchase_items = [];

    private string $return_url;

    private string $cancel_url;

    /**
     * Get the value of intent
     *
     * @return string
     */
    public function get_intent(): string {
        return $this->intent;
    }
    
    /**
     * Set the value of intent
     *
     * @param string $intent
     * @return self
     */
    public function set_intent( string $intent ): self {
        if ( CheckoutPaymentIntent::is_valid( $intent ) ) {
            $this->intent = $intent;
            return $this; 
        }
    
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
     * @return self
     */
    public function set_currency( string $currency ): self {
        $this->currency = $currency;
    
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
     * @return self
     */
    public function set_final_amount( float $final_amount ): self {
        $this->final_amount = $final_amount;
    
        return $this;
    }

    /**
     * Get the value of purchase_items
     *
     * @return array
     */
    public function get_purchase_items(): array {
        return $this->purchase_items;
    }
    
    /**
     * Set the value of purchase_items
     *
     * @param array $purchase_items
     * @return self
     */
    public function set_purchase_items( OrderPurchaseItemDTO ...$purchase_items ): self {
        $this->purchase_items = $purchase_items;
    
        return $this;
    }

    /**
     * Get the value of return_url
     *
     * @return string
     */
    public function get_return_url(): string {
        return $this->return_url;
    }
    
    /**
     * Set the value of return_url
     *
     * @param string $return_url
     * @return self
     */
    public function set_return_url( string $return_url ): self {
        $this->return_url = $return_url;
    
        return $this;
    }

    /**
     * Get the value of cancel_url
     *
     * @return string
     */
    public function get_cancel_url(): string {
        return $this->cancel_url;
    }
    
    /**
     * Set the value of cancel_url
     *
     * @param string $cancel_url
     * @return self
     */
    public function set_cancel_url( string $cancel_url ): self {
        $this->cancel_url = $cancel_url;
    
        return $this;
    }
}