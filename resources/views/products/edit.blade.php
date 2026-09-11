<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    Edit Product: {{ $product->name }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Update recipe details, pricing matrix, and catalog availability
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Product Name</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold text-gray-900" />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Category</label>
                        <select name="category_id" required class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="description" rows="2" class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none">{{ old('description', $product->description) }}</textarea>
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center gap-2.5 cursor-pointer p-3 bg-[#f0f8f5] rounded-xl border border-emerald-100">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="rounded text-[#155d49] focus:ring-[#155d49]">
                            <span class="text-sm font-bold text-gray-900">Active (Visible in POS Menu Catalog)</span>
                        </label>
                    </div>

                    <!-- Sizes & Pricing Matrix -->
                    <div class="border-t border-gray-100 pt-5">
                        <div class="flex justify-between items-center mb-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-800">Cup Sizes & Pricing</label>
                                <p class="text-xs text-gray-500">Update prices or add new serving sizes</p>
                            </div>
                            <button type="button" onclick="addSizeRow()" class="px-3.5 py-1.5 bg-[#f0f8f5] hover:bg-emerald-100 text-[#155d49] text-xs font-bold rounded-xl transition">
                                + Add Size
                            </button>
                        </div>

                        <div id="size-rows-container" class="space-y-3">
                            @foreach($product->productSizes as $idx => $ps)
                                <div class="size-row flex items-center gap-3 p-3.5 bg-gray-50 rounded-2xl border border-gray-200">
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Size</label>
                                        <select name="sizes[{{ $idx }}][size_id]" required class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold">
                                            @foreach($sizes as $s)
                                                <option value="{{ $s->id }}" {{ $s->id == $ps->size_id ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Selling Price (₱)</label>
                                        <input type="number" step="0.01" min="0" name="sizes[{{ $idx }}][price]" value="{{ old('sizes.'.$idx.'.price', $ps->price) }}" required class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-bold text-gray-900" />
                                    </div>
                                    <button type="button" onclick="removeSizeRow(this)" class="mt-4 p-2 text-rose-500 hover:text-rose-700 rounded-xl hover:bg-rose-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-5 flex justify-end gap-3">
                        <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow transition">
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const allSizes = @json($sizes);
        let rowCount = {{ $product->productSizes->count() }};

        function addSizeRow() {
            const container = document.getElementById('size-rows-container');
            const newRow = document.createElement('div');
            newRow.className = 'size-row flex items-center gap-3 p-3.5 bg-gray-50 rounded-2xl border border-gray-200';
            
            let options = allSizes.map(s => `<option value="${s.id}">${s.name}</option>`).join('');

            newRow.innerHTML = `
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Size</label>
                    <select name="sizes[${rowCount}][size_id]" required class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold">
                        ${options}
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-0.5">Selling Price (₱)</label>
                    <input type="number" step="0.01" min="0" name="sizes[${rowCount}][price]" required placeholder="0.00" class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-bold text-gray-900" />
                </div>
                <button type="button" onclick="removeSizeRow(this)" class="mt-4 p-2 text-rose-500 hover:text-rose-700 rounded-xl hover:bg-rose-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            `;
            container.appendChild(newRow);
            rowCount++;
        }

        function removeSizeRow(btn) {
            const rows = document.querySelectorAll('.size-row');
            if (rows.length <= 1) {
                alert('Product must have at least one size.');
                return;
            }
            btn.closest('.size-row').remove();
        }
    </script>
    @endpush
</x-app-layout>
