<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\Product\ProductRepositoryInterface;
use App\Domain\Product\ProductService;
use App\Endpoint\Web\Filter\CreateProductFilter;
use App\Endpoint\Web\Resource\ProductResource;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Request\InputManager;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

/**
 * Публічне API каталогу (доступне через gateway).
 */
final readonly class ProductController
{
    public function __construct(
        private ProductService $service,
        private ProductRepositoryInterface $products,
        private ResponseWrapper $response,
    ) {}

    #[Route(route: '/api/products', name: 'products.index', methods: 'GET')]
    public function index(InputManager $input): array
    {
        $limit = \min(100, \max(1, (int) $input->query('limit', 20)));
        $offset = \max(0, (int) $input->query('offset', 0));

        return [
            'data' => \array_map(
                static fn ($p) => new ProductResource($p),
                $this->products->list($limit, $offset),
            ),
            'meta' => ['total' => $this->products->count(), 'limit' => $limit, 'offset' => $offset],
        ];
    }

    #[Route(route: '/api/products/<id:\d+>', name: 'products.show', methods: 'GET')]
    public function show(string $id): ProductResource
    {
        // Параметри маршруту завжди рядки, а Spiral суворо перевіряє типи аргументів
        return new ProductResource($this->service->get((int) $id));
    }

    #[Route(route: '/api/products', name: 'products.store', methods: 'POST')]
    public function store(CreateProductFilter $filter): ResponseInterface
    {
        $product = $this->service->create($filter->name, $filter->description, $filter->price, $filter->stock);

        return $this->response->json(new ProductResource($product), 201);
    }
}
