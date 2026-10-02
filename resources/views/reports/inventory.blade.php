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
            <div class="flex items-center gap-2 no-print">
                <a href="{{ route('reports.sales') }}" class="px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition">
                    &larr; Sales Report
                </a>
                <button onclick="exportReportCsv('inventory-report-table', 'inventory-report.csv')" class="px-3.5 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                    Export CSV
                </button>
                <button onclick="printReportTable('inventory-report-table')" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Data
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('reports.inventory') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
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
                            Generate Report
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
                    <table id="inventory-report-table" class="w-full text-left text-sm">
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

        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    function exportReportCsv(tableId, fileName) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tr')).map(row => Array.from(row.children).map(cell => '"' + (cell.textContent || '').replace(/"/g, '""').trim() + '"').join(','));
        const csv = rows.join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        link.click();
        URL.revokeObjectURL(url);
    }

    function printReportTable(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const printWindow = window.open('', '_blank', 'width=900,height=700');
        if (!printWindow) {
            alert('Please allow pop-ups to print the report data.');
            return;
        }

        const html = `
            <html>
                <head>
                    <title>Report Data</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 24px; color: #111827; }
                        table { width: 100%; border-collapse: collapse; font-size: 12px; }
                        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
                        th { background: #f3f4f6; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <h2 style="margin-bottom: 16px;">Inventory Report</h2>
                    ${table.outerHTML}
                </body>
            </html>
        `;

        printWindow.document.write(html);
        printWindow.document.close();
        setTimeout(() => printWindow.print(), 500);
    }
</script>
@endpush
