<?php

return [

    'defaults' => [
        'guard' => 'web', // Set 'web' as the default guard for web-based (admin) authentication
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // JWT-based API guard for API authentication
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
            // 'hash' => false,
        ],


        // __users quard
        'user' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],

        // __business guard
        'business' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],

        // __admin guard
        'admin' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ]




    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
