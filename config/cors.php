<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8080', // Development
        'http://localhost:8001', // Vue dev server
        'https://re-teech.my.id', // Your domain
        'https://www.re-teech.my.id', // WWW version
        'https://kasku-ruby.vercel.app/',
        'https://www.kasku-ruby.vercel.app/'
    ],

   'allowed_origins_patterns' => [
        '/^https?:\/\/re-teech.my.id/\.com/', // Allow semua subfolder
        '/^https?:\/\/www\.re-teech.my.id/\.com/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
