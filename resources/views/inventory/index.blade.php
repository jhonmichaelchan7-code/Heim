<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Inventory Management') }}
                </h2>
                <p class="text-xs text-gray-600 font-medium mt-0.5">
                    Monitor raw ingredients, stock levels, threshold warnings, and transactions
                </p>
            </div>
            
            <!-- Unified Header Actions: Stock Actions dropdown + Single Primary Button (G1, Inventory #1) -->
            <div class="flex items-center gap-2.5">
                <!-- Stock Actions Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" 
                            @click="open = !open" 
                            @click.away="open = false"
                            class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl border border-gray-200 shadow-2xs transition active:scale-[0.98]">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Stock actions</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-1.5 z-50 text-xs font-semibold" 
                         style="display: none;">
                        <a href="{{ route('inventory.stock-in') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-[#f0f8f5] hover:text-[#0e703c] transition">
                            <span class="w-5 text-center text-emerald-600 font-bold">＋</span>
                            <span>Stock In</span>
                        </a>
                        <a href="{{ route('inventory.stock-out') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                            <span class="w-5 text-center text-gray-500 font-bold">－</span>
                            <span>Stock Out</span>
                        </a>
                        <a href="{{ route('inventory.waste') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-rose-50 hover:text-rose-700 transition">
                            <span class="w-5 text-center text-rose-500">🗑</span>
                            <span>Record Waste</span>
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="{{ route('inventory.transactions') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                            <span class="w-5 text-center text-gray-500">📋</span>
                            <span>Ledger Logs</span>
                        </a>
                    </div>
                </div>

                <!-- Single Primary Green Button (G1) -->
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#0e703c] hover:bg-[#0b5930] text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add ingredient</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Summary Filter Chips & Live Search (G2, Inventory #2 & #5) -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 space-y-3.5">
                <!-- Status Quick Summary Chips -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('inventory.index', array_filter(['search' => request('search')])) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs {{ !request('status') ? 'bg-[#0e703c] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            All ({{ $totalCount ?? $ingredients->total() }})
                        </a>

                        <a href="{{ route('inventory.index', array_filter(['status' => 'low_stock', 'search' => request('search')])) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs flex items-center gap-1.5 {{ request('status') === 'low_stock' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100' }}">
                            <span>⚠️</span>
                            <span>{{ $lowStockCount ?? 0 }} low</span>
                        </a>

                        <a href="{{ route('inventory.index', array_filter(['status' => 'out_of_stock', 'search' => request('search')])) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs flex items-center gap-1.5 {{ request('status') === 'out_of_stock' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-900 border border-rose-200 hover:bg-rose-100' }}">
                            <span>🚨</span>
                            <span>{{ $outOfStockCount ?? 0 }} out of stock</span>
                        </a>

                        <a href="{{ route('inventory.index', array_filter(['status' => 'good', 'search' => request('search')])) }}" 
                           class="px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-2xs {{ request('status') === 'good' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-900 border border-emerald-200 hover:bg-emerald-100' }}">
                            Good standing
                        </a>
                    </div>

                    <!-- Result Count Text -->
                    <div class="text-xs font-semibold text-gray-600" id="inventory-result-count">
                        Showing {{ $ingredients->total() }} {{ $ingredients->total() === 1 ? 'ingredient' : 'ingredients' }}
                    </div>
                </div>

                <!-- Live Search and Status Filter (No Apply Filter button - G2) -->
                <form id="inventory-filter-form" method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                    <div class="sm:col-span-8 relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="inventory-search-input"
                               value="{{ request('search') }}" 
                               placeholder="Search ingredient by name..." 
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] focus:border-[#0e703c] outline-none font-medium placeholder-gray-500 shadow-2xs" />
                    </div>

                    <div class="sm:col-span-3">
                        <select name="status" 
                                id="inventory-status-select"
                                onchange="document.getElementById('inventory-filter-form').submit()" 
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] outline-none font-medium text-gray-700 shadow-2xs">
                            <option value="">All statuses</option>
                            <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low stock</option>
                            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                            <option value="good" {{ request('status') === 'good' ? 'selected' : '' }}>Good standing</option>
                        </select>
                    </div>

                    <div class="sm:col-span-1 text-right">
                        @if(request('search') || request('status'))
                            <a href="{{ route('inventory.index') }}" class="text-xs font-bold text-gray-600 hover:text-rose-600 transition underline">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Ingredients Table Card (Inventory #2, #3, #4, G5) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/80 text-gray-600 text-xs font-semibold border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-4 font-bold">Ingredient name</th>
                                <th class="py-3.5 px-4 text-right font-bold w-64">Current stock</th>
                                <th class="py-3.5 px-4 text-right font-bold">Min threshold</th>
                                <th class="py-3.5 px-4 text-center font-bold">Status</th>
                                <th class="py-3.5 px-4 text-right font-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ingredients as $ingredient)
                                @php
                                    $isPcs = strtolower($ingredient->unit) === 'pcs';
                                    $currFormatted = $isPcs ? number_format($ingredient->current_stock, 0) : number_format($ingredient->current_stock, 2);
                                    $minFormatted = $isPcs ? number_format($ingredient->minimum_stock, 0) : number_format($ingredient->minimum_stock, 2);

                                    $isOutOfStock = $ingredient->current_stock <= 0;
                                    $isLowStock = !$isOutOfStock && $ingredient->current_stock <= $ingredient->minimum_stock;
                                    $isNearThreshold = !$isOutOfStock && !$isLowStock && ($ingredient->current_stock <= $ingredient->minimum_stock * 1.5);

                                    // Progress bar calculation
                                    $maxRef = max(1, $ingredient->minimum_stock * 2);
                                    $pct = $isOutOfStock ? 0 : min(100, round(($ingredient->current_stock / $maxRef) * 100));

                                    // Row background highlighting (Inventory #2)
                                    $rowBg = $isOutOfStock ? 'bg-rose-50/40 hover:bg-rose-50/70' : ($isLowStock ? 'bg-amber-50/40 hover:bg-amber-50/70' : 'hover:bg-gray-50/80');
                                @endphp
                                <tr class="{{ $rowBg }} transition">
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        <div class="flex items-center gap-2">
                                            @if($isOutOfStock)
                                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                            @elseif($isLowStock)
                                                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                            @endif
                                            <span>{{ $ingredient->name }}</span>
                                        </div>
                                    </td>

                                    <!-- Merged Measurement Unit & Current Stock with Progress Bar (Inventory #2 & #3) -->
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="font-mono font-black text-sm {{ $isOutOfStock ? 'text-rose-600' : ($isLowStock ? 'text-amber-700' : 'text-gray-900') }}">
                                                {{ $currFormatted }} {{ $ingredient->unit }}
                                            </span>
                                            <!-- Mini Progress Bar (Current vs Threshold) -->
                                            <div class="w-36 h-1.5 bg-gray-200 rounded-full mt-1.5 overflow-hidden shadow-inner" title="{{ $pct }}% of optimal stock level">
                                                <div class="h-full rounded-full transition-all duration-300 {{ $isOutOfStock ? 'bg-rose-500 w-0' : ($isLowStock ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                     style="width: {{ $pct }}%;"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-right font-mono text-gray-600 text-xs font-semibold">
                                        {{ $minFormatted }} {{ $ingredient->unit }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        @if($isOutOfStock)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs rounded-full font-bold bg-rose-100 text-rose-800 border border-rose-200/60">
                                                <span>✕</span> Out of stock
                                            </span>
                                        @elseif($isLowStock)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs rounded-full font-bold bg-amber-100 text-amber-900 border border-amber-200/60">
                                                <span>⚠</span> Low stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs rounded-full font-bold bg-emerald-100 text-[#0e703c] border border-emerald-200/60">
                                                <span>✓</span> In stock
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Usable Row Actions (>= 36px height, Secondary outline - Inventory #4 & G1) -->
                                    <td class="py-3.5 px-4 text-right space-x-2">
                                        <button type="button" 
                                                onclick="openAdjustModal({{ json_encode($ingredient) }})" 
                                                class="h-9 min-h-[36px] px-3.5 bg-white hover:bg-gray-50 text-gray-700 hover:text-[#0e703c] border border-gray-200 rounded-xl text-xs font-semibold shadow-2xs transition active:scale-95 cursor-pointer" 
                                                title="Manual Stock Adjustment">
                                            Adjust
                                        </button>
                                        <a href="{{ route('inventory.edit', $ingredient) }}" 
                                           class="inline-flex items-center justify-center h-9 min-h-[36px] px-3.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-xl text-xs font-semibold shadow-2xs transition active:scale-95" 
                                           title="Edit Ingredient Details">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-500 font-medium">
                                        No ingredients registered matching your filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($ingredients->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/40">
                        {{ $ingredients->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Quick Adjust Modal -->
    <div id="adjust-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-100 animate-scale-up">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]/80">
                <div>
                    <h3 class="font-extrabold text-gray-900 text-lg">Stock Adjustment</h3>
                    <p id="adjust-ing-name" class="text-xs font-semibold text-[#0e703c]">-</p>
                </div>
                <button type="button" onclick="closeAdjustModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.adjust') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="adjust-ing-id" name="ingredient_id" value="" />

                <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200 text-xs flex justify-between items-center">
                    <span class="text-gray-600 font-semibold">Current stock level:</span>
                    <span id="adjust-curr-stock" class="font-mono font-black text-sm text-gray-900">-</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Adjustment quantity (Delta +/-)</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="quantity" required placeholder="e.g. +50 or -20" class="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] outline-none font-bold pr-14 text-gray-900" />
                        <span id="adjust-unit-label" class="absolute right-3.5 top-2.5 text-xs font-bold text-[#0e703c]">unit</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1 font-medium">Use positive numbers to add stock, negative to deduct.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Reason for adjustment</label>
                    <select name="reason" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] outline-none font-medium text-gray-800">
                        <option value="Physical Count Reconciliation">Physical Count Reconciliation</option>
                        <option value="Data Entry Correction">Data Entry Correction</option>
                        <option value="Audit Adjustment">Audit Adjustment</option>
                        <option value="Other">Other Reason</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Audit notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Provide extra explanation for audit logs..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] outline-none placeholder-gray-400"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2.5">
                    <button type="button" onclick="closeAdjustModal()" class="h-10 px-4 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition shadow-2xs">
                        Cancel
                    </button>
                    <button type="submit" class="h-10 px-5 py-2 bg-[#0e703c] hover:bg-[#0b5930] text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-[0.98]">
                        Save adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openAdjustModal(ing) {
            document.getElementById('adjust-ing-id').value = ing.id;
            document.getElementById('adjust-ing-name').innerText = `Ingredient: ${ing.name}`;
            const isPcs = (ing.unit || '').toLowerCase() === 'pcs';
            const val = parseFloat(ing.current_stock);
            document.getElementById('adjust-curr-stock').innerText = `${isPcs ? Math.round(val) : val.toFixed(2)} ${ing.unit}`;
            document.getElementById('adjust-unit-label').innerText = ing.unit;
            document.getElementById('adjust-modal').classList.remove('hidden');
        }

        function closeAdjustModal() {
            document.getElementById('adjust-modal').classList.add('hidden');
        }

        // Live search with 300ms debounce (G2)
        let searchTimeout = null;
        const searchInput = document.getElementById('inventory-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('inventory-filter-form').submit();
                }, 350);
            });
        }
    </script>
    @endpush
</x-app-layout>
