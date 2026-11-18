<?php

declare(strict_types=1);

return [
    'directory' => directory('app') . 'database/Migrations/',
    'table' => 'migrations',
    // У контейнері міграції запускаються без інтерактивного підтвердження
    'safe' => env('SAFE_MIGRATIONS', true),
];
