<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('modifier_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_group_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->foreignId('ingredient_id')->nullable()->constrained()->onDelete('restrict');
            $table->decimal('grams_per_wing', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['modifier_group_id', 'name']);
        });

        Schema::create('product_modifier_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('modifier_group_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('min_choices')->default(0);
            $table->unsignedInteger('max_choices')->default(1);
            $table->unsignedInteger('wings_per_order')->default(0);
            $table->unsignedInteger('dips_included')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'modifier_group_id']);
        });

        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('modifier_option_id')->nullable()->constrained()->onDelete('set null');
            $table->string('group_name');
            $table->string('option_name');
            $table->decimal('price', 10, 2)->default(0);
            $table->foreignId('ingredient_id')->nullable()->constrained()->onDelete('restrict');
            $table->decimal('consumed_quantity', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');
        Schema::dropIfExists('product_modifier_rules');
        Schema::dropIfExists('modifier_options');
        Schema::dropIfExists('modifier_groups');
    }
};
