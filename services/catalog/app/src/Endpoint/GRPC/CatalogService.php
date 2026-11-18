<?php

declare(strict_types=1);

namespace App\Endpoint\GRPC;

use App\Domain\Product\Product as ProductEntity;
use App\Domain\Product\ProductService;
use GRPC\Catalog\V1\CatalogServiceInterface;
use GRPC\Catalog\V1\GetProductRequest;
use GRPC\Catalog\V1\Product;
use GRPC\Catalog\V1\ReserveStockRequest;
use GRPC\Catalog\V1\ReserveStockResponse;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * gRPC-"контролер": реалізує інтерфейс, згенерований з proto/catalog/v1/catalog.proto.
 * Spiral знаходить його сам (tokenizer шукає реалізації ServiceInterface) — реєструвати не треба.
 * Доменні винятки перетворює на gRPC-статуси DomainExceptionInterceptor.
 */
final readonly class CatalogService implements CatalogServiceInterface
{
    public function __construct(private ProductService $products) {}

    public function GetProduct(ContextInterface $ctx, GetProductRequest $in): Product
    {
        return $this->toMessage($this->products->get((int) $in->getId()));
    }

    public function ReserveStock(ContextInterface $ctx, ReserveStockRequest $in): ReserveStockResponse
    {
        if ($in->getQuantity() < 1) {
            throw GRPCException::create('quantity must be >= 1', StatusCode::INVALID_ARGUMENT);
        }

        $product = $this->products->reserve((int) $in->getProductId(), $in->getQuantity());

        return new ReserveStockResponse(['product' => $this->toMessage($product)]);
    }

    private function toMessage(ProductEntity $product): Product
    {
        return new Product([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
        ]);
    }
}
