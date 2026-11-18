<?php

declare(strict_types=1);

namespace App\Domain\Order\Exception;

abstract class HttpAwareException extends \DomainException
{
    abstract public function statusCode(): int;
}
