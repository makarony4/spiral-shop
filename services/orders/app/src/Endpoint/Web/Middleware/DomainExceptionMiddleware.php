<?php

declare(strict_types=1);

namespace App\Endpoint\Web\Middleware;

use App\Domain\Order\Exception\HttpAwareException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\ResponseWrapper;

/**
 * Звичайний PSR-15 middleware: доменні винятки -> JSON-відповідь з потрібним статусом.
 */
final readonly class DomainExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseWrapper $response) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (HttpAwareException $e) {
            return $this->response->json(['error' => $e->getMessage()], $e->statusCode());
        }
    }
}
