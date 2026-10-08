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
     * Deduct ingredients based on recipe and dynamic add-on modifiers for a completed order.
     * All operations are wrapped in a DB transaction.
     */
    public function deductForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                // 1. Base Recipe BOM Deductions
                $recipe = Recipe::where('product_id', $item->product_id)
                    ->where('size_id', $item->size_id)
                    ->with('recipeIngredients.ingredient')
                    ->first();

                if ($recipe) {
                    foreach ($recipe->recipeIngredients as $recipeIngredient) {
                        $ingredient = $recipeIngredient->ingredient;
                        if (! $ingredient) {
                            continue;
                        }

                        $totalQty = $recipeIngredient->quantity * $item->quantity;
                        $previousStock = $ingredient->current_stock;
                        $newStock = $previousStock - $totalQty;

                        $ingredient->update(['current_stock' => $newStock]);

                        InventoryTransaction::create([
                            'ingredient_id' => $ingredient->id,
                            'type' => 'sales_consumption',
                            'quantity' => $totalQty,
                            'previous_stock' => $previousStock,
                            'new_stock' => $newStock,
                            'reference_type' => 'order',
                            'reference_id' => $order->id,
                            'notes' => "Base BOM: {$item->product_name} ({$item->size_name}) x{$item->quantity}",
                            'performed_by' => auth()->id(),
                        ]);

                        $this->checkStockLevel($ingredient->fresh());
                    }
                }

                // 2. Dynamic Modifier Deductions (Add-ons BOM)
                if ($item->addOns && $item->addOns->isNotEmpty()) {
                    foreach ($item->addOns as $orderItemAddOn) {
                        $snapshots = $orderItemAddOn->ingredientSnapshots;
                        if ($snapshots->isNotEmpty()) {
                            foreach ($snapshots as $snapshot) {
                                $ingredient = $snapshot->ingredient;
                                if (! $ingredient) {
                                    throw new \RuntimeException("Missing inventory ingredient for add-on {$orderItemAddOn->add_on_name}.");
                                }
                                $quantity = (float) $snapshot->quantity;
                                $previousStock = $ingredient->current_stock;
                                $newStock = $previousStock - $quantity;
                                $ingredient->update(['current_stock' => $newStock]);
                                InventoryTransaction::create([
                                    'ingredient_id' => $ingredient->id,
                                    'type' => 'sales_consumption',
                                    'quantity' => $quantity,
                                    'previous_stock' => $previousStock,
                                    'new_stock' => $newStock,
                                    'reference_type' => 'order',
                                    'reference_id' => $order->id,
                                    'notes' => "Add-on BOM: +{$orderItemAddOn->add_on_name} on {$item->product_name} x{$item->quantity}",
                                    'performed_by' => auth()->id(),
                                ]);
                                $this->checkStockLevel($ingredient->fresh());
                            }

                            continue;
                        }

                        $addOn = \App\Models\AddOn::find($orderItemAddOn->add_on_id);
                        if ($addOn && $addOn->ingredient_id && $addOn->quantity > 0) {
                            $ingredient = $addOn->ingredient;
                            if (! $ingredient) {
                                continue;
                            }

                            $totalModifierQty = $addOn->quantity * $item->quantity;
                            $previousStock = $ingredient->current_stock;
                            $newStock = $previousStock - $totalModifierQty;

                            $ingredient->update(['current_stock' => $newStock]);

                            InventoryTransaction::create([
                                'ingredient_id' => $ingredient->id,
                                'type' => 'sales_consumption',
                                'quantity' => $totalModifierQty,
                                'previous_stock' => $previousStock,
                                'new_stock' => $newStock,
                                'reference_type' => 'order',
                                'reference_id' => $order->id,
                                'notes' => "Modifier BOM: +{$addOn->name} on {$item->product_name} x{$item->quantity}",
                                'performed_by' => auth()->id(),
                            ]);

                            $this->checkStockLevel($ingredient->fresh());
                        }
                    }
                }

                foreach ($item->modifiers ?? [] as $modifier) {
                    if ((float) $modifier->consumed_quantity <= 0) {
                        continue;
                    }

                    $ingredient = $modifier->ingredient;
                    if (! $ingredient) {
                        throw new \RuntimeException(
                            "Missing inventory ingredient for {$modifier->group_name} modifier {$modifier->option_name}."
                        );
                    }

                    $quantity = (float) $modifier->consumed_quantity;
                    $previousStock = $ingredient->current_stock;
                    $newStock = $previousStock - $quantity;
                    $ingredient->update(['current_stock' => $newStock]);

                    InventoryTransaction::create([
                        'ingredient_id' => $ingredient->id,
                        'type' => 'sales_consumption',
                        'quantity' => $quantity,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'notes' => "Modifier BOM: {$modifier->group_name} - {$modifier->option_name} on {$item->product_name} x{$item->quantity}",
                        'performed_by' => auth()->id(),
                    ]);

                    $this->checkStockLevel($ingredient->fresh());
                }
            }
        });
    }

    /**
     * Restore ingredients for a refunded, cancelled, or voided order (Optional Inventory Restoration).
     */
    public function restoreForOrder(Order $order, string $reason = 'Order refund / void restoration'): void
    {
        DB::transaction(function () use ($order, $reason) {
            foreach ($order->items as $item) {
                // Restore Base Recipe BOM
                $recipe = Recipe::where('product_id', $item->product_id)
                    ->where('size_id', $item->size_id)
                    ->with('recipeIngredients.ingredient')
                    ->first();

                if ($recipe) {
                    foreach ($recipe->recipeIngredients as $recipeIngredient) {
                        $ingredient = $recipeIngredient->ingredient;
                        if (! $ingredient) {
                            continue;
                        }

                        $totalQty = $recipeIngredient->quantity * $item->quantity;
                        $previousStock = $ingredient->current_stock;
                        $newStock = $previousStock + $totalQty;

                        $ingredient->update(['current_stock' => $newStock]);

                        InventoryTransaction::create([
                            'ingredient_id' => $ingredient->id,
                            'type' => 'adjustment',
                            'quantity' => $totalQty,
                            'previous_stock' => $previousStock,
                            'new_stock' => $newStock,
                            'reason' => "Restoration: {$reason} (Order #{$order->order_number})",
                            'reference_type' => 'order',
                            'reference_id' => $order->id,
                            'performed_by' => auth()->id(),
                        ]);

                        $this->resolveStockNotifications($ingredient->fresh());
                    }
                }

                // Restore Dynamic Add-on Modifier BOM
                if ($item->addOns && $item->addOns->isNotEmpty()) {
                    foreach ($item->addOns as $orderItemAddOn) {
                        $snapshots = $orderItemAddOn->ingredientSnapshots;
                        if ($snapshots->isNotEmpty()) {
                            foreach ($snapshots as $snapshot) {
                                $ingredient = $snapshot->ingredient;
                                if (! $ingredient) {
                                    throw new \RuntimeException("Missing inventory ingredient for add-on {$orderItemAddOn->add_on_name}.");
                                }
                                $quantity = (float) $snapshot->quantity;
                                $previousStock = $ingredient->current_stock;
                                $newStock = $previousStock + $quantity;
                                $ingredient->update(['current_stock' => $newStock]);
                                InventoryTransaction::create([
                                    'ingredient_id' => $ingredient->id,
                                    'type' => 'adjustment',
                                    'quantity' => $quantity,
                                    'previous_stock' => $previousStock,
                                    'new_stock' => $newStock,
                                    'reason' => "Add-on restoration: +{$orderItemAddOn->add_on_name} on {$item->product_name} (Order #{$order->order_number}); {$reason}",
                                    'reference_type' => 'order',
                                    'reference_id' => $order->id,
                                    'performed_by' => auth()->id(),
                                ]);
                                $this->resolveStockNotifications($ingredient->fresh());
                            }

                            continue;
                        }

                        $addOn = \App\Models\AddOn::find($orderItemAddOn->add_on_id);
                        if ($addOn && $addOn->ingredient_id && $addOn->quantity > 0) {
                            $ingredient = $addOn->ingredient;
                            if (! $ingredient) {
                                continue;
                            }

                            $totalModifierQty = $addOn->quantity * $item->quantity;
                            $previousStock = $ingredient->current_stock;
                            $newStock = $previousStock + $totalModifierQty;

                            $ingredient->update(['current_stock' => $newStock]);

                            InventoryTransaction::create([
                                'ingredient_id' => $ingredient->id,
                                'type' => 'adjustment',
                                'quantity' => $totalModifierQty,
                                'previous_stock' => $previousStock,
                                'new_stock' => $newStock,
                                'reason' => "Modifier Restoration: +{$addOn->name} on {$item->product_name} (Order #{$order->order_number})",
                                'reference_type' => 'order',
                                'reference_id' => $order->id,
                                'performed_by' => auth()->id(),
                            ]);

                            $this->resolveStockNotifications($ingredient->fresh());
                        }
                    }
                }

                foreach ($item->modifiers ?? [] as $modifier) {
                    if ((float) $modifier->consumed_quantity <= 0 || ! $modifier->ingredient_id) {
                        continue;
                    }

                    $ingredient = $modifier->ingredient;
                    if (! $ingredient) {
                        throw new \RuntimeException(
                            "Missing inventory ingredient for {$modifier->group_name} modifier {$modifier->option_name}."
                        );
                    }

                    $quantity = (float) $modifier->consumed_quantity;
                    $previousStock = $ingredient->current_stock;
                    $newStock = $previousStock + $quantity;
                    $ingredient->update(['current_stock' => $newStock]);

                    InventoryTransaction::create([
                        'ingredient_id' => $ingredient->id,
                        'type' => 'adjustment',
                        'quantity' => $quantity,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'reason' => "Modifier Restoration: {$modifier->group_name} - {$modifier->option_name} (Order #{$order->order_number}); {$reason}",
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'performed_by' => auth()->id(),
                    ]);

                    $this->resolveStockNotifications($ingredient->fresh());
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
     * Process a stock-out transaction (waste, damage, expiration, transfer, manual pull-out).
     */
    public function stockOut(Ingredient $ingredient, float $quantity, string $reason, ?string $notes = null): InventoryTransaction
    {
        return DB::transaction(function () use ($ingredient, $quantity, $reason, $notes) {
            $previousStock = $ingredient->current_stock;
            $newStock = $previousStock - $quantity;

            $ingredient->update(['current_stock' => $newStock]);

            $transaction = InventoryTransaction::create([
                'ingredient_id' => $ingredient->id,
                'type' => 'stock_out',
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
                'notes' => $notes,
                'performed_by' => auth()->id(),
            ]);

            AuditLog::log(
                'stock_out',
                'inventory',
                "Stock out: {$quantity} {$ingredient->unit} of {$ingredient->name} - {$reason}",
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
                'quantity' => $quantity,
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

            if (! $exists) {
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

            if (! $exists) {
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
