<?php

declare(strict_types=1);

namespace App\Domain\Product;

use App\Domain\Product\Exception\InsufficientStockException;
use App\Domain\Product\Exception\ProductNotFoundException;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Injection\Expression;
use Cycle\ORM\EntityManagerInterface;

final readonly class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private EntityManagerInterface $em,
        private DatabaseInterface $db,
    ) {}

    public function get(int $id): Product
    {
        return $this->products->findById($id) ?? throw new ProductNotFoundException($id);
    }

    public function create(string $name, string $description, int $price, int $stock): Product
    {
        $product = new Product($name, $description, $price, $stock);
        $this->em->persist($product)->run();

        return $product;
    }

    /**
     * Атомарне списання залишку одним UPDATE ... WHERE stock >= ? —
     * без гонок між паралельними воркерами RoadRunner.
     */
    public function reserve(int $id, int $quantity): Product
    {
        $affected = $this->db
            ->update('products', ['stock' => new Expression('stock - ?', $quantity)], [
                'id' => $id,
                'stock' => ['>=' => $quantity],
            ])
            ->run();

        $product = $this->get($id);

        if ($affected === 0) {
            throw new InsufficientStockException($id, $quantity);
        }

        return $product;
    }

    public function release(int $id, int $quantity): Product
    {
        $affected = $this->db
            ->update('products', ['stock' => new Expression('stock + ?', $quantity)], [
                'id' => $id,
            ])
            ->run();

        if ($affected === 0) {
            throw new ProductNotFoundException($id);
        }

        return $this->get($id);
    }
}
