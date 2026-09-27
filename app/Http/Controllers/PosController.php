<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddOn;
use App\Models\Payment;
use App\Models\ProductSize;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use App\Models\PosSetting;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::active()->ordered()->with(['activeProducts.sizes'])->get();
        $addOns = AddOn::active()->orderBy('name')->get();
        $activeShift = Shift::where('status', 'open')->latest()->first();
        $requireShift = (bool) PosSetting::get('require_user_shift', true);

        return view('pos.index', compact('categories', 'addOns', 'activeShift', 'requireShift'));
    }

    public function store(Request $request, InventoryService $inventoryService)
    {
        $requireShift = (bool) PosSetting::get('require_user_shift', true);
        $activeShift = Shift::where('status', 'open')->latest()->first();

        if ($requireShift && !$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'A shift must be started before processing orders. Please click Start Shift.',
            ], 422);
        }

        $request->validate([
            'cashier_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.add_ons' => 'nullable|array',
            'items.*.add_ons.*' => 'exists:add_ons,id',
            'payment_method' => 'required|in:cash,online,gcash,card',
            'amount_tendered' => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
        ]);

        try {
            $order = DB::transaction(function () use ($request, $inventoryService, $activeShift) {
                // Create order
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'cashier_name' => $request->cashier_name,
                    'user_id' => auth()->id(),
                    'shift_id' => $activeShift?->id,
                    'status' => 'completed',
                ]);

                $subtotal = 0;

                foreach ($request->items as $itemData) {
                    $productSize = ProductSize::where('product_id', $itemData['product_id'])
                        ->where('size_id', $itemData['size_id'])
                        ->firstOrFail();

                    $product = $productSize->product;
                    $size = $productSize->size;
                    $itemSubtotal = $productSize->price * $itemData['quantity'];

                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'size_id' => $size->id,
                        'product_name' => $product->name,
                        'size_name' => $size->name,
                        'unit_price' => $productSize->price,
                        'quantity' => $itemData['quantity'],
                        'subtotal' => $itemSubtotal,
                    ]);

                    // Add-ons
                    if (!empty($itemData['add_ons'])) {
                        foreach ($itemData['add_ons'] as $addOnId) {
                            $addOn = AddOn::findOrFail($addOnId);
                            $addOnTotal = $addOn->price * $itemData['quantity'];

                            OrderItemAddOn::create([
                                'order_item_id' => $orderItem->id,
                                'add_on_id' => $addOn->id,
                                'add_on_name' => $addOn->name,
                                'add_on_price' => $addOn->price,
                            ]);

                            $itemSubtotal += $addOnTotal;
                        }
                    }

                    $subtotal += $itemSubtotal;
                }

                // Update order totals
                $order->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                ]);

                // Create payment
                $change = max(0, $request->amount_tendered - $subtotal);
                Payment::create([
                    'order_id' => $order->id,
                    'method' => $request->payment_method,
                    'amount_tendered' => $request->amount_tendered,
                    'change' => $change,
                    'reference_number' => $request->reference_number,
                ]);

                // Deduct inventory
                $inventoryService->deductForOrder($order->load('items'));

                // Audit log
                AuditLog::log(
                    'order_completed',
                    'orders',
                    "Order {$order->order_number} completed by {$order->cashier_name}. Total: ₱" . number_format($order->total, 2),
                    null,
                    'order',
                    $order->id,
                    ['total' => $order->total, 'payment_method' => $request->payment_method]
                );

                // Update active shift metrics
                if ($activeShift) {
                    $metrics = $activeShift->calculateMetrics();
                    $activeShift->update([
                        'cash_in' => $metrics['cash_in'],
                        'cash_out' => $metrics['cash_out'],
                        'expected_cash' => $metrics['expected_cash'],
                    ]);
                }

                return $order;
            });

            return response()->json([
                'success' => true,
                'order' => $order->load('items.addOns', 'payment'),
                'message' => 'Order completed successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
