<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Shift;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FryersMenuImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_fryers_csv_import_updates_existing_rows_without_duplicates(): void
    {
        $category = Category::create([
            'name' => 'Existing Chicken',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $existingProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Chicken Wings & Fries',
            'is_active' => true,
        ]);
        Ingredient::create([
            'name' => 'Salted Egg Sauce',
            'unit' => 'g',
            'current_stock' => 2222,
            'minimum_stock' => 100,
        ]);

        $this->artisan('fryers:import')->assertExitCode(0);

        $this->assertDatabaseHas('products', [
            'id' => $existingProduct->id,
            'category_id' => Category::where('name', 'Chicken Wings')->value('id'),
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('product_sizes', [
            'product_id' => $existingProduct->id,
            'size_id' => Size::where('name', 'Regular')->value('id'),
            'price' => 259.00,
        ]);
        $this->assertDatabaseHas('ingredients', [
            'name' => 'Chicken Wing',
            'unit' => 'pcs',
            'current_stock' => 400,
            'minimum_stock' => 80,
        ]);
        $this->assertDatabaseHas('ingredients', [
            'name' => 'Salted Egg Sauce',
            'current_stock' => 2222,
        ]);
        $this->assertDatabaseHas('ingredients', [
            'name' => 'Dip Sauce',
            'notes' => 'Ask client which dips are offered; split into separate items if they differ',
        ]);
        $this->assertDatabaseHas('recipe_ingredients', [
            'recipe_id' => \App\Models\Recipe::where('product_id', $existingProduct->id)->value('id'),
            'ingredient_id' => Ingredient::where('name', 'Chicken Wing')->value('id'),
            'quantity' => 4,
        ]);
        $this->assertSame(7, ModifierOption::count());
        $this->assertSame(8, \App\Models\ProductModifierRule::count());

        $counts = [
            'categories' => Category::count(),
            'products' => Product::count(),
            'sizes' => Size::count(),
            'product_sizes' => ProductSize::count(),
            'ingredients' => Ingredient::count(),
            'recipes' => \App\Models\Recipe::count(),
            'recipe_ingredients' => \App\Models\RecipeIngredient::count(),
            'modifier_groups' => ModifierGroup::count(),
            'modifier_options' => ModifierOption::count(),
            'product_modifier_rules' => \App\Models\ProductModifierRule::count(),
        ];

        $this->artisan('fryers:import')->assertExitCode(0);

        $this->assertSame($counts, [
            'categories' => Category::count(),
            'products' => Product::count(),
            'sizes' => Size::count(),
            'product_sizes' => ProductSize::count(),
            'ingredients' => Ingredient::count(),
            'recipes' => \App\Models\Recipe::count(),
            'recipe_ingredients' => \App\Models\RecipeIngredient::count(),
            'modifier_groups' => ModifierGroup::count(),
            'modifier_options' => ModifierOption::count(),
            'product_modifier_rules' => \App\Models\ProductModifierRule::count(),
        ]);
        $this->assertSame(1, Product::where('name', 'Chicken Wings & Fries')->count());
    }

    public function test_wing_flavor_is_required_deducted_and_restored_with_inclusive_vat(): void
    {
        $this->artisan('fryers:import')->assertExitCode(0);

        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $this->actingAs($manager)->get('/pos')
            ->assertOk()
            ->assertSee('Buffalo Wings', false);

        $cashier = User::factory()->create(['role' => 'cashier']);
        Shift::create([
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'starting_cash' => 1000.00,
            'status' => 'open',
        ]);

        $regularSizeId = Size::where('name', 'Regular')->value('id');
        $lone = Product::where('name', 'Lone')->firstOrFail();
        $drip = Product::where('name', 'Cheesy Buffalo Drip')->firstOrFail();
        $coke = Product::where('name', 'Coke in Can')->firstOrFail();
        $buffaloFlavor = ModifierOption::where('name', 'Buffalo Wings')->firstOrFail();

        $ingredients = Ingredient::whereIn('name', [
            'Chicken Wing', 'Rice', 'Dip Sauce', 'Dip Cup', 'Meal Box',
            'Buffalo Sauce', 'Cheese Drip Sauce', 'Coke in Can',
        ])->get()->keyBy('name');

        $items = [
            [
                'product_id' => $lone->id,
                'size_id' => $regularSizeId,
                'quantity' => 1,
                'add_ons' => [],
                'wing_flavors' => [$buffaloFlavor->id],
            ],
            [
                'product_id' => $drip->id,
                'size_id' => $regularSizeId,
                'quantity' => 1,
                'add_ons' => [],
            ],
            [
                'product_id' => $coke->id,
                'size_id' => $regularSizeId,
                'quantity' => 1,
                'add_ons' => [],
            ],
        ];

        $missingFlavorItems = $items;
        $missingFlavorItems[0]['wing_flavors'] = [];
        $this->actingAs($cashier)->postJson('/pos/order', [
            'cashier_name' => 'Test Cashier',
            'items' => $missingFlavorItems,
            'payment_method' => 'cash',
            'amount_tendered' => 294.00,
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.wing_flavors');

        $this->actingAs($cashier)->postJson('/pos/order', [
            'cashier_name' => 'Test Cashier',
            'items' => $items,
            'payment_method' => 'cash',
            'amount_tendered' => 294.00,
        ])->assertOk()->assertJsonPath('success', true);

        $order = Order::with('items.modifiers')->firstOrFail();
        $this->assertSame('294.00', (string) $order->total);
        $this->assertSame('262.50', (string) $order->vatable_sales);
        $this->assertSame('31.50', (string) $order->tax);

        $expectedAfterSale = [
            'Chicken Wing' => '396.00',
            'Rice' => '14930.00',
            'Dip Sauce' => '2970.00',
            'Dip Cup' => '299.00',
            'Meal Box' => '199.00',
            'Buffalo Sauce' => '2945.00',
            'Cheese Drip Sauce' => '2960.00',
            'Coke in Can' => '47.00',
        ];
        foreach ($expectedAfterSale as $name => $stock) {
            $this->assertSame($stock, (string) $ingredients[$name]->fresh()->current_stock, $name);
        }

        $modifierSnapshot = $order->items->firstWhere('product_id', $lone->id)->modifiers->first();
        $this->assertSame('Buffalo Wings', $modifierSnapshot->option_name);
        $this->assertSame('40.00', (string) $modifierSnapshot->consumed_quantity);

        $this->actingAs($manager)->post(route('orders.refund', $order), [
            'action_type' => 'void',
            'reason' => 'Wrong item or recipe',
            'restore_inventory' => true,
        ])->assertRedirect(route('orders.show', $order));

        foreach ([
            'Chicken Wing' => '400.00',
            'Rice' => '15000.00',
            'Dip Sauce' => '3000.00',
            'Dip Cup' => '300.00',
            'Meal Box' => '200.00',
            'Buffalo Sauce' => '3000.00',
            'Cheese Drip Sauce' => '3000.00',
            'Coke in Can' => '48.00',
        ] as $name => $stock) {
            $this->assertSame($stock, (string) $ingredients[$name]->fresh()->current_stock, $name);
        }

        $this->assertDatabaseHas('inventory_transactions', [
            'ingredient_id' => $ingredients['Buffalo Sauce']->id,
            'type' => 'adjustment',
            'quantity' => 40,
            'reference_type' => 'order',
            'reference_id' => $order->id,
        ]);
    }

    public function test_wingstreak_salted_egg_and_cheesy_buffalo_drip_total_and_restore_inventory(): void
    {
        $this->artisan('fryers:import')->assertExitCode(0);

        $cashier = User::factory()->create(['role' => 'cashier']);
        Shift::create([
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'starting_cash' => 1000.00,
            'status' => 'open',
        ]);

        $wingstreak = Product::where('name', 'Wingstreak')->firstOrFail();
        $regular = Size::where('name', 'Regular')->firstOrFail();
        $saltedEgg = ModifierOption::where('name', 'Salted Egg')->firstOrFail();
        $cheesyBuffalo = AddOn::where('name', 'Cheesy Buffalo Drip')->firstOrFail();

        $this->actingAs($cashier)->postJson('/pos/order', [
            'cashier_name' => 'Test Cashier',
            'items' => [[
                'product_id' => $wingstreak->id,
                'size_id' => $regular->id,
                'quantity' => 1,
                'add_ons' => [$cheesyBuffalo->id],
                'wing_flavors' => [$saltedEgg->id],
            ]],
            'payment_method' => 'cash',
            'amount_tendered' => 409.00,
        ])->assertOk()->assertJsonPath('success', true);

        $order = Order::with('items.addOns.ingredientSnapshots', 'items.modifiers')->firstOrFail();
        $this->assertSame('409.00', (string) $order->total);
        $this->assertSame('365.18', (string) $order->vatable_sales);
        $this->assertSame('43.82', (string) $order->tax);
        $this->assertSame('96.00', (string) $order->items->first()->modifiers->first()->consumed_quantity);
        $this->assertSame(2, $order->items->first()->addOns->first()->ingredientSnapshots->count());
        $this->assertSame('2904.00', (string) Ingredient::where('name', 'Salted Egg Sauce')->value('current_stock'));
        $this->assertSame('2960.00', (string) Ingredient::where('name', 'Cheese Drip Sauce')->value('current_stock'));
        $this->assertSame('2985.00', (string) Ingredient::where('name', 'Buffalo Sauce')->value('current_stock'));

        $manager = User::factory()->create(['role' => 'manager']);
        $this->actingAs($manager)->post(route('orders.refund', $order), [
            'action_type' => 'void',
            'reason' => 'Wrong item or recipe',
            'restore_inventory' => true,
        ])->assertRedirect(route('orders.show', $order));

        $this->assertSame('3000.00', (string) Ingredient::where('name', 'Salted Egg Sauce')->value('current_stock'));
        $this->assertSame('3000.00', (string) Ingredient::where('name', 'Cheese Drip Sauce')->value('current_stock'));
        $this->assertSame('3000.00', (string) Ingredient::where('name', 'Buffalo Sauce')->value('current_stock'));
    }

    public function test_latte_accepts_coffee_addons_and_rejects_wing_addons(): void
    {
        $this->artisan('fryers:import')->assertExitCode(0);

        $espressoCategory = Category::where('name', 'Espresso')->firstOrFail();
        $icedSize = Size::where('name', 'Iced 12oz')->firstOrFail();
        $latte = Product::create([
            'category_id' => $espressoCategory->id,
            'name' => 'Latte',
            'is_active' => true,
        ]);
        ProductSize::create([
            'product_id' => $latte->id,
            'size_id' => $icedSize->id,
            'price' => 130.00,
        ]);

        $cashier = User::factory()->create(['role' => 'cashier']);
        Shift::create([
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'starting_cash' => 1000.00,
            'status' => 'open',
        ]);

        $caramelSyrup = AddOn::where('name', 'Caramel Syrup')->firstOrFail();
        Ingredient::where('name', 'Caramel Syrup')->update(['current_stock' => 1000]);
        $wingAddOn = AddOn::where('name', 'Cheesy Buffalo Drip')->firstOrFail();
        $payload = [
            'cashier_name' => 'Test Cashier',
            'items' => [[
                'product_id' => $latte->id,
                'size_id' => $icedSize->id,
                'quantity' => 1,
                'add_ons' => [$wingAddOn->id],
            ]],
            'payment_method' => 'cash',
            'amount_tendered' => 150.00,
        ];

        $this->actingAs($cashier)->postJson('/pos/order', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items.0.add_ons');

        $payload['items'][0]['add_ons'] = [$caramelSyrup->id];
        $this->actingAs($cashier)->postJson('/pos/order', $payload)
            ->assertOk()
            ->assertJsonPath('order.total', '150.00');
    }

    public function test_product_sizes_are_category_scoped_and_existing_retired_sizes_remain_editable(): void
    {
        $this->artisan('fryers:import')->assertExitCode(0);

        $manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $espresso = Category::where('name', 'Espresso')->firstOrFail();
        $retiredSize = Size::where('name', '12oz')->firstOrFail();
        $regular = Size::where('name', 'Regular')->firstOrFail();

        $this->actingAs($manager)->post(route('products.store'), [
            'name' => 'Invalid Size Product',
            'category_id' => $espresso->id,
            'sizes' => [['size_id' => $regular->id, 'price' => 100.00]],
        ])->assertSessionHasErrors('sizes');

        $product = Product::create([
            'category_id' => $espresso->id,
            'name' => 'Legacy Size Drink',
            'is_active' => true,
        ]);
        ProductSize::create([
            'product_id' => $product->id,
            'size_id' => $retiredSize->id,
            'price' => 100.00,
        ]);

        $this->actingAs($manager)->put(route('products.update', $product), [
            'name' => $product->name,
            'category_id' => $espresso->id,
            'is_active' => 1,
            'sizes' => [['size_id' => $retiredSize->id, 'price' => 110.00]],
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('product_sizes', [
            'product_id' => $product->id,
            'size_id' => $retiredSize->id,
            'price' => 110.00,
        ]);
    }
}
