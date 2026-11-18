<?php

declare(strict_types=1);

namespace App\Endpoint\Console;

use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Product\ProductService;
use Spiral\Console\Attribute\AsCommand;
use Spiral\Console\Command;

/**
 * Аналог artisan-команди: php app.php products:seed
 */
#[AsCommand(name: 'products:seed', description: 'Fill catalog with demo products')]
final class SeedProductsCommand extends Command
{
    public function __invoke(ProductService $service, ProductRepositoryInterface $products): int
    {
        if ($products->count() > 0) {
            $this->info('Catalog is not empty, skipping.');

            return self::SUCCESS;
        }

        $items = [
            ['Mechanical keyboard', 'Hot-swap, brown switches', 349900, 10],
            ['Wireless mouse', 'Silent clicks, 2.4GHz + BT', 89900, 25],
            ['USB-C hub', '7-in-1, HDMI 4K', 129900, 5],
        ];

        foreach ($items as [$name, $description, $price, $stock]) {
            $product = $service->create($name, $description, $price, $stock);
            $this->writeln(\sprintf('Created #%d %s', $product->getId(), $name));
        }

        return self::SUCCESS;
    }
}
