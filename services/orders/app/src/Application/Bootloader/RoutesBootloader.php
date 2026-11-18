<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Endpoint\Web\Middleware\DomainExceptionMiddleware;
use Spiral\Bootloader\Http\RoutesBootloader as BaseRoutesBootloader;
use Spiral\Filter\ValidationHandlerMiddleware;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;
use Spiral\Http\Middleware\JsonPayloadMiddleware;

/**
 * Глобальні middleware + групи. Самі маршрути описані атрибутами #[Route] прямо в контролерах.
 */
final class RoutesBootloader extends BaseRoutesBootloader
{
    protected function globalMiddleware(): array
    {
        return [
            ErrorHandlerMiddleware::class,
            JsonPayloadMiddleware::class,      // JSON body -> parsed body
            ValidationHandlerMiddleware::class, // ValidationException -> 422 JSON
            DomainExceptionMiddleware::class,   // доменні помилки -> 404/409/503 JSON
        ];
    }

    protected function middlewareGroups(): array
    {
        return [];
    }
}
