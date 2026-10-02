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
            <!-- Action buttons: Export Excel, Print Data Only, and Google Sheet Sync -->
            <div class="flex flex-wrap items-center gap-2 no-print">
                <button type="button" 
                        onclick="exportCompleteSalesReportExcel()" 
                        class="inline-flex items-center justify-center gap-1.5 h-10 px-3.5 py-2 bg-[#107c41] hover:bg-[#0c6133] text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs hover:shadow transition-all duration-150 active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Excel / CSV</span>
                </button>

                <button type="button" 
                        onclick="printSalesDataOnly()" 
                        class="inline-flex items-center justify-center gap-1.5 h-10 px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs hover:shadow transition-all duration-150 active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print Data Only</span>
                </button>

                <button type="button" 
                        onclick="openGoogleSheetModal()" 
                        id="btn-report-sync-google"
                        title="Auto-sync to Google Sheet when online"
                        class="inline-flex items-center justify-center gap-1.5 h-10 px-3.5 py-2 bg-[#0f9d58] hover:bg-[#0b8043] text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs hover:shadow transition-all duration-150 active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm4-4H6v-2h10v2zm0-4H6V7h10v2z"/>
                    </svg>
                    <span>Sync Google Sheet</span>
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

            <script>
                window.exportCompleteSalesReportExcel = function() {
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

                    const escapeCsv = (str) => '"' + String(str ?? '').replace(/"/g, '""') + '"';

                    let csv = "";
                    csv += escapeCsv(reportData.title) + "\n";
                    csv += escapeCsv("Reporting Period:") + "," + escapeCsv(reportData.period) + "\n";
                    csv += escapeCsv("Generated At:") + "," + escapeCsv(reportData.generatedAt) + "\n\n";

                    // Section 1: Executive KPI Summary
                    csv += escapeCsv("--- EXECUTIVE KPI SUMMARY ---") + "\n";
                    csv += escapeCsv("Metric") + "," + escapeCsv("Value") + "\n";
                    reportData.summary.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "\n";
                    });
                    csv += "\n";

                    // Section 2: Payment Breakdown
                    csv += escapeCsv("--- PAYMENT METHOD BREAKDOWN ---") + "\n";
                    csv += escapeCsv("Payment Method") + "," + escapeCsv("Transactions Count") + "," + escapeCsv("Total Revenue (PHP)") + "\n";
                    reportData.payments.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "\n";
                    });
                    csv += "\n";

                    // Section 3: Cashier Performance
                    csv += escapeCsv("--- CASHIER PERFORMANCE (SHIFT ATTRIBUTION) ---") + "\n";
                    csv += escapeCsv("Cashier Name") + "," + escapeCsv("Orders Processed") + "," + escapeCsv("Total Revenue (PHP)") + "\n";
                    reportData.cashiers.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "\n";
                    });
                    csv += "\n";

                    // Section 4: Top Selling Items
                    csv += escapeCsv("--- TOP SELLING BEVERAGES & PRODUCTS ---") + "\n";
                    csv += escapeCsv("Rank") + "," + escapeCsv("Product Name") + "," + escapeCsv("Size") + "," + escapeCsv("Quantity Sold") + "," + escapeCsv("Total Revenue (PHP)") + "\n";
                    reportData.products.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "," + escapeCsv(row[3]) + "," + escapeCsv(row[4]) + "\n";
                    });

                    // Add UTF-8 BOM (\uFEFF) so Excel opens with proper character encoding and formatting
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
                                <div class="kpi-label">Total Items Sold</div>
                                <div class="kpi-val">{{ $totalItems }}</div>
                            </div>
                            <div class="kpi-card">
                                <div class="kpi-label">Total Refunds</div>
                                <div class="kpi-val" style="color: #e11d48;">₱{{ number_format($totalRefunds, 2) }}</div>
                            </div>
                        </div>

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
                                        <td><strong>{{ in_array($method, ['online', 'gcash']) ? 'Online (GCash/Maya)' : ucfirst($method) }}</strong></td>
                                        <td class="num">{{ $data['count'] }}</td>
                                        <td class="num">₱{{ number_format($data['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" style="text-align:center; color:#9ca3af;">No payment records in this period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="section-title">Cashier Sales Performance (Shift Attribution)</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Cashier Name</th>
                                    <th class="num">Orders Processed</th>
                                    <th class="num">Total Revenue (PHP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesByCashier as $cashier => $data)
                                    <tr>
                                        <td><strong>{{ $cashier }}</strong></td>
                                        <td class="num">{{ $data['orders'] }}</td>
                                        <td class="num">₱{{ number_format($data['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" style="text-align:center; color:#9ca3af;">No cashier records in this period.</td></tr>
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
                            }
                        }));
                    }
                });
            </script>
        </div>
    </div>

    @include('reports.google-sheet-modal')
</x-app-layout>
