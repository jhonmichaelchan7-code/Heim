<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

        $orders = Order::whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
            ->completed()
            ->with('items', 'payment')
            ->get();

        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();
        $totalItems = $orders->flatMap->items->sum('quantity');

        // Payment breakdown
        $paymentBreakdown = $orders->groupBy(fn ($o) => $o->payment?->method ?? 'unknown')
            ->map(fn ($group) => [
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ]);

        // Best sellers
        $bestSellers = OrderItem::whereHas('order', fn ($q) =>
                $q->whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])->completed()
            )
            ->selectRaw('product_name, size_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_name', 'size_name')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        // Sales by cashier
        $salesByCashier = $orders->groupBy('cashier_name')
            ->map(fn ($group) => [
                'orders' => $group->count(),
                'total' => $group->sum('total'),
            ])->sortByDesc('total');

        // Refunds
        $refundedOrders = Order::whereBetween('created_at', ["{$dateFrom} 00:00:00", "{$dateTo} 23:59:59"])
            ->where('status', 'refunded')
            ->get();
        $totalRefunds = $refundedOrders->sum('total');

        // Log report generation
        AuditLog::log('report_generated', 'reports', "Sales report generated: {$period} ({$dateFrom} to {$dateTo})");

        $googleSheetWebhookUrl = PosSetting::get('google_sheet_webhook_url', '');
        $googleSheetAutoSync = (bool) PosSetting::get('google_sheet_auto_sync', false);

        return view('reports.sales', compact(
            'period', 'dateFrom', 'dateTo',
            'totalSales', 'totalOrders', 'totalItems',
            'paymentBreakdown', 'bestSellers', 'salesByCashier',
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
