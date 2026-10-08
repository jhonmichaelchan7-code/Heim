<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Order::with('items', 'payment', 'branch')->latest();

        // Cashiers only view their own orders; Supervisors, Managers, and Owners view all orders
        if (!$user->isAtLeast('supervisor')) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('cashier_name', $user->name);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('order_type')) {
            $query->where('order_type', $request->order_type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                  ->orWhere('cashier_name', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate(20);
        $branches = Branch::active()->get();

        return view('orders.index', compact('orders', 'branches'));
    }

    public function show(Order $order)
    {
        $user = auth()->user();

        // Cashiers can only view their own order details
        if (!$user->isAtLeast('supervisor') && $order->user_id !== $user->id && $order->cashier_name !== $user->name) {
            abort(403, 'Unauthorized access to order details.');
        }

        $order->load('items.addOns', 'payment', 'payments', 'branch', 'refunds.authorizer');
        return view('orders.show', compact('order'));
    }

    public function refund(Request $request, Order $order, InventoryService $inventoryService)
    {
        $request->validate([
            'action_type' => 'required|string|in:refund,void,cancellation',
            'reason' => 'required|string|in:Wrong item or recipe,Customer changed their mind,Duplicate entry,Payment problem,Item unavailable,Other',
            'note' => 'required_if:reason,Other|nullable|string|max:1000',
            'restore_inventory' => 'nullable|boolean',
        ], [
            'reason.required' => 'Please select a reason for this action.',
            'note.required_if' => 'A note is required when "Other" is selected.',
        ]);

        $actionType = $request->input('action_type', 'refund');
        $restoreInventory = $request->boolean('restore_inventory');
        $authorizer = auth()->user();

        // Refund Policy Enforcement:
        // Refunds strictly restricted to cash transactions to maintain external payment gateway integrity.
        $hasOnlinePayment = $order->payments()->whereIn('method', ['online', 'gcash', 'card'])->exists() 
            || in_array($order->payment?->method, ['online', 'gcash', 'card']);

        if ($actionType === 'refund' && $hasOnlinePayment) {
            return back()->with('error', 'Refund Policy: Digital/online payments cannot be refunded in cash. Please choose Void instead.');
        }

        if (in_array($order->status, ['refunded', 'cancelled', 'voided'])) {
            return back()->with('error', "Order is already marked as {$order->status}.");
        }

        $newStatus = match ($actionType) {
            'cancellation' => 'cancelled',
            'void' => 'voided',
            default => 'refunded',
        };

        $reasonText = $request->reason === 'Other' && $request->filled('note')
            ? "Other: {$request->note}"
            : ($request->filled('note') ? "{$request->reason} ({$request->note})" : $request->reason);

        // Create refund / void record
        Refund::create([
            'order_id' => $order->id,
            'action_type' => $actionType,
            'refund_amount' => $order->total,
            'reason' => $reasonText,
            'restored_inventory' => $restoreInventory,
            'cashier_name' => $order->cashier_name,
            'authorized_by' => $authorizer->id,
        ]);

        $order->update(['status' => $newStatus]);

        // Optional inventory restoration
        if ($restoreInventory) {
            $inventoryService->restoreForOrder($order, "Restored during {$actionType}");
        }

        AuditLog::log(
            'order_' . $newStatus,
            'orders',
            "Order {$order->order_number} ({$order->order_type_label}) marked as {$newStatus}. Amount: ₱" . number_format($order->total, 2) . ". Reason: {$reasonText}. Restored to stock: " . ($restoreInventory ? 'Yes' : 'No') . ". Authorized by: {$authorizer->name} ({$authorizer->role})",
            $authorizer,
            'order',
            $order->id,
            [
                'action_type' => $actionType,
                'refund_amount' => $order->total,
                'reason' => $reasonText,
                'restored_inventory' => $restoreInventory,
                'authorized_by' => $authorizer->name,
                'authorized_by_role' => $authorizer->role,
                'ip_address' => $request->ip(),
            ]
        );

        $actionWord = $actionType === 'void' ? 'voided' : 'refunded';
        return redirect()->route('orders.show', $order)->with('success', "Order #{$order->order_number} successfully {$actionWord}.");
    }

    public function recordPartialPayment(Request $request, Order $order)
    {
        $user = auth()->user();

        // Cashiers may record split or partial payments only for orders assigned to them
        if (!$user->isAtLeast('supervisor') && $order->user_id !== $user->id && $order->cashier_name !== $user->name) {
            abort(403, 'You may only record partial or split payments for orders assigned to you.');
        }

        $remaining = $order->remaining_balance;
        if ($remaining <= 0) {
            return back()->with('error', 'This order is already fully paid.');
        }

        $request->validate([
            'payment_method' => 'required|in:cash,online,gcash,card',
            'amount_tendered' => 'required|numeric|min:0.01|max:' . ($remaining + 10000),
            'reference_number' => 'nullable|string|max:255',
        ]);

        if (in_array($request->payment_method, ['online', 'gcash', 'card']) && empty(trim($request->reference_number ?? ''))) {
            return back()->with('error', 'Online payments strictly require a valid transaction reference number.');
        }

        $tendered = (float) $request->amount_tendered;
        $change = max(0, $tendered - $remaining);

        Payment::create([
            'order_id' => $order->id,
            'method' => $request->payment_method,
            'amount_tendered' => $tendered,
            'change' => $change,
            'reference_number' => $request->reference_number,
        ]);

        // If remaining balance is settled, complete the order
        if ($order->fresh()->remaining_balance <= 0) {
            $order->update(['status' => 'completed']);
        }

        AuditLog::log(
            'partial_payment_recorded',
            'orders',
            "Recorded partial payment of ₱" . number_format($tendered, 2) . " ({$request->payment_method}) for Order {$order->order_number} by {$user->name}.",
            $user,
            'order',
            $order->id,
            ['amount' => $tendered, 'method' => $request->payment_method, 'reference_number' => $request->reference_number]
        );

        return redirect()->route('orders.show', $order)->with('success', 'Partial payment recorded successfully.');
    }
}
