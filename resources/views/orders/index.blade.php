<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Orders Management') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    View transaction histories, cashier shift records, and manage order statuses
                </p>
            </div>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow-sm transition gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New POS Sale
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Filters Bar -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Search Order # or Cashier</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. ORD-2026... or John" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Order Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Statuses</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Filter by Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-sm">
                            Apply Filter
                        </button>
                        <a href="{{ route('orders.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Orders Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Order Number</th>
                                <th class="py-3.5 px-4">Date & Time</th>
                                <th class="py-3.5 px-4">Cashier</th>
                                <th class="py-3.5 px-4">Items Qty</th>
                                <th class="py-3.5 px-4">Total Amount</th>
                                <th class="py-3.5 px-4">Payment Method</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-gray-900">
                                        <a href="{{ route('orders.show', $order) }}" class="hover:text-[#155d49] transition">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 text-xs">
                                        {{ $order->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-800">
                                        {{ $order->cashier_name }}
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-[#f0f8f5] text-[#155d49]">
                                            {{ $order->items->sum('quantity') }} items
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-black text-gray-900">
                                        ₱{{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-1 text-xs rounded-full uppercase font-bold
                                            {{ $order->payment?->method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                            {{ $order->payment?->method === 'gcash' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $order->payment?->method === 'card' ? 'bg-purple-100 text-purple-800' : '' }}
                                        ">
                                            {{ $order->payment?->method ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-1 text-xs rounded-full font-bold
                                            {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                            {{ $order->status === 'refunded' ? 'bg-rose-100 text-rose-800' : '' }}
                                        ">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center px-3 py-1.5 bg-[#f0f8f5] hover:bg-emerald-100 text-[#155d49] rounded-lg text-xs font-bold transition">
                                            View Details &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-400">
                                        No orders matched your search criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $orders->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
