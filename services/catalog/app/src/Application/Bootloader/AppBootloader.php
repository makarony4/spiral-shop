<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Domain\Product\Product;
use App\Domain\Product\ProductRepositoryInterface;
use Cycle\ORM\ORMInterface;
use Spiral\Boot\Bootloader\Bootloader;

/**
 * Аналог ServiceProvider у Laravel: тут описуємо DI-зв'язки застосунку.
 */
final class AppBootloader extends Bootloader
{
    protected const SINGLETONS = [
        ProductRepositoryInterface::class => [self::class, 'productRepository'],
    ];

    private function productRepository(ORMInterface $orm): ProductRepositoryInterface
    {
        return $orm->getRepository(Product::class);
    }
}
