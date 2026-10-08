<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shift;
use App\Models\SystemNotification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedBranchId = $user->branch_id ?: $request->get('branch_id');
        $branches = Branch::active()->get();

        // Base query for completed orders with optional branch filtering
        $baseOrdersQuery = Order::completed();
        if ($selectedBranchId) {
            $baseOrdersQuery->where('branch_id', $selectedBranchId);
        }

        // Today's stats
        $todayOrders = (clone $baseOrdersQuery)->whereDate('created_at', today())->with('items', 'payment', 'payments')->get();
        $todayRevenue = $todayOrders->sum('total');
        $todayOrderCount = $todayOrders->count();
        $todayItemsSold = $todayOrders->flatMap->items->sum('quantity');

        // Order Type breakdown today
        $orderTypesBreakdown = [
            'dine_in' => [
                'count' => $todayOrders->where('order_type', 'dine_in')->count(),
                'total' => $todayOrders->where('order_type', 'dine_in')->sum('total'),
            ],
            'takeout' => [
                'count' => $todayOrders->where('order_type', 'takeout')->count(),
                'total' => $todayOrders->where('order_type', 'takeout')->sum('total'),
            ],
            'grab_delivery' => [
                'count' => $todayOrders->where('order_type', 'grab_delivery')->count(),
                'total' => $todayOrders->where('order_type', 'grab_delivery')->sum('total'),
            ],
        ];

        // Payment Method breakdown today (Cash vs Online)
        $cashTotal = 0.0;
        $onlineTotal = 0.0;
        foreach ($todayOrders as $ord) {
            $pm = $ord->payment?->method ?? 'cash';
            if ($pm === 'cash') {
                $cashTotal += (float) $ord->total;
            } else {
                $onlineTotal += (float) $ord->total;
            }
        }

        // Weekly revenue (last 7 days)
        $weeklyRevenue = (clone $baseOrdersQuery)->where('created_at', '>=', now()->subDays(7))->sum('total');

        // Low stock ingredients
        $lowStockIngredients = Ingredient::whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->take(10)
            ->get();

        // Shift Information
        $activeShiftQuery = Shift::where('status', 'open');
        if ($selectedBranchId) {
            $activeShiftQuery->where('branch_id', $selectedBranchId);
        }
        $activeShift = $activeShiftQuery->latest()->first();
        $activeShiftMetrics = $activeShift ? $activeShift->calculateMetrics() : null;
        $recentShifts = ($user->isManager() || in_array($user->role, ['manager', 'owner'])) 
            ? Shift::latest()->take(5)->get() 
            : collect();

        // Recent orders
        $recentOrdersQuery = Order::with('items', 'payment', 'branch')->latest();
        if ($selectedBranchId) {
            $recentOrdersQuery->where('branch_id', $selectedBranchId);
        }

        if (!$user->isAtLeast('manager')) {
            if ($activeShift) {
                $recentOrdersQuery->where('shift_id', $activeShift->id)
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhere('cashier_name', $user->name);
                    });
            } else {
                $recentOrdersQuery->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('cashier_name', $user->name);
                });
            }
        }

        $recentOrders = $recentOrdersQuery->take(15)->get();

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
        $bestSellers = OrderItem::whereHas('order', function ($q) use ($selectedBranchId) {
                $q->whereDate('created_at', today())->where('status', 'completed');
                if ($selectedBranchId) {
                    $q->where('branch_id', $selectedBranchId);
                }
            })
            ->selectRaw('product_name, SUM(quantity) as total_qty')
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'todayRevenue', 'todayOrderCount', 'todayItemsSold',
            'weeklyRevenue', 'lowStockIngredients', 'recentOrders',
            'notifications', 'bestSellers', 'activeShift', 'activeShiftMetrics', 'recentShifts',
            'branches', 'selectedBranchId', 'orderTypesBreakdown', 'cashTotal', 'onlineTotal'
        ));
    }
}
