<?php

declare(strict_types=1);

namespace App\Endpoint\Web\Filter;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;
use Spiral\Filters\Model\FilterDefinitionInterface;
use Spiral\Filters\Model\HasFilterDefinition;
use Spiral\Validator\FilterDefinition;

/**
 * Аналог FormRequest у Laravel: достатньо вказати фільтр аргументом екшену —
 * Spiral сам заповнить його з запиту і провалідує (інакше — 422).
 */
final class CreateProductFilter extends Filter implements HasFilterDefinition
{
    #[Post]
    public string $name;

    #[Post]
    public string $description = '';

    #[Post]
    public int $price;

    #[Post]
    public int $stock = 0;

    public function filterDefinition(): FilterDefinitionInterface
    {
        return new FilterDefinition([
            'name' => ['required', 'string', ['string::range', 2, 255]],
            'description' => ['string'],
            'price' => ['required', 'integer', ['number::higher', 1]],
            'stock' => ['integer', ['number::higher', 0]],
        ]);
    }
}
