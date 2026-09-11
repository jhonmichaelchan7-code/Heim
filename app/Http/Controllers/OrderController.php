<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\AuditLog;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items', 'payment')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.addOns', 'payment', 'refunds.authorizer');
        return view('orders.show', compact('order'));
    }

    public function refund(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'auth_email' => 'required|email',
            'auth_password' => 'required',
        ]);

        // Verify authorizer credentials
        $authorizer = User::where('email', $request->auth_email)->first();

        if (!$authorizer || !Hash::check($request->auth_password, $authorizer->password)) {
            return back()->with('error', 'Invalid authorization credentials.');
        }

        if (!$authorizer->isAtLeast('supervisor')) {
            return back()->with('error', 'Authorizer must be at least a Supervisor.');
        }

        if ($order->status !== 'completed') {
            return back()->with('error', 'Only completed orders can be refunded.');
        }

        Refund::create([
            'order_id' => $order->id,
            'refund_amount' => $order->total,
            'reason' => $request->reason,
            'cashier_name' => $order->cashier_name,
            'authorized_by' => $authorizer->id,
        ]);

        $order->update(['status' => 'refunded']);

        AuditLog::log(
            'order_refunded',
            'orders',
            "Order {$order->order_number} refunded. Amount: ₱" . number_format($order->total, 2) . ". Reason: {$request->reason}. Authorized by: {$authorizer->name} ({$authorizer->role})",
            $authorizer,
            'order',
            $order->id,
            ['refund_amount' => $order->total, 'reason' => $request->reason, 'authorized_by' => $authorizer->name]
        );

        return redirect()->route('orders.show', $order)->with('success', 'Order refunded successfully.');
    }
}
