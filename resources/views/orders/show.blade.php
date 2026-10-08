<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center h-10 w-10 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-700 transition active:scale-[0.98]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                            Order {{ $order->order_number }}
                        </h2>
                        <!-- Status Badge -->
                        <span class="px-2.5 py-0.5 text-xs rounded-full font-bold uppercase
                            {{ $order->status === 'completed' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                            {{ $order->status === 'refunded' ? 'bg-rose-100 text-rose-800' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-amber-100 text-amber-800' : '' }}
                            {{ $order->status === 'voided' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $order->status === 'pending' ? 'bg-blue-100 text-blue-800' : '' }}
                        ">
                            {{ ucfirst($order->status) }}
                        </span>
                        <!-- Branch Badge -->
                        @if($order->branch)
                            <span class="px-2.5 py-0.5 text-xs rounded-full font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                📍 {{ $order->branch->name }}
                            </span>
                        @endif
                        <!-- Order Type Badge -->
                        <span class="px-2.5 py-0.5 text-xs rounded-full font-bold
                            {{ in_array($order->order_type, ['grab', 'grab_delivery']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-300' : '' }}
                            {{ $order->order_type === 'takeout' ? 'bg-amber-50 text-amber-700 border border-amber-300' : '' }}
                            {{ (!in_array($order->order_type, ['grab', 'grab_delivery', 'takeout'])) ? 'bg-blue-50 text-blue-700 border border-blue-300' : '' }}
                        ">
                            @if(in_array($order->order_type, ['grab', 'grab_delivery']))
                                🛵 Grab Delivery
                            @elseif($order->order_type === 'takeout')
                                🛍️ Takeout
                            @else
                                🍽️ Dine-In
                            @endif
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Placed on {{ $order->created_at->format('F d, Y \a\t h:i:s A') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Print Slip (Secondary Outline Style) -->
                <button onclick="printThermalReceipt()" class="inline-flex items-center justify-center h-10 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl transition gap-2 shadow-2xs active:scale-[0.98]">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print slip</span>
                </button>

                @if($order->remaining_balance > 0)
                    <button onclick="openPartialPaymentModal()" class="inline-flex items-center justify-center h-10 px-4 py-2 bg-[#155d49] hover:bg-[#124e3d] text-white font-semibold text-xs sm:text-sm rounded-xl transition gap-2 shadow-xs active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Record Payment (₱{{ number_format($order->remaining_balance, 2) }} due)
                    </button>
                @endif

                <!-- Void / Refund (Shown Only for Completed Orders) -->
                @if($order->status === 'completed')
                    <button type="button" onclick="openRefundModal()" class="inline-flex items-center justify-center h-10 px-4 py-2 bg-white hover:bg-rose-50/50 border border-rose-300 text-rose-600 font-semibold text-xs sm:text-sm rounded-xl transition gap-2 shadow-2xs active:scale-[0.98]">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                        <span>Void / refund</span>
                    </button>
                @elseif(in_array($order->status, ['voided', 'refunded', 'cancelled']))
                    <!-- Gray Status Badge for Already Voided / Refunded Orders -->
                    <span class="inline-flex items-center h-10 px-3.5 bg-gray-100 border border-gray-200 text-gray-600 font-semibold text-xs rounded-xl">
                        <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                        {{ ucfirst($order->status) }}
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Success Toast Notification (Requirement 7) -->
            @if(session('success'))
                <div id="order-toast-success" class="fixed top-5 right-5 z-50 flex items-center gap-3 px-4 py-3 bg-[#155d49] text-white rounded-2xl shadow-xl transition-opacity duration-300">
                    <svg class="w-5 h-5 text-emerald-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                    <button type="button" onclick="document.getElementById('order-toast-success')?.remove()" class="ml-2 text-white/70 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <!-- Banner for Refunded / Cancelled / Voided -->
            @if(in_array($order->status, ['refunded', 'cancelled', 'voided']) && $order->refunds->count() > 0)
                @php $refund = $order->refunds->first(); @endphp
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-sm text-rose-900 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-base text-rose-800">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Order Action: {{ strtoupper($refund->action_type ?? $order->status) }}
                        </div>
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $refund->restored_inventory ? 'bg-emerald-100 text-[#155d49]' : 'bg-gray-100 text-gray-700' }}">
                            {{ $refund->restored_inventory ? '✓ Ingredients Restored to Stock' : '✗ Stock Not Restored' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2 text-xs border-t border-rose-200/60">
                        <div>
                            <span class="text-rose-500 font-semibold block">Total Value:</span>
                            <span class="font-bold text-sm text-rose-900">₱{{ number_format($refund->refund_amount, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-rose-500 font-semibold block">Authorized By (Manager/Owner):</span>
                            <span class="font-bold text-gray-800">{{ $refund->authorizer?->name ?? 'Authorized' }} ({{ ucfirst($refund->authorizer?->role ?? 'Manager') }})</span>
                        </div>
                        <div>
                            <span class="text-rose-500 font-semibold block">Action Date:</span>
                            <span class="font-bold text-gray-800">{{ $refund->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div>
                            <span class="text-rose-500 font-semibold block">Branch:</span>
                            <span class="font-bold text-gray-800">{{ $order->branch?->name ?? 'Main Branch' }}</span>
                        </div>
                    </div>
                    <div class="pt-2 text-xs">
                        <span class="text-rose-500 font-semibold">Reason:</span>
                        <p class="font-medium text-gray-800 bg-white p-3 rounded-xl border border-rose-200 mt-1">{{ $refund->reason }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left 2 Cols: Order Items Table -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-900 text-base">Purchased Items & Modifiers</h3>
                            <span class="text-xs font-semibold text-gray-500">{{ $order->items->count() }} item line(s)</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                    <tr>
                                        <th class="py-3 px-4">Item & Customizations</th>
                                        <th class="py-3 px-4 text-center">Size</th>
                                        <th class="py-3 px-4 text-center">Unit Price</th>
                                        <th class="py-3 px-4 text-center">Qty</th>
                                        <th class="py-3 px-4 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td class="py-4 px-4">
                                                <div class="font-bold text-gray-900">{{ $item->product_name }}</div>
                                                @if($item->addOns->count() > 0)
                                                    <div class="mt-1 space-y-0.5">
                                                        @foreach($item->addOns as $addon)
                                                            <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-[#f0f8f5] text-[#155d49] border border-emerald-100 mr-1">
                                                                + {{ $addon->add_on_name }} (₱{{ number_format($addon->add_on_price, 2) }})
                                                                @if($addon->ingredient)
                                                                    <span class="text-[10px] text-gray-500 font-normal">({{ $addon->quantity ?? 1 }} {{ $addon->ingredient->unit }})</span>
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if($item->discount > 0 || ($item->discount_type && $item->discount_type !== 'none'))
                                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                        @if($item->discount_type === 'pwd_senior')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                                                Senior/PWD (20% Off & VAT Exempt)
                                                            </span>
                                                            @if($item->id_number)
                                                                <span class="text-[11px] font-mono font-semibold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded">
                                                                    ID: {{ $item->id_number }}
                                                                </span>
                                                            @endif
                                                        @elseif($item->discount_type === 'staff')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-900 border border-blue-200">
                                                                Staff (10% Off)
                                                            </span>
                                                        @elseif($item->discount_type === 'custom_percentage')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                                                Custom ({{ $item->discount_rate }}% Off)
                                                            </span>
                                                        @elseif($item->discount_type === 'custom_fixed')
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                                                Fixed (-₱{{ number_format($item->discount, 2) }})
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                                                Discount
                                                            </span>
                                                        @endif
                                                        <span class="text-xs text-rose-600 font-bold">-₱{{ number_format($item->discount, 2) }}</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700 uppercase">
                                                    {{ $item->size_name }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-center text-gray-600 font-medium">
                                                ₱{{ number_format($item->unit_price, 2) }}
                                            </td>
                                            <td class="py-4 px-4 text-center font-bold text-gray-900">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                @if($item->discount > 0)
                                                    <span class="line-through text-xs text-gray-400 block font-normal">₱{{ number_format($item->subtotal, 2) }}</span>
                                                    <span class="font-black text-gray-900">₱{{ number_format($item->total ?? ($item->subtotal - $item->discount), 2) }}</span>
                                                @else
                                                    <span class="font-black text-gray-900">₱{{ number_format($item->subtotal, 2) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Summary Breakdown -->
                        <div class="p-5 bg-[#f0f8f5]/50 border-t border-gray-100 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal (Gross)</span>
                                <span class="font-semibold text-gray-800">₱{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            @if($order->discount > 0)
                            <div class="flex justify-between text-sm text-rose-600">
                                <span>Total Discounts Applied</span>
                                <span class="font-semibold">-₱{{ number_format($order->discount, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Vatable Sales</span>
                                <span class="font-semibold text-gray-800">₱{{ number_format($order->vatable_sales ?? round(($order->total - ($order->vat_exempt_sales ?? 0)) / 1.12, 2), 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>12% VAT (Value Added Tax)</span>
                                <span class="font-semibold text-gray-800">₱{{ number_format($order->tax, 2) }}</span>
                            </div>
                            @if(isset($order->vat_exempt_sales) && $order->vat_exempt_sales > 0)
                            <div class="flex justify-between text-sm text-amber-700">
                                <span>VAT-Exempt Sales (RA 9994/10754)</span>
                                <span class="font-bold text-amber-800">₱{{ number_format($order->vat_exempt_sales, 2) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-base font-extrabold text-gray-900 pt-2 border-t border-gray-200">
                                <span>Total Payable</span>
                                <span class="text-2xl font-black text-[#155d49]">₱{{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right 1 Col: Branch, Order & Multi-Payment Details -->
                <div class="space-y-6">

                    <!-- Branch & Fulfillment Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                        <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider border-b border-gray-100 pb-2">
                            Branch & Channel Info
                        </h4>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Branch Location</span>
                                <span class="font-bold text-gray-900">{{ $order->branch?->name ?? 'Main Branch' }}</span>
                            </div>
                            @if($order->branch?->code)
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Branch Code</span>
                                <span class="font-mono font-bold text-gray-700">{{ $order->branch->code }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500 font-medium">Order Type</span>
                                <span class="font-bold px-2 py-0.5 rounded text-xs
                                    {{ in_array($order->order_type, ['grab', 'grab_delivery']) ? 'bg-emerald-100 text-emerald-800' : '' }}
                                    {{ $order->order_type === 'takeout' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ (!in_array($order->order_type, ['grab', 'grab_delivery', 'takeout'])) ? 'bg-blue-100 text-blue-800' : '' }}
                                ">
                                    {{ $order->order_type_label }}
                                </span>
                            </div>
                            @if($order->grab_order_code)
                            <div class="flex justify-between items-center bg-emerald-50/80 p-2 rounded-xl border border-emerald-200">
                                <span class="text-emerald-900 font-bold">Grab Order Code</span>
                                <span class="font-mono font-black text-emerald-800">{{ $order->grab_order_code }}</span>
                            </div>
                            @endif
                            @if($order->rider_code)
                            <div class="flex justify-between items-center bg-emerald-50/80 p-2 rounded-xl border border-emerald-200">
                                <span class="text-emerald-900 font-bold">Rider Code</span>
                                <span class="font-mono font-black text-emerald-800">{{ $order->rider_code }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Cashier Assigned</span>
                                <span class="font-bold text-gray-800">{{ $order->cashier_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Terminal Account</span>
                                <span class="font-medium text-gray-700">{{ $order->user?->name ?? 'System' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Order Timestamp</span>
                                <span class="font-medium text-gray-700">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Tender & Settlement Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider">
                                Payment Settlement
                            </h4>
                            @if($order->remaining_balance > 0)
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-rose-100 text-rose-800">
                                    ₱{{ number_format($order->remaining_balance, 2) }} Unpaid
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                    Fully Settled
                                </span>
                            @endif
                        </div>

                        <!-- Multi-payment or Split list -->
                        @php $paymentsList = $order->payments->isNotEmpty() ? $order->payments : ($order->payment ? collect([$order->payment]) : collect([])); @endphp

                        @if($paymentsList->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($paymentsList as $idx => $p)
                                    <div class="p-3 rounded-xl border border-gray-100 bg-gray-50/70 text-xs space-y-1.5">
                                        <div class="flex justify-between items-center">
                                            <span class="font-bold text-gray-800">
                                                Payment #{{ $idx + 1 }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-bold
                                                {{ $p->method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : 'bg-sky-100 text-sky-800' }}
                                            ">
                                                {{ in_array($p->method, ['online', 'gcash']) ? 'Online Payment' : ucfirst($p->method) }}
                                            </span>
                                        </div>

                                        <div class="flex justify-between text-gray-600">
                                            <span>Amount Tendered:</span>
                                            <span class="font-bold text-gray-900">₱{{ number_format($p->amount_tendered, 2) }}</span>
                                        </div>

                                        @if($p->change > 0)
                                            <div class="flex justify-between text-gray-600">
                                                <span>Change Given:</span>
                                                <span class="font-bold text-[#155d49]">₱{{ number_format($p->change, 2) }}</span>
                                            </div>
                                        @endif

                                        @if($p->reference_number)
                                            <div class="flex justify-between text-gray-600 pt-1 border-t border-gray-200/50">
                                                <span>Gateway Reference:</span>
                                                <span class="font-mono font-bold text-sky-900">{{ $p->reference_number }}</span>
                                            </div>
                                        @endif

                                        <div class="text-[10px] text-gray-400 text-right">
                                            {{ $p->created_at->format('M d, Y h:i A') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 italic">No payment record found yet.</p>
                        @endif

                        <!-- Summary of tender vs due -->
                        <div class="pt-3 border-t border-gray-100 space-y-1.5 text-xs">
                            <div class="flex justify-between text-gray-600">
                                <span>Total Paid:</span>
                                <span class="font-bold text-gray-900">₱{{ number_format($order->paid_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Total Change:</span>
                                <span class="font-bold text-[#155d49]">₱{{ number_format($order->total_change, 2) }}</span>
                            </div>
                            @if($order->remaining_balance > 0)
                                <div class="flex justify-between text-rose-600 font-bold text-sm pt-1 border-t border-rose-100">
                                    <span>Remaining Balance:</span>
                                    <span>₱{{ number_format($order->remaining_balance, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Policy Info Card -->
                    <div class="bg-gray-50 rounded-2xl border border-gray-200 p-4 text-[11px] text-gray-600 space-y-2">
                        <div class="font-bold text-gray-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#155d49]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Audit & Policy Guidelines
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-gray-500">
                            <li>All refunds, cancellations, and voids require active Manager or Owner authorization.</li>
                            <li>Online payments (GCash, PayMaya, Card) are non-refundable in cash to preserve external gateway reconciliation.</li>
                            <li>Partial/split payments can be registered by the assigned cashier on shift.</li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Partial Payment Modal (If Remaining Balance > 0) -->
    @if($order->remaining_balance > 0)
    <div id="partial-payment-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-fade-in border border-emerald-100">
            <div class="p-5 bg-emerald-50 border-b border-emerald-100 flex justify-between items-center">
                <div class="flex items-center gap-2 text-[#155d49] font-bold">
                    <svg class="w-5 h-5 text-[#155d49]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <h3>Record Partial / Split Payment</h3>
                </div>
                <button onclick="closePartialPaymentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('orders.partial-payment', $order) }}" class="p-6 space-y-4">
                @csrf
                <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-200 text-xs">
                    <div class="flex justify-between text-gray-700">
                        <span>Total Due:</span>
                        <span class="font-bold">₱{{ number_format($order->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-[#155d49] font-bold mt-1 text-sm">
                        <span>Remaining Balance:</span>
                        <span>₱{{ number_format($order->remaining_balance, 2) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" id="partial-pay-method" onchange="togglePartialRef()" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="cash">Cash</option>
                        <option value="online">Online Payment (GCash / Maya / Card)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Amount Tendered (₱)</label>
                    <input type="number" step="0.01" min="0.01" name="amount_tendered" value="{{ $order->remaining_balance }}" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-gray-900" />
                </div>

                <div id="partial-ref-box" class="hidden">
                    <label class="block text-xs font-bold text-sky-800 mb-1">Digital Reference / Transaction # <span class="text-rose-500">*</span></label>
                    <input type="text" name="reference_number" id="partial-ref-input" placeholder="e.g. 100982348210" class="w-full px-3 py-2 text-sm border border-sky-300 rounded-xl focus:ring-2 focus:ring-sky-500 outline-none font-mono" />
                    <p class="text-[11px] text-sky-700 mt-1">Required for gateway audit & balance verification.</p>
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" onclick="closePartialPaymentModal()" class="flex-1 h-10 inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl text-sm transition active:scale-[0.98]">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 h-10 inline-flex items-center justify-center bg-[#155d49] hover:bg-[#124e3d] text-white font-bold rounded-xl text-sm shadow-sm transition active:scale-[0.98]">
                        Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Void or Refund Order Modal -->
    <div id="refund-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeRefundModal()">
        <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-100">
            <!-- Plain White Header with Title & Close Icon (Requirement 2) -->
            <div class="p-5 bg-white border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-lg text-gray-900 leading-tight">Void or refund order</h3>
                <button type="button" onclick="closeRefundModal()" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            @php
                $hasOnlinePay = $order->payments()->whereIn('method', ['online', 'gcash', 'card'])->exists() 
                    || in_array($order->payment?->method, ['online', 'gcash', 'card']);
            @endphp

            <form id="refund-form" method="POST" action="{{ route('orders.refund', $order) }}" onsubmit="handleRefundSubmit()" class="p-5 space-y-4">
                @csrf

                @if($hasOnlinePay)
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
                        <p class="font-bold">⚠️ Online Payment Notice:</p>
                        <p class="mt-0.5 text-amber-800">Digital transactions (GCash/Card) cannot be refunded in cash. Please choose <strong>Void</strong>.</p>
                    </div>
                @endif

                <!-- Order Number and Total Summary Row (Requirement 2) -->
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200/80 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-500 font-medium">Order:</span>
                        <span class="font-bold text-gray-900 ml-1">{{ $order->order_number }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-medium">Total:</span>
                        <span class="font-bold text-sm text-gray-900 ml-1">₱{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                <!-- Action Radio Cards: Refund & Void (Requirement 2) -->
                <div>
                    <label class="block text-xs font-bold text-gray-800 mb-2">Action</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="action-radio-group">
                        @if(!$hasOnlinePay)
                            <label id="card-refund" class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 border-rose-600 bg-rose-50/40 cursor-pointer transition select-none">
                                <input type="radio" name="action_type" value="refund" checked onchange="updateActionState(this.value)" class="mt-0.5 text-rose-600 focus:ring-rose-500 border-gray-300">
                                <div class="min-w-0">
                                    <span class="block text-xs font-bold text-gray-900">Refund</span>
                                    <span class="block text-[11px] text-gray-500 mt-0.5">Return cash</span>
                                </div>
                            </label>
                        @endif
                        <label id="card-void" class="relative flex items-start gap-3 p-3.5 rounded-xl border-2 {{ $hasOnlinePay ? 'border-rose-600 bg-rose-50/40' : 'border-gray-200 bg-white hover:border-gray-300' }} cursor-pointer transition select-none">
                            <input type="radio" name="action_type" value="void" {{ $hasOnlinePay ? 'checked' : '' }} onchange="updateActionState(this.value)" class="mt-0.5 text-rose-600 focus:ring-rose-500 border-gray-300">
                            <div class="min-w-0">
                                <span class="block text-xs font-bold text-gray-900">Void</span>
                                <span class="block text-[11px] text-gray-500 mt-0.5">Cancel order</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Required Reason Select (Requirement 4) -->
                <div>
                    <label class="block text-xs font-bold text-gray-800 mb-1">Reason <span class="text-rose-500">*</span></label>
                    <select name="reason" id="refund-reason" required onchange="handleReasonChange(this.value)" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none font-medium text-gray-900">
                        <option value="">Select reason...</option>
                        <option value="Wrong item or recipe" {{ old('reason') === 'Wrong item or recipe' ? 'selected' : '' }}>Wrong item or recipe</option>
                        <option value="Customer changed their mind" {{ old('reason') === 'Customer changed their mind' ? 'selected' : '' }}>Customer changed their mind</option>
                        <option value="Duplicate entry" {{ old('reason') === 'Duplicate entry' ? 'selected' : '' }}>Duplicate entry</option>
                        <option value="Payment problem" {{ old('reason') === 'Payment problem' ? 'selected' : '' }}>Payment problem</option>
                        <option value="Item unavailable" {{ old('reason') === 'Item unavailable' ? 'selected' : '' }}>Item unavailable</option>
                        <option value="Other" {{ old('reason') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('reason')
                        <span class="text-xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Note Textarea (Required Only When "Other" is Selected) -->
                <div id="refund-note-box" class="{{ old('reason') === 'Other' ? '' : 'hidden' }}">
                    <label class="block text-xs font-bold text-gray-800 mb-1">Note <span class="text-rose-500">*</span></label>
                    <textarea name="note" id="refund-note" rows="2" placeholder="Explain details for 'Other'..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none font-medium placeholder:text-gray-500">{{ old('note') }}</textarea>
                    @error('note')
                        <span class="text-xs text-rose-600 font-medium mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Return Ingredients to Stock Checkbox with Hint Line (Requirement 5) -->
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200/80">
                    <div class="flex items-start gap-2.5">
                        <input type="checkbox" id="restore_inventory" name="restore_inventory" value="1" checked class="mt-0.5 w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300">
                        <div>
                            <label for="restore_inventory" class="text-xs font-bold text-gray-900 cursor-pointer block">
                                Return ingredients to stock
                            </label>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Automatically restores recipe ingredient deductions and add-ons back to inventory.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Neutral Audit Note (Requirement 5) -->
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200/80 flex items-start gap-2 text-[11px] text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>This can't be undone. It will be saved in the audit log under your name.</span>
                </div>

                <!-- Footer: Cancel (Secondary) and Solid Red Confirm Button (Requirement 6) -->
                <div class="pt-2 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button type="button" onclick="closeRefundModal()" class="h-10 px-5 inline-flex items-center justify-center bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl transition shadow-2xs">
                        Cancel
                    </button>
                    <button type="submit" id="btn-confirm-refund" class="h-10 px-6 inline-flex items-center justify-center bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs transition active:scale-[0.98]">
                        <span id="btn-refund-label">{{ $hasOnlinePay ? 'Confirm void' : 'Confirm refund' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openRefundModal() {
            document.getElementById('refund-modal').classList.remove('hidden');
            // Auto-focus the Reason field on open (Requirement 7)
            setTimeout(() => {
                document.getElementById('refund-reason')?.focus();
            }, 60);
        }
        function closeRefundModal() {
            document.getElementById('refund-modal').classList.add('hidden');
        }

        // Close on Escape key (Requirement 7)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRefundModal();
                closePartialPaymentModal();
            }
        });

        // Auto-dismiss success toast after 4s (Requirement 7)
        setTimeout(() => {
            const toast = document.getElementById('order-toast-success');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);

        function updateActionState(val) {
            const cardRefund = document.getElementById('card-refund');
            const cardVoid = document.getElementById('card-void');
            const label = document.getElementById('btn-refund-label');
            
            if (val === 'refund') {
                if (cardRefund) cardRefund.className = "relative flex items-start gap-3 p-3.5 rounded-xl border-2 border-rose-600 bg-rose-50/40 cursor-pointer transition select-none";
                if (cardVoid) cardVoid.className = "relative flex items-start gap-3 p-3.5 rounded-xl border-2 border-gray-200 bg-white hover:border-gray-300 cursor-pointer transition select-none";
                if (label) label.textContent = 'Confirm refund';
            } else {
                if (cardRefund) cardRefund.className = "relative flex items-start gap-3 p-3.5 rounded-xl border-2 border-gray-200 bg-white hover:border-gray-300 cursor-pointer transition select-none";
                if (cardVoid) cardVoid.className = "relative flex items-start gap-3 p-3.5 rounded-xl border-2 border-rose-600 bg-rose-50/40 cursor-pointer transition select-none";
                if (label) label.textContent = 'Confirm void';
            }
        }

        function handleReasonChange(val) {
            const box = document.getElementById('refund-note-box');
            const input = document.getElementById('refund-note');
            if (val === 'Other') {
                box.classList.remove('hidden');
                input.setAttribute('required', 'required');
                input.focus();
            } else {
                box.classList.add('hidden');
                input.removeAttribute('required');
            }
        }

        function handleRefundSubmit() {
            const btn = document.getElementById('btn-confirm-refund');
            if (btn) btn.disabled = true; // Disable while submitting (Requirement 6)
        }

        function openPartialPaymentModal() {
            const m = document.getElementById('partial-payment-modal');
            if (m) m.classList.remove('hidden');
        }
        function closePartialPaymentModal() {
            const m = document.getElementById('partial-payment-modal');
            if (m) m.classList.add('hidden');
        }

        function togglePartialRef() {
            const method = document.getElementById('partial-pay-method')?.value;
            const refBox = document.getElementById('partial-ref-box');
            const refInput = document.getElementById('partial-ref-input');
            if (method === 'online' || method === 'gcash' || method === 'card') {
                refBox.classList.remove('hidden');
                refInput.setAttribute('required', 'required');
            } else {
                refBox.classList.add('hidden');
                refInput.removeAttribute('required');
            }
        }

        // Thermal Receipt Printing for Orders Show
        const orderData = @json($order);

        function printThermalReceipt() {
            if (!orderData) return;

            const is58 = (localStorage.getItem('heim_receipt_paper_width') || '80mm') === '58mm';
            const paperCssWidth = is58 ? '58mm' : '80mm';
            const bodyWidth = is58 ? '48mm' : '72mm';
            const baseFontSize = is58 ? '10px' : '12px';

            const branchName = (orderData.branch && orderData.branch.name) ? orderData.branch.name : 'HEIM COFFEE';
            const branchAddress = (orderData.branch && orderData.branch.address) ? orderData.branch.address : 'Bangkal, Davao City, PH';
            const orderTypeLabel = (orderData.order_type || 'dine_in').replace('_', ' ').toUpperCase();

            const itemsRows = (orderData.items || []).map(item => {
                let addons = '';
                const addOns = item.add_ons || item.addOns || [];
                if (addOns.length > 0) {
                    addons = addOns.map(a => `
                        <div style="display:flex; justify-content:space-between; padding-left:10px; font-size:0.9em; color:#222;">
                            <span>+ ${a.add_on_name}</span>
                            <span>₱${parseFloat(a.add_on_price || 0).toFixed(2)}</span>
                        </div>
                    `).join('');
                }

                let discRow = '';
                if (parseFloat(item.discount || 0) > 0) {
                    const dLabel = item.discount_type === 'pwd_senior' 
                        ? 'SC/PWD 20% (VAT-Exempt)' 
                        : (item.discount_type === 'staff' ? 'Staff 10%' : 'Disc');
                    discRow = `
                        <div style="display:flex; justify-content:space-between; padding-left:10px; font-size:0.85em; color:#333; font-style:italic;">
                            <span>> ${dLabel}${item.id_number ? ' ID:' + item.id_number : ''}</span>
                            <span>-₱${parseFloat(item.discount).toFixed(2)}</span>
                        </div>
                    `;
                }

                return `
                    <div style="margin-bottom: 3px;">
                        <div style="display:flex; justify-content:space-between; font-weight:bold;">
                            <span>${item.quantity}x ${item.product_name} (${item.size_name})</span>
                            <span>₱${parseFloat(item.subtotal).toFixed(2)}</span>
                        </div>
                        ${addons}
                        ${discRow}
                    </div>
                `;
            }).join('');

            const payments = (orderData.payments && orderData.payments.length > 0) 
                ? orderData.payments 
                : (orderData.payment ? [orderData.payment] : []);

            let paymentsHtml = '';
            if (payments.length > 1) {
                paymentsHtml = `<div style="font-weight:bold; font-size:0.85em; margin-top:2px;">SPLIT PAYMENTS:</div>` + payments.map((p, i) => `
                    <div style="display:flex; justify-content:space-between; font-size:0.85em;">
                        <span>#${i+1} ${(p.method || 'cash').toUpperCase()}:</span>
                        <span>₱${parseFloat(p.amount_tendered || 0).toFixed(2)}</span>
                    </div>
                    ${p.reference_number ? `<div style="font-size:0.8em; padding-left:6px; color:#333;">Ref: ${p.reference_number}</div>` : ''}
                `).join('');
            } else if (payments.length === 1) {
                const p = payments[0];
                const isOnline = ['online', 'gcash', 'card'].includes((p.method || '').toLowerCase());
                paymentsHtml = `
                    <div class="row">
                        <span>Payment:</span>
                        <span class="bold">${isOnline ? 'ONLINE PAYMENT' : 'CASH'}</span>
                    </div>
                    ${p.reference_number ? `
                    <div class="row">
                        <span>Ref #:</span>
                        <span class="bold">${p.reference_number}</span>
                    </div>` : ''}
                    <div class="row">
                        <span>Tendered:</span>
                        <span>₱${parseFloat(p.amount_tendered || orderData.total).toFixed(2)}</span>
                    </div>
                    <div class="row">
                        <span class="bold">Change:</span>
                        <span class="bold">₱${parseFloat(p.change || 0).toFixed(2)}</span>
                    </div>
                `;
            }

            const dateFormatted = new Date(orderData.created_at).toLocaleString('en-US', {
                month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true
            });

            const vatablePrint = parseFloat(orderData.vatable_sales !== undefined && orderData.vatable_sales !== null ? orderData.vatable_sales : ((parseFloat(orderData.total || 0) - parseFloat(orderData.vat_exempt_sales || 0)) / 1.12));
            const vatExemptPrint = parseFloat(orderData.vat_exempt_sales || 0);

            const receiptHtml = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt ${orderData.order_number}</title>
    <style>
        @page {
            size: portrait;
            margin: 0mm;
        }
        @media print {
            html, body {
                width: ${bodyWidth} !important;
                max-width: ${bodyWidth} !important;
                margin: 0 auto !important;
                padding: 2mm 1mm 4mm 1mm !important;
                background: #fff !important;
                color: #000 !important;
                font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace !important;
                font-size: ${baseFontSize} !important;
                line-height: 1.3 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .receipt-wrapper {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
        body {
            width: ${bodyWidth};
            max-width: ${bodyWidth};
            margin: 0 auto;
            padding: 2mm 1mm 4mm 1mm;
            background: #fff;
            color: #000;
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            font-size: ${baseFontSize};
            line-height: 1.3;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .receipt-wrapper {
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .center { text-align: center; }
        .dashed { border-top: 1px dashed #000; margin: 4px 0; }
        .double-dashed { border-top: 2px dashed #000; margin: 5px 0; }
        .row { display: flex; justify-content: space-between; }
        .bold { font-weight: bold; }
        .store-name { font-size: ${is58 ? '14px' : '16px'}; font-weight: 900; letter-spacing: 1px; }
        .total-amount { font-size: ${is58 ? '14px' : '16px'}; font-weight: 900; }
    </style>
</head>
<body>
<div class="receipt-wrapper">
    <div class="center">
        <div class="store-name">HEIM COFFEE</div>
        <div style="font-size: 0.9em; font-weight: bold;">${branchName.toUpperCase()}</div>
        <div style="font-size: 0.85em;">${branchAddress}</div>
    </div>

    <div class="dashed"></div>

    <div class="row bold">
        <span>ORDER:</span>
        <span>${orderData.order_number}</span>
    </div>
    <div class="row bold" style="color: #111;">
        <span>TYPE:</span>
        <span>${orderTypeLabel}</span>
    </div>
    ${orderData.grab_order_code ? `
    <div class="row bold" style="color:#0e703c;">
        <span>GRAB ORDER:</span>
        <span>${orderData.grab_order_code}</span>
    </div>
    ` : ''}
    ${orderData.rider_code ? `
    <div class="row bold" style="color:#0e703c;">
        <span>RIDER CODE:</span>
        <span>${orderData.rider_code}</span>
    </div>
    ` : ''}
    <div class="row">
        <span>DATE:</span>
        <span>${dateFormatted}</span>
    </div>
    <div class="row">
        <span>CASHIER:</span>
        <span>${orderData.cashier_name}</span>
    </div>

    <div class="dashed"></div>

    <div class="row bold" style="font-size: 0.9em;">
        <span>QTY ITEM</span>
        <span>PRICE</span>
    </div>
    <div class="dashed" style="border-top-style: dotted;"></div>

    <div>
        ${itemsRows}
    </div>

    <div class="dashed"></div>

    <div class="row">
        <span>Subtotal (Gross):</span>
        <span>₱${parseFloat(orderData.subtotal).toFixed(2)}</span>
    </div>
    <div class="row">
        <span>Total Discounts:</span>
        <span>-₱${parseFloat(orderData.discount || 0).toFixed(2)}</span>
    </div>
    <div class="row">
        <span>Vatable Sales:</span>
        <span>₱${vatablePrint.toFixed(2)}</span>
    </div>
    <div class="row">
        <span>VAT (${parseFloat(orderData.tax_rate || 12).toFixed(0)}%):</span>
        <span>₱${parseFloat(orderData.tax || 0).toFixed(2)}</span>
    </div>
    ${vatExemptPrint > 0 ? `
    <div class="row">
        <span>VAT-Exempt Sales:</span>
        <span>₱${vatExemptPrint.toFixed(2)}</span>
    </div>
    ` : ''}

    <div class="double-dashed"></div>

    <div class="row total-amount">
        <span>TOTAL DUE:</span>
        <span>₱${parseFloat(orderData.total).toFixed(2)}</span>
    </div>

    <div class="dashed"></div>

    ${paymentsHtml}

    <div class="dashed"></div>

    <div class="center" style="font-size: 0.85em; margin-top: 4px;">
        <div class="bold">THANK YOU FOR CHOOSING HEIM!</div>
        <div>Please come again.</div>
        <div style="font-size: 0.9em; margin-top: 2px;">Wi-Fi: HeimGuest</div>
        <div style="font-size: 0.8em; margin-top: 4px;">*** Heim POS Thermal Slip ***</div>
    </div>
</div>
</body>
</html>
            `;

            let printFrame = document.getElementById('heim-thermal-frame-order');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'heim-thermal-frame-order';
                printFrame.style.position = 'fixed';
                printFrame.style.right = '0';
                printFrame.style.bottom = '0';
                printFrame.style.width = '0';
                printFrame.style.height = '0';
                printFrame.style.border = '0';
                document.body.appendChild(printFrame);
            }

            const doc = printFrame.contentWindow.document;
            doc.open();
            doc.write(receiptHtml);
            doc.close();

            setTimeout(() => {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            }, 300);
        }
    </script>
    @endpush
</x-app-layout>
