<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Shift;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosOrderTaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_order_calculates_discount_and_tax_and_blocks_out_of_stock_items(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        Shift::create([
            'opened_by' => $user->id,
            'opened_at' => now(),
            'starting_cash' => 500.00,
            'status' => 'open',
        ]);

        $category = Category::create([
            'name' => 'Coffee',
            'description' => 'Hot beverages',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $size = Size::create(['name' => 'Regular', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'description' => 'Strong coffee',
            'is_active' => true,
        ]);

        ProductSize::create([
            'product_id' => $product->id,
            'size_id' => $size->id,
            'price' => 100.00,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Coffee Beans',
            'unit' => 'g',
            'current_stock' => 200,
            'minimum_stock' => 50,
        ]);

        $recipe = Recipe::create([
            'product_id' => $product->id,
            'size_id' => $size->id,
            'notes' => 'standard',
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 20,
        ]);

        $response = $this->actingAs($user)->postJson('/pos/order', [
            'cashier_name' => 'Alice',
            'items' => [[
                'product_id' => $product->id,
                'size_id' => $size->id,
                'quantity' => 1,
                'add_ons' => [],
            ]],
            'discount' => 10.00,
            'payment_method' => 'cash',
            'amount_tendered' => 100.80,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('10.80', (string) $order->tax);
        $this->assertSame('100.80', (string) $order->total);
        $this->assertSame('90.00', number_format($order->subtotal - $order->discount, 2));

        $ingredient->refresh();
        $this->assertSame('180.00', (string) $ingredient->current_stock);

        $ingredient->update(['current_stock' => 0]);

        $outOfStockResponse = $this->actingAs($user)->postJson('/pos/order', [
            'cashier_name' => 'Alice',
            'items' => [[
                'product_id' => $product->id,
                'size_id' => $size->id,
                'quantity' => 1,
                'add_ons' => [],
            ]],
            'discount' => 0,
            'payment_method' => 'cash',
            'amount_tendered' => 100.00,
        ]);

        $outOfStockResponse->assertStatus(422);
        $outOfStockResponse->assertJsonValidationErrors('items.0.product_id');
    }

    public function test_pos_order_line_item_discounts_and_senior_pwd_vat_exemption(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        Shift::create([
            'opened_by' => $user->id,
            'opened_at' => now(),
            'starting_cash' => 500.00,
            'status' => 'open',
        ]);

        $category = Category::create([
            'name' => 'Coffee',
            'description' => 'Hot beverages',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $size = Size::create(['name' => 'Regular', 'sort_order' => 1]);
        $product1 = Product::create([
            'category_id' => $category->id,
            'name' => 'Americano',
            'description' => 'Classic Americano',
            'is_active' => true,
        ]);
        $product2 = Product::create([
            'category_id' => $category->id,
            'name' => 'Latte',
            'description' => 'Creamy Latte',
            'is_active' => true,
        ]);

        ProductSize::create([
            'product_id' => $product1->id,
            'size_id' => $size->id,
            'price' => 100.00,
        ]);
        ProductSize::create([
            'product_id' => $product2->id,
            'size_id' => $size->id,
            'price' => 100.00,
        ]);

        $response = $this->actingAs($user)->postJson('/pos/order', [
            'cashier_name' => 'Cashier Juan',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'size_id' => $size->id,
                    'quantity' => 1,
                    'add_ons' => [],
                    'discount_type' => 'none',
                    'discount_rate' => 0,
                    'discount' => 0,
                ],
                [
                    'product_id' => $product2->id,
                    'size_id' => $size->id,
                    'quantity' => 1,
                    'add_ons' => [],
                    'discount_type' => 'pwd_senior',
                    'discount_rate' => 20,
                    'discount' => 20.00,
                    'id_number' => 'OSCA-98765',
                ],
            ],
            'payment_method' => 'cash',
            'amount_tendered' => 200.00,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $order = Order::with('items')->first();
        $this->assertNotNull($order);
        $this->assertSame('200.00', (string) $order->subtotal);
        $this->assertSame('20.00', (string) $order->discount);
        $this->assertSame('100.00', (string) $order->vatable_sales);
        $this->assertSame('80.00', (string) $order->vat_exempt_sales);
        $this->assertSame('12.00', (string) $order->tax);
        $this->assertSame('192.00', (string) $order->total);

        $this->assertCount(2, $order->items);

        $regularItem = $order->items->firstWhere('product_id', $product1->id);
        $this->assertSame('none', $regularItem->discount_type);
        $this->assertFalse((bool) $regularItem->is_vat_exempt);
        $this->assertSame('0.00', (string) $regularItem->discount);
        $this->assertSame('12.00', (string) $regularItem->tax);
        $this->assertSame('112.00', (string) $regularItem->total);

        $seniorItem = $order->items->firstWhere('product_id', $product2->id);
        $this->assertSame('pwd_senior', $seniorItem->discount_type);
        $this->assertTrue((bool) $seniorItem->is_vat_exempt);
        $this->assertSame('20.00', (string) $seniorItem->discount);
        $this->assertSame('0.00', (string) $seniorItem->tax);
        $this->assertSame('80.00', (string) $seniorItem->total);
        $this->assertSame('OSCA-98765', $seniorItem->id_number);
    }

    public function test_order_total_of_280_has_250_vatable_sales_and_30_vat(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        Shift::create([
            'opened_by' => $user->id,
            'opened_at' => now(),
            'starting_cash' => 500.00,
            'status' => 'open',
        ]);

        $category = Category::create([
            'name' => 'Espresso',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $size = Size::create(['name' => 'Hot 8oz', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Latte',
            'is_active' => true,
        ]);

        ProductSize::create([
            'product_id' => $product->id,
            'size_id' => $size->id,
            'price' => 280.00,
        ]);

        $response = $this->actingAs($user)->postJson('/pos/order', [
            'cashier_name' => 'Alice',
            'items' => [[
                'product_id' => $product->id,
                'size_id' => $size->id,
                'quantity' => 1,
                'add_ons' => [],
            ]],
            'payment_method' => 'cash',
            'amount_tendered' => 280.00,
        ]);

        $response->assertOk();
        $order = Order::firstOrFail();
        $this->assertSame('280.00', (string) $order->total);
        $this->assertSame('250.00', (string) $order->vatable_sales);
        $this->assertSame('30.00', (string) $order->tax);
    }
}
