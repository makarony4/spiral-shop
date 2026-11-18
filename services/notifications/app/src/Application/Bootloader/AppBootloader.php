<?php

declare(strict_types=1);

namespace App\Application\Bootloader;

use App\Domain\OrderNotifier;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

final class AppBootloader extends Bootloader
{
    protected const SINGLETONS = [
        MailerInterface::class => [self::class, 'mailer'],
        OrderNotifier::class => [self::class, 'notifier'],
    ];

    private function mailer(EnvironmentInterface $env): MailerInterface
    {
        return new Mailer(Transport::fromDsn((string) $env->get('MAILER_DSN', 'smtp://mailpit:1025')));
    }

    private function notifier(MailerInterface $mailer, EnvironmentInterface $env): OrderNotifier
    {
        return new OrderNotifier($mailer, (string) $env->get('MAILER_FROM', 'shop@example.test'));
    }
}
