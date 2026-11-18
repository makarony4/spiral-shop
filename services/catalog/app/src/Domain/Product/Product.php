<?php

declare(strict_types=1);

namespace App\Domain\Product;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

/**
 * Cycle ORM — DataMapper (як Doctrine), а не ActiveRecord (як Eloquent).
 * Сутність — звичайний PHP-об'єкт, маппінг описаний атрибутами.
 */
#[Entity(table: 'products', repository: ProductRepository::class)]
class Product
{
    #[Column(type: 'primary')]
    private int $id;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'text')]
    private string $description;

    /** Ціна в копійках — ніяких float для грошей. */
    #[Column(type: 'integer')]
    private int $price;

    #[Column(type: 'integer')]
    private int $stock;

    #[Column(type: 'datetime', name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name, string $description, int $price, int $stock)
    {
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getPrice(): int { return $this->price; }
    public function getStock(): int { return $this->stock; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
