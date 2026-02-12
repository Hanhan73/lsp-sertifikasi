<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Server Key dan Client Key dari Midtrans Dashboard
    | https://dashboard.midtrans.com/
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Set to true untuk production environment
    | Set to false untuk sandbox/testing
    |
    */
    
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    
    /*
    |--------------------------------------------------------------------------
    | Sanitized & 3DS
    |--------------------------------------------------------------------------
    |
    | Sanitized: Set to true untuk sanitize input data
    | 3DS: Set to true untuk enable 3D Secure authentication
    |
    */
    
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];