<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\PaymentController;
use FormGent\App\Http\Controllers\CaptchaController;
use FormGent\App\Http\Controllers\ZapierController;
use FormGent\App\Http\Controllers\AnalyticsController;
use FormGent\App\Http\Controllers\ResponseController;
use FormGent\App\Http\Controllers\UserController;
use FormGent\App\Http\Controllers\AttachmentController;
// use FormGent\App\Http\Controllers\FormController;
use FormGent\WpMVC\Routing\Route;
use FormGent\App\Jobs\Queue;
use FormGent\WpMVC\Routing\Response;

include __DIR__ . '/admin.php';

Route::get( 'payment/success/{payment_gateway}', [PaymentController::class, 'success'] );
Route::get( 'payment/cancel/{payment_gateway}', [PaymentController::class, 'cancel'] );
Route::get( 'payment/retry/{order_hash}', [PaymentController::class, 'retry'] );

Route::group(
    'responses', function() {
        Route::post( '/', [ResponseController::class, 'store'] );
        Route::post( 'attachments', [AttachmentController::class, 'store'] );
        Route::delete( 'attachments', [AttachmentController::class, 'delete'] );
    }
);

Route::post( 'responses/generate-token', [ResponseController::class, 'generate_token'] );
Route::get( 'countries', [UserController::class, 'get_countries'] );
// Route::get( 'forms/{id}', [FormController::class, 'show'] );

Route::group(
    'analytics', function() {
        Route::post( 'forms/{id}/update-view-count', [ AnalyticsController::class, 'increment_or_decrement_form_view_count' ], [] );
    }
);

Route::group(
    'zapier', function() {
        Route::get( '/me', [ZapierController::class, 'me'] );
        Route::get( '/forms', [ZapierController::class, 'forms'] );
        Route::get( '/responses', [ZapierController::class, 'responses'] );
    }, ['zapier']
);

Route::group(
    'captcha', function() {
        Route::post( 'google', [CaptchaController::class, 'google'] );
        Route::post( 'hcaptcha', [CaptchaController::class, 'hcaptcha'] );
        Route::post( 'turnstile', [CaptchaController::class, 'turnstile'] );
    }
);

Route::post(
    'queue/dispatch', function() {
        $queue = formgent_singleton( Queue::class );
        $queue->dispatch_queue();

        return Response::send( [] );
    } 
);