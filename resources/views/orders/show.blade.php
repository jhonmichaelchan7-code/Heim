<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                            Order {{ $order->order_number }}
                        </h2>
                        <span class="px-2.5 py-0.5 text-xs rounded-full font-bold
                            {{ $order->status === 'completed' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                            {{ $order->status === 'refunded' ? 'bg-rose-100 text-rose-800' : '' }}
                        ">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Placed on {{ $order->created_at->format('F d, Y \a\t h:i:s A') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs rounded-xl transition gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>

                @if($order->status === 'completed')
                    <button onclick="openRefundModal()" class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                        Issue Refund
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Refund Banner if refunded -->
            @if($order->status === 'refunded' && $order->refunds->count() > 0)
                @php $refund = $order->refunds->first(); @endphp
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 text-sm text-rose-900 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-base text-rose-800">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Order Was Refunded
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-xs">
                        <div>
                            <span class="text-rose-500 font-semibold block">Refund Amount:</span>
                            <span class="font-bold text-sm text-rose-900">₱{{ number_format($refund->refund_amount, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-rose-500 font-semibold block">Authorized By:</span>
                            <span class="font-bold text-gray-800">{{ $refund->authorizer?->name }} ({{ ucfirst($refund->authorizer?->role) }})</span>
                        </div>
                        <div>
                            <span class="text-rose-500 font-semibold block">Refund Date:</span>
                            <span class="font-bold text-gray-800">{{ $refund->created_at->format('M d, Y h:i A') }}</span>
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
                            <h3 class="font-bold text-gray-900 text-base">Purchased Items</h3>
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
                                                            </span>
                                                        @endforeach
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
                                            <td class="py-4 px-4 text-right font-black text-gray-900">
                                                ₱{{ number_format($item->subtotal, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-5 bg-[#f0f8f5]/50 border-t border-gray-100 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-gray-800">₱{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-extrabold text-gray-900 pt-2 border-t border-gray-200">
                                <span>Total Amount</span>
                                <span class="text-2xl font-black text-[#155d49]">₱{{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right 1 Col: Order & Payment Info -->
                <div class="space-y-6">
                    <!-- Order Details Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                        <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider border-b border-gray-100 pb-2">
                            Transaction Summary
                        </h4>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Order Number</span>
                                <span class="font-mono font-bold text-gray-900">{{ $order->order_number }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Cashier on Shift</span>
                                <span class="font-bold text-gray-800">{{ $order->cashier_name }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">System Account</span>
                                <span class="font-medium text-gray-700">{{ $order->user?->name ?? 'System' }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-500 font-medium">Date & Time</span>
                                <span class="font-medium text-gray-700">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details Card -->
                    @if($order->payment)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">
                            <h4 class="font-bold text-gray-900 text-xs uppercase tracking-wider border-b border-gray-100 pb-2">
                                Payment Details
                            </h4>

                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 font-medium">Payment Method</span>
                                    <span class="px-2.5 py-1 text-xs rounded-full uppercase font-bold
                                        {{ $order->payment->method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                        {{ $order->payment->method === 'gcash' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->payment->method === 'card' ? 'bg-purple-100 text-purple-800' : '' }}
                                    ">
                                        {{ $order->payment->method }}
                                    </span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-medium">Amount Tendered</span>
                                    <span class="font-bold text-gray-800">₱{{ number_format($order->payment->amount_tendered, 2) }}</span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-500 font-medium">Change Given</span>
                                    <span class="font-bold text-[#155d49]">₱{{ number_format($order->payment->change, 2) }}</span>
                                </div>

                                @if($order->payment->reference_number)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 font-medium">Ref / Approval #</span>
                                        <span class="font-mono font-bold text-gray-800">{{ $order->payment->reference_number }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>

    <!-- Refund Authorization Modal -->
    <div id="refund-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-fade-in border border-rose-100">
            <div class="p-5 bg-rose-50 border-b border-rose-100 flex justify-between items-center">
                <div class="flex items-center gap-2 text-rose-800 font-bold">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h3>Authorize Order Refund</h3>
                </div>
                <button onclick="closeRefundModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('orders.refund', $order) }}" class="p-6 space-y-4">
                @csrf
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600">
                    Refund Amount: <span class="font-bold text-sm text-gray-900">₱{{ number_format($order->total, 2) }}</span>
                    <p class="mt-1 text-emerald-800 font-semibold">Supervisor or higher credentials required to authorize this refund.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Reason for Refund</label>
                    <textarea name="reason" rows="2" required placeholder="e.g. Wrong drink prepared, customer cancelled..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Authorizer Email (Supervisor+)</label>
                    <input type="email" name="auth_email" required placeholder="supervisor@coffee.com" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Authorizer Password</label>
                    <input type="password" name="auth_password" required placeholder="••••••••" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none" />
                </div>

                <div class="pt-2 flex gap-3">
                    <button type="button" onclick="closeRefundModal()" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow transition">
                        Authorize Refund
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openRefundModal() {
            document.getElementById('refund-modal').classList.remove('hidden');
        }
        function closeRefundModal() {
            document.getElementById('refund-modal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
