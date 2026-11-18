<?php

declare(strict_types=1);

namespace App\Domain\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    /** @return list<Product> */
    public function list(int $limit, int $offset): array;

    public function count(): int;
}
