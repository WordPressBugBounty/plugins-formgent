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

class PaymentServiceProvider implements Provider
{
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

        // Resolve the payment block field for subscription meta/validation.
        $payment_field_attr = null;
        foreach ( $fields as $field ) {
            if ( ( $field['field_type'] ?? '' ) === 'payment' ) {
                $payment_field_attr = $field;
                break;
            }
        }
        $is_subscription = ( ( $payment_field_attr['payment_type'] ?? '' ) === 'subscription' );

        // Enforce allowed gateways for subscriptions (filterable by pro).
        $allowed_gateways = apply_filters( 'formgent_subscription_allowed_gateways', ['stripe'], $payment_field_attr );
        if ( $is_subscription && ! in_array( $payment_gateway, $allowed_gateways, true ) ) {
            return array_merge(
                $response_data,
                [
                    'payment_data' => [
                        'success' => false,
                        'message' => __( 'The selected payment method does not support subscriptions.', 'formgent' ),
                        'code'    => 422,
                    ],
                ]
            );
        }

        // Build subscription meta (populated by formgent-pro).
        $subscription_meta = [];
        if ( $is_subscription && $payment_field_attr ) {
            $subscription_meta = apply_filters(
                'formgent_subscription_payment_meta',
                [],
                $payment_field_attr,
                $form_data
            );
        }

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
            ->set_order_items( $order_item_dtos )
            ->set_meta( $subscription_meta );

