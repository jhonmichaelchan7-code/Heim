<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('type')->default('food');
            $table->boolean('can_be_addon')->default(true);
        });

        Schema::table('sizes', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });

        Schema::create('category_size_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('size_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'size_id']);
        });

        Schema::create('add_on_category_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('add_on_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->boolean('only_iced_sizes')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['add_on_id', 'category_id']);
        });

        Schema::create('add_on_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('add_on_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('restrict');
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['add_on_id', 'ingredient_id']);
        });

        Schema::create('order_item_add_on_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_add_on_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('restrict');
            $table->decimal('quantity', 12, 2);
            $table->timestamps();

            $table->unique(['order_item_add_on_id', 'ingredient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_add_on_ingredients');
        Schema::dropIfExists('add_on_ingredients');
        Schema::dropIfExists('add_on_category_rules');
        Schema::dropIfExists('category_size_rules');

        Schema::table('sizes', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['type', 'can_be_addon']);
        });
    }
};
