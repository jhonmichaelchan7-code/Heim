<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'stock_out' to enum type in inventory_transactions table (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE inventory_transactions MODIFY COLUMN type ENUM('stock_in', 'sales_consumption', 'waste', 'adjustment', 'stock_out') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE inventory_transactions MODIFY COLUMN type ENUM('stock_in', 'sales_consumption', 'waste', 'adjustment') NOT NULL");
        }
    }
};
