<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

final class ProductNotFoundException extends HttpAwareException
{
    public function __construct(int $id)
    {
        parent::__construct(\sprintf('Product #%d not found', $id));
    }

    public function statusCode(): int
    {
        return 404;
    }
}
