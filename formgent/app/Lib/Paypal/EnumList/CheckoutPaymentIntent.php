<?php

namespace FormGent\App\Lib\Paypal\EnumList;

defined( 'ABSPATH' ) || exit;

class CheckoutPaymentIntent {
    public const CAPTURE = 'CAPTURE';

    public const AUTHORIZE = 'AUTHORIZE';

    public static function is_valid( string $key ): bool {
        return in_array( $key, [ self::AUTHORIZE, self::CAPTURE ], true );
    }
}