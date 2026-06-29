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

    'crm_integration' => [
        'key' => env('CRM_INTEGRATION_KEY'),
    ],

    'business_central' => [
        'tenant_id' => env('BC_TENANT_ID'),
        'client_id' => env('BC_CLIENT_ID'),
        'client_secret' => env('BC_CLIENT_SECRET'),
        'environment' => env('BC_ENVIRONMENT', 'Production'),
        'product_measures_endpoint' => env('BC_PRODUCT_MEASURES_ENDPOINT', 'api/juan/app1/v2.1/pruebaCotizador'),
        'product_measures_cache_ttl' => env('BC_PRODUCT_MEASURES_CACHE_TTL', 900),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
