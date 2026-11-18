<?php

declare(strict_types=1);

namespace App\Domain\Catalog;

/**
 * Те, що нам потрібно знати про товар із чужого сервісу. Ніяких спільних сутностей між сервісами.
 */
final readonly class ReservedProduct
{
    public function __construct(
        public int $id,
        public string $name,
        public int $price,
    ) {}
}
