<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(12.00)->after('discount');
            }
            if (!Schema::hasColumn('orders', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->after('tax_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tax')) {
                $table->dropColumn('tax');
            }
            if (Schema::hasColumn('orders', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
    }
};
