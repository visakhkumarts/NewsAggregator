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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | News API Services
    |--------------------------------------------------------------------------
    |
    | Configuration for various news API services used by the aggregator.
    |
    */

    'newsapi' => [
        'key' => env('NEWS_API_KEY'),
        'base_url' => 'https://newsapi.org/v2/',
        'rate_limit' => 1000, // requests per day
    ],

    'guardian' => [
        'key' => env('GUARDIAN_API_KEY'),
        'base_url' => 'https://content.guardianapis.com/',
        'rate_limit' => 5000, // requests per day
    ],

    'nytimes' => [
        'key' => env('NYTIMES_API_KEY'),
        'base_url' => 'https://api.nytimes.com/svc/',
        'rate_limit' => 1000, // requests per day
    ],

];