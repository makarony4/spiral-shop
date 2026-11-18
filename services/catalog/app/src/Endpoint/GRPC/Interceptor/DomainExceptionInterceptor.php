<?php

declare(strict_types=1);

namespace App\Endpoint\GRPC\Interceptor;

use App\Domain\Product\Exception\InsufficientStockException;
use App\Domain\Product\Exception\ProductNotFoundException;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\CoreInterface;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * Аналог DomainExceptionMiddleware для HTTP: доменні винятки -> gRPC-статуси.
 * Підключається в app/config/grpc.php (секція interceptors).
 */
final class DomainExceptionInterceptor implements CoreInterceptorInterface
{
    public function process(string $controller, string $action, array $parameters, CoreInterface $core): mixed
    {
        try {
            return $core->callAction($controller, $action, $parameters);
        } catch (ProductNotFoundException $e) {
            throw GRPCException::create($e->getMessage(), StatusCode::NOT_FOUND, $e);
        } catch (InsufficientStockException $e) {
            throw GRPCException::create($e->getMessage(), StatusCode::FAILED_PRECONDITION, $e);
        }
    }
}