        try {
            $redirect_url = $processor->pay( $dto );

            // Persist subscription meta only after successful payment initiation.
            if ( $is_subscription && ! empty( $subscription_meta ) ) {
                $meta_value = wp_json_encode(
                    array_merge(
                        $subscription_meta,
                        [
                            'payment_id'  => $payment_dto->get_id(),
                            'order_id'    => $order_dto->get_id(),
                            'response_id' => $response->id,
                            'gateway'     => $payment_gateway,
                            'currency'    => $currency,
                        ]
                    )
                );
                formgent_response_repository()->update_meta(
                    $response->id,
                    'formgent_subscription_meta_' . $payment_dto->get_id(),
                    $meta_value
                );
            }

            $response_data['payment_data'] = [
                'success'      => true,
                'redirect_url' => $redirect_url,
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

        // Check if we have a unified payment block
        $has_unified_payment = false;
        foreach ( $fields as $name => $field ) {
            if ( $field['field_type'] === 'payment' ) {
                $has_unified_payment = true;
                break;
            }
        }

        // If unified payment block exists, use it exclusively
        if ( $has_unified_payment ) {
            return $this->build_unified_order_items( $fields, $form_data );
        }

        // Legacy path: payment-item + custom-payment-amount + quantity
        $quantity_fields = $this->get_quantity_field_map( $fields );

        foreach ( $fields as $name => $field ) {
            $quantity = abs(
                isset( $quantity_fields[$name], $form_data[$quantity_fields[$name]] )
                ? (int) $form_data[$quantity_fields[$name]]
                : 1
            );

            switch ( $field['field_type'] ) {
                case 'payment-item':
                    if ( ! isset( $form_data[$name] ) )
                        break;

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
                        case 'select':
                            $option = array_find(
                                $field['options'],
                                function ( $option ) use ( $form_data, $name ) {
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
                    if ( ! isset( $form_data[$name] ) )
                        break;

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

    /**
     * Build order items from the unified payment block configuration.
     */
    private function build_unified_order_items( array $fields, array $form_data ): array {
        $items = [];
        $total = 0;

        $payment_field = null;
        $payment_name  = null;

        foreach ( $fields as $name => $field ) {
            if ( $field['field_type'] === 'payment' ) {
                $payment_field = $field;
                $payment_name  = $name;
                break;
            }
        }

        if ( ! $payment_field ) {
            return [$items, $total];
        }

        $payment_type = $payment_field['payment_type'] ?? 'one_time';
        if ( $payment_type === 'subscription' ) {
            // Processing is delegated to formgent-pro via filter.
            return apply_filters( 'formgent_build_subscription_order_items', [$items, $total], $payment_field, $fields, $form_data );
        }
        if ( $payment_type !== 'one_time' ) {
            return [$items, $total];
        }

        $amount_type       = $payment_field['amount_type'] ?? 'fixed';
        $quantity_enabled  = ! empty( $payment_field['quantity_enabled'] );
        $quantity_apply_to = $payment_field['quantity_apply_to'] ?? [];

        if ( $amount_type === 'fixed' ) {
            // Fixed amount uses a single payment-level quantity input
            $quantity = 1;
            if ( $quantity_enabled ) {
                $qty_field_name = $payment_name . '_quantity';
                $quantity       = abs( (int) ( $form_data[$qty_field_name] ?? 1 ) );
                if ( $quantity < 1 )
                    $quantity   = 1;
            }

            $unit    = abs( (float) ( $payment_field['fixed_price'] ?? 0 ) );
            $amount  = $unit * $quantity;
            $items[] = [
                'title'        => $payment_field['fixed_label'] ?? __( 'Payment', 'formgent' ),
                'unit_amount'  => $unit,
                'quantity'     => $quantity,
                'total_amount' => $amount,
            ];
            $total  += $amount;
        } elseif ( $amount_type === 'from_fields' ) {
            // Amount from selected fields — each field has its own per-field quantity
            $amount_field_names = $payment_field['amount_fields'] ?? [];

            foreach ( $amount_field_names as $field_name ) {
                if ( ! isset( $fields[$field_name], $form_data[$field_name] ) ) {
                    continue;
                }

                $ref_field = $fields[$field_name];
                $ref_type  = $ref_field['field_type'] ?? '';

                // Read per-field quantity from form data
                $apply_qty = $quantity_enabled && ! empty( $quantity_apply_to ) && in_array( $field_name, $quantity_apply_to, true );
                if ( $apply_qty ) {
                    $per_field_qty_key               = $field_name . '_quantity';
                    $field_qty                       = abs( (int) ( $form_data[$per_field_qty_key] ?? 1 ) );
                    if ( $field_qty < 1 ) $field_qty = 1;
                } else {
                    $field_qty = 1;
                }

                switch ( $ref_type ) {
                    case 'number':
                    case 'custom-payment-amount':
                        $unit    = abs( (float) ( $form_data[$field_name] ?? 0 ) );
                        $amount  = $unit * $field_qty;
                        $items[] = [
                            'title'        => $ref_field['label'] ?? $field_name,
                            'unit_amount'  => $unit,
                            'quantity'     => $field_qty,
                            'total_amount' => $amount,
                        ];
                        $total  += $amount;
                        break;

                    case 'single-choice':
                    case 'dropdown':
                        // Single selection - match option price
                        if ( ! empty( $ref_field['options'] ) ) {
                            $selected_value = $form_data[$field_name];
                            // Could be an object like { value: true } or just a string
                            if ( is_array( $selected_value ) ) {
                                // Extract the actual selected value from the object
                                foreach ( $selected_value as $val => $checked ) {
                                    if ( $checked ) {
                                        $selected_value = $val;
                                        break;
                                    }
                                }
                            }
                            foreach ( $ref_field['options'] as $opt ) {
                                $raw_unit = $opt['price'] ?? ( $opt['numeric_value'] ?? ( $opt['numericValue'] ?? null ) );
                                if ( $opt['value'] === $selected_value && $raw_unit !== null && $raw_unit !== '' ) {
                                    $unit    = abs( (float) $raw_unit );
                                    $amount  = $unit * $field_qty;
                                    $items[] = [
                                        'title'        => $opt['label'] ?? $field_name,
                                        'unit_amount'  => $unit,
                                        'quantity'     => $field_qty,
                                        'total_amount' => $amount,
                                    ];
                                    $total  += $amount;
                                    break;
                                }
                            }
                        }
                        break;

                    case 'multiple-choice':
                        // Multi selection - sum selected options' prices
                        if ( ! empty( $ref_field['options'] ) && is_array( $form_data[$field_name] ) ) {
                            foreach ( $form_data[$field_name] as $val => $checked ) {
                                if ( ! $checked )
                                    continue;
                                foreach ( $ref_field['options'] as $opt ) {
                                    $raw_unit = $opt['price'] ?? ( $opt['numeric_value'] ?? ( $opt['numericValue'] ?? null ) );
                                    if ( $opt['value'] === $val && $raw_unit !== null && $raw_unit !== '' ) {
                                        $unit    = abs( (float) $raw_unit );
                                        $amount  = $unit * $field_qty;
                                        $items[] = [
                                            'title'        => $opt['label'] ?? $val,
                                            'unit_amount'  => $unit,
                                            'quantity'     => $field_qty,
                                            'total_amount' => $amount,
                                        ];
                                        $total  += $amount;
                                        break;
                                    }
                                }
                            }
                        }
                        break;
                }
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
