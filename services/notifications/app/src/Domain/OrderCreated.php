<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Локальне представлення події з orders-сервісу.
 * Сервіси не шарять PHP-класи — лише JSON-контракт.
 */
final readonly class OrderCreated
{
    public function __construct(
        public int $orderId,
        public string $customerEmail,
        public string $productName,
        public int $quantity,
        public int $total,
    ) {}

    public static function fromPayload(array $payload): self
    {
        return new self(
            orderId: (int) $payload['order_id'],
            customerEmail: (string) $payload['customer_email'],
            productName: (string) $payload['product_name'],
            quantity: (int) $payload['quantity'],
            total: (int) $payload['total'],
        );
    }
}
