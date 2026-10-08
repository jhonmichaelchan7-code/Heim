<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $period = $request->get('period', 'daily');
        $dateFrom = $request->get('date_from', today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', today()->format('Y-m-d'));
        $user = auth()->user();
        $branchId = $user?->branch_id ?: $request->get('branch_id');
        $orderType = $request->get('order_type');
        $branches = Branch::active()->get();

        switch ($period) {
            case 'weekly':
                $dateFrom = now()->startOfWeek()->format('Y-m-d');
                $dateTo = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $dateFrom = now()->startOfMonth()->format('Y-m-d');
                $dateTo = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'yearly':
                $dateFrom = now()->startOfYear()->format('Y-m-d');
                $dateTo = now()->endOfYear()->format('Y-m-d');
                break;
            case 'overall':
                $dateFrom = Order::min('created_at')?->format('Y-m-d') ?? today()->format('Y-m-d');
                $dateTo = today()->format('Y-m-d');
                break;
            case 'custom':
                // Use provided dates
                break;
        }

        $query = Order::whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
            ->completed()
            ->with('items', 'payment', 'payments', 'branch');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($orderType && $orderType !== 'all') {
            $query->where('order_type', $orderType);
        }

        $orders = $query->get();

        $totalSales = (float) $orders->sum('total');
        $totalOrders = $orders->count();
        $totalItems = $orders->flatMap->items->sum('quantity');
        $avgTicketSize = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0.00;

        // Payment breakdown (Cash vs Online digital breakdown)
        $paymentBreakdown = $orders->groupBy(function ($o) {
            $m = strtolower($o->payment?->method ?? 'cash');
            if (in_array($m, ['online', 'gcash', 'card'])) return 'online';
            return 'cash';
        })->map(fn ($group) => [
            'count' => $group->count(),
            'total' => (float) $group->sum('total'),
            'pct' => $totalSales > 0 ? round(($group->sum('total') / $totalSales) * 100, 1) : 0,
        ]);

        // Detailed Payment Methods (Cash, GCash, Online, Card, Split)
        $detailedPayments = $orders->groupBy(fn ($o) => $o->payment?->method ?? 'cash')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'total' => (float) $group->sum('total'),
            ]);

        // Order Type stats (Dine-In, Takeout, Grab Delivery)
        $orderTypesStats = [
            'dine_in' => [
                'label' => 'Dine-In',
                'icon' => '🍽️',
                'count' => $orders->where('order_type', 'dine_in')->count(),
                'total' => (float) $orders->where('order_type', 'dine_in')->sum('total'),
                'pct' => $totalSales > 0 ? round(($orders->where('order_type', 'dine_in')->sum('total') / $totalSales) * 100, 1) : 0,
            ],
            'takeout' => [
                'label' => 'Takeout',
                'icon' => '🛍️',
                'count' => $orders->where('order_type', 'takeout')->count(),
                'total' => (float) $orders->where('order_type', 'takeout')->sum('total'),
                'pct' => $totalSales > 0 ? round(($orders->where('order_type', 'takeout')->sum('total') / $totalSales) * 100, 1) : 0,
            ],
            'grab_delivery' => [
                'label' => 'Grab Delivery',
                'icon' => '🛵',
                'count' => $orders->where('order_type', 'grab_delivery')->count(),
                'total' => (float) $orders->where('order_type', 'grab_delivery')->sum('total'),
                'pct' => $totalSales > 0 ? round(($orders->where('order_type', 'grab_delivery')->sum('total') / $totalSales) * 100, 1) : 0,
            ],
        ];

        // Branch Performance Comparison
        $branchStats = Branch::active()->get()->map(function ($b) use ($dateFrom, $dateTo) {
            $bOrders = Order::where('branch_id', $b->id)
                ->whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
                ->completed()
                ->get();
            return [
                'id' => $b->id,
                'name' => $b->name,
                'code' => $b->code,
                'orders_count' => $bOrders->count(),
                'total_sales' => (float) $bOrders->sum('total'),
            ];
        });

        // Peak Ordering Periods (Rush hours 6 AM to 10 PM)
        $peakHours = [];
        for ($h = 6; $h <= 22; $h++) {
            $label = date('g A', mktime($h, 0, 0));
            $hOrders = $orders->filter(fn ($o) => (int) $o->created_at->format('G') === $h);
            $peakHours[] = [
                'hour' => $label,
                'count' => $hOrders->count(),
                'sales' => (float) $hOrders->sum('total'),
            ];
        }

        // Best sellers
        $bestSellers = OrderItem::whereHas('order', function ($q) use ($dateFrom, $dateTo, $branchId, $orderType) {
            $q->whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])->completed();
            if ($branchId) $q->where('branch_id', $branchId);
            if ($orderType && $orderType !== 'all') $q->where('order_type', $orderType);
        })
        ->selectRaw('product_name, size_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
        ->groupBy('product_name', 'size_name')
        ->orderByDesc('total_qty')
        ->take(10)
        ->get();

        // Sales by cashier
        $salesByCashier = $orders->groupBy('cashier_name')
            ->map(fn ($group) => [
                'orders' => $group->count(),
                'total' => (float) $group->sum('total'),
            ])->sortByDesc('total');

        // Refunds, Voids & Cancellations
        $refundedOrdersQuery = Order::whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
            ->whereIn('status', ['refunded', 'cancelled', 'voided']);
        if ($branchId) $refundedOrdersQuery->where('branch_id', $branchId);
        $refundedOrders = $refundedOrdersQuery->get();
        $totalRefunds = (float) $refundedOrders->sum('total');

        // Log report generation
        AuditLog::log('report_generated', 'reports', "Sales report generated: {$period} ({$dateFrom} to {$dateTo})");

        $googleSheetWebhookUrl = PosSetting::get('google_sheet_webhook_url', '');
        $googleSheetAutoSync = (bool) PosSetting::get('google_sheet_auto_sync', false);

        return view('reports.sales', compact(
            'period', 'dateFrom', 'dateTo', 'branchId', 'orderType', 'branches',
            'totalSales', 'totalOrders', 'totalItems', 'avgTicketSize',
            'paymentBreakdown', 'detailedPayments', 'orderTypesStats', 'branchStats', 'peakHours',
            'bestSellers', 'salesByCashier',
            'totalRefunds', 'refundedOrders',
            'googleSheetWebhookUrl', 'googleSheetAutoSync'
        ));
    }

    public function inventory(Request $request)
    {
        $ingredients = Ingredient::orderBy('name')->get();

        $dateFrom = $request->get('date_from', today()->format('Y-m-d'));
        $dateTo = $request->get('date_to', today()->format('Y-m-d'));

        $transactionSummary = InventoryTransaction::whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
            ->selectRaw('ingredient_id, type, SUM(quantity) as total_quantity')
            ->groupBy('ingredient_id', 'type')
            ->get()
            ->groupBy('ingredient_id');

        AuditLog::log('report_generated', 'reports', "Inventory report generated ({$dateFrom} to {$dateTo})");

        $googleSheetWebhookUrl = PosSetting::get('google_sheet_webhook_url', '');
        $googleSheetAutoSync = (bool) PosSetting::get('google_sheet_auto_sync', false);

        return view('reports.inventory', compact(
            'ingredients', 'transactionSummary', 'dateFrom', 'dateTo',
            'googleSheetWebhookUrl', 'googleSheetAutoSync'
        ));
    }

    public function getGoogleSheetsSettings()
    {
        return response()->json([
            'webhook_url' => PosSetting::get('google_sheet_webhook_url', ''),
            'auto_sync' => (bool) PosSetting::get('google_sheet_auto_sync', false),
        ]);
    }

    public function saveGoogleSheetsSettings(Request $request)
    {
        $validated = $request->validate([
            'webhook_url' => 'nullable|url',
            'auto_sync' => 'nullable|boolean',
        ]);

        PosSetting::set('google_sheet_webhook_url', $validated['webhook_url'] ?? '');
        PosSetting::set('google_sheet_auto_sync', $request->boolean('auto_sync') ? '1' : '0');

        AuditLog::log('settings_updated', 'reports', 'Updated Google Sheets integration settings');

        return response()->json([
            'success' => true,
            'message' => 'Google Sheets settings saved successfully!',
            'webhook_url' => PosSetting::get('google_sheet_webhook_url', ''),
            'auto_sync' => (bool) PosSetting::get('google_sheet_auto_sync', false),
        ]);
    }

    public function syncGoogleSheet(Request $request)
    {
        $webhookUrl = PosSetting::get('google_sheet_webhook_url', '');
        if (empty($webhookUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'Google Sheet Webhook URL is not configured. Please paste your Apps Script URL in settings.'
            ], 422);
        }

        $payload = [
            'action' => $request->input('action', 'sync_sales'),
            'report_title' => $request->input('report_title', 'Heim POS Report'),
            'period' => $request->input('period', today()->format('Y-m-d')),
            'generated_at' => now()->toDateTimeString(),
            'data' => $request->input('data', [])
        ];

        try {
            $response = Http::timeout(15)->post($webhookUrl, $payload);

            if ($response->successful()) {
                AuditLog::log('google_sheet_synced', 'reports', "Synced {$payload['action']} to Google Sheet");
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully synced data to Google Sheet!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Google Sheet webhook responded with code: ' . $response->status()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
