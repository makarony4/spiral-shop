<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Domain\Order\Order;
use App\Domain\Order\OrderRepositoryInterface;
use Cycle\ORM\ORMInterface;
use Spiral\Boot\Bootloader\Bootloader;

final class AppBootloader extends Bootloader
{
    protected const SINGLETONS = [
        OrderRepositoryInterface::class => [self::class, 'orderRepository'],
    ];

    private function orderRepository(ORMInterface $orm): OrderRepositoryInterface
    {
        return $orm->getRepository(Order::class);
    }
}
