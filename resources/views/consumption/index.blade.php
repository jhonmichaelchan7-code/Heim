<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Daily Ingredient Consumption') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Track daily raw ingredient usage from sales, waste, and recipe deductions
                </p>
            </div>
            <form method="GET" action="{{ route('consumption.index') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}" class="px-3.5 py-2 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold text-gray-800" />
                <button type="submit" class="px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white text-xs font-bold rounded-xl transition shadow-sm">
                    View Date
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Summary KPI Header Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Date Selected</p>
                        <h3 class="text-xl font-black text-gray-900 mt-1">{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</h3>
                        <p class="text-xs text-[#155d49] font-semibold mt-0.5">{{ \Carbon\Carbon::parse($date)->isToday() ? "Today's Consumption" : "Historical Report" }}</p>
                    </div>
                    <div class="p-3.5 bg-[#f0f8f5] text-[#155d49] rounded-2xl border border-emerald-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Orders Processed</p>
                        <h3 class="text-xl font-black text-gray-900 mt-1">{{ $ordersCount }} Orders</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Completed sales</p>
                    </div>
                    <div class="p-3.5 bg-blue-50 text-blue-600 rounded-2xl border border-blue-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Items / Cups Sold</p>
                        <h3 class="text-xl font-black text-gray-900 mt-1">{{ $itemsSold }} Items</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Total beverages & food</p>
                    </div>
                    <div class="p-3.5 bg-emerald-50 text-[#155d49] rounded-2xl border border-emerald-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Detailed Consumption Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-[#f0f8f5]/60 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-base">Ingredient Usage Matrix</h3>
                    <button onclick="window.print()" class="px-3.5 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-xs font-bold transition">
                        Print Daily Report
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Ingredient</th>
                                <th class="py-3.5 px-4 text-center">Unit</th>
                                <th class="py-3.5 px-4 text-right text-blue-700">Sales Usage</th>
                                <th class="py-3.5 px-4 text-right text-rose-700">Waste / Spoilage</th>
                                <th class="py-3.5 px-4 text-right text-purple-700">Adjustments</th>
                                <th class="py-3.5 px-4 text-right text-[#155d49]">Stock Received</th>
                                <th class="py-3.5 px-4 text-right">Net Daily Change</th>
                                <th class="py-3.5 px-4 text-right">Current Stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($ingredients as $ing)
                                @php
                                    $ingTxns = $transactions->get($ing->id, collect());
                                    $salesUse = abs($ingTxns->where('type', 'sales_consumption')->sum('total_quantity'));
                                    $wasteUse = abs($ingTxns->where('type', 'waste')->sum('total_quantity'));
                                    $adjustUse = $ingTxns->where('type', 'adjustment')->sum('total_quantity');
                                    $stockIn = $ingTxns->where('type', 'stock_in')->sum('total_quantity');
                                    $netChange = $stockIn + $adjustUse - $salesUse - $wasteUse;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-4 px-4 font-bold text-gray-900">
                                        {{ $ing->name }}
                                        @if(isset($productBreakdown[$ing->id]) && $productBreakdown[$ing->id]->count() > 0)
                                            <div class="text-[11px] text-[#155d49] font-normal mt-0.5">
                                                Used in: 
                                                @foreach($productBreakdown[$ing->id] as $pItem)
                                                    <span>{{ $pItem->total_qty }}x {{ $pItem->product_name }} ({{ $pItem->size_name }}){{ !$loop->last ? ',' : '' }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-md bg-[#f0f8f5] text-[#155d49] text-xs font-bold">
                                            {{ $ing->unit }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-sm {{ $salesUse > 0 ? 'text-blue-600 font-bold' : 'text-gray-400' }}">
                                        {{ $salesUse > 0 ? '-' . number_format($salesUse, 2) : '0.00' }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-sm {{ $wasteUse > 0 ? 'text-rose-600 font-bold' : 'text-gray-400' }}">
                                        {{ $wasteUse > 0 ? '-' . number_format($wasteUse, 2) : '0.00' }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-sm {{ $adjustUse != 0 ? 'text-purple-600 font-bold' : 'text-gray-400' }}">
                                        {{ $adjustUse > 0 ? '+' : '' }}{{ number_format($adjustUse, 2) }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-sm {{ $stockIn > 0 ? 'text-[#155d49] font-bold' : 'text-gray-400' }}">
                                        {{ $stockIn > 0 ? '+' . number_format($stockIn, 2) : '0.00' }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-sm font-black {{ $netChange < 0 ? 'text-rose-600' : ($netChange > 0 ? 'text-[#155d49]' : 'text-gray-400') }}">
                                        {{ $netChange > 0 ? '+' : '' }}{{ number_format($netChange, 2) }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono font-bold text-gray-900 text-sm">
                                        {{ number_format($ing->current_stock, 2) }} {{ $ing->unit }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
