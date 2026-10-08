    <x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Inventory Status & Movement Report') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Periodic summary of stock on hand, incoming deliveries, waste, and recipe consumption
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
                        <button type="button" @click="open = false; exportCompleteInventoryReportExcel()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export Excel (.csv)</span>
                        </button>
                        <button type="button" @click="open = false; printInventoryDataOnly()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
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
                   class="inline-flex items-center h-9 px-4 rounded-xl text-xs font-semibold bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 transition">
                    Sales & Revenue
                </a>
                <a href="{{ route('reports.inventory') }}" 
                   class="inline-flex items-center h-9 px-4 rounded-xl text-xs font-bold bg-[#155d49] text-white shadow-xs">
                    Inventory Movement
                </a>
            </div>

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('reports.inventory') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date from</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date to</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium" />
                    </div>

                    <div class="flex items-center pb-1">
                        <a href="{{ route('reports.inventory') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 transition underline underline-offset-4">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Movement Summary Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-[#f0f8f5]/60 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-base">Period Stock Movement Summary</h3>
                    <span class="text-xs font-bold text-[#155d49]">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-700 text-xs font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Ingredient name</th>
                                <th class="py-3.5 px-4 text-center">Unit</th>
                                <th class="py-3.5 px-4 text-right text-[#155d49]">Stock received</th>
                                <th class="py-3.5 px-4 text-right text-blue-700">Sales consumption</th>
                                <th class="py-3.5 px-4 text-right text-rose-700">Spoilage / waste</th>
                                <th class="py-3.5 px-4 text-right text-purple-700">Net adjustments</th>
                                <th class="py-3.5 px-4 text-right">Current stock</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($ingredients as $ing)
                                @php
                                    $txns = $transactionSummary->get($ing->id, collect());
                                    $stockIn = $txns->where('type', 'stock_in')->sum('total_quantity');
                                    $sales = abs($txns->where('type', 'sales_consumption')->sum('total_quantity'));
                                    $waste = abs($txns->where('type', 'waste')->sum('total_quantity'));
                                    $adjust = $txns->where('type', 'adjustment')->sum('total_quantity');
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        {{ $ing->name }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-md bg-[#f0f8f5] text-[#155d49] text-xs font-bold">
                                            {{ $ing->unit }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $stockIn > 0 ? 'text-[#155d49] font-bold' : 'text-gray-400' }}">
                                        {{ $stockIn > 0 ? '+' . number_format($stockIn, 2) : '0.00' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $sales > 0 ? 'text-blue-600 font-bold' : 'text-gray-400' }}">
                                        {{ $sales > 0 ? '-' . number_format($sales, 2) : '0.00' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $waste > 0 ? 'text-rose-600 font-bold' : 'text-gray-400' }}">
                                        {{ $waste > 0 ? '-' . number_format($waste, 2) : '0.00' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $adjust != 0 ? 'text-purple-600 font-bold' : 'text-gray-400' }}">
                                        {{ $adjust > 0 ? '+' : '' }}{{ number_format($adjust, 2) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900 text-sm">
                                        {{ number_format($ing->current_stock, 2) }} {{ $ing->unit }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($ing->current_stock <= 0)
                                            <span class="px-2.5 py-0.5 text-[11px] rounded-full font-bold bg-rose-100 text-rose-800">
                                                Out of Stock
                                            </span>
                                        @elseif($ing->current_stock <= $ing->minimum_stock)
                                            <span class="px-2.5 py-0.5 text-[11px] rounded-full font-bold bg-amber-100 text-amber-800">
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-[11px] rounded-full font-bold bg-emerald-100 text-[#155d49]">
                                                In Stock
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                window.exportCompleteInventoryReportExcel = function() {
                    const reportData = {
                        title: "HEIM COFFEE - INVENTORY STATUS & MOVEMENT REPORT",
                        period: "{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}",
                        generatedAt: new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }),
                        items: [
                            @foreach($ingredients as $ing)
                            @php
                                $txns = $transactionSummary->get($ing->id, collect());
                                $stockIn = $txns->where('type', 'stock_in')->sum('total_quantity');
                                $sales = abs($txns->where('type', 'sales_consumption')->sum('total_quantity'));
                                $waste = abs($txns->where('type', 'waste')->sum('total_quantity'));
                                $adjust = $txns->where('type', 'adjustment')->sum('total_quantity');
                                $status = $ing->current_stock <= 0 ? 'Out of Stock' : ($ing->current_stock <= $ing->minimum_stock ? 'Low Stock' : 'In Stock');
                            @endphp
                            [
                                "{{ addslashes($ing->name) }}",
                                "{{ $ing->unit }}",
                                "{{ $stockIn > 0 ? '+' . number_format($stockIn, 2, '.', '') : '0.00' }}",
                                "{{ $sales > 0 ? '-' . number_format($sales, 2, '.', '') : '0.00' }}",
                                "{{ $waste > 0 ? '-' . number_format($waste, 2, '.', '') : '0.00' }}",
                                "{{ ($adjust > 0 ? '+' : '') . number_format($adjust, 2, '.', '') }}",
                                "{{ number_format($ing->current_stock, 2, '.', '') }}",
                                "{{ $status }}"
                            ],
                            @endforeach
                        ]
                    };

                    const escapeCsv = (str) => '"' + String(str ?? '').replace(/"/g, '""') + '"';

                    let csv = "";
                    csv += escapeCsv(reportData.title) + "\n";
                    csv += escapeCsv("Date Range:") + "," + escapeCsv(reportData.period) + "\n";
                    csv += escapeCsv("Generated At:") + "," + escapeCsv(reportData.generatedAt) + "\n\n";

                    csv += escapeCsv("--- PERIOD STOCK MOVEMENT SUMMARY ---") + "\n";
                    csv += escapeCsv("Ingredient Name") + "," + escapeCsv("Unit") + "," + escapeCsv("Stock Received (+)") + "," + escapeCsv("Sales Consumption (-)") + "," + escapeCsv("Spoilage / Waste (-)") + "," + escapeCsv("Net Adjustments") + "," + escapeCsv("Current Stock") + "," + escapeCsv("Status") + "\n";

                    reportData.items.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "," + escapeCsv(row[3]) + "," + escapeCsv(row[4]) + "," + escapeCsv(row[5]) + "," + escapeCsv(row[6]) + "," + escapeCsv(row[7]) + "\n";
                    });

                    // Add UTF-8 BOM so Excel opens with proper characters and column delimiters
                    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `Heim_Inventory_Report_{{ $dateFrom }}_to_{{ $dateTo }}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                };

                window.printInventoryDataOnly = function() {
                    const printWindow = window.open('', '_blank', 'width=1050,height=750');
                    if (!printWindow) {
                        alert('Please allow popups to print report data.');
                        return;
                    }

                    const printHtml = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Heim Coffee - Inventory Movement Report</title>
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
                            .badge { font-weight: bold; font-size: 9px; padding: 2px 6px; border-radius: 4px; display: inline-block; }
                            .badge-in { background: #d1fae5; color: #065f46; }
                            .badge-low { background: #fef3c7; color: #92400e; }
                            .badge-out { background: #fee2e2; color: #991b1b; }
                            .footer { margin-top: 16px; border-top: 1px dashed #d1d5db; padding-top: 6px; font-size: 9px; color: #9ca3af; text-align: center; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <div>
                                <div class="store-title">HEIM COFFEE</div>
                                <div class="report-title">Inventory Status & Stock Movement Report</div>
                            </div>
                            <div class="meta">
                                <div><strong>Period:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</div>
                                <div><strong>Printed:</strong> ${new Date().toLocaleString()}</div>
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>Ingredient Name</th>
                                    <th class="center" style="width: 60px;">Unit</th>
                                    <th class="num">Stock In (+)</th>
                                    <th class="num">Sales Deductions (-)</th>
                                    <th class="num">Waste / Loss (-)</th>
                                    <th class="num">Adjustments (+/-)</th>
                                    <th class="num">Current Stock</th>
                                    <th class="center" style="width: 90px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ingredients as $ing)
                                    @php
                                        $txns = $transactionSummary->get($ing->id, collect());
                                        $stockIn = $txns->where('type', 'stock_in')->sum('total_quantity');
                                        $sales = abs($txns->where('type', 'sales_consumption')->sum('total_quantity'));
                                        $waste = abs($txns->where('type', 'waste')->sum('total_quantity'));
                                        $adjust = $txns->where('type', 'adjustment')->sum('total_quantity');
                                        $statusClass = $ing->current_stock <= 0 ? 'badge-out' : ($ing->current_stock <= $ing->minimum_stock ? 'badge-low' : 'badge-in');
                                        $statusLabel = $ing->current_stock <= 0 ? 'Out of Stock' : ($ing->current_stock <= $ing->minimum_stock ? 'Low Stock' : 'In Stock');
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $ing->name }}</strong></td>
                                        <td class="center">{{ $ing->unit }}</td>
                                        <td class="num">{{ $stockIn > 0 ? '+' . number_format($stockIn, 2) : '0.00' }}</td>
                                        <td class="num">{{ $sales > 0 ? '-' . number_format($sales, 2) : '0.00' }}</td>
                                        <td class="num">{{ $waste > 0 ? '-' . number_format($waste, 2) : '0.00' }}</td>
                                        <td class="num">{{ ($adjust > 0 ? '+' : '') . number_format($adjust, 2) }}</td>
                                        <td class="num"><strong>{{ number_format($ing->current_stock, 2) }}</strong></td>
                                        <td class="center"><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="footer">
                            *** Heim POS Official Inventory Movement Ledger &bull; Printed strictly for business records ***
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
                            action: 'sync_inventory',
                            report_title: 'Inventory Status & Movement Report',
                            period: "{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}",
                            data: {
                                items: [
                                    @foreach($ingredients as $ing)
                                    @php
                                        $txns = $transactionSummary->get($ing->id, collect());
                                        $stockIn = $txns->where('type', 'stock_in')->sum('total_quantity');
                                        $sales = abs($txns->where('type', 'sales_consumption')->sum('total_quantity'));
                                        $waste = abs($txns->where('type', 'waste')->sum('total_quantity'));
                                        $adjust = $txns->where('type', 'adjustment')->sum('total_quantity');
                                        $status = $ing->current_stock <= 0 ? 'Out of Stock' : ($ing->current_stock <= $ing->minimum_stock ? 'Low Stock' : 'In Stock');
                                    @endphp
                                    [
                                        "{{ addslashes($ing->name) }}",
                                        "{{ $ing->unit }}",
                                        "{{ $stockIn > 0 ? '+' . number_format($stockIn, 2, '.', '') : '0.00' }}",
                                        "{{ $sales > 0 ? '-' . number_format($sales, 2, '.', '') : '0.00' }}",
                                        "{{ $waste > 0 ? '-' . number_format($waste, 2, '.', '') : '0.00' }}",
                                        "{{ ($adjust > 0 ? '+' : '') . number_format($adjust, 2, '.', '') }}",
                                        "{{ number_format($ing->current_stock, 2, '.', '') }}",
                                        "{{ $status }}"
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
