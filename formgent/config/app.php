<?php

defined( 'ABSPATH' ) || exit;

use FormGent\App\Providers\AppseroServiceProvider;
use FormGent\App\Http\Middleware\EnsureIsUserAdmin;
use FormGent\App\Http\Middleware\Zapier;
use FormGent\App\Providers\EmailNotificationServiceProvider;
use FormGent\App\Providers\BlockServiceProvider;
use FormGent\App\Providers\ElementorServiceProvider;
use FormGent\App\Providers\PostTypeServiceProvider;
use FormGent\App\Providers\Admin\MenuServiceProvider;
use FormGent\App\Providers\ShortCodeServiceProvider;
use FormGent\App\Providers\FileUploadServiceProvider;
use FormGent\App\Providers\SpreadsheetServiceProvider;
use FormGent\App\Providers\PaymentServiceProvider;
use FormGent\App\Providers\QueueServiceProvider;
use FormGent\App\Providers\QuizProvider;
use FormGent\App\Providers\WPMLCompatibilityProvider;
use FormGent\App\Providers\MailchimpProvider;
use FormGent\App\Providers\ZohoCRMServiceProvider;
use FormGent\App\Providers\DirectoristScriptProvider;
use FormGent\WpMVC\Helpers\Helpers;
use FormGent\Database\Migration\V122 as V122Migration;

return [
    'version'                 => Helpers::get_plugin_version( 'formgent' ),

    'rest_api'                => [
        'namespace' => 'formgent',
        'versions'  => []
    ],

    'ajax_api'                => [
        'namespace' => 'formgent',
        'versions'  => []
    ],

    'providers'               => [
        ShortCodeServiceProvider::class,
        ElementorServiceProvider::class,
        BlockServiceProvider::class,
        PostTypeServiceProvider::class,
        EmailNotificationServiceProvider::class,
        FileUploadServiceProvider::class,
        SpreadsheetServiceProvider::class,
        PaymentServiceProvider::class,
        QuizProvider::class,
        WPMLCompatibilityProvider::class,
        AppseroServiceProvider::class,
        MailchimpProvider::class,
        ZohoCRMServiceProvider::class,
        QueueServiceProvider::class,
        DirectoristScriptProvider::class,
    ],

    'admin_providers'         => [
        MenuServiceProvider::class,
    ],

    'middleware'              => [
        'admin'  => EnsureIsUserAdmin::class,
        'zapier' => Zapier::class
    ],

    'post_type'               => 'formgent_form',

    'migration_db_option_key' => 'formgent_migrations',

    'migrations'              => [
        '1.2.2' => V122Migration::class
    ]
];