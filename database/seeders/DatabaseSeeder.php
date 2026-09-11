<?php

namespace Database\Seeders;

use App\Models\AddOn;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Size;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──
        User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@coffee.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);
        User::create([
            'name' => 'Maria Santos',
            'email' => 'manager@coffee.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);
        User::create([
            'name' => 'Carlos Reyes',
            'email' => 'supervisor@coffee.com',
            'password' => bcrypt('password'),
            'role' => 'supervisor',
        ]);
        User::create([
            'name' => 'POS Cashier',
            'email' => 'cashier@coffee.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
        ]);

        // ── Sizes ──
        $size12 = Size::create(['name' => '12oz', 'sort_order' => 1]);
        $size16 = Size::create(['name' => '16oz', 'sort_order' => 2]);
        $size22 = Size::create(['name' => '22oz', 'sort_order' => 3]);

        // ── Categories ──
        $hotCoffee = Category::create(['name' => 'Hot Coffee', 'sort_order' => 1]);
        $icedCoffee = Category::create(['name' => 'Iced Coffee', 'sort_order' => 2]);
        $frappe = Category::create(['name' => 'Frappe', 'sort_order' => 3]);
        $nonCoffee = Category::create(['name' => 'Non-Coffee', 'sort_order' => 4]);
        $snacks = Category::create(['name' => 'Snacks & Pastries', 'sort_order' => 5]);

        // ── Products ──
        // Hot Coffee
        $americano = Product::create(['category_id' => $hotCoffee->id, 'name' => 'Americano']);
        $americano->sizes()->attach([
            $size12->id => ['price' => 89],
            $size16->id => ['price' => 109],
            $size22->id => ['price' => 129],
        ]);

        $cappuccino = Product::create(['category_id' => $hotCoffee->id, 'name' => 'Cappuccino']);
        $cappuccino->sizes()->attach([
            $size12->id => ['price' => 99],
            $size16->id => ['price' => 119],
            $size22->id => ['price' => 139],
        ]);

        $latte = Product::create(['category_id' => $hotCoffee->id, 'name' => 'Café Latte']);
        $latte->sizes()->attach([
            $size12->id => ['price' => 99],
            $size16->id => ['price' => 119],
            $size22->id => ['price' => 139],
        ]);

        $mocha = Product::create(['category_id' => $hotCoffee->id, 'name' => 'Mocha']);
        $mocha->sizes()->attach([
            $size12->id => ['price' => 109],
            $size16->id => ['price' => 129],
            $size22->id => ['price' => 149],
        ]);

        // Iced Coffee
        $icedAmericano = Product::create(['category_id' => $icedCoffee->id, 'name' => 'Iced Americano']);
        $icedAmericano->sizes()->attach([
            $size16->id => ['price' => 109],
            $size22->id => ['price' => 129],
        ]);

        $icedLatte = Product::create(['category_id' => $icedCoffee->id, 'name' => 'Iced Latte']);
        $icedLatte->sizes()->attach([
            $size16->id => ['price' => 129],
            $size22->id => ['price' => 149],
        ]);

        $icedMocha = Product::create(['category_id' => $icedCoffee->id, 'name' => 'Iced Mocha']);
        $icedMocha->sizes()->attach([
            $size16->id => ['price' => 139],
            $size22->id => ['price' => 159],
        ]);

        $spanishLatte = Product::create(['category_id' => $icedCoffee->id, 'name' => 'Spanish Latte']);
        $spanishLatte->sizes()->attach([
            $size16->id => ['price' => 139],
            $size22->id => ['price' => 159],
        ]);

        // Frappe
        $caramelFrappe = Product::create(['category_id' => $frappe->id, 'name' => 'Caramel Frappe']);
        $caramelFrappe->sizes()->attach([
            $size16->id => ['price' => 149],
            $size22->id => ['price' => 169],
        ]);

        $mochaFrappe = Product::create(['category_id' => $frappe->id, 'name' => 'Mocha Frappe']);
        $mochaFrappe->sizes()->attach([
            $size16->id => ['price' => 149],
            $size22->id => ['price' => 169],
        ]);

        // Non-Coffee
        $matcha = Product::create(['category_id' => $nonCoffee->id, 'name' => 'Matcha Latte']);
        $matcha->sizes()->attach([
            $size16->id => ['price' => 129],
            $size22->id => ['price' => 149],
        ]);

        $chocoLatte = Product::create(['category_id' => $nonCoffee->id, 'name' => 'Chocolate Latte']);
        $chocoLatte->sizes()->attach([
            $size16->id => ['price' => 119],
            $size22->id => ['price' => 139],
        ]);

        // ── Add-ons ──
        AddOn::create(['name' => 'Extra Shot', 'price' => 25]);
        AddOn::create(['name' => 'Vanilla Syrup', 'price' => 20]);
        AddOn::create(['name' => 'Caramel Syrup', 'price' => 20]);
        AddOn::create(['name' => 'Hazelnut Syrup', 'price' => 20]);
        AddOn::create(['name' => 'Whipped Cream', 'price' => 15]);
        AddOn::create(['name' => 'Extra Milk', 'price' => 15]);
        AddOn::create(['name' => 'Pearl/Boba', 'price' => 25]);

        // ── Ingredients ──
        $coffeeBeans = Ingredient::create(['name' => 'Coffee Beans', 'unit' => 'g', 'current_stock' => 5000, 'minimum_stock' => 500]);
        $freshMilk = Ingredient::create(['name' => 'Fresh Milk', 'unit' => 'ml', 'current_stock' => 10000, 'minimum_stock' => 2000]);
        $condensedMilk = Ingredient::create(['name' => 'Condensed Milk', 'unit' => 'ml', 'current_stock' => 5000, 'minimum_stock' => 1000]);
        $chocolate = Ingredient::create(['name' => 'Chocolate Powder', 'unit' => 'g', 'current_stock' => 3000, 'minimum_stock' => 500]);
        $matchaPowder = Ingredient::create(['name' => 'Matcha Powder', 'unit' => 'g', 'current_stock' => 1000, 'minimum_stock' => 200]);
        $caramelSyrup = Ingredient::create(['name' => 'Caramel Syrup', 'unit' => 'ml', 'current_stock' => 2000, 'minimum_stock' => 500]);
        $vanillaSyrup = Ingredient::create(['name' => 'Vanilla Syrup', 'unit' => 'ml', 'current_stock' => 2000, 'minimum_stock' => 500]);
        $hazelnutSyrup = Ingredient::create(['name' => 'Hazelnut Syrup', 'unit' => 'ml', 'current_stock' => 2000, 'minimum_stock' => 500]);
        $ice = Ingredient::create(['name' => 'Ice', 'unit' => 'g', 'current_stock' => 20000, 'minimum_stock' => 5000]);
        $water = Ingredient::create(['name' => 'Water', 'unit' => 'ml', 'current_stock' => 50000, 'minimum_stock' => 10000]);
        $whippedCream = Ingredient::create(['name' => 'Whipped Cream', 'unit' => 'g', 'current_stock' => 2000, 'minimum_stock' => 400]);
        $sugar = Ingredient::create(['name' => 'Sugar', 'unit' => 'g', 'current_stock' => 5000, 'minimum_stock' => 1000]);
        $cup12 = Ingredient::create(['name' => '12oz Cup', 'unit' => 'pcs', 'current_stock' => 200, 'minimum_stock' => 50]);
        $cup16 = Ingredient::create(['name' => '16oz Cup', 'unit' => 'pcs', 'current_stock' => 200, 'minimum_stock' => 50]);
        $cup22 = Ingredient::create(['name' => '22oz Cup', 'unit' => 'pcs', 'current_stock' => 200, 'minimum_stock' => 50]);
        $lid = Ingredient::create(['name' => 'Lid', 'unit' => 'pcs', 'current_stock' => 500, 'minimum_stock' => 100]);
        $straw = Ingredient::create(['name' => 'Straw', 'unit' => 'pcs', 'current_stock' => 500, 'minimum_stock' => 100]);

        // ── Sample Recipes ──
        // Americano 12oz
        $r = Recipe::create(['product_id' => $americano->id, 'size_id' => $size12->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $coffeeBeans->id, 'quantity' => 14]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $water->id, 'quantity' => 250]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup12->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);

        // Americano 16oz
        $r = Recipe::create(['product_id' => $americano->id, 'size_id' => $size16->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $coffeeBeans->id, 'quantity' => 18]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $water->id, 'quantity' => 350]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup16->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);

        // Iced Latte 16oz
        $r = Recipe::create(['product_id' => $icedLatte->id, 'size_id' => $size16->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $coffeeBeans->id, 'quantity' => 18]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $freshMilk->id, 'quantity' => 200]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $ice->id, 'quantity' => 150]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup16->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $straw->id, 'quantity' => 1]);

        // Iced Latte 22oz
        $r = Recipe::create(['product_id' => $icedLatte->id, 'size_id' => $size22->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $coffeeBeans->id, 'quantity' => 22]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $freshMilk->id, 'quantity' => 300]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $ice->id, 'quantity' => 200]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup22->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $straw->id, 'quantity' => 1]);

        // Cappuccino 16oz
        $r = Recipe::create(['product_id' => $cappuccino->id, 'size_id' => $size16->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $coffeeBeans->id, 'quantity' => 18]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $freshMilk->id, 'quantity' => 180]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup16->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);

        // Spanish Latte 16oz
        $r = Recipe::create(['product_id' => $spanishLatte->id, 'size_id' => $size16->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $coffeeBeans->id, 'quantity' => 18]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $freshMilk->id, 'quantity' => 150]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $condensedMilk->id, 'quantity' => 40]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $ice->id, 'quantity' => 150]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup16->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $straw->id, 'quantity' => 1]);

        // Mocha 16oz
        $r = Recipe::create(['product_id' => $mocha->id, 'size_id' => $size16->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $coffeeBeans->id, 'quantity' => 18]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $freshMilk->id, 'quantity' => 180]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $chocolate->id, 'quantity' => 20]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup16->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);

        // Matcha Latte 16oz
        $r = Recipe::create(['product_id' => $matcha->id, 'size_id' => $size16->id]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $matchaPowder->id, 'quantity' => 15]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $freshMilk->id, 'quantity' => 250]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $ice->id, 'quantity' => 150]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $cup16->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $lid->id, 'quantity' => 1]);
        RecipeIngredient::create(['recipe_id' => $r->id, 'ingredient_id' => $straw->id, 'quantity' => 1]);
    }
}
