<?php

declare(strict_types=1);

namespace App\Domain\Order\Exception;

final class OutOfStockException extends HttpAwareException
{
    public function __construct(int $productId)
    {
        parent::__construct(\sprintf('Product #%d is out of stock', $productId));
    }

    public function statusCode(): int
    {
        return 409;
    }
}
