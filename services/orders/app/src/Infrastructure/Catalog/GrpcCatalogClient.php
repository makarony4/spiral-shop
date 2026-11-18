<?php

declare(strict_types=1);

namespace App\Infrastructure\Catalog;

use App\Domain\Catalog\CatalogClientInterface;
use App\Domain\Catalog\ReservedProduct;
use App\Domain\Order\Exception\CatalogUnavailableException;
use App\Domain\Order\Exception\OutOfStockException;
use App\Domain\Order\Exception\ProductNotFoundException;
use GRPC\Catalog\V1\CatalogServiceInterface;
use GRPC\Catalog\V1\ReserveStockRequest;
use Spiral\RoadRunner\GRPC\Context;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * Адаптер: домен orders знає лише про CatalogClientInterface,
 * а як саме ми ходимо в catalog (раніше HTTP, тепер gRPC) — деталь інфраструктури.
 *
 * CatalogServiceInterface тут — згенерований CatalogServiceClient (див. generated/Bootloader/ServiceBootloader.php).
 */
final readonly class GrpcCatalogClient implements CatalogClientInterface
{
    public function __construct(
        private CatalogServiceInterface $catalog,
        private int $timeoutMs = 2000,
    ) {}

    public function reserve(int $productId, int $quantity): ReservedProduct
    {
        // Дедлайн обов'язковий: без нього завислий catalog "з'їсть" HTTP-воркер orders
        $ctx = new Context(['options' => ['timeout' => $this->timeoutMs * 1000]]); // мікросекунди

        try {
            $response = $this->catalog->ReserveStock($ctx, new ReserveStockRequest([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]));
        } catch (GRPCException $e) {
            throw match ($e->getCode()) {
                StatusCode::NOT_FOUND => new ProductNotFoundException($productId),
                StatusCode::FAILED_PRECONDITION => new OutOfStockException($productId),
                default => new CatalogUnavailableException($e),
            };
        }

        $product = $response->getProduct();

        return new ReservedProduct((int) $product->getId(), $product->getName(), (int) $product->getPrice());
    }
}
