<?php

declare(strict_types=1);

namespace App\Domain\Order;

use App\Domain\Catalog\CatalogClientInterface;
use App\Domain\Order\Exception\OrderNotFoundException;
use Cycle\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Spiral\Queue\QueueInterface;

final readonly class OrderService
{
    /** Ім'я задачі = контракт між сервісами (notifications підписаний саме на нього). */
    public const EVENT_ORDER_CREATED = 'order.created';

    public function __construct(
        private CatalogClientInterface $catalog,
        private OrderRepositoryInterface $orders,
        private EntityManagerInterface $em,
        private QueueInterface $queue,
        private LoggerInterface $logger,
    ) {}

    public function get(int $id): Order
    {
        return $this->orders->findById($id) ?? throw new OrderNotFoundException($id);
    }

    public function place(int $productId, int $quantity, string $customerEmail): Order
    {
        // 1. Синхронно: резервуємо товар у catalog-сервісі (транспорт — деталь CatalogClientInterface)
        $product = $this->catalog->reserve($productId, $quantity);

        // 2. Зберігаємо замовлення у власній БД
        $order = new Order($product->id, $product->name, $product->price, $quantity, $customerEmail);
        $this->em->persist($order)->run();

        // 3. Асинхронно: публікуємо подію в RabbitMQ — нас не цікавить, хто і коли її обробить.
        //    (У продакшені тут варто застосувати Transactional Outbox.)
        $this->queue->push(self::EVENT_ORDER_CREATED, [
            'order_id' => $order->getId(),
            'customer_email' => $order->getCustomerEmail(),
            'product_name' => $order->getProductName(),
            'quantity' => $order->getQuantity(),
            'total' => $order->getTotal(),
        ]);

        $this->logger->info('Order placed', ['order_id' => $order->getId()]);

        return $order;
    }
}
