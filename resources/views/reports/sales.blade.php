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
            <!-- HCI Theory: Single prominent primary action button (Hick's Law & Fitts's Law) -->
            <div class="flex items-center no-print">
                <button type="button" 
                        onclick="exportCompleteSalesReportExcel()" 
                        class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#107c41] hover:bg-[#0c6133] text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs hover:shadow transition-all duration-150 active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6C4.89 2 4 2.89 4 4v16c0 1.11.89 2 2 2h12c1.11 0 2-.89 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                    <span>Export Report (Excel)</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-5">

            <!-- Report Navigation Tabs (Clean separation of Navigation vs Action) -->
            <div class="flex items-center gap-2 border-b border-gray-200/80 pb-3 no-print">
                <a href="{{ route('reports.sales') }}" 
                   class="inline-flex items-center h-9 px-4 rounded-xl text-xs font-bold bg-[#155d49] text-white shadow-xs">
                    Sales & Revenue
                </a>
                <a href="{{ route('reports.inventory') }}" 
                   class="inline-flex items-center h-9 px-4 rounded-xl text-xs font-semibold bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 transition">
                    Inventory Movement
                </a>
            </div>

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Time Period</label>
                        <select name="period" id="period-select" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
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
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div class="flex">
                        <button type="submit" class="w-full h-10 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            <span>Filter Results</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Executive KPI Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Net Sales</p>
                    <h3 class="text-2xl font-black text-[#155d49] mt-1 font-mono">₱{{ number_format($totalSales, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Completed Orders</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1 font-mono">{{ $totalOrders }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ $totalItems }} total items sold</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Avg Order Value</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1 font-mono">₱{{ $totalOrders > 0 ? number_format($totalSales / $totalOrders, 2) : '0.00' }}</h3>
                    <p class="text-xs text-[#155d49] font-semibold mt-1">Per transaction average</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Refunds</p>
                    <h3 class="text-2xl font-black text-rose-600 mt-1 font-mono">₱{{ number_format($totalRefunds, 2) }}</h3>
                    <p class="text-xs text-rose-500 mt-1">{{ $refundedOrders->count() }} refunded orders</p>
                </div>
            </div>

            <!-- Two-Column Grid: Payment Breakdown & Sales By Cashier -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Breakdown -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-900 text-base mb-4">Payment Method Breakdown</h3>
                    <div class="space-y-3">
                        @forelse($paymentBreakdown as $method => $data)
                            <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 text-xs rounded-full font-bold
                                        {{ $method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                        {{ in_array($method, ['online', 'gcash']) ? 'bg-sky-100 text-sky-800' : '' }}
                                        {{ $method === 'card' ? 'bg-purple-100 text-purple-800' : '' }}
                                    ">
                                        {{ in_array($method, ['online', 'gcash']) ? 'Online Payment (GCash/Maya)' : ucfirst($method) }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-medium">{{ $data['count'] }} transactions</span>
                                </div>
                                <span class="font-bold text-gray-900 text-sm font-mono">₱{{ number_format($data['total'], 2) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">No payments recorded in this period.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Sales by Cashier -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-900 text-base mb-4">Cashier Performance (Shift Attribution)</h3>
                    <div class="space-y-3">
                        @forelse($salesByCashier as $cashier => $data)
                            <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">{{ $cashier }}</p>
                                    <p class="text-xs text-gray-500">{{ $data['orders'] }} orders processed</p>
                                </div>
                                <span class="font-black text-[#155d49] text-base font-mono">₱{{ number_format($data['total'], 2) }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">No cashier data for this period.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Best Selling Products Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-[#f0f8f5]/60 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900 text-base">Top Selling Beverages & Items</h3>
                    <span class="text-xs font-semibold text-gray-500">{{ $bestSellers->count() }} top items</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4"># Rank</th>
                                <th class="py-3.5 px-4">Product Name</th>
                                <th class="py-3.5 px-4">Size</th>
                                <th class="py-3.5 px-4 text-center">Quantity Sold</th>
                                <th class="py-3.5 px-4 text-right">Revenue Generated</th>
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
                                    <td class="py-3.5 px-4 text-center font-black text-gray-900 font-mono">
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

@push('scripts')
<script>
    function exportCompleteSalesReportExcel() {
        const reportData = {
            title: "HEIM COFFEE - SALES & REVENUE ANALYTICS REPORT",
            period: "{{ ucfirst($period) }} ({{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }})",
            generatedAt: new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }),
            summary: [
                ["Total Net Sales (PHP)", "{{ number_format($totalSales, 2, '.', '') }}"],
                ["Completed Orders Count", "{{ $totalOrders }}"],
                ["Total Items Sold", "{{ $totalItems }}"],
                ["Average Order Value (PHP)", "{{ $totalOrders > 0 ? number_format($totalSales / $totalOrders, 2, '.', '') : '0.00' }}"],
                ["Total Refunds (PHP)", "{{ number_format($totalRefunds, 2, '.', '') }}"]
            ],
            payments: [
                @foreach($paymentBreakdown as $method => $data)
                ["{{ in_array($method, ['online', 'gcash']) ? 'Online Payment (GCash/Maya)' : ucfirst($method) }}", "{{ $data['count'] }}", "{{ number_format($data['total'], 2, '.', '') }}"],
                @endforeach
            ],
            cashiers: [
                @foreach($salesByCashier as $cashier => $data)
                ["{{ addslashes($cashier) }}", "{{ $data['orders'] }}", "{{ number_format($data['total'], 2, '.', '') }}"],
                @endforeach
            ],
            products: [
                @foreach($bestSellers as $idx => $seller)
                ["{{ $idx + 1 }}", "{{ addslashes($seller->product_name) }}", "{{ $seller->size_name }}", "{{ $seller->total_qty }}", "{{ number_format($seller->total_revenue, 2, '.', '') }}"],
                @endforeach
            ]
        };

        let csv = "";
        csv += `"${reportData.title}"\n`;
        csv += `"Reporting Period:","${reportData.period}"\n`;
        csv += `"Generated At:","${reportData.generatedAt}"\n\n`;

        // Section 1: Executive KPI Summary
        csv += `"--- EXECUTIVE KPI SUMMARY ---"\n`;
        csv += `"Metric","Value"\n`;
        reportData.summary.forEach(row => {
            csv += `"${row[0]}","${row[1]}"\n`;
        });
        csv += `\n`;

        // Section 2: Payment Breakdown
        csv += `"--- PAYMENT METHOD BREAKDOWN ---"\n`;
        csv += `"Payment Method","Transactions Count","Total Revenue (PHP)"\n`;
        reportData.payments.forEach(row => {
            csv += `"${row[0]}","${row[1]}","${row[2]}"\n`;
        });
        csv += `\n`;

        // Section 3: Cashier Performance
        csv += `"--- CASHIER PERFORMANCE (SHIFT ATTRIBUTION) ---"\n`;
        csv += `"Cashier Name","Orders Processed","Total Revenue (PHP)"\n`;
        reportData.cashiers.forEach(row => {
            csv += `"${row[0]}","${row[1]}","${row[2]}"\n`;
        });
        csv += `\n`;

        // Section 4: Top Selling Items
        csv += `"--- TOP SELLING BEVERAGES & PRODUCTS ---"\n`;
        csv += `"Rank","Product Name","Size","Quantity Sold","Total Revenue (PHP)"\n`;
        reportData.products.forEach(row => {
            csv += `"${row[0]}","${row[1]}","${row[2]}","${row[3]}","${row[4]}"\n`;
        });

        // Add UTF-8 BOM (\uFEFF) so Excel opens with proper character encoding and formatting
        const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `Heim_Sales_Report_{{ $dateFrom }}_to_{{ $dateTo }}.csv`;
        link.click();
        URL.revokeObjectURL(url);
    }
</script>
@endpush
