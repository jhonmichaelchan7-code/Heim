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
            <!-- Single Primary Solid Green Button (G1) -->
            <a href="{{ route('recipes.create') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition active:scale-[0.98]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Create New Recipe</span>
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6" x-data="recipeManager()">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Live Filter & Search Toolbar (G2: No Filter button, Live 300ms debounce) -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-100">
                <form id="recipe-filter-form" method="GET" action="{{ route('recipes.index') }}" class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center justify-between">
                    <div class="flex flex-col sm:flex-row flex-1 gap-3 items-center">
                        <!-- Search Field (Live 300ms debounce) -->
                        <div class="relative w-full sm:w-80">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input 
                                type="text" 
                                id="recipe-search"
                                name="search" 
                                value="{{ request('search') }}" 
                                placeholder="Search recipes by product..." 
                                class="w-full h-10 pl-10 pr-3 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#155d49] focus:border-transparent outline-none transition placeholder:text-gray-500 font-medium"
                                oninput="debounceRecipeSubmit()"
                            >
                        </div>

                        <!-- Category Filter Dropdown (Live onchange) -->
                        <div class="w-full sm:w-48">
                            <select 
                                name="category_id" 
                                onchange="this.form.submit()" 
                                class="w-full h-10 px-3 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#155d49] focus:border-transparent outline-none transition font-medium text-gray-700"
                            >
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product Filter Dropdown (Live onchange) -->
                        <div class="w-full sm:w-56">
                            <select 
                                name="product_id" 
                                onchange="this.form.submit()" 
                                class="w-full h-10 px-3 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-[#155d49] focus:border-transparent outline-none transition font-medium text-gray-700"
                            >
                                <option value="">All Products</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Reset Link (G2) -->
                        @if(request()->filled('search') || request()->filled('category_id') || request()->filled('product_id'))
                            <a href="{{ route('recipes.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-800 px-2 py-1 transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                <span>Reset</span>
                            </a>
                        @endif
                    </div>

                    <!-- Toolbar Actions: Counter & Expand/Collapse Toggle -->
                    <div class="flex items-center justify-between lg:justify-end gap-3 pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-100">
                        <span class="text-xs font-medium text-gray-500">
                            Showing <span class="font-semibold text-gray-800">{{ $recipes->count() }}</span> products (<span class="font-semibold text-gray-800">{{ $recipes->flatten()->count() }}</span> recipes)
                        </span>
                        @if($recipes->isNotEmpty())
                            <button 
                                type="button" 
                                @click="toggleAll()" 
                                class="h-9 px-3 text-xs font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition flex items-center gap-1.5"
                            >
                                <span x-text="allExpanded ? 'Collapse all' : 'Expand all'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': allExpanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Product Accordions List (Collapsible, Collapsed by default) -->
            <div class="space-y-3.5">
                @forelse($recipes as $productId => $productRecipes)
                    @php 
                        $product = $productRecipes->first()->product; 
                        $sizeCount = $productRecipes->count();
                        $hasFilters = request()->filled('search') || request()->filled('category_id') || request()->filled('product_id');
                    @endphp

                    <div 
                        x-data="{ open: {{ $hasFilters ? 'true' : 'false' }} }" 
                        x-init="$watch('allExpanded', val => open = val)"
                        class="bg-white rounded-2xl shadow-xs border border-gray-200/80 transition-all overflow-hidden"
                        :class="{ 'ring-1 ring-[#155d49]/30 border-[#155d49]/40': open }"
                    >
                        <!-- Accordion Header: Name, Category, Size count -->
                        <button 
                            type="button" 
                            @click="open = !open" 
                            class="w-full text-left p-4 sm:p-4.5 flex items-center justify-between gap-4 hover:bg-gray-50/70 transition select-none"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-[#f0f8f5] text-[#155d49] border border-[#155d49]/15 flex items-center justify-center shrink-0 text-lg shadow-2xs">
                                    ☕
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-gray-900 text-base leading-tight truncate">{{ $product->name }}</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-600 border border-gray-200/60">
                                            {{ $product->category?->name ?? 'Uncategorized' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $sizeCount }} {{ Str::plural('size', $sizeCount) }} configured
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-[#155d49] border border-emerald-200/60">
                                    {{ $sizeCount }} {{ Str::plural('size', $sizeCount) }}
                                </span>
                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-gray-600 transition">
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </button>

                        <!-- Accordion Body: 3-column responsive grid filling full width -->
                        <div x-show="open" x-collapse x-cloak>
                            <div class="p-4 sm:p-5 pt-1 border-t border-gray-100 bg-gray-50/40">
                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    @foreach($productRecipes as $recipe)
                                        <div 
                                            x-data="{ menuOpen: false }" 
                                            class="bg-white rounded-xl p-4 border border-gray-200/90 shadow-2xs hover:shadow-xs hover:border-gray-300 transition flex flex-col justify-between"
                                        >
                                            <div>
                                                <!-- Card Header: Size badge, Edit button, More actions (⋮) dropdown -->
                                                <div class="flex justify-between items-center mb-3">
                                                    <span class="inline-flex items-center px-2.5 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg tracking-wide">
                                                        {{ $recipe->size?->name ?? 'Default Size' }}
                                                    </span>

                                                    <!-- Actions (G1: Secondary outline edit, ⋮ dropdown for duplicate & delete) -->
                                                    <div class="flex items-center gap-1.5 relative">
                                                        <!-- Edit Button (Secondary White Outline) -->
                                                        <a 
                                                            href="{{ route('recipes.edit', $recipe) }}" 
                                                            class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:text-[#155d49] hover:border-[#155d49] hover:bg-emerald-50/40 transition shadow-2xs" 
                                                            title="Edit Recipe"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                        </a>

                                                        <!-- ⋮ Dropdown Menu Button -->
                                                        <div class="relative">
                                                            <button 
                                                                type="button" 
                                                                @click="menuOpen = !menuOpen" 
                                                                @click.outside="menuOpen = false"
                                                                class="h-8 w-8 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-2xs" 
                                                                title="More actions"
                                                            >
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                                </svg>
                                                            </button>

                                                            <!-- Dropdown Menu Content -->
                                                            <div 
                                                                x-show="menuOpen" 
                                                                x-cloak
                                                                class="absolute right-0 mt-1.5 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-30 animate-in fade-in zoom-in-95 duration-100"
                                                            >
                                                                <!-- Duplicate Recipe Action -->
                                                                <a 
                                                                    href="{{ route('recipes.create', ['duplicate_from' => $recipe->id]) }}" 
                                                                    class="flex items-center gap-2.5 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 font-medium transition"
                                                                >
                                                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                                                                    </svg>
                                                                    <span>Duplicate recipe</span>
                                                                </a>

                                                                <div class="border-t border-gray-100 my-1"></div>

                                                                <!-- Delete Recipe Action (Opens Confirmation Modal) -->
                                                                <button 
                                                                    type="button" 
                                                                    @click="menuOpen = false; promptDelete('{{ $recipe->id }}', '{{ addslashes($recipe->size?->name ?? 'Recipe') }}', '{{ addslashes($product->name) }}')"
                                                                    class="flex items-center gap-2.5 w-full text-left px-3 py-2 text-xs text-rose-600 hover:bg-rose-50 font-medium transition"
                                                                >
                                                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                    </svg>
                                                                    <span>Delete recipe</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Ingredient Lines (G5: Clean, no decimals for pcs, right-aligned) -->
                                                <div class="space-y-2">
                                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                                                        Ingredients per serving
                                                    </p>
                                                    <div class="space-y-1 bg-gray-50/70 p-2.5 rounded-xl border border-gray-100">
                                                        @forelse($recipe->recipeIngredients as $ri)
                                                            @php
                                                                $unit = strtolower($ri->ingredient?->unit ?? '');
                                                                $qty = (float)$ri->quantity;
                                                                $isWhole = ($unit === 'pcs' || $unit === 'pc' || floor($qty) == $qty);
                                                                $formattedQty = $isWhole ? number_format($qty, 0) : rtrim(rtrim(number_format($qty, 2), '0'), '.');
                                                            @endphp
                                                            <div class="flex justify-between items-center text-xs py-0.5 border-b border-gray-100/70 last:border-b-0">
                                                                <span class="text-gray-700 font-medium truncate pr-2">{{ $ri->ingredient?->name }}</span>
                                                                <span class="text-gray-900 font-medium text-right shrink-0">
                                                                    <span class="font-mono font-semibold">{{ $formattedQty }}</span> {{ $ri->ingredient?->unit }}
                                                                </span>
                                                            </div>
                                                        @empty
                                                            <div class="text-xs text-gray-400 italic py-1 text-center">
                                                                No ingredients added
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>

                                            @if($recipe->notes)
                                                <div class="mt-3 pt-2 border-t border-gray-100">
                                                    <p class="text-[11px] text-gray-500 italic line-clamp-2">"{{ $recipe->notes }}"</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-12 text-center text-gray-400">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-3 text-xl">
                            ☕
                        </div>
                        <p class="font-semibold text-gray-700 text-sm">No recipe configurations found</p>
                        <p class="text-xs text-gray-400 mt-1">Try adjusting your search criteria or create your first drink recipe.</p>
                        <a href="{{ route('recipes.create') }}" class="inline-flex items-center gap-1.5 mt-4 text-[#155d49] font-bold text-xs hover:underline">
                            + Create new recipe
                        </a>
                    </div>
                @endforelse
            </div>

        </div>

        <!-- Hidden Delete Form -->
        <form id="recipe-delete-form" method="POST" action="" class="hidden">
            @csrf
            @method('DELETE')
        </form>

        <!-- Delete Confirmation Modal (Error Prevention) -->
        <div 
            x-show="showDeleteModal" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="showDeleteModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-bold text-gray-900" id="modal-title">Delete Recipe</h3>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">
                                Are you sure you want to delete the <span class="font-bold text-gray-900" x-text="deleteSize"></span> recipe for <span class="font-bold text-gray-900" x-text="deleteProduct"></span>?
                            </p>
                            <p class="text-[11px] text-rose-600 font-medium mt-2">
                                This will remove automatic ingredient deductions for this size.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="showDeleteModal = false"
                            class="h-9 px-4 text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition"
                        >
                            Cancel
                        </button>
                        <button 
                            type="button" 
                            @click="executeDelete()"
                            class="h-9 px-4 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-xs"
                        >
                            Delete Recipe
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Live Debounce & Alpine Component Script -->
    <script>
        let searchTimer = null;
        function debounceRecipeSubmit() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                document.getElementById('recipe-filter-form').submit();
            }, 300);
        }

        function recipeManager() {
            return {
                allExpanded: false,
                showDeleteModal: false,
                deleteRecipeId: null,
                deleteSize: '',
                deleteProduct: '',
                toggleAll() {
                    this.allExpanded = !this.allExpanded;
                },
                promptDelete(id, size, product) {
                    this.deleteRecipeId = id;
                    this.deleteSize = size;
                    this.deleteProduct = product;
                    this.showDeleteModal = true;
                },
                executeDelete() {
                    if (!this.deleteRecipeId) return;
                    const form = document.getElementById('recipe-delete-form');
                    form.action = `/recipes/${this.deleteRecipeId}`;
                    form.submit();
                }
            };
        }
    </script>
</x-app-layout>
