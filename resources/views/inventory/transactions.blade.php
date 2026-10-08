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
            <div class="flex flex-wrap items-center gap-2 no-print">
                <!-- Primary Green Button (Max One Per Page) -->
                <a href="{{ route('inventory.stock-in') }}" class="inline-flex items-center px-3.5 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Stock In
                </a>
                <!-- Secondary Outline Buttons -->
                <a href="{{ route('inventory.stock-out') }}" class="inline-flex items-center px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs rounded-xl shadow-2xs transition gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    Stock Out
                </a>
                <a href="{{ route('inventory.waste') }}" class="inline-flex items-center px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs rounded-xl shadow-2xs transition gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Record Waste
                </a>
                <!-- Unified Export Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="inline-flex items-center gap-2 h-10 px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl shadow-2xs transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Export</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-1.5 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-30">
                        <button type="button" @click="open = false; exportReportCsv('transactions-table', 'inventory-transactions-ledger.csv')" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export Excel (.csv)</span>
                        </button>
                        <button type="button" @click="open = false; printReportTable('transactions-table')" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print Ledger</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Filter Card (Live onchange, No Filter button, Sentence Case) -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('inventory.transactions') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Ingredient</label>
                        <select name="ingredient_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Ingredients</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}" {{ request('ingredient_id') == $ing->id ? 'selected' : '' }}>{{ $ing->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Transaction type</label>
                        <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Types</option>
                            <option value="stock_in" {{ request('type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                            <option value="stock_out" {{ request('type') === 'stock_out' ? 'selected' : '' }}>Stock Out</option>
                            <option value="sales_consumption" {{ request('type') === 'sales_consumption' ? 'selected' : '' }}>Sales Consumption</option>
                            <option value="waste" {{ request('type') === 'waste' ? 'selected' : '' }}>Waste / Spoilage</option>
                            <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date from</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date to</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div class="flex items-center pb-1">
                        <a href="{{ route('inventory.transactions') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 transition underline underline-offset-4">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Ledger Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table id="transactions-table" class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-700 text-xs font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Date & time</th>
                                <th class="py-3.5 px-4">Ingredient</th>
                                <th class="py-3.5 px-4">Type</th>
                                <th class="py-3.5 px-4 text-right">Quantity</th>
                                <th class="py-3.5 px-4">Recorded by</th>
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
                                            {{ $txn->type === 'stock_out' ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $txn->type === 'sales_consumption' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $txn->type === 'waste' ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ $txn->type === 'adjustment' ? 'bg-purple-100 text-purple-800' : '' }}
                                        ">
                                            {{ str_replace('_', ' ', $txn->type) }}
                                        </span>
                                    </td>
                                    @php
                                        $unit = strtolower($txn->ingredient?->unit ?? '');
                                        $qty = (float)$txn->quantity;
                                        $isWhole = ($unit === 'pcs' || $unit === 'pc' || floor($qty) == $qty);
                                        $formattedQty = $isWhole ? number_format($qty, 0) : number_format($qty, 2);
                                        $isDeduction = in_array($txn->type, ['sales_consumption', 'waste', 'stock_out']);
                                    @endphp
                                    <td class="py-3.5 px-4 text-right font-mono font-bold
                                        {{ in_array($txn->type, ['stock_out', 'waste']) ? 'text-rose-600' : ($txn->type === 'sales_consumption' ? 'text-gray-900' : 'text-[#155d49]') }}
                                    ">
                                        {{ $isDeduction ? '-' : '+' }}{{ $formattedQty }} {{ $txn->ingredient?->unit }}
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
                    <div class="p-4 border-t border-gray-100 no-print">
                        {{ $transactions->withQueryString()->links() }}
                    </div>
                @endif
            </div>

            <script>
                function exportReportCsv(tableId, fileName) {
                    const table = document.getElementById(tableId);
                    if (!table) return;

                    const rows = Array.from(table.querySelectorAll('tr')).map(row => 
                        Array.from(row.children).map(cell => '"' + (cell.textContent || '').replace(/"/g, '""').replace(/\s+/g, ' ').trim() + '"').join(',')
                    );
                    const csv = rows.join('\n');
                    // Add UTF-8 BOM
                    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', fileName);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                }

                function printReportTable(tableId) {
                    const table = document.getElementById(tableId);
                    if (!table) return;

                    const printWindow = window.open('', '_blank', 'width=950,height=700');
                    if (!printWindow) {
                        alert('Please allow pop-ups to print the report data.');
                        return;
                    }

                    const html = `
                        <html>
                            <head>
                                <title>Inventory Transactions Ledger</title>
                                <style>
                                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; padding: 20px; color: #111827; }
                                    h2 { margin: 0 0 4px 0; font-size: 18px; color: #155d49; }
                                    p { margin: 0 0 16px 0; font-size: 11px; color: #6b7280; }
                                    table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
                                    th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
                                    th { background: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 9.5px; }
                                    @media print { body { margin: 0; padding: 10px; } }
                                </style>
                            </head>
                            <body>
                                <h2>HEIM COFFEE - INVENTORY TRANSACTIONS LEDGER</h2>
                                <p>Printed on: ${new Date().toLocaleString()}</p>
                                ${table.outerHTML}
                            </body>
                        </html>
                    `;

                    printWindow.document.write(html);
                    printWindow.document.close();
                    setTimeout(() => printWindow.print(), 350);
                }
            </script>
        </div>
    </div>
</x-app-layout>
