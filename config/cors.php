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

    'paths' => ['api/*', 'sanctum/csrf-cookie',],
    'allowed_methods' => ['*'],
    // Allows requests from any domain
    'allowed_origins' => [
        // '*'
        'http://192.168.8.182:9000', // 👈 Add this
        'http://localhost:9000',
        'http://localhost:9001',
        'http://localhost:9002',
        // 'http://localhost:8000',
        // 'http://192.168.100.105:9000',
        'http://192.168.8.80:9000',
        'http://192.168.8.80:9001',
        'http://10.0.1.35:9000',
        'http://192.168.8.182:7000',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,

];
