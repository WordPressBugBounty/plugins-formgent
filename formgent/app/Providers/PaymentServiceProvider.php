<?php

namespace FormGent\App\Providers;

defined( 'ABSPATH' ) || exit;

use stdClass;
use WP_REST_Request;
use FormGent\App\DTO\OrderDTO;
use FormGent\App\DTO\OrderItemDTO;
use FormGent\App\DTO\PayDTO;
use FormGent\App\DTO\PaymentDTO;
use FormGent\WpMVC\Contracts\Provider;
use FormGent\WpMVC\Exceptions\Exception;
use FormGent\WpMVC\View\View;

class PaymentServiceProvider implements Provider {
    public function boot() {
        add_filter( 'formgent_form_submission_response', [$this, 'maybe_add_payment_data'], 10, 4 );
        add_shortcode( 'formgent_payment_success', [$this, 'payment_success_shortcode'] );
        add_shortcode( 'formgent_payment_failed', [$this, 'payment_failed_shortcode'] );
    }

    public function maybe_add_payment_data( array $response_data, WP_REST_Request $request, stdClass $form, stdClass $response ): array {
        if ( ! $response->is_completed || ! $request->get_param( 'payment_gateway' ) ) {
            return $response_data;
        }

        $payment_gateway  = $request->get_param( 'payment_gateway' );
        $payment_gateways = formgent_get_payment_gateways();

        if ( ! isset( $payment_gateways[$payment_gateway] ) ) {
            return $response_data;
        }

        try {
            $processor = formgent_payment_processor( $payment_gateway );
        } catch ( Exception $e ) {
            return $response_data;
        }

        $fields    = formgent_get_form_fields( $form );
        $form_data = $request->get_param( 'form_data' );

        [$order_items, $order_total_amount] = $this->build_order_items( $fields, $form_data );

        $currency = formgent_settings_repository()->get_by_key( 'payment', [] )['currency'] ?? 'USD';

        $order_dto = ( new OrderDTO )
            ->set_response_id( $response->id )
            ->set_amount( $order_total_amount )
            ->set_currency( $currency )
            ->set_final_amount( $order_total_amount )
            ->set_hash( wp_generate_uuid4() );

        $order_dto->set_id( formgent_order_repository()->create( $order_dto ) );

        $order_item_dtos = $this->store_order_items( $order_items, $order_dto->get_id() );
        $payment_dto     = ( new PaymentDTO )
            ->set_order_id( $order_dto->get_id() )
            ->set_amount( $order_total_amount )
            ->set_currency( $currency )
            ->set_method( $payment_gateway );

        $payment_dto->set_id( formgent_payment_repository()->create( $payment_dto ) );

        $dto = ( new PayDTO )
            ->set_order( $order_dto )
            ->set_payment( $payment_dto )
            ->set_order_items( $order_item_dtos );

        try {
            $response_data['payment_data'] = [
                'success'      => true,
                'redirect_url' => $processor->pay( $dto ),
            ];
        } catch ( Exception $e ) {
            $response_data['payment_data'] = [
                'success' => false,
                'message' => $e->getMessage(),
                'code'    => $e->getCode(),
            ];
        }

        return $response_data;
    }

    private function get_quantity_field_map( array $fields ): array {
        $map = [];
        foreach ( $fields as $name => $field ) {
            if ( $field['field_type'] === 'quantity' && ! empty( $field['field_mapping'] ) ) {
                $map[$field['field_mapping']] = $name;
            }
        }
        return $map;
    }

    private function build_order_items( array $fields, array $form_data ): array {
        $items = [];
        $total = 0;

        $quantity_fields = $this->get_quantity_field_map( $fields );

        foreach ( $fields as $name => $field ) {
            $quantity = abs(
                isset( $quantity_fields[$name], $form_data[$quantity_fields[$name]] )
                ? (int) $form_data[$quantity_fields[$name]]
                : 1
            );

            switch ( $field['field_type'] ) {
                case 'payment-item':
                    if ( ! isset( $form_data[$name] ) ) break;

                    $display_type = $field['product_display_type'] ?? 'single';

                    switch ( $display_type ) {
                        case 'single':
                            $unit    = abs( (float) ( $field['single_amount'] ?? 0 ) );
                            $amount  = $unit * $quantity;
                            $items[] = [
                                'title'        => $field['single_amount_label'],
                                'unit_amount'  => $unit,
                                'quantity'     => $quantity,
                                'total_amount' => $amount,
                            ];
                            $total  += $amount;
                            break;

                        case 'checkbox':
                            foreach ( $form_data[$name] as $val ) {
                                foreach ( $field['options'] as $opt ) {
                                    if ( $opt['value'] === $val ) {
                                        $unit    = abs( (float) $opt['price'] );
                                        $amount  = $unit * $quantity;
                                        $items[] = [
                                            'title'        => $opt['label'],
                                            'unit_amount'  => $unit,
                                            'quantity'     => $quantity,
                                            'total_amount' => $amount,
                                        ];
                                        $total  += $amount;
                                        break;
                                    }
                                }
                            }
                            break;

                        case 'radio':
                        case 'dropdown':
                            $option = array_find(
                                $field['options'], function ( $option ) use ( $form_data, $name ) {
                                    return $option['value'] === $form_data[$name];
                                }
                            );

                            if ( ! empty( $option['price'] ) ) {
                                $unit    = abs( (float) $option['price'] );
                                $amount  = $unit * $quantity;
                                $items[] = [
                                    'title'        => $option['label'],
                                    'unit_amount'  => $unit,
                                    'quantity'     => $quantity,
                                    'total_amount' => $amount,
                                ];
                                $total  += $amount;
                            }
                            break;
                    }
                    break;

                case 'custom-payment-amount':
                    if ( ! isset( $form_data[$name] ) ) break;

                    $unit   = abs( (float) ( $form_data[$name] ?? 0 ) );
                    $amount = $unit * $quantity;

                    $items[] = [
                        'title'        => $field['label'],
                        'unit_amount'  => $unit,
                        'quantity'     => $quantity,
                        'total_amount' => $amount,
                    ];
                    $total  += $amount;
                    break;
            }
        }

        return [$items, $total];
    }

    private function store_order_items( array $order_items, int $order_id ): array {
        $repository = formgent_order_item_repository();
        $dtos       = [];

        foreach ( $order_items as $item ) {
            $dto = ( new OrderItemDTO )
                ->set_order_id( $order_id )
                ->set_title( $item['title'] )
                ->set_unit_amount( $item['unit_amount'] )
                ->set_quantity( $item['quantity'] )
                ->set_total_amount( $item['total_amount'] );

            $dto->set_id( $repository->create( $dto ) );
            $dtos[] = $dto;
        }

        return $dtos;
    }

    public function payment_success_shortcode() {
        return View::get( 'shortcodes/payment-success' );
    }

    public function payment_failed_shortcode() {
        return View::get( 'shortcodes/payment-failed' );
    }
}
