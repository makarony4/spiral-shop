<?php

declare(strict_types=1);

namespace App\Endpoint\Web;

use App\Domain\Order\OrderRepositoryInterface;
use App\Domain\Order\OrderService;
use App\Endpoint\Web\Filter\PlaceOrderFilter;
use App\Endpoint\Web\Resource\OrderResource;
use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Request\InputManager;
use Spiral\Http\ResponseWrapper;
use Spiral\Router\Annotation\Route;

final readonly class OrderController
{
    public function __construct(
        private OrderService $service,
        private OrderRepositoryInterface $orders,
        private ResponseWrapper $response,
    ) {}

    #[Route(route: '/api/orders', name: 'orders.index', methods: 'GET')]
    public function index(InputManager $input): array
    {
        $email = $input->query('email');

        return [
            'data' => \array_map(
                static fn ($o) => new OrderResource($o),
                $this->orders->latest(50, \is_string($email) && $email !== '' ? $email : null),
            ),
        ];
    }

    #[Route(route: '/api/orders/<id:\d+>', name: 'orders.show', methods: 'GET')]
    public function show(string $id): OrderResource
    {
        return new OrderResource($this->service->get((int) $id));
    }

    #[Route(route: '/api/orders', name: 'orders.store', methods: 'POST')]
    public function store(PlaceOrderFilter $filter): ResponseInterface
    {
        $order = $this->service->place($filter->productId, $filter->quantity, $filter->email);

        return $this->response->json(new OrderResource($order), 201);
    }
}
