<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Products & Menu Catalog') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Manage beverages, size pricing matrix, recipe associations, and catalog visibility
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl border border-gray-200 shadow-xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <span>Categories</span>
                </a>
                <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Product</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Search Product Name</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. Latte, Americano..." class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Filter by Category</label>
                        <select name="category" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 h-10 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('products.index') }}" class="h-10 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition flex items-center justify-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Products Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Product</th>
                                <th class="py-3.5 px-4">Category</th>
                                <th class="py-3.5 px-4">Serving Sizes & Prices</th>
                                <th class="py-3.5 px-4">POS Status</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-[#f0f8f5] flex items-center justify-center text-xl shrink-0">
                                                ☕
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900">{{ $product->name }}</p>
                                                @if($product->description)
                                                    <p class="text-xs text-gray-400 line-clamp-1">{{ $product->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#f0f8f5] text-[#155d49]">
                                            {{ $product->category?->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($product->sizes as $size)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-50 text-gray-800 border border-gray-200">
                                                    {{ $size->name }}: <strong class="ml-1 text-[#155d49]">₱{{ number_format($size->pivot->price, 2) }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <form method="POST" action="{{ route('products.toggle-active', $product) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" title="Click to toggle status (currently {{ $product->is_active ? 'Active' : 'Inactive' }})" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition shadow-xs cursor-pointer
                                                {{ $product->is_active ? 'bg-emerald-100 text-[#155d49] hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}
                                            ">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-[#155d49]' : 'bg-gray-400' }}"></span>
                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 px-4 text-right space-x-1.5">
                                        <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center justify-center h-8 px-3 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold shadow-xs transition active:scale-[0.98]">
                                            Edit
                                         </a>
                                        <form method="POST" action="{{ route('products.toggle-active', $product) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center h-8 px-3 rounded-lg text-xs font-semibold shadow-xs transition active:scale-[0.98]
                                                {{ $product->is_active ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-[#155d49] hover:bg-[#114a3b] text-white' }}
                                            ">
                                                {{ $product->is_active ? 'Set Inactive' : 'Set Active' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400">
                                        No products found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $products->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
