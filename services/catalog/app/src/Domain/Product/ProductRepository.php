<?php

declare(strict_types=1);

namespace App\Domain\Product;

use Cycle\ORM\Select\Repository;

/**
 * @extends Repository<Product>
 */
final class ProductRepository extends Repository implements ProductRepositoryInterface
{
    public function findById(int $id): ?Product
    {
        return $this->findByPK($id);
    }

    public function list(int $limit, int $offset): array
    {
        return $this->select()->orderBy('id')->limit($limit)->offset($offset)->fetchAll();
    }

    public function count(): int
    {
        return $this->select()->count();
    }
}
