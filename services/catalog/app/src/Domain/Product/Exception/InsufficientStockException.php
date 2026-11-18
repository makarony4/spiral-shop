<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

final class InsufficientStockException extends HttpAwareException
{
    public function __construct(int $id, int $requested)
    {
        parent::__construct(\sprintf('Not enough stock for product #%d (requested %d)', $id, $requested));
    }

    public function statusCode(): int
    {
        return 409;
    }
}
