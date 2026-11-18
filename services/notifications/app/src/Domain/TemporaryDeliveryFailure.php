<?php

declare(strict_types=1);

namespace App\Domain;

use Spiral\Queue\Exception\RetryableExceptionInterface;
use Spiral\Queue\RetryPolicyInterface;

/**
 * Тимчасовий збій (SMTP недоступний тощо). Spiral повторює задачу лише для винятків
 * з RetryableExceptionInterface — помилки на кшталт битого payload ретраїти немає сенсу.
 */
final class TemporaryDeliveryFailure extends \RuntimeException implements RetryableExceptionInterface
{
    public function isRetryable(): bool
    {
        return true;
    }

    /** null -> береться політика з атрибута #[RetryPolicy] на обробнику */
    public function getRetryPolicy(): ?RetryPolicyInterface
    {
        return null;
    }
}
