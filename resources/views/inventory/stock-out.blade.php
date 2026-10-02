<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Stock Out Item') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Record deductions for waste, damages, expiration, transfers, or manual inventory pull-outs
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('inventory.stock-out.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Select Ingredient</label>
                        <select name="ingredient_id" id="stock-out-ing-select" required onchange="updateStockOutInfo()" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none font-semibold text-gray-900">
                            <option value="">Choose an ingredient to deduct</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" data-stock="{{ $ing->current_stock }}">
                                    {{ $ing->name }} (Available: {{ number_format($ing->current_stock, 2) }} {{ $ing->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('ingredient_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Quantity to Deduct</label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0.01" name="quantity" id="stock-out-qty" required placeholder="0.00" oninput="calculateRemainingStock()" class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none font-bold pr-14 text-gray-900" />
                                <span id="stock-out-unit-tag" class="absolute right-3.5 top-2 text-xs font-bold text-gray-400">unit</span>
                            </div>
                            <p id="remaining-stock-hint" class="text-[11px] text-gray-500 mt-1"></p>
                            @error('quantity') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Reason for Stock Out</label>
                            <select name="reason" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none font-medium">
                                <option value="Spoilage / Expired">Spoilage / Expired</option>
                                <option value="Damaged / Broken Packaging">Damaged / Broken Packaging</option>
                                <option value="Spilled / Dropped">Spilled / Dropped</option>
                                <option value="Staff / Internal Consumption">Staff / Internal Consumption</option>
                                <option value="Testing & Calibration">Testing & Calibration</option>
                                <option value="Branch Transfer / Pull-out">Branch Transfer / Pull-out</option>
                                <option value="Return to Supplier">Return to Supplier</option>
                                <option value="Other">Other</option>
                            </select>
                            @error('reason') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Notes / Disposal Memo (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="Details about batch, incident report, or transfer destination..." class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none"></textarea>
                    </div>

                    <div class="border-t border-gray-100 pt-4 flex justify-end gap-3">
                        <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl shadow transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            Confirm Stock Out
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateStockOutInfo() {
            const select = document.getElementById('stock-out-ing-select');
            const opt = select.options[select.selectedIndex];
            const unit = opt.getAttribute('data-unit') || 'unit';
            document.getElementById('stock-out-unit-tag').innerText = unit;
            calculateRemainingStock();
        }

        function calculateRemainingStock() {
            const select = document.getElementById('stock-out-ing-select');
            const opt = select.options[select.selectedIndex];
            const hint = document.getElementById('remaining-stock-hint');
            if (!opt || !opt.value) {
                hint.innerText = '';
                return;
            }
            const currentStock = parseFloat(opt.getAttribute('data-stock') || 0);
            const unit = opt.getAttribute('data-unit') || '';
            const qty = parseFloat(document.getElementById('stock-out-qty').value) || 0;
            const remaining = currentStock - qty;
            
            if (qty > 0) {
                if (remaining < 0) {
                    hint.innerHTML = `<span class="text-rose-600 font-bold">Warning: Deduction exceeds current stock by ${Math.abs(remaining).toFixed(2)} ${unit}</span>`;
                } else {
                    hint.innerHTML = `<span class="text-gray-600">Remaining after deduction: <strong>${remaining.toFixed(2)} ${unit}</strong></span>`;
                }
            } else {
                hint.innerText = `Current available: ${currentStock.toFixed(2)} ${unit}`;
            }
        }
    </script>
    @endpush
</x-app-layout>
