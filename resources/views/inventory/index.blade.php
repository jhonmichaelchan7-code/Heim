<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Inventory Management') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Monitor raw ingredients, stock levels, threshold warnings, and transactions
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('inventory.transactions') }}" class="inline-flex items-center px-3.5 py-2 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Ledger Logs
                </a>
                <a href="{{ route('inventory.stock-in') }}" class="inline-flex items-center px-3.5 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Stock In
                </a>
                <a href="{{ route('inventory.waste') }}" class="inline-flex items-center px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Record Waste
                </a>
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Ingredient
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Search Ingredient</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. Milk, Espresso Beans..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Stock Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Statuses</option>
                            <option value="good" {{ request('status') === 'good' ? 'selected' : '' }}>Good Standing</option>
                            <option value="low_stock" {{ request('status') === 'low_stock' ? 'selected' : '' }}>Low Stock (Below Threshold)</option>
                            <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (Zero / Negative)</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-sm">
                            Apply Filter
                        </button>
                        <a href="{{ route('inventory.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Ingredients Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Ingredient Name</th>
                                <th class="py-3.5 px-4">Measurement Unit</th>
                                <th class="py-3.5 px-4 text-right">Current Stock</th>
                                <th class="py-3.5 px-4 text-right">Min Threshold</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ingredients as $ingredient)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-4 px-4 font-bold text-gray-900">
                                        {{ $ingredient->name }}
                                    </td>
                                    <td class="py-4 px-4 text-gray-500">
                                        <span class="px-2.5 py-0.5 rounded-md bg-[#f0f8f5] text-[#155d49] text-xs font-bold">
                                            {{ $ingredient->unit }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono font-bold text-sm {{ $ingredient->current_stock <= 0 ? 'text-rose-600' : ($ingredient->current_stock <= $ingredient->minimum_stock ? 'text-amber-600' : 'text-gray-900') }}">
                                        {{ number_format($ingredient->current_stock, 2) }} {{ $ingredient->unit }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-mono text-gray-500 text-xs font-semibold">
                                        {{ number_format($ingredient->minimum_stock, 2) }} {{ $ingredient->unit }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if($ingredient->current_stock <= 0)
                                            <span class="px-2.5 py-1 text-xs rounded-full font-bold bg-rose-100 text-rose-800">
                                                Out of Stock
                                            </span>
                                        @elseif($ingredient->current_stock <= $ingredient->minimum_stock)
                                            <span class="px-2.5 py-1 text-xs rounded-full font-bold bg-amber-100 text-amber-800">
                                                Low Stock
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs rounded-full font-bold bg-emerald-100 text-[#155d49]">
                                                In Stock
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right space-x-1.5">
                                        <button onclick="openAdjustModal({{ json_encode($ingredient) }})" class="px-2.5 py-1 bg-[#f0f8f5] hover:bg-emerald-100 text-[#155d49] rounded-lg text-xs font-bold transition" title="Manual Stock Adjustment">
                                            Adjust
                                        </button>
                                        <a href="{{ route('inventory.edit', $ingredient) }}" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        No ingredients registered in the system.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($ingredients->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $ingredients->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Quick Adjust Modal -->
    <div id="adjust-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-fade-in border border-emerald-100">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Stock Adjustment</h3>
                    <p id="adjust-ing-name" class="text-xs font-semibold text-[#155d49]">-</p>
                </div>
                <button onclick="closeAdjustModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('inventory.adjust') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="adjust-ing-id" name="ingredient_id" value="" />

                <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-200 text-xs flex justify-between items-center">
                    <span class="text-gray-500 font-medium">Current Stock Level:</span>
                    <span id="adjust-curr-stock" class="font-bold text-sm text-gray-900">-</span>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Adjustment Quantity (Delta +/-)</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="quantity" required placeholder="e.g. +50 or -20" class="w-full px-3.5 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-bold pr-12 text-gray-900" />
                        <span id="adjust-unit-label" class="absolute right-3.5 top-2 text-xs font-bold text-[#155d49]">unit</span>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Use positive numbers to add stock, negative to deduct.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Reason for Adjustment</label>
                    <select name="reason" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                        <option value="Physical Count Reconciliation">Physical Count Reconciliation</option>
                        <option value="Data Entry Correction">Data Entry Correction</option>
                        <option value="Audit Adjustment">Audit Adjustment</option>
                        <option value="Other">Other Reason</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Audit Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Provide extra explanation for audit logs..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeAdjustModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow transition">
                        Save Adjustment
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
            document.getElementById('adjust-curr-stock').innerText = `${parseFloat(ing.current_stock).toFixed(2)} ${ing.unit}`;
            document.getElementById('adjust-unit-label').innerText = ing.unit;
            document.getElementById('adjust-modal').classList.remove('hidden');
        }
        function closeAdjustModal() {
            document.getElementById('adjust-modal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
