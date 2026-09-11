<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('recipes.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Create Drink Recipe') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Map exact ingredient quantities to deduct per drink served
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('recipes.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Product</label>
                            <select name="product_id" required class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} ({{ $product->category?->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Cup Size</label>
                            <select name="size_id" required class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                                <option value="">Select Size</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}" {{ old('size_id') == $size->id ? 'selected' : '' }}>
                                        {{ $size->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('size_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Recipe Notes (Optional)</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="e.g. Standard 2 shots espresso, double pumps syrup..." class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <!-- Dynamic Ingredients List -->
                    <div class="border-t border-gray-100 pt-5">
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-800">Required Ingredients</label>
                                <p class="text-xs text-gray-500">Specify each raw ingredient and quantity deducted per sale</p>
                            </div>
                            <button type="button" onclick="addIngredientRow()" class="px-3.5 py-1.5 bg-[#f0f8f5] hover:bg-emerald-100 text-[#155d49] text-xs font-bold rounded-xl transition">
                                + Add Ingredient
                            </button>
                        </div>

                        <div id="ingredient-rows-container" class="space-y-3">
                            <div class="ing-row flex items-center gap-3 p-3.5 bg-gray-50 rounded-2xl border border-gray-200">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Ingredient</label>
                                    <select name="ingredients[0][ingredient_id]" required onchange="updateUnitLabel(this)" class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold">
                                        <option value="">Select Ingredient</option>
                                        @foreach($ingredients as $ing)
                                            <option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">{{ $ing->name }} ({{ $ing->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-36">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Qty Deducted</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" min="0.01" name="ingredients[0][quantity]" required placeholder="0.00" class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-bold text-gray-900 pr-10" />
                                        <span class="unit-label absolute right-2.5 top-1.5 text-xs text-[#155d49] font-bold">-</span>
                                    </div>
                                </div>
                                <button type="button" onclick="removeIngredientRow(this)" class="mt-4 p-2 text-rose-500 hover:text-rose-700 rounded-xl hover:bg-rose-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5 flex justify-end gap-3">
                        <a href="{{ route('recipes.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow transition">
                            Save Recipe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const allIngredients = @json($ingredients);
        let rowCount = 1;

        function updateUnitLabel(select) {
            const selectedOption = select.options[select.selectedIndex];
            const unit = selectedOption.getAttribute('data-unit') || '-';
            const row = select.closest('.ing-row');
            row.querySelector('.unit-label').innerText = unit;
        }

        function addIngredientRow() {
            const container = document.getElementById('ingredient-rows-container');
            const newRow = document.createElement('div');
            newRow.className = 'ing-row flex items-center gap-3 p-3.5 bg-gray-50 rounded-2xl border border-gray-200';
            
            let options = '<option value="">Select Ingredient</option>' + allIngredients.map(i => `<option value="${i.id}" data-unit="${i.unit}">${i.name} (${i.unit})</option>`).join('');

            newRow.innerHTML = `
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Ingredient</label>
                    <select name="ingredients[${rowCount}][ingredient_id]" required onchange="updateUnitLabel(this)" class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold">
                        ${options}
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Qty Deducted</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" name="ingredients[${rowCount}][quantity]" required placeholder="0.00" class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-bold text-gray-900 pr-10" />
                        <span class="unit-label absolute right-2.5 top-1.5 text-xs text-[#155d49] font-bold">-</span>
                    </div>
                </div>
                <button type="button" onclick="removeIngredientRow(this)" class="mt-4 p-2 text-rose-500 hover:text-rose-700 rounded-xl hover:bg-rose-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            `;
            container.appendChild(newRow);
            rowCount++;
        }

        function removeIngredientRow(btn) {
            const rows = document.querySelectorAll('.ing-row');
            if (rows.length <= 1) {
                alert('Recipe must contain at least one ingredient.');
                return;
            }
            btn.closest('.ing-row').remove();
        }
    </script>
    @endpush
</x-app-layout>
