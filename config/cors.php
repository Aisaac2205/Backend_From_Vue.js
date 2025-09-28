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
        env('FRONTEND_URL', 'http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com'),
        'http://localhost:5173', // Vite dev server (para desarrollo local)
        'http://127.0.0.1:5173',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
        'http://ec2-18-219-51-191.us-east-2.compute.amazonaws.com',
        'https://ec2-18-219-51-191.us-east-2.compute.amazonaws.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
