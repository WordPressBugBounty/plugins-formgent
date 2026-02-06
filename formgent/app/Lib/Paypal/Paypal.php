<?php

namespace FormGent\App\Lib\Paypal;

defined( "ABSPATH" ) || exit;

use Exception;
use FormGent\PaypalServerSdkLib\Environment;
use FormGent\PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use FormGent\PaypalServerSdkLib\PaypalServerSdkClient;

use FormGent\PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use FormGent\PaypalServerSdkLib\Http\ApiResponse;

use FormGent\PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use FormGent\PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use FormGent\PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use FormGent\PaypalServerSdkLib\Models\AmountBreakdown;
use FormGent\PaypalServerSdkLib\Models\Money;
use FormGent\PaypalServerSdkLib\Models\Item;
use FormGent\PaypalServerSdkLib\Models\PaymentSource;
use FormGent\PaypalServerSdkLib\Models\PaypalWallet;
use FormGent\PaypalServerSdkLib\Models\PaypalWalletExperienceContext;

use FormGent\App\Lib\Paypal\DTO\OrderDTO;
use FormGent\App\Lib\Paypal\DTO\OrderPurchaseItemDTO;

class Paypal {
    protected PaypalServerSdkClient $sdk_client;

    public function __construct( string $client_id, string $client_secret, bool $test_mode = false ) {
        $this->sdk_client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init(
                    $client_id,
                    $client_secret
                )
            )
            ->environment( $test_mode ? Environment::SANDBOX : Environment::PRODUCTION )
            ->build();
    }

    public function create_order( OrderDTO $order_dto ) {
        $currency     = $order_dto->get_currency();
        $amount       = $order_dto->get_amount();
        $final_amount = $order_dto->get_final_amount();
        $discount     = $amount - $final_amount;

        $amount_builder = AmountWithBreakdownBuilder::init( $currency, $this->format_amount( $final_amount ) );

        // START::BREAKDOWN
        $breakdown = new AmountBreakdown();

        $breakdown->setItemTotal( new Money( $currency, $this->format_amount( $amount ) ) );

        // START::ADD_DISCOUNT: Add discount if applicable
        if ( $discount > 0 ) {      
            $breakdown->setDiscount( new Money( $currency, $this->format_amount( $discount ) ) );
        }
        // END::ADD_DISCOUNT

        $amount_builder->breakdown( $breakdown );
        // END::BREAKDOWN
        
        $purchase_unit_builder = PurchaseUnitRequestBuilder::init( $amount_builder->build() );
        
        // START::ADD_ITEMS: Add items to the purchase unit
        $items = [];

        foreach ( $order_dto->get_purchase_items() as $item ) {
            /**
             * @var OrderPurchaseItemDTO $item
             */
            $items[] = new Item(
                $item->get_name(),
                new Money( $currency, $this->format_amount( $item->get_amount() ) ),
                $item->get_quantity(),
            );
        }

        $purchase_unit_builder = $purchase_unit_builder->items( $items );
        // END::ADD_ITEMS

        $paypal_wallet_experience_context = new PaypalWalletExperienceContext();
        $paypal_wallet_experience_context->setShippingPreference( 'GET_FROM_FILE' );
        $paypal_wallet_experience_context->setShippingPreference( 'GET_FROM_FILE' );
        $paypal_wallet_experience_context->setLandingPage( 'LOGIN' );
        $paypal_wallet_experience_context->setUserAction( 'PAY_NOW' );
        $paypal_wallet_experience_context->setReturnUrl( $order_dto->get_return_url() );
        $paypal_wallet_experience_context->setCancelUrl( $order_dto->get_cancel_url() );

        $paypal_wallet = new PaypalWallet();
        $paypal_wallet->setExperienceContext( $paypal_wallet_experience_context );

        $payment_source = new PaymentSource();
        $payment_source->setPaypal( $paypal_wallet );

        return $this->response(
            $this->sdk_client->getOrdersController()->createOrder(
                [
                    'body' => OrderRequestBuilder::init(
                        $order_dto->get_intent(), 
                        [ $purchase_unit_builder->build() ] 
                    )
                        ->paymentSource( $payment_source )
                        ->build(),
                ] 
            )
        );
    }

    private function format_amount( $number ): string {
        return number_format( $number, 2, '.', '' );
    }

    public function capture_order( $order_id ): array {
        return $this->response(
            $this->sdk_client->getOrdersController()->captureOrder( [ 'id' => $order_id ] )
        );
    }

    /**
     * @throws Exception
     */
    protected function response( ApiResponse $response ) {
        $result = $response->getResult();

        if ( is_object( $result ) ) {
            $result = json_decode( json_encode( $result ), true );
        }
        
        if ( $response->getStatusCode() >= 200 && $response->getStatusCode() < 300 ) {
            return $result;
        }

        $message = is_array( $result ) && ! empty( $result['message'] ) ? $result['message'] : 'Something went wrong, please try again.';

        //phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        throw new Exception( $message, $response->getStatusCode() );
    }
}
