<?php

declare(strict_types=1);

namespace App\Domain\Order\Exception;

final class ProductNotFoundException extends HttpAwareException
{
    public function __construct(int $productId)
    {
        parent::__construct(\sprintf('Product #%d not found', $productId));
    }

    public function statusCode(): int
    {
        return 404;
    }
}
