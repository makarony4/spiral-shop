<?php

declare(strict_types=1);

use App\Endpoint\Job\OrderCreatedHandler;

return [
    'default' => 'roadrunner',

    'connections' => [
        'roadrunner' => ['driver' => 'roadrunner', 'pipeline' => 'notifications'],
    ],

    // Маппінг "ім'я задачі" -> обробник. Ім'я — це контракт з orders-сервісом.
    'registry' => [
        'handlers' => [
            'order.created' => OrderCreatedHandler::class,
        ],
    ],

    'defaultSerializer' => 'json',
];
