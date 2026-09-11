<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Recipe;
use App\Models\SystemNotification;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct ingredients based on recipe for a completed order.
     * All operations are wrapped in a DB transaction.
     */
    public function deductForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $recipe = Recipe::where('product_id', $item->product_id)
                    ->where('size_id', $item->size_id)
                    ->with('recipeIngredients.ingredient')
                    ->first();

                if (!$recipe) {
                    continue; // No recipe defined for this product/size
                }

                foreach ($recipe->recipeIngredients as $recipeIngredient) {
                    $ingredient = $recipeIngredient->ingredient;
                    $totalQty = $recipeIngredient->quantity * $item->quantity;
                    $previousStock = $ingredient->current_stock;
                    $newStock = $previousStock - $totalQty;

                    // Update ingredient stock
                    $ingredient->update(['current_stock' => $newStock]);

                    // Create inventory transaction
                    InventoryTransaction::create([
                        'ingredient_id' => $ingredient->id,
                        'type' => 'sales_consumption',
                        'quantity' => $totalQty,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'performed_by' => auth()->id(),
                    ]);

                    // Check stock levels and create notifications
                    $this->checkStockLevel($ingredient->fresh());
                }
            }
        });
    }

    /**
     * Process a stock-in transaction.
     */
    public function stockIn(Ingredient $ingredient, float $quantity, ?string $supplier = null, ?string $notes = null): InventoryTransaction
    {
        return DB::transaction(function () use ($ingredient, $quantity, $supplier, $notes) {
            $previousStock = $ingredient->current_stock;
            $newStock = $previousStock + $quantity;

            $ingredient->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'ingredient_id' => $ingredient->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'supplier' => $supplier,
                'notes' => $notes,
                'performed_by' => auth()->id(),
            ]);

            AuditLog::log(
                'stock_in',
                'inventory',
                "Stock in: {$quantity} {$ingredient->unit} of {$ingredient->name}",
                null,
                'ingredient',
                $ingredient->id,
                ['quantity' => $quantity, 'supplier' => $supplier]
            );

            // Resolve low-stock notifications if replenished
            $this->resolveStockNotifications($ingredient->fresh());

            return $transaction;
        });
    }

    /**
     * Record waste/spoilage.
     */
    public function recordWaste(Ingredient $ingredient, float $quantity, string $reason, ?string $notes = null): InventoryTransaction
    {
        return DB::transaction(function () use ($ingredient, $quantity, $reason, $notes) {
            $previousStock = $ingredient->current_stock;
            $newStock = $previousStock - $quantity;

            $ingredient->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'ingredient_id' => $ingredient->id,
                'type' => 'waste',
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'notes' => $notes,
                'performed_by' => auth()->id(),
            ]);

            AuditLog::log(
                'waste_recorded',
                'inventory',
                "Waste: {$quantity} {$ingredient->unit} of {$ingredient->name} - {$reason}",
                null,
                'ingredient',
                $ingredient->id,
                ['quantity' => $quantity, 'reason' => $reason]
            );

            $this->checkStockLevel($ingredient->fresh());

            return $transaction;
        });
    }

    /**
     * Record a stock adjustment (authorized).
     */
    public function adjustStock(Ingredient $ingredient, float $quantity, string $reason, ?string $notes = null): InventoryTransaction
    {
        return DB::transaction(function () use ($ingredient, $quantity, $reason, $notes) {
            $previousStock = $ingredient->current_stock;
            $newStock = $previousStock + $quantity; // quantity can be negative

            $ingredient->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'ingredient_id' => $ingredient->id,
                'type' => 'adjustment',
                'quantity' => abs($quantity),
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'notes' => $notes,
                'performed_by' => auth()->id(),
            ]);

            AuditLog::log(
                'stock_adjusted',
                'inventory',
                "Adjustment: {$quantity} {$ingredient->unit} of {$ingredient->name} - {$reason}",
                null,
                'ingredient',
                $ingredient->id,
                ['quantity' => $quantity, 'reason' => $reason]
            );

            if ($quantity < 0) {
                $this->checkStockLevel($ingredient->fresh());
            } else {
                $this->resolveStockNotifications($ingredient->fresh());
            }

            return $transaction;
        });
    }

    /**
     * Check stock level and create notifications if needed.
     */
    public function checkStockLevel(Ingredient $ingredient): void
    {
        if ($ingredient->current_stock <= 0) {
            // Check if unresolved out-of-stock notification already exists
            $exists = SystemNotification::where('type', 'out_of_stock')
                ->where('data->ingredient_id', $ingredient->id)
                ->whereNull('resolved_at')
                ->exists();

            if (!$exists) {
                SystemNotification::create([
                    'type' => 'out_of_stock',
                    'title' => 'Out of Stock',
                    'message' => "{$ingredient->name} is out of stock!",
                    'data' => ['ingredient_id' => $ingredient->id, 'current_stock' => $ingredient->current_stock],
                    'target_role' => 'manager',
                ]);
            }
        } elseif ($ingredient->current_stock <= $ingredient->minimum_stock) {
            $exists = SystemNotification::where('type', 'low_stock')
                ->where('data->ingredient_id', $ingredient->id)
                ->whereNull('resolved_at')
                ->exists();

            if (!$exists) {
                SystemNotification::create([
                    'type' => 'low_stock',
                    'title' => 'Low Stock Alert',
                    'message' => "{$ingredient->name} is running low ({$ingredient->current_stock} {$ingredient->unit} remaining, minimum: {$ingredient->minimum_stock} {$ingredient->unit})",
                    'data' => ['ingredient_id' => $ingredient->id, 'current_stock' => $ingredient->current_stock, 'minimum_stock' => $ingredient->minimum_stock],
                    'target_role' => 'manager',
                ]);
            }
        }
    }

    /**
     * Resolve stock notifications when stock is replenished.
     */
    private function resolveStockNotifications(Ingredient $ingredient): void
    {
        if ($ingredient->current_stock > $ingredient->minimum_stock) {
            SystemNotification::whereIn('type', ['low_stock', 'out_of_stock'])
                ->where('data->ingredient_id', $ingredient->id)
                ->whereNull('resolved_at')
                ->update([
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);
        }
    }
}
