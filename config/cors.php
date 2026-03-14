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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'mobile/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins_patterns' => [
        'http://localhost:*',
        'http://127.0.0.1:*',
        'https://localhost:*',
        'https://127.0.0.1:*',
    ],
    
    'allowed_origins' => [
        'http://localhost:1118',
        'http://127.0.0.1:1118',
        'http://localhost:7233',
        'http://127.0.0.1:7233',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
        // Flutter development server
        'http://localhost:8080',
        'http://127.0.0.1:8080',
        // Flutter web build
        'http://localhost:5000',
        'http://127.0.0.1:5000',
        // Mobile app origins
        'capacitor://localhost',
        'ionic://localhost',
        'http://localhost',
        'https://localhost',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-Socket-ID',
        'Origin',
        'Cache-Control',
        'Pragma',
        'Expires',
        'Last-Modified',
        'If-Modified-Since',
        'If-Unmodified-Since',
        'If-None-Match',
        'If-Match',
        'Range',
        'Content-Range',
        'X-HTTP-Method-Override',
    ],

    'exposed_headers' => [
        'Authorization',
        'X-Pagination-Current-Page',
        'X-Pagination-Page-Count',
        'X-Pagination-Per-Page',
        'X-Pagination-Total-Count',
    ],

    'max_age' => 86400, // 24 hours

    'supports_credentials' => true,

];
