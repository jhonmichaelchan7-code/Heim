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
use App\Models\Recipe;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use App\Models\PosSetting;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::active()->ordered()->with(['activeProducts.sizes'])->get();
        $addOns = AddOn::active()->orderBy('name')->get();
        $activeShift = Shift::where('status', 'open')->latest()->first();
        $requireShift = (bool) PosSetting::get('require_user_shift', true);
        $branches = \App\Models\Branch::active()->get();

        // Calculate Best Sellers / Popular Products (Phase 3)
        $popularProductIds = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(8)
            ->pluck('product_id')
            ->toArray();

        // Fallback: If fresh database with few orders, take first active products
        if (count($popularProductIds) < 3) {
            $fallbackIds = \App\Models\Product::where('is_active', true)->limit(6)->pluck('id')->toArray();
            $popularProductIds = array_values(array_unique(array_merge($popularProductIds, $fallbackIds)));
        }

        return view('pos.index', compact('categories', 'addOns', 'activeShift', 'requireShift', 'branches', 'popularProductIds'));
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
            'order_type' => 'nullable|string|in:dine_in,takeout,grab_delivery',
            'branch_id' => 'nullable|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id' => 'required|exists:sizes,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.add_ons' => 'nullable|array',
            'items.*.add_ons.*' => 'exists:add_ons,id',
            'items.*.discount_type' => 'nullable|string|in:none,pwd_senior,employee,staff,custom_pct,custom_percentage,custom_fixed',
            'items.*.discount_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.id_number' => 'nullable|string|max:100',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,online,gcash,card,split',
            'amount_tendered' => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'split_cash_amount' => 'nullable|numeric|min:0',
            'split_online_amount' => 'nullable|numeric|min:0',
            'split_reference_number' => 'nullable|string|max:255',
            'grab_order_code' => 'nullable|string|max:50',
            'rider_code' => 'nullable|string|max:50',
        ]);

        // Enforce GrabFood order details
        if ($request->order_type === 'grab_delivery') {
            if (empty(trim($request->grab_order_code ?? ''))) {
                return response()->json([
                    'success' => false,
                    'message' => 'GrabFood orders strictly require a Grab Order Code.',
                    'errors' => ['grab_order_code' => ['Grab Order Code is required.']],
                ], 422);
            }
            if (empty(trim($request->rider_code ?? ''))) {
                return response()->json([
                    'success' => false,
                    'message' => 'GrabFood orders strictly require a Rider Code.',
                    'errors' => ['rider_code' => ['Rider Code is required.']],
                ], 422);
            }
        }

        // Enforce digital payment verification per System Analysis Paper requirement
        if (in_array($request->payment_method, ['online', 'gcash', 'card']) && empty(trim($request->reference_number ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Online payments strictly require a valid external transaction reference number / confirmation ID.',
                'errors' => ['reference_number' => ['Reference number is required for online digital payments.']],
            ], 422);
        }

        if ($request->payment_method === 'split' && (float)($request->split_online_amount ?? 0) > 0 && empty(trim($request->split_reference_number ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'The digital/online portion of split payment requires an external transaction reference number.',
                'errors' => ['split_reference_number' => ['Reference number required for online split portion.']],
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $inventoryService, $activeShift) {
                $lineTotals = [];

                foreach ($request->items as $index => $itemData) {
                    $productSize = ProductSize::where('product_id', $itemData['product_id'])
                        ->where('size_id', $itemData['size_id'])
                        ->firstOrFail();

                    $product = $productSize->product;
                    $size = $productSize->size;
                    $quantity = (int) $itemData['quantity'];

                    $recipe = Recipe::where('product_id', $product->id)
                        ->where('size_id', $size->id)
                        ->with('recipeIngredients.ingredient')
                        ->first();

                    if ($recipe) {
                        foreach ($recipe->recipeIngredients as $recipeIngredient) {
                            $ingredient = $recipeIngredient->ingredient;
                            $needed = $recipeIngredient->quantity * $quantity;

                            if (!$ingredient || $ingredient->current_stock < $needed) {
                                throw ValidationException::withMessages([
                                    'items.' . $index . '.product_id' => 'Stock out: ' . $product->name . ' cannot be sold because ' . ($ingredient?->name ?? 'an ingredient') . ' is below the required quantity.',
                                ]);
                            }
                        }
                    }

                    $lineSubtotal = $productSize->price * $quantity;

                    if (!empty($itemData['add_ons'])) {
                        foreach ($itemData['add_ons'] as $addOnId) {
                            $addOn = AddOn::findOrFail($addOnId);
                            $lineSubtotal += $addOn->price * $quantity;
                        }
                    }

                    $lineTotals[] = [
                        'itemData' => $itemData,
                        'lineSubtotal' => $lineSubtotal,
                        'productSize' => $productSize,
                        'product' => $product,
                        'size' => $size,
                    ];
                }

                $subtotal = collect($lineTotals)->sum('lineSubtotal');

                $branchId = $request->branch_id ?? $activeShift?->branch_id ?? \App\Models\Branch::where('is_active', true)->first()?->id;
                $orderType = $request->order_type ?: 'dine_in';

                // Create order
                $order = Order::create([
                    'order_number' => Order::generateOrderNumber(),
                    'order_type' => $orderType,
                    'grab_order_code' => $orderType === 'grab_delivery' ? trim($request->grab_order_code) : null,
                    'rider_code' => $orderType === 'grab_delivery' ? trim($request->rider_code) : null,
                    'branch_id' => $branchId,
                    'cashier_name' => $request->cashier_name,
                    'user_id' => auth()->id(),
                    'shift_id' => $activeShift?->id,
                    'status' => 'completed',
                ]);

                $taxRate = (float) PosSetting::get('tax_rate', 12.00);
                $hasExplicitLineDiscounts = collect($request->items)->contains(function ($it) {
                    return !empty($it['discount_type']) && $it['discount_type'] !== 'none';
                });

                $legacyOrderDiscount = round((float) $request->input('discount', 0), 2);
                $legacyDiscountRemaining = (!$hasExplicitLineDiscounts && $legacyOrderDiscount > 0) ? $legacyOrderDiscount : 0;

                foreach ($lineTotals as $index => $line) {
                    $itemData = $line['itemData'];
                    $productSize = $line['productSize'];
                    $product = $line['product'];
                    $size = $line['size'];
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

                    // Calculate Per-Line Discount & Tax (VAT-Inclusive Philippine Retail Standard)
                    $discType = $itemData['discount_type'] ?? 'none';
                    $discRate = (float)($itemData['discount_rate'] ?? 0);
                    $lineDiscount = 0.00;
                    $isVatExempt = false;
                    $idNumber = !empty($itemData['id_number']) ? trim($itemData['id_number']) : null;

                    // Menu prices in Heim are VAT-inclusive
                    $taxMultiplier = 1 + ($taxRate / 100);

                    if ($discType === 'pwd_senior') {
                        // RA 9994 / RA 10754: 20% discount on Net of VAT price + VAT Exemption
                        $discRate = 20.00;
                        $netOfVat = $itemSubtotal / $taxMultiplier;
                        $lineDiscount = round($netOfVat * 0.20, 2);
                        $isVatExempt = true;
                        $lineTax = 0.00;
                        $lineTotal = round($netOfVat - $lineDiscount, 2);
                    } elseif ($discType === 'employee' || $discType === 'staff') {
                        $discRate = 10.00;
                        $lineDiscount = round($itemSubtotal * 0.10, 2);
                        $isVatExempt = false;
                        $lineTotal = round(max(0, $itemSubtotal - $lineDiscount), 2);
                        $vatablePortion = round($lineTotal / $taxMultiplier, 2);
                        $lineTax = round($lineTotal - $vatablePortion, 2);
                    } elseif ($discType === 'custom_pct' || $discType === 'custom_percentage') {
                        $pct = min(100, max(0, $discRate > 0 ? $discRate : (float)($itemData['discount'] ?? 0)));
                        $discRate = $pct;
                        $lineDiscount = round($itemSubtotal * ($pct / 100), 2);
                        $isVatExempt = false;
                        $lineTotal = round(max(0, $itemSubtotal - $lineDiscount), 2);
                        $vatablePortion = round($lineTotal / $taxMultiplier, 2);
                        $lineTax = round($lineTotal - $vatablePortion, 2);
                    } elseif ($discType === 'custom_fixed') {
                        $fixed = min($itemSubtotal, max(0, (float)($itemData['discount'] ?? 0)));
                        $lineDiscount = round($fixed, 2);
                        $discRate = $itemSubtotal > 0 ? round(($lineDiscount / $itemSubtotal) * 100, 2) : 0;
                        $isVatExempt = false;
                        $lineTotal = round(max(0, $itemSubtotal - $lineDiscount), 2);
                        $vatablePortion = round($lineTotal / $taxMultiplier, 2);
                        $lineTax = round($lineTotal - $vatablePortion, 2);
                    } elseif ($legacyDiscountRemaining > 0) {
                        // Distribute legacy flat discount to line items
                        $alloc = min($itemSubtotal, $legacyDiscountRemaining);
                        $lineDiscount = round($alloc, 2);
                        $legacyDiscountRemaining -= $alloc;
                        $discType = 'custom_fixed';
                        $discRate = $itemSubtotal > 0 ? round(($lineDiscount / $itemSubtotal) * 100, 2) : 0;
                        $isVatExempt = false;
                        $lineTotal = round(max(0, $itemSubtotal - $lineDiscount), 2);
                        $vatablePortion = round($lineTotal / $taxMultiplier, 2);
                        $lineTax = round($lineTotal - $vatablePortion, 2);
                    } else {
                        // Standard line item with no discount: Fixed price is VAT-INCLUSIVE
                        $discType = 'none';
                        $discRate = 0.00;
                        $lineDiscount = 0.00;
                        $isVatExempt = false;
                        $lineTotal = round($itemSubtotal, 2);
                        $vatablePortion = round($lineTotal / $taxMultiplier, 2);
                        $lineTax = round($lineTotal - $vatablePortion, 2);
                    }

                    $orderItem->update([
                        'subtotal' => $itemSubtotal,
                        'discount_type' => $discType,
                        'discount_rate' => $discRate,
                        'discount' => $lineDiscount,
                        'is_vat_exempt' => $isVatExempt,
                        'tax' => $lineTax,
                        'total' => $lineTotal,
                        'id_number' => $idNumber,
                    ]);
                }

                // Compute Order-Level Aggregates from Line Items
                $order->refresh();
                $totalDiscount = (float) $order->items->sum('discount');
                $vatableSales = (float) $order->items->where('is_vat_exempt', false)->sum(function ($it) use ($taxMultiplier) {
                    return round((float)$it->total / $taxMultiplier, 2);
                });
                $vatExemptSales = (float) $order->items->where('is_vat_exempt', true)->sum('total');
                $totalTax = (float) $order->items->where('is_vat_exempt', false)->sum('tax');
                $totalDue = (float) $order->items->sum('total');

                if ($request->payment_method === 'split') {
                    $splitCash = (float) ($request->split_cash_amount ?? 0);
                    $splitOnline = (float) ($request->split_online_amount ?? 0);
                    $totalTendered = $splitCash + $splitOnline;

                    if ($totalTendered < $totalDue) {
                        throw ValidationException::withMessages([
                            'amount_tendered' => 'Total split tendered (₱' . number_format($totalTendered, 2) . ') is less than the total due (₱' . number_format($totalDue, 2) . ').',
                        ]);
                    }

                    $change = max(0, $totalTendered - $totalDue);

                    if ($splitCash > 0) {
                        Payment::create([
                            'order_id' => $order->id,
                            'method' => 'cash',
                            'amount_tendered' => $splitCash,
                            'change' => $change,
                            'reference_number' => null,
                        ]);
                    }
                    if ($splitOnline > 0) {
                        Payment::create([
                            'order_id' => $order->id,
                            'method' => 'online',
                            'amount_tendered' => $splitOnline,
                            'change' => 0,
                            'reference_number' => $request->split_reference_number,
                        ]);
                    }
                } else {
                    $amountTendered = $request->payment_method === 'cash'
                        ? (float) $request->amount_tendered
                        : $totalDue;

                    if ($request->payment_method === 'cash' && $amountTendered < $totalDue) {
                        throw ValidationException::withMessages([
                            'amount_tendered' => 'Amount tendered is less than the total due (₱' . number_format($totalDue, 2) . ').',
                        ]);
                    }

                    $change = max(0, $amountTendered - $totalDue);
                    Payment::create([
                        'order_id' => $order->id,
                        'method' => $request->payment_method,
                        'amount_tendered' => $amountTendered,
                        'change' => $change,
                        'reference_number' => $request->reference_number,
                    ]);
                }

                // Update order totals
                $order->update([
                    'subtotal' => $subtotal,
                    'discount' => $totalDiscount,
                    'tax_rate' => $taxRate,
                    'tax' => $totalTax,
                    'vatable_sales' => $vatableSales,
                    'vat_exempt_sales' => $vatExemptSales,
                    'total' => $totalDue,
                ]);

                // Deduct inventory
                $inventoryService->deductForOrder($order->load('items.addOns'));

                // Audit log
                AuditLog::log(
                    'order_completed',
                    'orders',
                    "Order {$order->order_number} ({$order->order_type_label}) completed by {$order->cashier_name}. Total: ₱" . number_format($order->total, 2),
                    null,
                    'order',
                    $order->id,
                    ['total' => $order->total, 'payment_method' => $request->payment_method, 'order_type' => $order->order_type, 'branch_id' => $order->branch_id]
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
                'order' => $order->load('items.addOns', 'payments', 'payment', 'branch'),
                'message' => 'Order completed successfully!',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
