<?php

namespace FormGent\App\Http\Controllers;

defined( 'ABSPATH' ) || exit;

use FormGent\App\PaymentProcessors\Mollie;
use FormGent\WpMVC\Routing\Response;
use WP_REST_Request;

class WebhookController extends Controller {
    public function mollie( WP_REST_Request $request ): array {
        $processor = formgent_singleton( Mollie::class );
        $processor->handle_webhook( $request );

        return Response::send(
            [
                'status' => 'ok',
            ]
        );
    }
}
