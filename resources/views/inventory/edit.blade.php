<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    Edit Ingredient: {{ $ingredient->name }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Update ingredient parameters and low stock alert thresholds
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('inventory.update', $ingredient) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Ingredient Name</label>
                        <input type="text" name="name" value="{{ old('name', $ingredient->name) }}" required class="w-full px-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold text-gray-900" />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Unit of Measurement</label>
                            <select name="unit" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                                <option value="g" {{ old('unit', $ingredient->unit) == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                <option value="ml" {{ old('unit', $ingredient->unit) == 'ml' ? 'selected' : '' }}>Milliliters (ml)</option>
                                <option value="pcs" {{ old('unit', $ingredient->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                                <option value="oz" {{ old('unit', $ingredient->unit) == 'oz' ? 'selected' : '' }}>Ounces (oz)</option>
                                <option value="kg" {{ old('unit', $ingredient->unit) == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                <option value="L" {{ old('unit', $ingredient->unit) == 'L' ? 'selected' : '' }}>Liters (L)</option>
                            </select>
                            @error('unit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1">Minimum Alert Threshold</label>
                            <input type="number" step="0.01" min="0" name="minimum_stock" value="{{ old('minimum_stock', $ingredient->minimum_stock) }}" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-bold text-gray-900" />
                            @error('minimum_stock') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="p-3.5 bg-[#f0f8f5] rounded-2xl border border-emerald-100 flex justify-between items-center text-xs">
                        <span class="text-gray-600 font-medium">Current Stock on Hand:</span>
                        <span class="font-mono font-bold text-sm text-[#155d49]">{{ number_format($ingredient->current_stock, 2) }} {{ $ingredient->unit }}</span>
                    </div>

                    <div class="border-t border-gray-100 pt-4 flex justify-end gap-3">
                        <a href="{{ route('inventory.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow transition">
                            Update Ingredient
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
