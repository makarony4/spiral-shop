<?php

declare(strict_types=1);

/**
 * Налаштування gRPC (spiral/roadrunner-bridge).
 * Код із .proto генерується командою `php app.php grpc:generate` (див. make proto).
 */
return [
    // Плагін protoc від RoadRunner (є лише в codegen-образі)
    'binaryPath' => '/usr/local/bin/protoc-gen-php-grpc',

    'generatedPath' => directory('root') . 'generated',
    'namespace' => 'GRPC',

    'servicesBasePath' => directory('root') . 'proto',
    'services' => [
        directory('root') . 'proto/catalog/v1/catalog.proto',
    ],

    // catalog — лише сервер: потрібні повідомлення + інтерфейс сервісу, клієнт не генеруємо
    'generators' => [],

    // Серверні interceptor-и (логування, auth, трейсинг...)
    'interceptors' => [
        \App\Endpoint\GRPC\Interceptor\DomainExceptionInterceptor::class,
    ],
];
