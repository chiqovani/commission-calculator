<?php

define('BASE_CURRENCY', 'EUR');

return [
    'api_key' => 'f347eccf3286b6bce05efe3d56612a92',
    'api_base_url' => 'http://api.exchangeratesapi.io/v1/',

    'fees' => [
        'deposit' => 0.0003, // 0.03%
        'withdraw' => [
            'private' => [
                'rate' => 0.003, // 0.3%
                'weekly_free_limit' => 1000.00,
                'free_operations_limit' => 3,
            ],
            'business' => [
                'rate' => 0.005, // 0.5%
            ],
        ],
    ],

    'currency_precision' => [
        'EUR' => 2,
        'USD' => 2,
        'JPY' => 0,
    ],
];