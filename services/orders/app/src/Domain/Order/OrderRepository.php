<?php

declare(strict_types=1);

namespace App\Domain\Order;

use Cycle\ORM\Select\Repository;

/**
 * @extends Repository<Order>
 */
final class OrderRepository extends Repository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return $this->findByPK($id);
    }

    public function latest(int $limit, ?string $customerEmail = null): array
    {
        $select = $this->select()->orderBy('id', 'DESC')->limit($limit);

        if ($customerEmail !== null) {
            $select->where('customerEmail', $customerEmail);
        }

        return $select->fetchAll();
    }

    public function cancelled(int $limit, ?string $customerEmail = null): array
    {
        $select = $this->select()->where('cancelledAt', '!=', null)->orderBy('id', 'DESC')->limit($limit);

        if ($customerEmail !== null) {
            $select->where('customerEmail', $customerEmail);
        }

        return $select->fetchAll();
    }
}
