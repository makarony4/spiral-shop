<?php

declare(strict_types=1);

namespace App\Endpoint\Web\Resource;

use App\Domain\Product\Product;

final readonly class ProductResource implements \JsonSerializable
{
    public function __construct(private Product $product) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->product->getId(),
            'name' => $this->product->getName(),
            'description' => $this->product->getDescription(),
            'price' => $this->product->getPrice(),
            'stock' => $this->product->getStock(),
            'created_at' => $this->product->getCreatedAt()->format(\DATE_ATOM),
        ];
    }
}
