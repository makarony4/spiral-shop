<?php

declare(strict_types=1);

namespace App\Domain\Product\Exception;

/**
 * Доменна помилка, яку можна безпечно показати клієнту.
 */
abstract class HttpAwareException extends \DomainException
{
    abstract public function statusCode(): int;
}
