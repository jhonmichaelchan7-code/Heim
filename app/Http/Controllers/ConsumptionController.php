<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ConsumptionController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        // Today's orders
        $ordersCount = Order::whereDate('created_at', $date)->completed()->count();
        $itemsSold = OrderItem::whereHas('order', fn ($q) => $q->whereDate('created_at', $date)->completed())->sum('quantity');

        // Get all ingredients
        $ingredients = Ingredient::orderBy('name')->get();

        // Get transactions for the day
        $transactions = InventoryTransaction::whereDate('created_at', $date)
            ->selectRaw('ingredient_id, type, SUM(quantity) as total_quantity')
            ->groupBy('ingredient_id', 'type')
            ->get()
            ->groupBy('ingredient_id');

        // Product-level breakdown per ingredient (sales consumption)
        $productBreakdown = InventoryTransaction::whereDate('created_at', $date)
            ->where('type', 'sales_consumption')
            ->where('reference_type', 'order')
            ->with('ingredient')
            ->get()
            ->groupBy('ingredient_id')
            ->map(function ($txns) use ($date) {
                // Get order items for each referenced order
                $orderIds = $txns->pluck('reference_id')->unique();
                $items = OrderItem::whereIn('order_id', $orderIds)
                    ->selectRaw('product_name, size_name, SUM(quantity) as total_qty')
                    ->groupBy('product_name', 'size_name')
                    ->get();
                return $items;
            });

        return view('consumption.index', compact(
            'date', 'ordersCount', 'itemsSold',
            'ingredients', 'transactions', 'productBreakdown'
        ));
    }
}
