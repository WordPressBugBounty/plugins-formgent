<?php

namespace FormGent\App\Contracts;

defined( "ABSPATH" ) || exit;

use FormGent\App\DTO\PayDTO;
use FormGent\App\DTO\PaymentReturnDTO;
use FormGent\WpMVC\RequestValidator\Validator;
use WP_REST_Request;

interface PaymentInterface {
    public static function get_key(): string;

    public function pay( PayDTO $dto );

    public function success( Validator $validator, WP_REST_Request $request ): PaymentReturnDTO;

    public function cancel( WP_REST_Request $request ): ?PaymentReturnDTO;
}
