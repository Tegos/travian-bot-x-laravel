<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'travian' => [
        'domain' => env('TRAVIAN_DOMAIN'),
        'login' => env('TRAVIAN_LOGIN'),
        'password' => env('TRAVIAN_PASSWORD'),
        'farm_list_enabled' => boolval(env('TRAVIAN_FARM_LIST_ENABLED', 1)),
        'profile_observe_enabled' => boolval(env('TRAVIAN_PROFILE_OBSERVE_ENABLED', 0)),
        'profile_observe_list' => explode(',', env('TRAVIAN_PROFILE_OBSERVE_LIST')),
        'timezone' => env('TRAVIAN_TIMEZONE'),
        'auction_ignored_items' => explode(',', env('TRAVIAN_AUCTION_IGNORED_ITEMS')),
        'min_horses_amount' => env('TRAVIAN_MIN_HORSES_AMOUNT', 100),
    ],
];
