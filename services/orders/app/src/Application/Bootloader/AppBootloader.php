<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Domain\Catalog\CatalogClientInterface;
use App\Domain\Order\Order;
use App\Domain\Order\OrderRepositoryInterface;
use App\Infrastructure\Catalog\GrpcCatalogClient;
use Cycle\ORM\ORMInterface;
use GRPC\Bootloader\ServiceBootloader;
use GRPC\Catalog\V1\CatalogServiceInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;

final class AppBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        // Згенерований bootloader: прив'язує CatalogServiceInterface -> CatalogServiceClient (ext-grpc)
        ServiceBootloader::class,
    ];

    protected const SINGLETONS = [
        OrderRepositoryInterface::class => [self::class, 'orderRepository'],
        CatalogClientInterface::class => [self::class, 'catalogClient'],
    ];

    private function orderRepository(ORMInterface $orm): OrderRepositoryInterface
    {
        return $orm->getRepository(Order::class);
    }

    private function catalogClient(CatalogServiceInterface $catalog, EnvironmentInterface $env): CatalogClientInterface
    {
        return new GrpcCatalogClient($catalog, (int) $env->get('CATALOG_GRPC_TIMEOUT_MS', 2000));
    }
}
