<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Drink Recipe Configurations') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Define exact raw ingredient deduction amounts per product and cup size
                </p>
            </div>
            <a href="{{ route('recipes.create') }}" class="inline-flex items-center px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-sm transition gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create New Recipe
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('recipes.index') }}" class="flex flex-col sm:flex-row gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Filter by Product</label>
                        <select name="product_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Products</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->category?->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-sm">Filter</button>
                        <a href="{{ route('recipes.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl transition">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Recipes Grouped by Product -->
            <div class="space-y-6">
                @forelse($recipes as $productId => $productRecipes)
                    @php $product = $productRecipes->first()->product; @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-4 bg-[#f0f8f5]/60 border-b border-gray-100 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">☕</span>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-base">{{ $product->name }}</h3>
                                    <span class="text-xs font-semibold text-[#155d49]">{{ $product->category?->name }} • {{ $productRecipes->count() }} Recipe Size(s)</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($productRecipes as $recipe)
                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-center mb-3">
                                            <span class="px-3 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg uppercase">
                                                Size: {{ $recipe->size?->name }}
                                            </span>
                                            <div class="flex items-center gap-1">
                                                <a href="{{ route('recipes.edit', $recipe) }}" class="p-1.5 text-gray-500 hover:text-[#155d49] rounded-lg hover:bg-white transition" title="Edit Recipe">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                                <form method="POST" action="{{ route('recipes.destroy', $recipe) }}" onsubmit="return confirm('Are you sure you want to delete this recipe?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-500 hover:text-rose-600 rounded-lg hover:bg-white transition" title="Delete Recipe">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="space-y-2 text-xs">
                                            <p class="font-bold text-gray-500 uppercase tracking-wider text-[10px]">Ingredients per serving:</p>
                                            <div class="space-y-1.5 bg-white p-3 rounded-xl border border-gray-100">
                                                @foreach($recipe->recipeIngredients as $ri)
                                                    <div class="flex justify-between items-center text-gray-700 font-medium">
                                                        <span>{{ $ri->ingredient?->name }}</span>
                                                        <span class="font-mono font-bold text-[#155d49]">{{ $ri->quantity }} {{ $ri->ingredient?->unit }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($recipe->notes)
                                                <p class="text-[11px] text-gray-500 italic mt-2">"{{ $recipe->notes }}"</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-400">
                        <p class="font-medium">No recipe configurations found.</p>
                        <a href="{{ route('recipes.create') }}" class="inline-block mt-3 text-[#155d49] font-bold text-sm hover:underline">
                            + Add your first recipe
                        </a>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
