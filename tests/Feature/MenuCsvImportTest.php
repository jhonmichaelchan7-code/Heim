<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_csv_import_is_idempotent_and_deactivates_old_products(): void
    {
        $oldCategory = Category::create([
            'name' => 'Legacy',
            'is_active' => true,
            'sort_order' => 99,
        ]);
        $oldProduct = Product::create([
            'category_id' => $oldCategory->id,
            'name' => 'Legacy Drink',
            'is_active' => true,
        ]);

        $this->artisan('menu:import')
            ->assertExitCode(0);

        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 25);
        $this->assertDatabaseCount('sizes', 3);
        $this->assertDatabaseCount('product_sizes', 33);
        $this->assertDatabaseHas('products', [
            'id' => $oldProduct->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $oldCategory->id,
            'is_active' => false,
        ]);

        $this->artisan('menu:import')
            ->assertExitCode(0);

        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('products', 25);
        $this->assertDatabaseCount('sizes', 3);
        $this->assertDatabaseCount('product_sizes', 33);
        $this->assertDatabaseCount('recipes', 0);
    }
}
