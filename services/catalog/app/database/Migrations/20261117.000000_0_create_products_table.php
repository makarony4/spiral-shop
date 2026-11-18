<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

/**
 * Можна згенерувати автоматично з сутностей: php app.php cycle:migrate
 */
class CreateProductsTableMigration extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('products')
            ->addColumn('id', 'primary')
            ->addColumn('name', 'string', ['size' => 255])
            ->addColumn('description', 'text')
            ->addColumn('price', 'integer')
            ->addColumn('stock', 'integer')
            ->addColumn('created_at', 'datetime')
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('products')->drop();
    }
}
