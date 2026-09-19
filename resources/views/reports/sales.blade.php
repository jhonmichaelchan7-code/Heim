<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Sales & Revenue Analytics') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Periodic revenue tracking, cashier shift performance, and best selling beverages
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.inventory') }}" class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition">
                    Inventory Report &rarr;
                </a>
                <button onclick="window.print()" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Time Period</label>
                        <select name="period" id="period-select" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Today (Daily)</option>
                            <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>This Week</option>
                            <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>This Month</option>
                            <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>This Year</option>
                            <option value="overall" {{ $period === 'overall' ? 'selected' : '' }}>All Time (Overall)</option>
                            <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-sm">
                            Generate
                        </button>
                    </div>
                </form>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Net Sales</p>
                    <h3 class="text-2xl font-black text-[#155d49] mt-1">₱{{ number_format($totalSales, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Completed Orders</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $totalOrders }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $totalItems }} total items sold</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Avg Order Value</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1">₱{{ $totalOrders > 0 ? number_format($totalSales / $totalOrders, 2) : '0.00' }}</h3>
                    <p class="text-xs text-[#155d49] font-semibold mt-1">Per transaction average</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Refunds</p>
                    <h3 class="text-2xl font-black text-rose-600 mt-1">₱{{ number_format($totalRefunds, 2) }}</h3>
                    <p class="text-xs text-rose-500 mt-1">{{ $refundedOrders->count() }} refunded orders</p>
                </div>
            </div>

            <!-- Two-Column Grid: Payment Breakdown & Sales By Cashier -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Breakdown -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 text-base mb-4">Payment Method Breakdown</h3>
                    <div class="space-y-3">
                        @forelse($paymentBreakdown as $method => $data)
                            <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 text-xs rounded-full uppercase font-bold
                                        {{ $method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                        {{ $method === 'gcash' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $method === 'card' ? 'bg-purple-100 text-purple-800' : '' }}
                                    ">
                                        {{ $method }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-medium">{{ $data['count'] }} transactions</span>
                                </div>
                                <span class="font-bold text-gray-900 text-sm">₱{{ number_format($data['total'], 2) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">No payments recorded in this period.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Sales by Cashier -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 text-base mb-4">Cashier Performance (Shift Attribution)</h3>
                    <div class="space-y-3">
                        @forelse($salesByCashier as $cashier => $data)
                            <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $cashier }}</p>
                                    <p class="text-xs text-gray-500">{{ $data['orders'] }} orders processed</p>
                                </div>
                                <span class="font-black text-[#155d49] text-base">₱{{ number_format($data['total'], 2) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">No cashier data for this period.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Best Selling Products Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-[#f0f8f5]/60">
                    <h3 class="font-bold text-gray-900 text-base">Top Selling Beverages & Items</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3 px-4"># Rank</th>
                                <th class="py-3 px-4">Product Name</th>
                                <th class="py-3 px-4">Size</th>
                                <th class="py-3 px-4 text-center">Quantity Sold</th>
                                <th class="py-3 px-4 text-right">Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($bestSellers as $idx => $seller)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3.5 px-4 font-bold text-gray-400 font-mono">
                                        #{{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        {{ $seller->product_name }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#f0f8f5] text-[#155d49] uppercase">
                                            {{ $seller->size_name }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-black text-gray-900">
                                        {{ $seller->total_qty }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-black text-[#155d49] font-mono">
                                        ₱{{ number_format($seller->total_revenue, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        No sales recorded in this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
