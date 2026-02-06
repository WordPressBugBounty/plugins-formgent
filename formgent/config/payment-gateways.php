<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\PaymentProcessors\Stripe;
use FormGent\App\PaymentProcessors\Paypal;

return apply_filters(
    'formgent_payment_gateways', [
        Paypal::get_key() => [
            'processor'   => Paypal::class,
            'label'       => __( 'PayPal', 'formgent' ),
            'description' => __( 'PayPal is a widely used online payment system that allows users to make payments and transfer money securely over the internet.', 'formgent' )
        ],
        Stripe::get_key() => [
            'processor'   => Stripe::class,
            'label'       => __( 'Stripe', 'formgent' ),
            'description' => __( 'Stripe is a popular payment gateway that enables secure credit card payments and supports a wide range of currencies and payment methods.', 'formgent' ),
        ]
    ] 
);