<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Http\Controllers\Admin\TemplateController;
use FormGent\App\Http\Controllers\Admin\OrderController;
use FormGent\App\Http\Controllers\Admin\PageController;
use FormGent\App\Http\Controllers\Admin\SummaryController;
use FormGent\App\Http\Controllers\Admin\NoteController;
use FormGent\App\Http\Controllers\Admin\EmailNotificationController;
use FormGent\App\Http\Controllers\Admin\GoogleSheetController;
use FormGent\App\Http\Controllers\Admin\GoogleSpreadsheetController;
use FormGent\App\Http\Controllers\Admin\MailchimpController;
use FormGent\App\Http\Controllers\Admin\SettingsController;
use FormGent\App\Http\Controllers\Admin\ResponseController;
use FormGent\App\Http\Controllers\Admin\FormController;
use FormGent\App\Http\Controllers\Admin\AnalyticsController;
use FormGent\App\Http\Controllers\Admin\ZohoCRMController;
use FormGent\App\Http\Controllers\Admin\ZohoCRMFeedController;
use FormGent\WpMVC\Routing\Route;
Route::group(
    'admin', function() {
        Route::get( 'page', [PageController::class, 'index'] );
        Route::group(
            'forms', function() {
                Route::group(
                    'email-notifications', function() {
                        Route::patch( '{id}/status', [EmailNotificationController::class, 'update_status'] );
                        Route::post( '{id}/duplicate', [EmailNotificationController::class, 'duplicate'] );
                        Route::resource( '/', EmailNotificationController::class );
                    }
                );
                Route::group(
                    '{id}', function() {
                        Route::get( 'settings', [FormController::class, 'get_settings'] );
                        Route::post( 'settings', [FormController::class, 'update_settings'] );
                        Route::patch( 'status', [FormController::class, 'update_status'] );
                        Route::patch( 'title', [FormController::class, 'update_title'] );
                        Route::post( 'duplicate', [FormController::class, 'duplicate'] );
                        Route::get( 'preset-fields', [FormController::class, 'get_preset_fields'] );

                        Route::group(
                            'summary', function() {
                                Route::get( '/', [SummaryController::class, 'index'] );
                                Route::get( 'field', [SummaryController::class, 'field'] );
                            }
                        );


                        Route::group(
                            'google/spreadsheets', function() {
                                Route::get( '/', [GoogleSpreadSheetController::class, 'index'] );
                                Route::post( '/', [GoogleSpreadSheetController::class, 'store'] );
                                Route::delete( '/{spread_id}', [GoogleSpreadSheetController::class, 'delete'] );
                                Route::patch( '/{spread_id}', [GoogleSpreadSheetController::class, 'update'] );
                                Route::patch( '/{spread_id}/status', [GoogleSpreadSheetController::class, 'update_status'] );
                            }
                        );

                        Route::group(
                            'mailchimp/feeds', function() {
                                Route::get( '/', [ MailchimpController::class, 'index' ] );
                                Route::post( '/', [ MailchimpController::class, 'store' ] );
                                Route::delete( '/{feed_id}', [ MailchimpController::class, 'delete' ] );
                                Route::patch( '/{feed_id}', [ MailchimpController::class, 'update' ] );
                                Route::patch( '/{feed_id}/status', [ MailchimpController::class, 'update_status' ] );
                            }
                        );
                    }
                );

                Route::post( 'status', [FormController::class, 'update_bulk_status'] );
                Route::get( 'select', [FormController::class, 'select'] );
                Route::post( 'media', [FormController::class, 'insert_media'] );
                Route::delete( '/', [FormController::class, 'delete_bulk_form'] );
                Route::resource( '/', FormController::class );
            }
        );
        Route::group(
            'responses', function() {
                Route::resource( 'notes', NoteController::class );
                Route::patch( '{id}/starred', [ResponseController::class, 'update_starred'] );
                Route::patch( '{id}/read', [ResponseController::class, 'update_read'] );
                Route::get( '{id}/quiz-result', [ResponseController::class, 'quiz_result'] );
                Route::group(
                    '{response_id}/order', function() {
                        Route::get( '/', [OrderController::class, 'order'] );
                        Route::patch( '/{id}/status', [OrderController::class, 'update_status'] );
                    } 
                );
                Route::get( 'table', [ResponseController::class, 'get_fields'] );
                Route::group(
                    'fields', function() {
                        Route::post( '/', [ResponseController::class, 'update_fields'] );
                    }
                );
                Route::get( 'export', [ResponseController::class, 'export'] );
                Route::get( 'single', [ResponseController::class, 'show'] );
                Route::delete( '/', [ResponseController::class, 'delete_bulk_response'] );
                Route::resource( '/', ResponseController::class );
            }
        );

        Route::group(
            'analytics', function() {
                Route::get( 'forms/{id}/summary', [ AnalyticsController::class, 'form_summary' ] );
            }
        );

        Route::get( 'settings', [SettingsController::class, 'index'] );
        Route::post( 'settings', [SettingsController::class, 'update'] );
        Route::group(
            'google', function() {
                Route::get( 'spreadsheets', [GoogleSheetController::class, 'index'] );
                Route::post( 'spreadsheets', [GoogleSheetController::class, 'store'] );
                Route::group(
                    'spreadsheets/{sheet_id}', function() {
                        Route::get( 'sheets', [GoogleSheetController::class, 'sheets'] );
                    }
                );
                Route::get( 'email', [GoogleSheetController::class, 'email'] );
                Route::post( 'disconnect', [GoogleSheetController::class, 'disconnect'] );
            }
        );

        Route::post( 'templates/insert-attachment', [TemplateController::class, 'insert_media'] );

        Route::group(
            'mailchimp', function() {
                Route::get( '/lists', [ MailchimpController::class, 'list' ] );
                Route::get( '/lists/{list_id}/groups', [ MailchimpController::class, 'list_groups' ] );
                Route::get( '/lists/{list_id}/groups/{group_id}/options', [ MailchimpController::class, 'list_group_options' ] );
                Route::get( '/lists/{list_id}/fields', [ MailchimpController::class, 'list_fields' ] );
                Route::get( '/lists/{list_id}/tags', [ MailchimpController::class, 'get_tags' ] );
            }
        );

        Route::group(
            'zohocrm', function() {
                Route::get( 'modules', [ZohoCRMController::class, 'get_modules'] );
                Route::get( 'fields/{module}', [ZohoCRMController::class, 'get_fields'] );

                Route::group(
                    'feeds', function() {
                        Route::patch( '{id}/status', [ZohoCRMFeedController::class, 'update_status'] );
                        Route::resource( '/', ZohoCRMFeedController::class );
                    }
                );
            }
        );
    }, ['admin']
);