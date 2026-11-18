<?php

declare(strict_types=1);

namespace App\Domain\Order;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Table\Index;

#[Entity(table: 'orders', repository: OrderRepository::class)]
#[Index(columns: ['customer_email'])]
class Order
{
    #[Column(type: 'primary')]
    private int $id;

    #[Column(type: 'integer', name: 'product_id')]
    private int $productId;

    /** Денормалізуємо назву/ціну: замовлення не повинно змінюватися, якщо товар відредагують у каталозі. */
    #[Column(type: 'string', name: 'product_name')]
    private string $productName;

    #[Column(type: 'integer', name: 'unit_price')]
    private int $unitPrice;

    #[Column(type: 'integer')]
    private int $quantity;

    #[Column(type: 'string', name: 'customer_email')]
    private string $customerEmail;

    #[Column(type: 'string', typecast: OrderStatus::class)]
    private OrderStatus $status;

    #[Column(type: 'datetime', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct(int $productId, string $productName, int $unitPrice, int $quantity, string $customerEmail)
    {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
        $this->customerEmail = $customerEmail;
        $this->status = OrderStatus::Created;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getProductId(): int { return $this->productId; }
    public function getProductName(): string { return $this->productName; }
    public function getUnitPrice(): int { return $this->unitPrice; }
    public function getQuantity(): int { return $this->quantity; }
    public function getTotal(): int { return $this->unitPrice * $this->quantity; }
    public function getCustomerEmail(): string { return $this->customerEmail; }
    public function getStatus(): OrderStatus { return $this->status; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
