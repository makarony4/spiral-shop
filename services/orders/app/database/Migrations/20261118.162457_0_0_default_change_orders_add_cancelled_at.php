<?php

declare(strict_types=1);

namespace Migration;

use Cycle\Migrations\Migration;

class OrmDefaultE22fdb13215a50203718770c446b490e extends Migration
{
    protected const DATABASE = 'default';

    public function up(): void
    {
        $this->table('orders')
        ->addColumn('cancelled_at', 'datetime', [
            'nullable' => true,
            'defaultValue' => null,
            'withTimezone' => false,
            'comment' => '',
        ])
        ->update();
    }

    public function down(): void
    {
        $this->table('orders')
        ->dropColumn('cancelled_at')
        ->update();
    }
}
