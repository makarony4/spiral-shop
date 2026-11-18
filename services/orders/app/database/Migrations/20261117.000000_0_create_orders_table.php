<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class CreateOrdersTableMigration extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('orders')
            ->addColumn('id', 'primary')
            ->addColumn('product_id', 'integer')
            ->addColumn('product_name', 'string', ['size' => 255])
            ->addColumn('unit_price', 'integer')
            ->addColumn('quantity', 'integer')
            ->addColumn('customer_email', 'string', ['size' => 255])
            ->addColumn('status', 'string', ['size' => 32])
            ->addColumn('created_at', 'datetime')
            ->addIndex(['customer_email'])
            ->setPrimaryKeys(['id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('orders')->drop();
    }
}
