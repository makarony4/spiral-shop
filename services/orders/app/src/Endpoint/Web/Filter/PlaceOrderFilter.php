<?php

declare(strict_types=1);

namespace App\Endpoint\Web\Filter;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

final class PlaceOrderFilter extends Filter implements HasFilterDefinition
{
    #[Post(key: 'product_id')]
    public int $productId;

    #[Post]
    public int $quantity = 1;

    #[Post]
    public string $email;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'productId' => ['required', 'integer'],
            'quantity' => ['integer', ['number::range', 1, 100]],
            'email' => ['required', 'email'],
        ]);
    }
}
