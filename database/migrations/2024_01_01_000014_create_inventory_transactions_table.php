<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['stock_in', 'sales_consumption', 'waste', 'adjustment']);
            $table->decimal('quantity', 12, 2); // positive for in, positive for consumed/wasted
            $table->decimal('previous_stock', 12, 2);
            $table->decimal('new_stock', 12, 2);
            $table->string('reference_type')->nullable(); // e.g. 'order', 'manual'
            $table->unsignedBigInteger('reference_id')->nullable(); // e.g. order_id
            $table->string('supplier')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['ingredient_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
