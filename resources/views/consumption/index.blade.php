<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Daily Ingredient Consumption') }}
                </h2>
                <p class="text-xs text-gray-600 font-medium mt-0.5">
                    Track daily raw ingredient usage from sales, waste, and recipe deductions
                </p>
            </div>

            <!-- Simplified Date Control with Prev/Next and Today (Consumption #6, G2) -->
            <form method="GET" action="{{ route('consumption.index') }}" class="flex items-center gap-1.5 bg-white p-1 rounded-2xl border border-gray-200 shadow-2xs">
                @php
                    $carbonDate = \Carbon\Carbon::parse($date);
                    $prevDate = $carbonDate->copy()->subDay()->format('Y-m-d');
                    $nextDate = $carbonDate->copy()->addDay()->format('Y-m-d');
                    $isToday = $carbonDate->isToday();
                @endphp
                <a href="{{ route('consumption.index', ['date' => $prevDate]) }}" 
                   class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition" 
                   title="Previous day ({{ \Carbon\Carbon::parse($prevDate)->format('M d') }})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>

                <input type="date" 
                       name="date" 
                       value="{{ $date }}" 
                       onchange="this.form.submit()" 
                       class="h-9 px-2.5 py-1 text-xs sm:text-sm border-0 focus:ring-0 outline-none font-bold text-gray-800 cursor-pointer" />

                <a href="{{ route('consumption.index', ['date' => $nextDate]) }}" 
                   class="p-2 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition" 
                   title="Next day ({{ \Carbon\Carbon::parse($nextDate)->format('M d') }})">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                @if(!$isToday)
                    <a href="{{ route('consumption.index', ['date' => today()->format('Y-m-d')]) }}" 
                       class="px-2.5 py-1 text-xs font-bold bg-[#f0f8f5] text-[#0e703c] rounded-xl hover:bg-emerald-100 transition mr-1">
                        Today
                    </a>
                @endif
            </form>
        </div>
    </x-slot>

    @php
        // Calculate active vs unused ingredients for summary cards
        $activeCount = 0;
        $totalWasteCount = 0;
        foreach ($ingredients as $ing) {
            $txns = $transactions->get($ing->id, collect());
            if ($txns->sum('total_quantity') != 0) {
                $activeCount++;
            }
            if ($txns->where('type', 'waste')->sum('total_quantity') > 0) {
                $totalWasteCount++;
            }
        }
    @endphp

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Summary KPI Header Cards (Consumption #5: Replaced redundant date card, pluralized grammar) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-gray-500">Orders Processed</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-1">{{ pluralize($ordersCount, 'order') }}</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Completed customer sales</p>
                    </div>
                    <div class="p-3.5 bg-blue-50 text-blue-700 rounded-2xl border border-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H9a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-gray-500">Items / Cups Sold</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-1">{{ pluralize($itemsSold, 'item') }}</h3>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Total beverages & food</p>
                    </div>
                    <div class="p-3.5 bg-emerald-50 text-[#0e703c] rounded-2xl border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>

                <!-- Useful Metric Card replacing redundant Date Selected card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-gray-500">Active Ingredients</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $activeCount }} of {{ $ingredients->count() }}</h3>
                        <p class="text-xs text-[#0e703c] font-semibold mt-0.5">{{ $totalWasteCount > 0 ? pluralize($totalWasteCount, 'waste event') . ' logged' : 'No waste events logged' }}</p>
                    </div>
                    <div class="p-3.5 bg-amber-50 text-amber-700 rounded-2xl border border-amber-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
            </div>

            <!-- Detailed Consumption Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Toolbar: Hide Zero Toggle & Quiet Export Menu (Consumption #1 & #4) -->
                <div class="p-4 border-b border-gray-100 bg-[#f0f8f5]/60 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 no-print">
                    <div class="flex flex-wrap items-center gap-4">
                        <div>
                            <h3 class="font-extrabold text-gray-900 text-base">Ingredient Usage Matrix</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Recipe deductions, waste, and net changes for {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
                        </div>

                        <!-- Toggle: Show unused ingredients (Off by default - Consumption #1) -->
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none bg-white px-3 py-1.5 rounded-xl border border-gray-200 shadow-2xs hover:bg-gray-50 transition">
                            <input type="checkbox" id="toggle-unused-ingredients" onchange="toggleUnusedRows(this.checked)" class="sr-only peer">
                            <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-[#0e703c] relative"></div>
                            <span class="text-xs font-semibold text-gray-700" id="unused-toggle-label">Show unused ingredients</span>
                        </label>
                    </div>

                    <!-- Consolidated Export Dropdown (Secondary Outline) -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.outside="open = false" class="inline-flex items-center gap-2 h-9 px-3.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs rounded-xl shadow-2xs transition">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export</span>
                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak class="absolute right-0 mt-1.5 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-30">
                            <button type="button" @click="open = false; exportCompleteConsumptionReportExcel()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Export Excel (.csv)</span>
                            </button>
                            <button type="button" @click="open = false; printConsumptionDataOnly()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print Matrix</span>
                            </button>
                            <div class="border-t border-gray-100 my-1"></div>
                            <button type="button" @click="open = false; openGoogleSheetModal()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                                <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm4-4H6v-2h10v2zm0-4H6V7h10v2z"/></svg>
                                <span>Sync Google Sheet</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table with Sticky Header and Accessible Legend (Consumption #1, #2, #3) -->
                <div class="overflow-x-auto max-h-[620px]">
                    <table id="consumption-table" class="w-full text-left text-sm">
                        <thead class="sticky top-0 bg-gray-50/95 backdrop-blur-xs text-gray-700 text-xs font-bold border-b border-gray-200 z-10 shadow-2xs">
                            <tr>
                                <th class="py-3 px-4 font-bold">Ingredient</th>
                                <th class="py-3 px-3 text-center font-bold">Unit</th>
                                <th class="py-3 px-4 text-right font-bold" title="Routine sales deductions from recipes">🛒 Sales usage</th>
                                <th class="py-3 px-4 text-right font-bold" title="Flagged waste, spoilage, or spills">🗑 Waste / loss</th>
                                <th class="py-3 px-4 text-right font-bold" title="Manual audit adjustments">⚖️ Adjustments</th>
                                <th class="py-3 px-4 text-right font-bold" title="New inventory received">📥 Stock received</th>
                                <th class="py-3 px-4 text-right font-bold" title="Net change today">📊 Net change</th>
                                <th class="py-3 px-4 text-right font-bold" title="Current physical balance">📦 Current stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($ingredients as $ing)
                                @php
                                    $isPcs = strtolower($ing->unit) === 'pcs';
                                    $fmt = fn($val) => $isPcs ? number_format($val, 0) : number_format($val, 2);

                                    $ingTxns = $transactions->get($ing->id, collect());
                                    $salesUse = abs($ingTxns->where('type', 'sales_consumption')->sum('total_quantity'));
                                    $wasteUse = abs($ingTxns->where('type', 'waste')->sum('total_quantity'));
                                    $adjustUse = $ingTxns->where('type', 'adjustment')->sum('total_quantity');
                                    $stockIn = $ingTxns->where('type', 'stock_in')->sum('total_quantity');
                                    $netChange = $stockIn + $adjustUse - $salesUse - $wasteUse;

                                    $hasActivity = ($salesUse > 0 || $wasteUse > 0 || $adjustUse != 0 || $stockIn > 0);
                                @endphp
                                <tr class="consumption-row transition {{ !$hasActivity ? 'unused-ingredient-row hidden bg-gray-50/30' : 'hover:bg-gray-50/80' }}"
                                    data-active="{{ $hasActivity ? '1' : '0' }}">
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        <div class="flex items-center gap-2">
                                            @if($hasActivity)
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-gray-300 shrink-0"></span>
                                            @endif
                                            <span>{{ $ing->name }}</span>
                                        </div>
                                        @if(isset($productBreakdown[$ing->id]) && $productBreakdown[$ing->id]->count() > 0)
                                            <div class="text-[11px] text-[#0e703c] font-normal mt-0.5 pl-4">
                                                Used in: 
                                                @foreach($productBreakdown[$ing->id] as $pItem)
                                                    <span>{{ $pItem->total_qty }}x {{ $pItem->product_name }} ({{ $pItem->size_name }}){{ !$loop->last ? ',' : '' }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-md bg-[#f0f8f5] text-[#0e703c] text-xs font-bold">
                                            {{ $ing->unit }}
                                        </span>
                                    </td>

                                    <!-- Sales Usage: Neutral dark number (Consumption #2) -->
                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $salesUse > 0 ? 'text-gray-900 font-bold' : 'text-gray-400' }}">
                                        {{ $salesUse > 0 ? '-' . $fmt($salesUse) : '0' }}
                                    </td>

                                    <!-- Waste: Red only for waste & loss (Consumption #2) -->
                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $wasteUse > 0 ? 'text-rose-600 font-black' : 'text-gray-400' }}">
                                        {{ $wasteUse > 0 ? '-' . $fmt($wasteUse) : '0' }}
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $adjustUse != 0 ? 'text-purple-700 font-bold' : 'text-gray-400' }}">
                                        {{ $adjustUse > 0 ? '+' : '' }}{{ $adjustUse != 0 ? $fmt($adjustUse) : '0' }}
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono text-sm {{ $stockIn > 0 ? 'text-[#0e703c] font-bold' : 'text-gray-400' }}">
                                        {{ $stockIn > 0 ? '+' . $fmt($stockIn) : '0' }}
                                    </td>

                                    <!-- Net Change: Neutral or positive green -->
                                    <td class="py-3.5 px-4 text-right font-mono text-sm font-black {{ $netChange < 0 ? 'text-gray-800' : ($netChange > 0 ? 'text-[#0e703c]' : 'text-gray-400') }}">
                                        {{ $netChange > 0 ? '+' : '' }}{{ $netChange != 0 ? $fmt($netChange) : '0' }}
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900 text-sm">
                                        {{ $fmt($ing->current_stock) }} {{ $ing->unit }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-gray-50/80 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span id="consumption-visible-count">Showing {{ $activeCount }} active ingredients</span>
                    <span class="text-[11px] text-gray-400">All data generated from verified order recipes and inventory transactions</span>
                </div>
            </div>

            <script>
                // Toggle Unused Rows (Consumption #1)
                function toggleUnusedRows(show) {
                    const unusedRows = document.querySelectorAll('.unused-ingredient-row');
                    unusedRows.forEach(row => {
                        if (show) {
                            row.classList.remove('hidden');
                        } else {
                            row.classList.add('hidden');
                        }
                    });

                    const label = document.getElementById('unused-toggle-label');
                    const countEl = document.getElementById('consumption-visible-count');
                    const totalIngs = {{ $ingredients->count() }};
                    const activeIngs = {{ $activeCount }};

                    if (label) {
                        label.innerText = show ? 'Hide unused ingredients' : 'Show unused ingredients';
                    }
                    if (countEl) {
                        countEl.innerText = show ? `Showing all ${totalIngs} ingredients` : `Showing ${activeIngs} active ingredients`;
                    }
                }

                // Excel Export
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

                // Print
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
                            .header { border-bottom: 2px solid #0e703c; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
                            .store-title { font-size: 18px; font-weight: 900; color: #0e703c; letter-spacing: 0.5px; }
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
