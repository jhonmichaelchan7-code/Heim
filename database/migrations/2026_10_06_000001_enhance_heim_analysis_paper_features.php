<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add branch_id and order_type to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('shift_id')->constrained('branches')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type', 30)->default('dine_in')->after('order_number');
            }
        });

        // 2. Add ingredient_id and quantity to add_ons table for Dynamic BOM Deductions
        Schema::table('add_ons', function (Blueprint $table) {
            if (!Schema::hasColumn('add_ons', 'ingredient_id')) {
                $table->foreignId('ingredient_id')->nullable()->after('price')->constrained('ingredients')->onDelete('set null');
            }
            if (!Schema::hasColumn('add_ons', 'quantity')) {
                $table->decimal('quantity', 10, 2)->default(0)->after('ingredient_id');
            }
        });

        // 3. Add restored_inventory and action_type to refunds table
        Schema::table('refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('refunds', 'restored_inventory')) {
                $table->boolean('restored_inventory')->default(false)->after('reason');
            }
            if (!Schema::hasColumn('refunds', 'action_type')) {
                $table->string('action_type', 20)->default('refund')->after('restored_inventory'); // refund, cancellation, void
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('orders', 'order_type')) {
                $table->dropColumn('order_type');
            }
        });

        Schema::table('add_ons', function (Blueprint $table) {
            if (Schema::hasColumn('add_ons', 'ingredient_id')) {
                $table->dropForeign(['ingredient_id']);
                $table->dropColumn('ingredient_id');
            }
            if (Schema::hasColumn('add_ons', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });

        Schema::table('refunds', function (Blueprint $table) {
            if (Schema::hasColumn('refunds', 'restored_inventory')) {
                $table->dropColumn('restored_inventory');
            }
            if (Schema::hasColumn('refunds', 'action_type')) {
                $table->dropColumn('action_type');
            }
        });
    }
};
