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
                <input type="date" name="date" value="{{ $date }}" class="h-10 px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold text-gray-800" />
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-10 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white text-xs sm:text-sm font-semibold rounded-xl transition shadow-xs active:scale-[0.98]">
                    <span>View Date</span>
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

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
                <div class="p-5 border-b border-gray-100 bg-[#f0f8f5]/60 flex justify-between items-center no-print">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Ingredient Usage Matrix</h3>
                        <p class="text-xs text-gray-500">Summary of all recipe deductions and adjustments for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" 
                                onclick="exportCompleteConsumptionReportExcel()" 
                                class="inline-flex items-center justify-center gap-1.5 h-10 px-3.5 py-2 bg-[#107c41] hover:bg-[#0c6133] text-white font-bold text-xs sm:text-sm rounded-xl shadow-xs hover:shadow transition-all duration-150 active:scale-[0.98]">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export Excel / CSV</span>
                        </button>

                        <button type="button" 
                                onclick="printConsumptionDataOnly()" 
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

                <div class="overflow-x-auto">
                    <table id="consumption-table" class="w-full text-left text-sm">
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

            <script>
                window.exportCompleteConsumptionReportExcel = function() {
                    const reportData = {
                        title: "HEIM COFFEE - DAILY INGREDIENT CONSUMPTION MATRIX",
                        date: "{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}",
                        generatedAt: new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }),
                        ordersCount: "{{ $ordersCount }}",
                        itemsSold: "{{ $itemsSold }}",
                        items: [
                            @foreach($ingredients as $ing)
                            @php
                                $ingTxns = $transactions->get($ing->id, collect());
                                $salesUse = abs($ingTxns->where('type', 'sales_consumption')->sum('total_quantity'));
                                $wasteUse = abs($ingTxns->where('type', 'waste')->sum('total_quantity'));
                                $adjustUse = $ingTxns->where('type', 'adjustment')->sum('total_quantity');
                                $stockIn = $ingTxns->where('type', 'stock_in')->sum('total_quantity');
                                $netChange = $stockIn + $adjustUse - $salesUse - $wasteUse;
                            @endphp
                            [
                                "{{ addslashes($ing->name) }}",
                                "{{ $ing->unit }}",
                                "{{ $salesUse > 0 ? '-' . number_format($salesUse, 2, '.', '') : '0.00' }}",
                                "{{ $wasteUse > 0 ? '-' . number_format($wasteUse, 2, '.', '') : '0.00' }}",
                                "{{ ($adjustUse > 0 ? '+' : '') . number_format($adjustUse, 2, '.', '') }}",
                                "{{ $stockIn > 0 ? '+' . number_format($stockIn, 2, '.', '') : '0.00' }}",
                                "{{ ($netChange > 0 ? '+' : '') . number_format($netChange, 2, '.', '') }}",
                                "{{ number_format($ing->current_stock, 2, '.', '') }}"
                            ],
                            @endforeach
                        ]
                    };

                    const escapeCsv = (str) => '"' + String(str ?? '').replace(/"/g, '""') + '"';

                    let csv = "";
                    csv += escapeCsv(reportData.title) + "\n";
                    csv += escapeCsv("Report Date:") + "," + escapeCsv(reportData.date) + "\n";
                    csv += escapeCsv("Generated At:") + "," + escapeCsv(reportData.generatedAt) + "\n";
                    csv += escapeCsv("Orders Processed:") + "," + escapeCsv(reportData.ordersCount) + "\n";
                    csv += escapeCsv("Total Items Sold:") + "," + escapeCsv(reportData.itemsSold) + "\n\n";

                    csv += escapeCsv("--- DAILY INGREDIENT USAGE MATRIX ---") + "\n";
                    csv += escapeCsv("Ingredient Name") + "," + escapeCsv("Unit") + "," + escapeCsv("Sales Usage (-)") + "," + escapeCsv("Waste / Spoilage (-)") + "," + escapeCsv("Adjustments (+/-)") + "," + escapeCsv("Stock Received (+)") + "," + escapeCsv("Net Daily Change") + "," + escapeCsv("Current Stock") + "\n";

                    reportData.items.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "," + escapeCsv(row[3]) + "," + escapeCsv(row[4]) + "," + escapeCsv(row[5]) + "," + escapeCsv(row[6]) + "," + escapeCsv(row[7]) + "\n";
                    });

                    // Add UTF-8 BOM so Excel opens with proper column structure
                    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `Heim_Daily_Consumption_{{ $date }}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                };

                window.printConsumptionDataOnly = function() {
                    const printWindow = window.open('', '_blank', 'width=1050,height=750');
                    if (!printWindow) {
                        alert('Please allow popups to print report data.');
                        return;
                    }

                    const printHtml = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Heim Coffee - Daily Ingredient Usage Matrix</title>
                        <style>
                            @page { size: landscape; margin: 10mm; }
                            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #111827; padding: 10px; margin: 0; font-size: 11px; }
                            .header { border-bottom: 2px solid #155d49; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
                            .store-title { font-size: 18px; font-weight: 900; color: #155d49; letter-spacing: 0.5px; }
                            .report-title { font-size: 13px; font-weight: 700; color: #374151; margin-top: 3px; }
                            .meta { font-size: 10px; color: #6b7280; text-align: right; }
                            table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10.5px; }
                            th, td { border: 1px solid #d1d5db; padding: 5px 8px; text-align: left; }
                            th { background: #f3f4f6; font-weight: 700; color: #374151; text-transform: uppercase; font-size: 9.5px; }
                            td.num, th.num { text-align: right; font-family: "Courier New", Courier, monospace; }
                            td.center, th.center { text-align: center; }
                            .footer { margin-top: 16px; border-top: 1px dashed #d1d5db; padding-top: 6px; font-size: 9px; color: #9ca3af; text-align: center; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <div>
                                <div class="store-title">HEIM COFFEE</div>
                                <div class="report-title">Daily Ingredient Consumption Matrix</div>
                            </div>
                            <div class="meta">
                                <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</div>
                                <div><strong>Orders:</strong> {{ $ordersCount }} &bull; <strong>Items Sold:</strong> {{ $itemsSold }}</div>
                                <div><strong>Printed:</strong> ${new Date().toLocaleString()}</div>
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>Ingredient Name</th>
                                    <th class="center" style="width: 60px;">Unit</th>
                                    <th class="num">Sales Usage (-)</th>
                                    <th class="num">Waste / Loss (-)</th>
                                    <th class="num">Adjustments (+/-)</th>
                                    <th class="num">Stock Received (+)</th>
                                    <th class="num">Net Change</th>
                                    <th class="num">Current Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ingredients as $ing)
                                    @php
                                        $ingTxns = $transactions->get($ing->id, collect());
                                        $salesUse = abs($ingTxns->where('type', 'sales_consumption')->sum('total_quantity'));
                                        $wasteUse = abs($ingTxns->where('type', 'waste')->sum('total_quantity'));
                                        $adjustUse = $ingTxns->where('type', 'adjustment')->sum('total_quantity');
                                        $stockIn = $ingTxns->where('type', 'stock_in')->sum('total_quantity');
                                        $netChange = $stockIn + $adjustUse - $salesUse - $wasteUse;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $ing->name }}</strong></td>
                                        <td class="center">{{ $ing->unit }}</td>
                                        <td class="num">{{ $salesUse > 0 ? '-' . number_format($salesUse, 2) : '0.00' }}</td>
                                        <td class="num">{{ $wasteUse > 0 ? '-' . number_format($wasteUse, 2) : '0.00' }}</td>
                                        <td class="num">{{ ($adjustUse > 0 ? '+' : '') . number_format($adjustUse, 2) }}</td>
                                        <td class="num">{{ $stockIn > 0 ? '+' . number_format($stockIn, 2) : '0.00' }}</td>
                                        <td class="num"><strong>{{ ($netChange > 0 ? '+' : '') . number_format($netChange, 2) }}</strong></td>
                                        <td class="num"><strong>{{ number_format($ing->current_stock, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="footer">
                            *** Heim POS Official Ingredient Usage Matrix &bull; Printed strictly for business records ***
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
                            action: 'sync_consumption',
                            report_title: 'Daily Ingredient Usage Matrix',
                            period: "{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}",
                            data: {
                                items: [
                                    @foreach($ingredients as $ing)
                                    @php
                                        $ingTxns = $transactions->get($ing->id, collect());
                                        $salesUse = abs($ingTxns->where('type', 'sales_consumption')->sum('total_quantity'));
                                        $wasteUse = abs($ingTxns->where('type', 'waste')->sum('total_quantity'));
                                        $adjustUse = $ingTxns->where('type', 'adjustment')->sum('total_quantity');
                                        $stockIn = $ingTxns->where('type', 'stock_in')->sum('total_quantity');
                                        $netChange = $stockIn + $adjustUse - $salesUse - $wasteUse;
                                    @endphp
                                    [
                                        "{{ addslashes($ing->name) }}",
                                        "{{ $ing->unit }}",
                                        "{{ $salesUse > 0 ? '-' . number_format($salesUse, 2, '.', '') : '0.00' }}",
                                        "{{ $wasteUse > 0 ? '-' . number_format($wasteUse, 2, '.', '') : '0.00' }}",
                                        "{{ ($adjustUse > 0 ? '+' : '') . number_format($adjustUse, 2, '.', '') }}",
                                        "{{ $stockIn > 0 ? '+' . number_format($stockIn, 2, '.', '') : '0.00' }}",
                                        "{{ ($netChange > 0 ? '+' : '') . number_format($netChange, 2, '.', '') }}",
                                        "{{ number_format($ing->current_stock, 2, '.', '') }}"
                                    ],
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
