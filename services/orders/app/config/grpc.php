<?php

declare(strict_types=1);

/**
 * orders — gRPC-КЛІЄНТ catalog. `php app.php grpc:generate` (make proto) генерує:
 *  - повідомлення та CatalogServiceInterface (як і в catalog),
 *  - CatalogServiceClient   — реалізацію інтерфейсу поверх ext-grpc,
 *  - Config/GRPCServicesConfig + Bootloader/ServiceBootloader — DI-зв'язки та адреси сервісів.
 */
return [
    'binaryPath' => '/usr/local/bin/protoc-gen-php-grpc',

    'generatedPath' => directory('root') . 'generated',
    'namespace' => 'GRPC',

    'servicesBasePath' => directory('root') . 'proto',
    'services' => [
        directory('root') . 'proto/catalog/v1/catalog.proto',
    ],
    // 'generators' не перевизначаємо — за замовчуванням генеруються клієнт, конфіг і bootloader
];
