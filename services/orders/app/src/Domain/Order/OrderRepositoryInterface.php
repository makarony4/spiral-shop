<?php

declare(strict_types=1);

namespace App\Domain\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    /** @return list<Order> */
    public function latest(int $limit, ?string $customerEmail = null): array;

    /** @return list<Order> */
    public function cancelled(int $limit, ?string $customerEmail = null): array;
}
