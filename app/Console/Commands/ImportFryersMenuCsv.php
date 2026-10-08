<?php

namespace App\Console\Commands;

use App\Models\AddOn;
use App\Models\AddOnCategoryRule;
use App\Models\AddOnIngredient;
use App\Models\Category;
use App\Models\CategorySizeRule;
use App\Models\Ingredient;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\ProductModifierRule;
use App\Models\ProductSize;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Size;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ImportFryersMenuCsv extends Command
{
    protected $signature = 'fryers:import {directory? : Directory containing the Fryers menu CSV files}';

    protected $description = 'Import the Fryers menu, inventory, recipes, flavors, and wing rules from CSV';

    public function handle(): int
    {
        $directory = $this->argument('directory') ?? database_path('seeders/fryers_menu_import');
        $ingredients = $this->readCsv($directory, 'ingredients.csv', [
            'name', 'unit', 'opening_stock', 'min_threshold', 'note',
        ]);
        $products = $this->readCsv($directory, 'products.csv', [
            'category', 'product', 'size', 'price_vat_inclusive',
        ]);
        $recipes = $this->readCsv($directory, 'recipes.csv', [
            'product', 'size', 'ingredient', 'quantity', 'unit',
        ]);
        $flavors = $this->readCsv($directory, 'flavors.csv', [
            'flavor', 'ingredient', 'grams_per_wing', 'price_extra',
        ]);
        $wingRules = $this->readCsv($directory, 'wing_rules.csv', [
            'product', 'wings_per_order', 'min_flavors', 'max_flavors', 'dips_included',
        ]);
        $ingredientTypes = $this->readCsv($directory, 'ingredient_types.csv', [
            'ingredient', 'type', 'can_be_addon',
        ]);
        $addOnRules = $this->readCsv($directory, 'addon_rules.csv', [
            'applies_to_category', 'addon', 'price_extra', 'ingredient', 'quantity', 'unit', 'only_sizes',
        ]);
        $sizeRules = $this->readCsv($directory, 'size_rules.csv', [
            'category', 'size', 'sort_order', 'status',
        ]);
        $descriptions = $this->readCsv($directory, 'product_descriptions.csv', [
            'product', 'description',
        ]);

        $this->validateRows($ingredients, $products, $recipes, $flavors, $wingRules, $ingredientTypes, $addOnRules, $sizeRules, $descriptions);

        DB::transaction(function () use ($ingredients, $products, $recipes, $flavors, $wingRules, $ingredientTypes, $addOnRules, $sizeRules, $descriptions): void {
            $ingredientModels = [];
            foreach ($ingredients as $row) {
                $ingredient = Ingredient::firstOrNew(['name' => $row['name']]);
                if (! $ingredient->exists) {
                    $ingredient->current_stock = $row['opening_stock'];
                }
                $ingredient->unit = $row['unit'];
                $ingredient->minimum_stock = $row['min_threshold'];
                $ingredient->notes = $row['note'] === '' ? null : $row['note'];
                $ingredient->save();
                $ingredientModels[$row['name']] = $ingredient;
            }

            $typeRows = [];
            foreach ($ingredientTypes as $row) {
                $typeRows[$row['ingredient']] = $row;
            }

            foreach ($ingredientTypes as $type) {
                $ingredient = $ingredientModels[$type['ingredient']]
                    ?? Ingredient::query()->where('name', $type['ingredient'])->first();
                if ($ingredient) {
                    $ingredient->update([
                        'type' => $type['type'],
                        'can_be_addon' => $type['can_be_addon'] === 'yes',
                    ]);
                    $ingredientModels[$type['ingredient']] = $ingredient;
                }
            }

            $ingredientUnits = [];
            foreach ($ingredients as $row) {
                $ingredientUnits[$row['name']] = $this->normalizeUnit($row['unit']);
            }
            foreach ($addOnRules as $row) {
                $ingredientUnits[$row['ingredient']] = $this->normalizeUnit($row['unit']);
            }

            foreach ($ingredientTypes as $row) {
                if (! isset($ingredientModels[$row['ingredient']]) && isset($ingredientUnits[$row['ingredient']])) {
                    $ingredientModels[$row['ingredient']] = Ingredient::updateOrCreate(
                        ['name' => $row['ingredient']],
                        [
                            'unit' => $ingredientUnits[$row['ingredient']],
                            'current_stock' => 0,
                            'minimum_stock' => 0,
                            'type' => $row['type'],
                            'can_be_addon' => $row['can_be_addon'] === 'yes',
                        ]
                    );
                }
            }

            foreach ($addOnRules as $row) {
                if (! isset($ingredientModels[$row['ingredient']])) {
                    $type = $typeRows[$row['ingredient']];
                    $ingredientModels[$row['ingredient']] = Ingredient::updateOrCreate(
                        ['name' => $row['ingredient']],
                        [
                            'unit' => $this->normalizeUnit($row['unit']),
                            'current_stock' => 0,
                            'minimum_stock' => 0,
                            'type' => $type['type'],
                            'can_be_addon' => $type['can_be_addon'] === 'yes',
                        ]
                    );
                }
            }

            $categoryNames = [];
            foreach ($products as $row) {
                $categoryNames[$row['category']] ??= count($categoryNames) + 1;
            }
            foreach (array_merge(
                array_column($sizeRules, 'category'),
                array_column($addOnRules, 'applies_to_category')
            ) as $categoryName) {
                if ($categoryName !== '(legacy, all categories)') {
                    $categoryNames[$categoryName] ??= count($categoryNames) + 1;
                }
            }
            foreach (['Add-ons', 'Drinks', 'Desserts'] as $requiredCategory) {
                $categoryNames[$requiredCategory] ??= count($categoryNames) + 1;
            }

            $categories = [];
            foreach ($categoryNames as $name => $sortOrder) {
                $categories[$name] = Category::updateOrCreate(
                    ['name' => $name],
                    ['is_active' => true, 'sort_order' => $sortOrder]
                );
            }

            $size = Size::updateOrCreate(['name' => 'Regular'], ['sort_order' => 1, 'is_active' => true]);
            $productsByName = [];
            foreach ($products as $row) {
                $product = Product::query()->where('name', $row['product'])->first();
                if (! $product) {
                    $product = new Product;
                    $product->name = $row['product'];
                }

                $product->category_id = $categories[$row['category']]->id;
                $product->is_active = true;
                $product->save();
                $productsByName[$row['product']] = $product;

                ProductSize::updateOrCreate(
                    ['product_id' => $product->id, 'size_id' => $size->id],
                    ['price' => $row['price_vat_inclusive']]
                );
            }

            foreach ($recipes as $row) {
                $product = $productsByName[$row['product']];
                $ingredient = $ingredientModels[$row['ingredient']];
                $recipe = Recipe::updateOrCreate(
                    ['product_id' => $product->id, 'size_id' => $size->id],
                    []
                );

                RecipeIngredient::updateOrCreate(
                    ['recipe_id' => $recipe->id, 'ingredient_id' => $ingredient->id],
                    ['quantity' => $row['quantity']]
                );
            }

            $flavorGroup = ModifierGroup::updateOrCreate(
                ['name' => 'Wing flavor'],
                ['price' => 0, 'is_active' => true]
            );

            foreach ($flavors as $row) {
                $ingredient = $row['ingredient'] === '' ? null : $ingredientModels[$row['ingredient']];
                ModifierOption::updateOrCreate(
                    ['modifier_group_id' => $flavorGroup->id, 'name' => $row['flavor']],
                    [
                        'price' => $row['price_extra'],
                        'ingredient_id' => $ingredient?->id,
                        'grams_per_wing' => $row['grams_per_wing'],
                        'is_active' => true,
                    ]
                );
            }

            foreach ($wingRules as $row) {
                ProductModifierRule::updateOrCreate(
                    [
                        'product_id' => $productsByName[$row['product']]->id,
                        'modifier_group_id' => $flavorGroup->id,
                    ],
                    [
                        'min_choices' => $row['min_flavors'],
                        'max_choices' => $row['max_flavors'],
                        'wings_per_order' => $row['wings_per_order'],
                        'dips_included' => $row['dips_included'],
                    ]
                );
            }

            CategorySizeRule::query()->update(['is_active' => false]);

            foreach ($sizeRules as $row) {
                $sizeModel = Size::updateOrCreate(
                    ['name' => $row['size']],
                    [
                        'sort_order' => $row['sort_order'],
                        'is_active' => $row['status'] === 'active',
                    ]
                );

                if ($row['category'] === '(legacy, all categories)') {
                    continue;
                }

                $categoryModel = Category::query()->where('name', $row['category'])->firstOrFail();
                CategorySizeRule::updateOrCreate(
                    ['category_id' => $categoryModel->id, 'size_id' => $sizeModel->id],
                    ['sort_order' => $row['sort_order'], 'is_active' => $row['status'] === 'active']
                );
            }

            $descriptionsByName = [];
            foreach ($descriptions as $row) {
                $descriptionsByName[$row['product']] = $row['description'];
            }
            foreach ($descriptionsByName as $name => $description) {
                Product::query()->where('name', $name)->update(['description' => $description]);
            }

            AddOnCategoryRule::query()->update(['is_active' => false]);
            AddOnIngredient::query()->update(['is_active' => false]);
            foreach ($addOnRules as $row) {
                $category = Category::query()->where('name', $row['applies_to_category'])->firstOrFail();
                $ingredient = $ingredientModels[$row['ingredient']];
                if ($ingredient->type !== 'food' || ! $ingredient->can_be_addon) {
                    throw new InvalidArgumentException(
                        "Packaging or non-addon ingredient '{$ingredient->name}' cannot be linked to add-ons."
                    );
                }

                $unit = $this->normalizeUnit($row['unit']);
                if ($unit !== $this->normalizeUnit($ingredient->unit)) {
                    throw new InvalidArgumentException(
                        "Add-on unit '{$row['unit']}' does not match inventory unit '{$ingredient->unit}' for '{$ingredient->name}'."
                    );
                }

                $addOn = AddOn::query()->where('name', $row['addon'])->first();
                if (! $addOn) {
                    $addOn = new AddOn(['name' => $row['addon']]);
                }
                $addOn->price = $row['price_extra'];
                $addOn->is_active = true;
                $addOn->save();

                AddOnCategoryRule::updateOrCreate(
                    ['add_on_id' => $addOn->id, 'category_id' => $category->id],
                    [
                        'only_iced_sizes' => $row['only_sizes'] === 'Iced sizes only',
                        'is_active' => true,
                    ]
                );

                AddOnIngredient::updateOrCreate(
                    ['add_on_id' => $addOn->id, 'ingredient_id' => $ingredient->id],
                    ['quantity' => $row['quantity'], 'unit' => $unit, 'is_active' => true]
                );
            }
        });

        $this->info('Fryers menu CSV import completed successfully.');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $expectedHeaders
     * @return list<array<string, string>>
     */
    private function readCsv(string $directory, string $filename, array $expectedHeaders): array
    {
        $path = $directory.DIRECTORY_SEPARATOR.$filename;
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
                    "Invalid header in {$path}. Expected: ".implode(',', $expectedHeaders)
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
     * @param  list<array<string, string>>  $flavors
     * @param  list<array<string, string>>  $wingRules
     */
    private function validateRows(
        array $ingredients,
        array $products,
        array $recipes,
        array $flavors,
        array $wingRules,
        array $ingredientTypes,
        array $addOnRules,
        array $sizeRules,
        array $descriptions
    ): void {
        $validUnits = ['g', 'ml', 'pcs', 'oz', 'kg', 'L'];
        $ingredientNames = [];
        foreach ($ingredients as $row) {
            if ($row['name'] === '' || ! in_array($row['unit'], $validUnits, true)) {
                throw new InvalidArgumentException('Each ingredient needs a name and supported inventory unit.');
            }
            $this->validateNumber($row['opening_stock'], 'ingredient opening stock');
            $this->validateNumber($row['min_threshold'], 'ingredient minimum threshold');
            if (isset($ingredientNames[$row['name']])) {
                throw new InvalidArgumentException("Duplicate ingredient name: {$row['name']}");
            }
            $ingredientNames[$row['name']] = $row['unit'];
        }

        $productNames = [];
        foreach ($products as $row) {
            foreach (['category', 'product', 'size'] as $field) {
                if ($row[$field] === '') {
                    throw new InvalidArgumentException("Product CSV field '{$field}' cannot be blank.");
                }
            }
            if ($row['size'] !== 'Regular') {
                throw new InvalidArgumentException('Fryers products must use the Regular size.');
            }
            $this->validateNumber($row['price_vat_inclusive'], 'VAT-inclusive product price');
            if (isset($productNames[$row['product']])) {
                throw new InvalidArgumentException("Duplicate product name: {$row['product']}");
            }
            $productNames[$row['product']] = true;
        }

        foreach ($recipes as $row) {
            foreach (['product', 'ingredient', 'quantity', 'unit'] as $field) {
                if ($row[$field] === '') {
                    throw new InvalidArgumentException("Recipe CSV field '{$field}' cannot be blank.");
                }
            }
            if (! isset($productNames[$row['product']]) || $row['size'] !== 'Regular') {
                throw new InvalidArgumentException("Recipe refers to an unknown product-size: {$row['product']} / {$row['size']}");
            }
            if (! isset($ingredientNames[$row['ingredient']])) {
                throw new InvalidArgumentException("Recipe refers to unknown ingredient '{$row['ingredient']}'.");
            }
            $recipeUnit = $row['unit'] === 'pc' ? 'pcs' : $row['unit'];
            if ($recipeUnit !== $ingredientNames[$row['ingredient']]) {
                throw new InvalidArgumentException(
                    "Recipe unit '{$row['unit']}' does not match inventory unit '{$ingredientNames[$row['ingredient']]}' for '{$row['ingredient']}'."
                );
            }
            $this->validateNumber($row['quantity'], 'recipe quantity');
            if ((float) $row['quantity'] <= 0) {
                throw new InvalidArgumentException('Recipe quantity must be greater than zero.');
            }
        }

        $flavorNames = [];
        foreach ($flavors as $row) {
            if ($row['flavor'] === '' || isset($flavorNames[$row['flavor']])) {
                throw new InvalidArgumentException("Flavor names must be present and unique: {$row['flavor']}");
            }
            if ($row['ingredient'] !== '' && ! isset($ingredientNames[$row['ingredient']])) {
                throw new InvalidArgumentException("Flavor refers to unknown ingredient '{$row['ingredient']}'.");
            }
            $this->validateNumber($row['grams_per_wing'], 'grams per wing');
            $this->validateNumber($row['price_extra'], 'flavor extra price');
            if ($row['ingredient'] === '' && (float) $row['grams_per_wing'] > 0) {
                throw new InvalidArgumentException("Flavor '{$row['flavor']}' needs an ingredient for its positive quantity.");
            }
            $flavorNames[$row['flavor']] = true;
        }

        $ruleProducts = [];
        foreach ($wingRules as $row) {
            if (! isset($productNames[$row['product']]) || isset($ruleProducts[$row['product']])) {
                throw new InvalidArgumentException("Wing rule has an unknown or duplicate product: {$row['product']}");
            }
            foreach (['wings_per_order', 'min_flavors', 'max_flavors', 'dips_included'] as $field) {
                $this->validateNumber($row[$field], "wing rule {$field}");
                if ((string) (int) $row[$field] !== $row[$field]) {
                    throw new InvalidArgumentException("Wing rule {$field} must be an integer.");
                }
            }
            if ((int) $row['wings_per_order'] < 1
                || (int) $row['min_flavors'] < 0
                || (int) $row['max_flavors'] < (int) $row['min_flavors']
                || (int) $row['max_flavors'] > count($flavorNames)) {
                throw new InvalidArgumentException("Invalid flavor choice rule for '{$row['product']}'.");
            }
            $ruleProducts[$row['product']] = true;
        }

        if ($products === [] || $flavors === [] || $wingRules === []) {
            throw new InvalidArgumentException('Products, flavors, and wing rules must each contain data rows.');
        }

        $typesByName = [];
        foreach ($ingredientTypes as $row) {
            if (! in_array($row['type'], ['food', 'packaging'], true)
                || ! in_array($row['can_be_addon'], ['yes', 'no'], true)
                || isset($typesByName[$row['ingredient']])) {
                throw new InvalidArgumentException("Invalid or duplicate ingredient type row: {$row['ingredient']}");
            }
            $typesByName[$row['ingredient']] = $row;
        }

        $validAddonCategories = [];
        $addonComponents = [];
        foreach ($addOnRules as $row) {
            if ($row['applies_to_category'] === '' || $row['addon'] === '' || $row['ingredient'] === '') {
                throw new InvalidArgumentException('Add-on rules require category, add-on, and ingredient names.');
            }
            if (! in_array($row['only_sizes'], ['All', 'Iced sizes only'], true)) {
                throw new InvalidArgumentException("Invalid add-on size rule: {$row['only_sizes']}");
            }
            if (! isset($typesByName[$row['ingredient']])
                || $typesByName[$row['ingredient']]['type'] !== 'food'
                || $typesByName[$row['ingredient']]['can_be_addon'] !== 'yes') {
                throw new InvalidArgumentException(
                    "Add-on ingredient '{$row['ingredient']}' must be typed as food and allowed for add-ons."
                );
            }
            $unit = $this->normalizeUnit($row['unit']);
            if (! in_array($unit, $validUnits, true)) {
                throw new InvalidArgumentException("Unsupported add-on ingredient unit '{$row['unit']}'.");
            }
            $this->validateNumber($row['quantity'], 'add-on ingredient quantity');
            $this->validateNumber($row['price_extra'], 'add-on price');
            if ((float) $row['quantity'] <= 0) {
                throw new InvalidArgumentException('Add-on ingredient quantity must be greater than zero.');
            }
            $validAddonCategories[$row['applies_to_category']] = true;
            $addonComponents[$row['addon']."\0".$row['ingredient']] = true;
        }

        $sizesByCategory = [];
        foreach ($sizeRules as $row) {
            if ($row['category'] === '' || $row['size'] === ''
                || ! in_array($row['status'], ['active', 'retired'], true)
                || ! ctype_digit($row['sort_order'])) {
                throw new InvalidArgumentException('Invalid size rule row.');
            }
            if ($row['category'] === '(legacy, all categories)' && $row['status'] !== 'retired') {
                throw new InvalidArgumentException('Legacy size rows must be retired.');
            }
            if ($row['category'] !== '(legacy, all categories)') {
                $sizesByCategory[$row['category']."\0".$row['size']] = true;
            }
        }

        foreach ($products as $row) {
            if (! isset($sizesByCategory[$row['category']."\0".$row['size']])) {
                throw new InvalidArgumentException(
                    "Product size '{$row['size']}' is not allowed in category '{$row['category']}'."
                );
            }
        }

        $descriptionProducts = [];
        foreach ($descriptions as $row) {
            if ($row['product'] === '' || isset($descriptionProducts[$row['product']])) {
                throw new InvalidArgumentException("Product descriptions must be non-blank and unique: {$row['product']}");
            }
            $descriptionProducts[$row['product']] = true;
        }
    }

    private function normalizeUnit(string $unit): string
    {
        return $unit === 'pc' ? 'pcs' : $unit;
    }

    private function validateNumber(string $value, string $label): void
    {
        if ($value === '' || ! is_numeric($value) || (float) $value < 0) {
            throw new InvalidArgumentException("{$label} must be a non-negative number.");
        }
    }
}
