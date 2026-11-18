<?php

declare(strict_types=1);

namespace App\Domain\Order\Exception;

use App\Domain\Order\OrderStatus;

final class OrderCannotBeCancelledException extends HttpAwareException
{
    public function __construct(int $orderId, OrderStatus $status)
    {
        parent::__construct(\sprintf('Order #%d cannot be cancelled in status "%s"', $orderId, $status->value));
    }

    public function statusCode(): int
    {
        return 409;
    }
}
