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
            <!-- HCI Theory: Single prominent primary action button (Hick's Law & Fitts's Law) -->
            <div class="flex items-center no-print">
                <button type="button" 
                        onclick="exportCompleteInventoryReportExcel()" 
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

            <!-- Movement Summary Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-[#f0f8f5]/60 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 text-base">Period Stock Movement Summary</h3>
                    <span class="text-xs font-bold text-[#155d49]">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Ingredient Name</th>
                                <th class="py-3.5 px-4 text-center">Unit</th>
                                <th class="py-3.5 px-4 text-right text-[#155d49]">Stock Received</th>
                                <th class="py-3.5 px-4 text-right text-blue-700">Sales Consumption</th>
                                <th class="py-3.5 px-4 text-right text-rose-700">Spoilage / Waste</th>
                                <th class="py-3.5 px-4 text-right text-purple-700">Net Adjustments</th>
                                <th class="py-3.5 px-4 text-right">Current Stock</th>
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
            </script>
        </div>
    </div>
</x-app-layout>
