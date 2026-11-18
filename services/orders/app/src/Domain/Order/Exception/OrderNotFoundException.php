<?php

declare(strict_types=1);

namespace App\Domain\Order\Exception;

final class OrderNotFoundException extends HttpAwareException
{
    public function __construct(int $orderId)
    {
        parent::__construct(\sprintf('Order #%d not found', $orderId));
    }

    public function statusCode(): int
    {
        return 404;
    }
}
