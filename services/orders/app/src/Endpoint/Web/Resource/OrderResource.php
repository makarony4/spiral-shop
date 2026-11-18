<?php

declare(strict_types=1);

namespace App\Endpoint\Web\Resource;

use App\Domain\Order\Order;

final readonly class OrderResource implements \JsonSerializable
{
    public function __construct(private Order $order) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->order->getId(),
            'status' => $this->order->getStatus()->value,
            'product' => [
                'id' => $this->order->getProductId(),
                'name' => $this->order->getProductName(),
            ],
            'unit_price' => $this->order->getUnitPrice(),
            'quantity' => $this->order->getQuantity(),
            'total' => $this->order->getTotal(),
            'customer_email' => $this->order->getCustomerEmail(),
            'created_at' => $this->order->getCreatedAt()->format(\DATE_ATOM),
        ];
    }
}
