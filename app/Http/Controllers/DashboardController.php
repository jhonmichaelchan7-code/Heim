<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\SystemNotification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Today's stats
        $todayOrders = Order::whereDate('created_at', today())->completed()->get();
        $todayRevenue = $todayOrders->sum('total');
        $todayOrderCount = $todayOrders->count();
        $todayItemsSold = $todayOrders->load('items')->flatMap->items->sum('quantity');

        // Weekly revenue (last 7 days)
        $weeklyRevenue = Order::where('created_at', '>=', now()->subDays(7))
            ->completed()
            ->sum('total');

        // Low stock ingredients
        $lowStockIngredients = Ingredient::whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->take(10)
            ->get();

        // Recent orders
        $recentOrders = Order::with('items', 'payment')
            ->latest()
            ->take(10)
            ->get();

        // Unread notifications
        $notifications = [];
        if ($user->isAtLeast('manager')) {
            $notifications = SystemNotification::unread()
                ->forRole($user->role)
                ->latest()
                ->take(5)
                ->get();
        }

        // Best sellers today
        $bestSellers = \App\Models\OrderItem::whereHas('order', function ($q) {
                $q->whereDate('created_at', today())->where('status', 'completed');
            })
            ->selectRaw('product_name, SUM(quantity) as total_qty')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'todayRevenue', 'todayOrderCount', 'todayItemsSold',
            'weeklyRevenue', 'lowStockIngredients', 'recentOrders',
            'notifications', 'bestSellers'
        ));
    }
}
