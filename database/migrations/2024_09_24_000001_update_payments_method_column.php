<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify 'method' column in payments table to VARCHAR(50) so it supports 'cash', 'online', 'gcash', 'card', etc.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN method VARCHAR(50) NOT NULL DEFAULT 'cash'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'gcash', 'card') NOT NULL DEFAULT 'cash'");
        }
    }
};
