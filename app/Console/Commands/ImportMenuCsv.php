<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Size;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ImportMenuCsv extends Command
{
    protected $signature = 'menu:import {directory? : Directory containing ingredients.csv, products.csv, and recipes.csv}';

    protected $description = 'Import the menu, ingredient stock, and recipe quantities from CSV files';

    public function handle(): int
    {
        $directory = $this->argument('directory') ?? database_path('seeders/menu');
        $ingredients = $this->readCsv($directory.DIRECTORY_SEPARATOR.'ingredients.csv', [
            'name', 'unit', 'stock', 'min_threshold',
        ]);
        $products = $this->readCsv($directory.DIRECTORY_SEPARATOR.'products.csv', [
            'category', 'product', 'size', 'price',
        ]);
        $recipes = $this->readCsv($directory.DIRECTORY_SEPARATOR.'recipes.csv', [
            'product', 'size', 'ingredient', 'quantity', 'unit',
        ]);

        $this->validateCsvRows($ingredients, $products, $recipes);

        DB::transaction(function () use ($ingredients, $products, $recipes): void {
            $categoryNames = [];
            $productCategories = [];
            $productSizeRows = [];
            $menuProducts = [];
            $menuSizes = [];

            foreach ($products as $row) {
                $categoryNames[$row['category']] ??= count($categoryNames) + 1;
                $productCategories[$row['product']] = $row['category'];
                $productSizeRows[] = $row;
                $menuProducts[$row['product']] = true;
                $menuSizes[$row['size']] ??= count($menuSizes) + 1;
            }

            $categories = [];
            foreach ($categoryNames as $name => $sortOrder) {
                $categories[$name] = Category::updateOrCreate(
                    ['name' => $name],
                    ['is_active' => true, 'sort_order' => $sortOrder]
                );
            }

            Category::query()
                ->whereNotIn('name', array_keys($categoryNames))
                ->update(['is_active' => false]);

            $menuProductModels = [];
            foreach ($productCategories as $name => $categoryName) {
                $product = Product::query()
                    ->where('name', $name)
                    ->where('category_id', $categories[$categoryName]->id)
                    ->first();

                if (! $product) {
                    $product = Product::query()
                        ->where('name', $name)
                        ->orderByDesc('is_active')
                        ->first();
                }

                if (! $product) {
                    $product = new Product;
                    $product->name = $name;
                }

                $product->category_id = $categories[$categoryName]->id;
                $product->is_active = true;
                $product->save();
                $menuProductModels[$name] = $product;
            }

            $menuProductIds = array_map(
                static fn (Product $product): int => $product->id,
                array_values($menuProductModels)
            );

            Product::query()
                ->whereNotIn('id', $menuProductIds)
                ->update(['is_active' => false]);

            $sizes = [];
            foreach ($menuSizes as $name => $sortOrder) {
                $sizes[$name] = Size::updateOrCreate(
                    ['name' => $name],
                    ['sort_order' => $sortOrder]
                );
            }

            $desiredSizeIdsByProduct = [];
            foreach ($productSizeRows as $row) {
                $product = $menuProductModels[$row['product']];
                $size = $sizes[$row['size']];

                ProductSize::updateOrCreate(
                    ['product_id' => $product->id, 'size_id' => $size->id],
                    ['price' => $row['price']]
                );

                $desiredSizeIdsByProduct[$product->id][] = $size->id;
            }

            foreach ($desiredSizeIdsByProduct as $productId => $sizeIds) {
                ProductSize::query()
                    ->where('product_id', $productId)
                    ->whereNotIn('size_id', array_unique($sizeIds))
                    ->delete();
            }

            foreach ($ingredients as $row) {
                Ingredient::updateOrCreate(
                    ['name' => $row['name']],
                    [
                        'unit' => $row['unit'],
                        'current_stock' => $row['stock'],
                        'minimum_stock' => $row['min_threshold'],
                    ]
                );
            }

            foreach ($recipes as $row) {
                if ($row['quantity'] === '') {
                    continue;
                }

                $product = $menuProductModels[$row['product']];
                $size = $sizes[$row['size']];
                $ingredient = Ingredient::query()
                    ->where('name', $row['ingredient'])
                    ->firstOrFail();

                if ($ingredient->unit !== $row['unit']) {
                    throw new InvalidArgumentException(
                        "Recipe unit '{$row['unit']}' does not match ingredient '{$ingredient->name}' unit '{$ingredient->unit}'."
                    );
                }

                $recipe = Recipe::updateOrCreate(
                    ['product_id' => $product->id, 'size_id' => $size->id],
                    []
                );

                RecipeIngredient::updateOrCreate(
                    ['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id],
                    ['quantity' => $row['quantity']]
                );
            }
        });

        $this->info('Menu CSV import completed successfully.');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $expectedHeaders
     * @return list<array<string, string>>
     */
    private function readCsv(string $path, array $expectedHeaders): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Required CSV file not found: {$path}");
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException("Unable to read CSV file: {$path}");
        }

        try {
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw new RuntimeException("CSV file is empty: {$path}");
            }

            $headers = array_map(static fn ($header): string => trim((string) $header), $headers);
            if ($headers !== $expectedHeaders) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid header in %s. Expected: %s',
                        $path,
                        implode(',', $expectedHeaders)
                    )
                );
            }

            $rows = [];
            $line = 1;
            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                $values = array_map(static fn ($value): string => trim((string) $value), $values);

                if (count($values) === 1 && $values[0] === '') {
                    continue;
                }

                if (count($values) !== count($headers)) {
                    throw new RuntimeException("Incorrect number of fields in {$path} on line {$line}.");
                }

                $rows[] = array_combine($headers, $values);
            }

            return $rows;
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  list<array<string, string>>  $ingredients
     * @param  list<array<string, string>>  $products
     * @param  list<array<string, string>>  $recipes
     */
    private function validateCsvRows(array $ingredients, array $products, array $recipes): void
    {
        if ($products === []) {
            throw new InvalidArgumentException('products.csv must contain at least one product-size row.');
        }

        $validUnits = ['g', 'ml', 'pcs', 'oz', 'kg', 'L'];
        $ingredientNames = [];
        foreach ($ingredients as $row) {
            if ($row['name'] === '' || ! in_array($row['unit'], $validUnits, true)) {
                throw new InvalidArgumentException('Each ingredient must have a name and a supported unit.');
            }
            $this->validateNonNegativeNumber($row['stock'], 'ingredient stock');
            $this->validateNonNegativeNumber($row['min_threshold'], 'ingredient minimum threshold');

            if (isset($ingredientNames[$row['name']])) {
                throw new InvalidArgumentException("Duplicate ingredient in ingredients.csv: {$row['name']}");
            }
            $ingredientNames[$row['name']] = true;
        }

        $productCategories = [];
        $productSizeKeys = [];
        $uniqueProducts = [];
        foreach ($products as $row) {
            foreach (['category', 'product', 'size'] as $field) {
                if ($row[$field] === '') {
                    throw new InvalidArgumentException("Product CSV field '{$field}' cannot be blank.");
                }
            }
            $this->validateNonNegativeNumber($row['price'], 'product price');

            if (isset($productCategories[$row['product']]) && $productCategories[$row['product']] !== $row['category']) {
                throw new InvalidArgumentException(
                    "Product '{$row['product']}' is assigned to more than one category in products.csv."
                );
            }
            $productCategories[$row['product']] = $row['category'];

            $sizeKey = $row['product']."\0".$row['size'];
            if (isset($productSizeKeys[$sizeKey])) {
                throw new InvalidArgumentException(
                    "Duplicate product-size row in products.csv: {$row['product']} / {$row['size']}"
                );
            }
            $productSizeKeys[$sizeKey] = true;
            $uniqueProducts[$row['product']] = true;
        }

        $recipeKeys = [];
        foreach ($recipes as $row) {
            if ($row['quantity'] === '') {
                continue;
            }

            foreach (['product', 'size', 'ingredient', 'unit'] as $field) {
                if ($row[$field] === '') {
                    throw new InvalidArgumentException("Recipe CSV field '{$field}' is required when quantity is set.");
                }
            }

            if (! isset($productSizeKeys[$row['product']."\0".$row['size']])) {
                throw new InvalidArgumentException(
                    "Recipe refers to an unknown product-size: {$row['product']} / {$row['size']}"
                );
            }

            $this->validateNonNegativeNumber($row['quantity'], 'recipe quantity');
            if ((float) $row['quantity'] <= 0) {
                throw new InvalidArgumentException('Recipe quantity must be greater than zero.');
            }

            $key = $row['product']."\0".$row['size']."\0".$row['ingredient'];
            if (isset($recipeKeys[$key])) {
                throw new InvalidArgumentException('Duplicate product-size-ingredient row in recipes.csv.');
            }
            $recipeKeys[$key] = true;
        }
    }

    private function validateNonNegativeNumber(string $value, string $label): void
    {
        if ($value === '' || ! is_numeric($value) || (float) $value < 0) {
            throw new InvalidArgumentException("{$label} must be a non-negative number.");
        }
    }
}
