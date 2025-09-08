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

    'paths' => ['api/*', 'admin/*', 'event/*', 'merch/*', 'membership/*'], // ✅ Tambahkan path yang digunakan di route kamu

    'allowed_methods' => ['*'], // izinkan semua metode: GET, POST, PUT, dll

    'allowed_origins' => ['*'], // izinkan semua origin (boleh fetch dari localhost, ngrok, dll)

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // izinkan semua header

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
