<?php

declare(strict_types=1);

namespace App\Infrastructure\Grpc;

use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * Згенерований клієнт повертає [$response, $status] і статус ІГНОРУЄ:
 * на помилку сервера ми отримали б null замість відповіді.
 * Цей interceptor перетворює будь-який не-OK статус на GRPCException з тим самим кодом.
 */
final class ThrowOnErrorStatusInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        [$response, $status] = $core->callAction($controller, $action, $parameters);

        if ($status->code !== StatusCode::OK) {
            throw GRPCException::create(
                \sprintf('%s failed: %s', $action, $status->details),
                $status->code,
            );
        }

        return [$response, $status];
    }
}
