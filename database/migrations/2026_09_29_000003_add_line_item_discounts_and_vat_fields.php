<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('order_items', 'discount_rate')) {
                $table->decimal('discount_rate', 5, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('order_items', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0)->after('discount_rate');
            }
            if (!Schema::hasColumn('order_items', 'is_vat_exempt')) {
                $table->boolean('is_vat_exempt')->default(false)->after('discount');
            }
            if (!Schema::hasColumn('order_items', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->after('is_vat_exempt');
            }
            if (!Schema::hasColumn('order_items', 'total')) {
                $table->decimal('total', 12, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('order_items', 'id_number')) {
                $table->string('id_number')->nullable()->after('total');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'vatable_sales')) {
                $table->decimal('vatable_sales', 12, 2)->default(0)->after('tax');
            }
            if (!Schema::hasColumn('orders', 'vat_exempt_sales')) {
                $table->decimal('vat_exempt_sales', 12, 2)->default(0)->after('vatable_sales');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $cols = ['discount_type', 'discount_rate', 'discount', 'is_vat_exempt', 'tax', 'total', 'id_number'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('order_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'vatable_sales')) {
                $table->dropColumn('vatable_sales');
            }
            if (Schema::hasColumn('orders', 'vat_exempt_sales')) {
                $table->dropColumn('vat_exempt_sales');
            }
        });
    }
};
