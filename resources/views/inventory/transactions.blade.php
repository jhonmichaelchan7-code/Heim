<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                        {{ __('Inventory Transactions Ledger') }}
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Historical audit trail of stock movements, deliveries, sales deductions, and adjustments
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.stock-in') }}" class="inline-flex items-center px-3.5 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Stock In
                </a>
                <a href="{{ route('inventory.waste') }}" class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Record Waste
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('inventory.transactions') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Ingredient</label>
                        <select name="ingredient_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Ingredients</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}" {{ request('ingredient_id') == $ing->id ? 'selected' : '' }}>{{ $ing->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Transaction Type</label>
                        <select name="type" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Types</option>
                            <option value="stock_in" {{ request('type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                            <option value="sales_consumption" {{ request('type') === 'sales_consumption' ? 'selected' : '' }}>Sales Consumption</option>
                            <option value="waste" {{ request('type') === 'waste' ? 'selected' : '' }}>Waste / Spoilage</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-sm">
                            Filter
                        </button>
                        <a href="{{ route('inventory.transactions') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Ledger Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Date & Time</th>
                                <th class="py-3.5 px-4">Ingredient</th>
                                <th class="py-3.5 px-4">Type</th>
                                <th class="py-3.5 px-4 text-right">Quantity</th>
                                <th class="py-3.5 px-4">Recorded By</th>
                                <th class="py-3.5 px-4">Reason / Supplier / Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $txn)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-xs text-gray-600 font-mono">
                                        {{ $txn->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        {{ $txn->ingredient?->name }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 text-xs rounded-full font-bold uppercase
                                            {{ $txn->type === 'stock_in' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                            {{ $txn->type === 'sales_consumption' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $txn->type === 'waste' ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $txn->type === 'adjustment' ? 'bg-purple-100 text-purple-800' : '' }}
                                        ">
                                            {{ str_replace('_', ' ', $txn->type) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold
                                        {{ $txn->quantity > 0 ? 'text-[#155d49]' : 'text-rose-600' }}
                                    ">
                                        {{ $txn->quantity > 0 ? '+' : '' }}{{ number_format($txn->quantity, 2) }} {{ $txn->ingredient?->unit }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-semibold text-gray-800">
                                        {{ $txn->performer?->name ?? 'System' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-gray-600">
                                        <div class="space-y-0.5">
                                            @if($txn->reason)
                                                <span class="font-bold text-gray-800">{{ $txn->reason }}</span>
                                            @endif
                                            @if($txn->supplier)
                                                <span class="text-gray-500 block">Supplier: {{ $txn->supplier }}</span>
                                            @endif
                                            @if($txn->reference_type && $txn->reference_id)
                                                <span class="text-gray-400 block font-mono text-[11px]">{{ ucfirst($txn->reference_type) }} #{{ $txn->reference_id }}</span>
                                            @endif
                                            @if($txn->notes)
                                                <span class="text-gray-500 italic block">{{ $txn->notes }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        No inventory transactions recorded for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $transactions->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
