<?php

declare(strict_types=1);

use Cycle\Database\Config;

return [
    'default' => 'default',

    'databases' => [
        'default' => ['driver' => 'pgsql'],
    ],

    'drivers' => [
        'pgsql' => new Config\PostgresDriverConfig(
            connection: new Config\Postgres\TcpConnectionConfig(
                database: env('DB_DATABASE', 'orders'),
                host: env('DB_HOST', 'postgres'),
                port: (int) env('DB_PORT', 5432),
                user: env('DB_USERNAME', 'shop'),
                password: env('DB_PASSWORD', 'secret'),
            ),
            schema: 'public',
            queryCache: true,
        ),
    ],
];
