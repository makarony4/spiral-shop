<?php

declare(strict_types=1);

namespace GRPC\Catalog\V1;

use Spiral\Core\InterceptableCore;
use Spiral\RoadRunner\GRPC\ContextInterface;

class CatalogServiceClient implements CatalogServiceInterface
{
    public function __construct(
        private readonly InterceptableCore $core,
    ) {
    }

    public function GetProduct(ContextInterface $ctx, GetProductRequest $in): Product
    {
        [$response, $status] = $this->core->callAction(CatalogServiceInterface::class, '/'.self::NAME.'/GetProduct', [
            'in' => $in,
            'ctx' => $ctx,
            'responseClass' => \GRPC\Catalog\V1\Product::class,
        ]);

        return $response;
    }

    public function ReserveStock(ContextInterface $ctx, ReserveStockRequest $in): ReserveStockResponse
    {
        [$response, $status] = $this->core->callAction(CatalogServiceInterface::class, '/'.self::NAME.'/ReserveStock', [
            'in' => $in,
            'ctx' => $ctx,
            'responseClass' => \GRPC\Catalog\V1\ReserveStockResponse::class,
        ]);

        return $response;
    }
}
