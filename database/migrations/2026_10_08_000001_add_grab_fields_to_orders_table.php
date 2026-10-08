<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'grab_order_code')) {
                $table->string('grab_order_code', 50)->nullable()->after('order_type');
            }
            if (!Schema::hasColumn('orders', 'rider_code')) {
                $table->string('rider_code', 50)->nullable()->after('grab_order_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'grab_order_code')) {
                $table->dropColumn('grab_order_code');
            }
            if (Schema::hasColumn('orders', 'rider_code')) {
                $table->dropColumn('rider_code');
            }
        });
    }
};
