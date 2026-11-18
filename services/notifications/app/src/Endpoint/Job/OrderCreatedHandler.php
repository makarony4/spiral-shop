<?php

declare(strict_types=1);

namespace App\Endpoint\Job;

use App\Domain\OrderCreated;
use App\Domain\OrderNotifier;
use Psr\Log\LoggerInterface;
use Spiral\Queue\JobHandler;

/**
 * Аналог Laravel Job / Symfony Messenger handler.
 * Аргументи методу invoke() резолвляться з DI-контейнера, $payload — тіло повідомлення.
 */
final class OrderCreatedHandler extends JobHandler
{
    public function invoke(array $payload, OrderNotifier $notifier, LoggerInterface $logger): void
    {
        $event = OrderCreated::fromPayload($payload);

        $notifier->orderCreated($event);

        $logger->info('Order confirmation sent', [
            'order_id' => $event->orderId,
            'email' => $event->customerEmail,
        ]);
    }
}
