<?php

declare(strict_types=1);

namespace App\Domain\Order\Exception;

final class CatalogUnavailableException extends HttpAwareException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Catalog service is unavailable, try again later', previous: $previous);
    }

    public function statusCode(): int
    {
        return 503;
    }
}
