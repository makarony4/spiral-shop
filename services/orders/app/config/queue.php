<?php

declare(strict_types=1);

return [
    'default' => env('QUEUE_CONNECTION', 'roadrunner'),

    'connections' => [
        'sync' => ['driver' => 'sync'],
        // Пайплайн "notifications" оголошений у .rr.yaml (AMQP / RabbitMQ)
        'roadrunner' => [
            'driver' => 'roadrunner',
            'pipeline' => 'notifications',
        ],
    ],

    // JSON замість PHP serialize(): повідомлення читає інший сервіс,
    // який нічого не знає про наші PHP-класи.
    'defaultSerializer' => 'json',
];
