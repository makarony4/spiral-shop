<?php

declare(strict_types=1);

namespace App\Domain;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class OrderNotifier
{
    public function __construct(
        private MailerInterface $mailer,
        private string $from,
    ) {}

    public function orderCreated(OrderCreated $event): void
    {
        $total = \number_format($event->total / 100, 2, '.', ' ');

        $email = (new Email())
            ->from($this->from)
            ->to($event->customerEmail)
            ->subject(\sprintf('Замовлення #%d прийнято', $event->orderId))
            ->text(\sprintf(
                "Дякуємо за замовлення!\n\n%s × %d\nРазом: %s грн\n",
                $event->productName,
                $event->quantity,
                $total,
            ));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new TemporaryDeliveryFailure($e->getMessage(), previous: $e);
        }
    }
}
