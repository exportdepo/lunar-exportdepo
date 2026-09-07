<?php

return [

    'default' => env('PAYMENTS_TYPE', 'cash-in-hand'),

    'types' => [
        'cash-in-hand' => [
            'driver' => 'offline',
            'authorized' => 'payment-offline',
        ],
            'stripe' => [
                'driver' => 'stripe',
                'secret' => env('STRIPE_SECRET'),
                'success_url' => env('STRIPE_SUCCESS_URL', env('APP_URL').'/checkout'),
                'cancel_url' => env('STRIPE_CANCEL_URL', env('APP_URL').'/checkout'),
            ],
    ],

];
