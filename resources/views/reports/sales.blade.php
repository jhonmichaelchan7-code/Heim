<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Sales & Revenue Analytics') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Multi-branch revenue tracking, order channels (Dine-in/Takeout/Grab), peak rush hours, and inventory-linked beverage analytics
                </p>
            </div>
            <!-- Consolidated Export Dropdown (Secondary Outline) -->
            <div class="flex flex-wrap items-center gap-2 no-print">
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="inline-flex items-center gap-2 h-10 px-4 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl shadow-2xs transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Export</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-1.5 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-30">
                        <button type="button" @click="open = false; exportCompleteSalesReportExcel()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export Excel (.csv)</span>
                        </button>
                        <button type="button" @click="open = false; printSalesDataOnly()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print Report</span>
                        </button>
                        <div class="border-t border-gray-100 my-1"></div>
                        <button type="button" @click="open = false; openGoogleSheetModal()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm4-4H6v-2h10v2zm0-4H6V7h10v2z"/></svg>
                            <span>Sync Google Sheet</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-5">

            <!-- Report Navigation Tabs -->
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

            <!-- Enhanced Filter Card (With Branch & Order Channel) -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('reports.sales') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Time period</label>
                        <select name="period" id="period-select" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium text-gray-700">
                            <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Today (Daily)</option>
                            <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>This Week</option>
                            <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>This Month</option>
                            <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>This Year</option>
                            <option value="overall" {{ $period === 'overall' ? 'selected' : '' }}>All Time (Overall)</option>
                            <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Branch</label>
                        <select name="branch_id" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium text-gray-700">
                            <option value="">All Branches</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$branchId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Order channel</label>
                        <select name="order_type" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium text-gray-700">
                            <option value="">All Channels</option>
                            <option value="dine_in" {{ $orderType === 'dine_in' ? 'selected' : '' }}>🍽️ Dine-In</option>
                            <option value="takeout" {{ $orderType === 'takeout' ? 'selected' : '' }}>🛍️ Takeout</option>
                            <option value="grab_delivery" {{ in_array($orderType, ['grab_delivery', 'grab']) ? 'selected' : '' }}>🛵 Grab Delivery</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date from</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date to</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium" />
                    </div>

                    <div class="flex items-center pb-1">
                        <a href="{{ route('reports.sales') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 transition underline underline-offset-4">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Executive KPI Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-600">Total net sales</p>
                    <h3 class="text-2xl font-black text-[#155d49] mt-1 font-mono">₱{{ number_format($totalSales, 2) }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-600">Completed orders</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1 font-mono">{{ $totalOrders }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ pluralize($totalItems, 'item') }} sold</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-600">Average ticket size</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-1 font-mono">₱{{ number_format($avgTicketSize, 2) }}</h3>
                    <p class="text-xs text-[#155d49] font-semibold mt-1">Per transaction average</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-bold text-gray-600">Total refunds & voids</p>
                    <h3 class="text-2xl font-black text-rose-600 mt-1 font-mono">₱{{ number_format($totalRefunds, 2) }}</h3>
                    <p class="text-xs text-rose-500 mt-1">{{ pluralize($refundedOrders->count(), 'actioned order') }}</p>
                </div>
            </div>

            <!-- Multi-Branch Performance & Order Channels Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Branch Performance Comparison -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900 text-base">Branch Performance Comparison</h3>
                        <span class="text-xs text-gray-400 font-medium">Bangkal vs San Rafael</span>
                    </div>
                    <div class="space-y-3">
                        @forelse($branchStats as $bs)
                            @php
                                $bPct = $totalSales > 0 ? round(($bs['total_sales'] / $totalSales) * 100, 1) : 0;
                            @endphp
                            <div class="p-3.5 bg-gray-50 rounded-xl space-y-2 border border-gray-100">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#155d49]"></span>
                                        <span class="font-bold text-gray-900 text-sm">{{ $bs['name'] }}</span>
                                        <span class="text-[11px] font-mono font-semibold px-2 py-0.5 rounded bg-gray-200 text-gray-700">{{ $bs['code'] }}</span>
                                    </div>
                                    <span class="font-black text-[#155d49] text-base font-mono">₱{{ number_format($bs['total_sales'], 2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>{{ $bs['orders_count'] }} orders completed</span>
                                    <span class="font-bold text-gray-700">{{ $bPct }}% of total revenue</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-[#155d49] h-2 rounded-full transition-all duration-500" style="width: {{ $bPct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4">No branch records available.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Order Channels Breakdown (Dine-in / Takeout / Grab) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900 text-base">Order Channel Distribution</h3>
                        <span class="text-xs text-gray-400 font-medium">Dine-In • Takeout • Grab Delivery</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($orderTypesStats as $channelKey => $c)
                            <div class="p-3.5 bg-gray-50 rounded-xl space-y-2 border border-gray-100">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">{{ $c['icon'] }}</span>
                                        <span class="font-bold text-gray-900 text-sm">{{ $c['label'] }}</span>
                                        <span class="text-xs text-gray-500 font-medium">({{ $c['count'] }} orders)</span>
                                    </div>
                                    <span class="font-black text-gray-900 text-base font-mono">₱{{ number_format($c['total'], 2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Share of Total Sales:</span>
                                    <span class="font-bold text-gray-800">{{ $c['pct'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500
                                        {{ $channelKey === 'grab_delivery' ? 'bg-emerald-500' : '' }}
                                        {{ $channelKey === 'takeout' ? 'bg-amber-500' : '' }}
                                        {{ $channelKey === 'dine_in' ? 'bg-blue-600' : '' }}
                                    " style="width: {{ $c['pct'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Peak Ordering Hours (Rush Periods: 6 AM - 10 PM) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Peak Ordering Traffic (Rush Hour Distribution)</h3>
                        <p class="text-xs text-gray-500">Hourly volume of completed transactions from 6:00 AM to 10:00 PM</p>
                    </div>
                    @php
                        $maxHourlyCount = max(1, collect($peakHours)->max('count') ?? 1);
                    @endphp
                    <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-[#155d49] border border-emerald-200 rounded-lg">
                        Peak Rush: {{ collect($peakHours)->sortByDesc('count')->first()['hour'] ?? 'N/A' }} ({{ collect($peakHours)->sortByDesc('count')->first()['count'] ?? 0 }} orders)
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 lg:grid-cols-17 gap-2 pt-2">
                    @foreach($peakHours as $ph)
                        @php
                            $barHeightPct = round(($ph['count'] / $maxHourlyCount) * 100);
                        @endphp
                        <div class="flex flex-col items-center justify-end h-28 bg-gray-50 rounded-xl p-2 border border-gray-100/80">
                            <span class="text-[11px] font-bold {{ $ph['count'] > 0 ? 'text-[#155d49]' : 'text-gray-400' }}">{{ $ph['count'] }}</span>
                            <div class="w-full bg-gray-200/80 rounded-t-md my-1.5 flex items-end h-14 overflow-hidden">
                                <div class="w-full {{ $ph['count'] > 0 ? 'bg-[#155d49]' : 'bg-transparent' }} rounded-t-md transition-all duration-300" style="height: {{ max(4, $barHeightPct) }}%"></div>
                            </div>
                            <span class="text-[9px] font-semibold text-gray-600 truncate text-center w-full">{{ $ph['hour'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Two-Column Grid: Payment Breakdown & Sales By Cashier -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Breakdown (Cash vs Online) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-900 text-base mb-4">Payment Method Breakdown</h3>
                    <div class="space-y-3">
                        @forelse($paymentBreakdown as $method => $data)
                            <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <span class="px-2.5 py-1 text-xs rounded-full font-bold
                                        {{ $method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : 'bg-sky-100 text-sky-800' }}
                                    ">
                                        {{ $method === 'online' ? 'Online Payment (GCash / Maya / Card)' : 'Cash Tender' }}
                                    </span>
                                    <span class="text-xs text-gray-500 font-medium">{{ $data['count'] }} transactions ({{ $data['pct'] }}%)</span>
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
                            <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between border border-gray-100">
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
                        <thead class="bg-gray-50 text-gray-700 text-xs font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4"># Rank</th>
                                <th class="py-3.5 px-4">Product name</th>
                                <th class="py-3.5 px-4">Size</th>
                                <th class="py-3.5 px-4 text-center">Quantity sold</th>
                                <th class="py-3.5 px-4 text-right">Revenue generated</th>
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

            <script>
                window.exportCompleteSalesReportExcel = function() {
                    const reportData = {
                        title: "HEIM COFFEE - SALES & REVENUE ANALYTICS REPORT",
                        period: "{{ ucfirst($period) }} ({{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }})",
                        branchFilter: "{{ $branchId ? ($branches->firstWhere('id', $branchId)?->name ?? 'Specific Branch') : 'All Branches (Bangkal & San Rafael)' }}",
                        channelFilter: "{{ $orderType ? ucfirst(str_replace('_', ' ', $orderType)) : 'All Channels' }}",
                        generatedAt: new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }),
                        summary: [
                            ["Total Net Sales (PHP)", "{{ number_format($totalSales, 2, '.', '') }}"],
                            ["Completed Orders Count", "{{ $totalOrders }}"],
                            ["Total Items Sold", "{{ $totalItems }}"],
                            ["Average Ticket Size (PHP)", "{{ number_format($avgTicketSize, 2, '.', '') }}"],
                            ["Total Refunds & Voids (PHP)", "{{ number_format($totalRefunds, 2, '.', '') }}"]
                        ],
                        branches: [
                            @foreach($branchStats as $bs)
                            ["{{ addslashes($bs['name']) }} ({{ $bs['code'] }})", "{{ $bs['orders_count'] }}", "{{ number_format($bs['total_sales'], 2, '.', '') }}"],
                            @endforeach
                        ],
                        channels: [
                            @foreach($orderTypesStats as $c)
                            ["{{ $c['label'] }}", "{{ $c['count'] }}", "{{ number_format($c['total'], 2, '.', '') }}", "{{ $c['pct'] }}%"],
                            @endforeach
                        ],
                        payments: [
                            @foreach($paymentBreakdown as $method => $data)
                            ["{{ $method === 'online' ? 'Online Payment (GCash/Maya/Card)' : 'Cash Tender' }}", "{{ $data['count'] }}", "{{ number_format($data['total'], 2, '.', '') }}"],
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

                    const escapeCsv = (str) => '"' + String(str ?? '').replace(/"/g, '""') + '"';

                    let csv = "";
                    csv += escapeCsv(reportData.title) + "\n";
                    csv += escapeCsv("Reporting Period:") + "," + escapeCsv(reportData.period) + "\n";
                    csv += escapeCsv("Branch Filter:") + "," + escapeCsv(reportData.branchFilter) + "\n";
                    csv += escapeCsv("Channel Filter:") + "," + escapeCsv(reportData.channelFilter) + "\n";
                    csv += escapeCsv("Generated At:") + "," + escapeCsv(reportData.generatedAt) + "\n\n";

                    // Section 1: Executive KPI Summary
                    csv += escapeCsv("--- EXECUTIVE KPI SUMMARY ---") + "\n";
                    csv += escapeCsv("Metric") + "," + escapeCsv("Value") + "\n";
                    reportData.summary.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "\n";
                    });
                    csv += "\n";

                    // Section 2: Branch Comparison
                    csv += escapeCsv("--- BRANCH PERFORMANCE COMPARISON ---") + "\n";
                    csv += escapeCsv("Branch") + "," + escapeCsv("Completed Orders") + "," + escapeCsv("Total Sales (PHP)") + "\n";
                    reportData.branches.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "\n";
                    });
                    csv += "\n";

                    // Section 3: Channel Breakdown
                    csv += escapeCsv("--- ORDER CHANNEL DISTRIBUTION ---") + "\n";
                    csv += escapeCsv("Channel") + "," + escapeCsv("Orders Count") + "," + escapeCsv("Total Sales (PHP)") + "," + escapeCsv("Share (%)") + "\n";
                    reportData.channels.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "," + escapeCsv(row[3]) + "\n";
                    });
                    csv += "\n";

                    // Section 4: Payment Breakdown
                    csv += escapeCsv("--- PAYMENT METHOD BREAKDOWN ---") + "\n";
                    csv += escapeCsv("Payment Method") + "," + escapeCsv("Transactions Count") + "," + escapeCsv("Total Revenue (PHP)") + "\n";
                    reportData.payments.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "\n";
                    });
                    csv += "\n";

                    // Section 5: Cashier Performance
                    csv += escapeCsv("--- CASHIER PERFORMANCE (SHIFT ATTRIBUTION) ---") + "\n";
                    csv += escapeCsv("Cashier Name") + "," + escapeCsv("Orders Processed") + "," + escapeCsv("Total Revenue (PHP)") + "\n";
                    reportData.cashiers.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "\n";
                    });
                    csv += "\n";

                    // Section 6: Top Selling Items
                    csv += escapeCsv("--- TOP SELLING BEVERAGES & PRODUCTS ---") + "\n";
                    csv += escapeCsv("Rank") + "," + escapeCsv("Product Name") + "," + escapeCsv("Size") + "," + escapeCsv("Quantity Sold") + "," + escapeCsv("Total Revenue (PHP)") + "\n";
                    reportData.products.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "," + escapeCsv(row[3]) + "," + escapeCsv(row[4]) + "\n";
                    });

                    // Add UTF-8 BOM (\uFEFF) so Excel opens with proper character encoding
                    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `Heim_Sales_Report_{{ $dateFrom }}_to_{{ $dateTo }}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                };

                window.printSalesDataOnly = function() {
                    const printWindow = window.open('', '_blank', 'width=1000,height=750');
                    if (!printWindow) {
                        alert('Please allow popups to print report data.');
                        return;
                    }

                    const printHtml = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Heim Coffee - Sales & Revenue Report</title>
                        <style>
                            @page { size: auto; margin: 12mm 15mm; }
                            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #111827; padding: 10px; margin: 0; font-size: 11px; }
                            .header { border-bottom: 2px solid #155d49; padding-bottom: 8px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-end; }
                            .store-title { font-size: 18px; font-weight: 900; color: #155d49; letter-spacing: 0.5px; }
                            .report-title { font-size: 13px; font-weight: 700; color: #374151; margin-top: 3px; }
                            .meta { font-size: 10px; color: #6b7280; text-align: right; }
                            .section-title { font-size: 11px; font-weight: 800; color: #111827; text-transform: uppercase; letter-spacing: 0.5px; margin: 14px 0 6px 0; background: #f3f4f6; padding: 4px 8px; border-left: 3px solid #155d49; }
                            table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10.5px; }
                            th, td { border: 1px solid #d1d5db; padding: 5px 8px; text-align: left; }
                            th { background: #f9fafb; font-weight: 700; color: #374151; text-transform: uppercase; font-size: 9.5px; }
                            td.num, th.num { text-align: right; font-family: "Courier New", Courier, monospace; }
                            .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 14px; }
                            .kpi-card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 10px; background: #fafafa; }
                            .kpi-label { font-size: 9px; text-transform: uppercase; font-weight: 700; color: #6b7280; }
                            .kpi-val { font-size: 14px; font-weight: 900; color: #111827; margin-top: 2px; font-family: "Courier New", Courier, monospace; }
                            .footer { margin-top: 16px; border-top: 1px dashed #d1d5db; padding-top: 6px; font-size: 9px; color: #9ca3af; text-align: center; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <div>
                                <div class="store-title">HEIM COFFEE</div>
                                <div class="report-title">Sales & Revenue Analytics Report</div>
                            </div>
                            <div class="meta">
                                <div><strong>Period:</strong> {{ ucfirst($period) }} ({{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }})</div>
                                <div><strong>Branch:</strong> {{ $branchId ? ($branches->firstWhere('id', $branchId)?->name ?? 'Specific Branch') : 'All Branches' }}</div>
                                <div><strong>Printed:</strong> ${new Date().toLocaleString()}</div>
                            </div>
                        </div>

                        <div class="kpi-grid">
                            <div class="kpi-card">
                                <div class="kpi-label">Total Net Sales</div>
                                <div class="kpi-val" style="color: #155d49;">₱{{ number_format($totalSales, 2) }}</div>
                            </div>
                            <div class="kpi-card">
                                <div class="kpi-label">Completed Orders</div>
                                <div class="kpi-val">{{ $totalOrders }}</div>
                            </div>
                            <div class="kpi-card">
                                <div class="kpi-label">Avg Ticket Size</div>
                                <div class="kpi-val">₱{{ number_format($avgTicketSize, 2) }}</div>
                            </div>
                            <div class="kpi-card">
                                <div class="kpi-label">Total Refunds / Voids</div>
                                <div class="kpi-val" style="color: #e11d48;">₱{{ number_format($totalRefunds, 2) }}</div>
                            </div>
                        </div>

                        <div class="section-title">Branch Performance Comparison</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Branch Name</th>
                                    <th>Code</th>
                                    <th class="num">Orders Processed</th>
                                    <th class="num">Total Sales (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branchStats as $bs)
                                    <tr>
                                        <td><strong>{{ $bs['name'] }}</strong></td>
                                        <td>{{ $bs['code'] }}</td>
                                        <td class="num">{{ $bs['orders_count'] }}</td>
                                        <td class="num">₱{{ number_format($bs['total_sales'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" style="text-align:center; color:#9ca3af;">No branch records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="section-title">Order Channel Distribution</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Channel</th>
                                    <th class="num">Orders</th>
                                    <th class="num">Total Sales (PHP)</th>
                                    <th class="num">Share (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orderTypesStats as $c)
                                    <tr>
                                        <td><strong>{{ $c['label'] }}</strong></td>
                                        <td class="num">{{ $c['count'] }}</td>
                                        <td class="num">₱{{ number_format($c['total'], 2) }}</td>
                                        <td class="num">{{ $c['pct'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="section-title">Payment Method Breakdown</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Payment Method</th>
                                    <th class="num">Transactions</th>
                                    <th class="num">Total Revenue (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentBreakdown as $method => $data)
                                    <tr>
                                        <td><strong>{{ $method === 'online' ? 'Online (GCash/Maya/Card)' : 'Cash' }}</strong></td>
                                        <td class="num">{{ $data['count'] }}</td>
                                        <td class="num">₱{{ number_format($data['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" style="text-align:center; color:#9ca3af;">No payment records in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="section-title">Top Selling Beverages & Products</div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 40px;"># Rank</th>
                                    <th>Product Name</th>
                                    <th style="width: 70px;">Size</th>
                                    <th class="num" style="width: 90px;">Quantity Sold</th>
                                    <th class="num" style="width: 120px;">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bestSellers as $idx => $seller)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td><strong>{{ $seller->product_name }}</strong></td>
                                        <td>{{ $seller->size_name }}</td>
                                        <td class="num">{{ $seller->total_qty }}</td>
                                        <td class="num">₱{{ number_format($seller->total_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" style="text-align:center; color:#9ca3af;">No sales recorded in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="footer">
                            *** Heim POS Official Sales Report &bull; Printed strictly for business records ***
                        </div>
                    </body>
                    </html>
                    `;

                    printWindow.document.open();
                    printWindow.document.write(printHtml);
                    printWindow.document.close();
                    setTimeout(() => {
                        printWindow.focus();
                        printWindow.print();
                    }, 350);
                };

                // Register Google Sheet Sync Payload Getter
                document.addEventListener('DOMContentLoaded', () => {
                    if (window.registerCurrentReportSyncGetter) {
                        window.registerCurrentReportSyncGetter(() => ({
                            action: 'sync_sales',
                            report_title: 'Sales & Revenue Analytics',
                            period: "{{ ucfirst($period) }} ({{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }})",
                            data: {
                                summary: [
                                    ["Total Net Sales (PHP)", "{{ number_format($totalSales, 2, '.', '') }}"],
                                    ["Completed Orders Count", "{{ $totalOrders }}"],
                                    ["Total Items Sold", "{{ $totalItems }}"],
                                    ["Average Ticket Size (PHP)", "{{ number_format($avgTicketSize, 2, '.', '') }}"],
                                    ["Total Refunds & Voids (PHP)", "{{ number_format($totalRefunds, 2, '.', '') }}"]
                                ],
                                branches: [
                                    @foreach($branchStats as $bs)
                                    ["{{ addslashes($bs['name']) }}", "{{ $bs['orders_count'] }}", "{{ number_format($bs['total_sales'], 2, '.', '') }}"],
                                    @endforeach
                                ],
                                channels: [
                                    @foreach($orderTypesStats as $c)
                                    ["{{ $c['label'] }}", "{{ $c['count'] }}", "{{ number_format($c['total'], 2, '.', '') }}"],
                                    @endforeach
                                ],
                                payments: [
                                    @foreach($paymentBreakdown as $method => $data)
                                    ["{{ $method === 'online' ? 'Online Payment (GCash/Maya)' : 'Cash Tender' }}", "{{ $data['count'] }}", "{{ number_format($data['total'], 2, '.', '') }}"],
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
                            }
                        }));
                    }
                });
            </script>
        </div>
    </div>

    @include('reports.google-sheet-modal')
</x-app-layout>
