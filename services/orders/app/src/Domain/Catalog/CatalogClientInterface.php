<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

interface CatalogClientInterface
{
    /**
     * @throws \App\Domain\Order\Exception\ProductNotFoundException
     * @throws \App\Domain\Order\Exception\OutOfStockException
     * @throws \App\Domain\Order\Exception\CatalogUnavailableException
     */
    public function reserve(int $productId, int $quantity): ReservedProduct;
}
