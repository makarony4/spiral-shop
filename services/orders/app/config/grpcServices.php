<?php

declare(strict_types=1);

use App\Infrastructure\Grpc\ThrowOnErrorStatusInterceptor;

/**
 * Конфіг gRPC-клієнтів (клас GRPC\Config\GRPCServicesConfig згенерований).
 * Адреси сервісів задає згенерований ServiceBootloader через env (CATALOG_SERVICE_HOST).
 */
return [
    // Клієнтські interceptor-и: тут же можна додати логування, трейсинг, передачу request-id у metadata
    'interceptors' => [
        ThrowOnErrorStatusInterceptor::class,
    ],
];
