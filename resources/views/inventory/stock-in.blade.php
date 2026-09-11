<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Stock In Delivery') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Record received ingredient shipments, supplier details, and replenish stocks
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('inventory.stock-in.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Select Ingredient</label>
                        <select name="ingredient_id" id="ing-select" required onchange="updateStockInfo()" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold">
                            <option value="">Choose an ingredient to replenish</option>
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}" data-stock="{{ $ing->current_stock }}">
                                    {{ $ing->name }} (Current: {{ number_format($ing->current_stock, 2) }} {{ $ing->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('ingredient_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Quantity Received</label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0.01" name="quantity" required placeholder="0.00" class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-bold pr-12 text-gray-900" />
                                <span id="unit-tag" class="absolute right-3.5 top-2 text-xs font-bold text-[#155d49]">unit</span>
                            </div>
                            @error('quantity') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Supplier / Vendor (Optional)</label>
                            <input type="text" name="supplier" placeholder="e.g. San Miguel Dairy, Bean Co." class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Notes / Invoice # (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Delivery receipt #DR-9921, fresh batch expiring Oct 2026..." class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none"></textarea>
                    </div>

                    <div class="border-t border-gray-100 pt-4 flex justify-end gap-3">
                        <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Confirm Stock In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateStockInfo() {
            const select = document.getElementById('ing-select');
            const opt = select.options[select.selectedIndex];
            const unit = opt.getAttribute('data-unit') || 'unit';
            document.getElementById('unit-tag').innerText = unit;
        }
    </script>
    @endpush
</x-app-layout>
