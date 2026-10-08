<x-app-layout>
@push('styles')
<style>
@keyframes modalPopIn {
    0% {
        opacity: 0;
        transform: scale(0.96) translateY(12px);
    }
    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
.shift-modal-box {
    animation: modalPopIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.custom-modal-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}
.custom-modal-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-modal-scrollbar::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 9999px;
}
.custom-modal-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
.custom-modal-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Touchscreen Tablet Ergonomics & Fast Tap */
html, body {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
.touch-card, .product-card, button, input {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
.product-card {
    user-select: none;
    -webkit-user-select: none;
}
.product-card:active {
    transform: scale(0.97);
}
</style>
@endpush
    <div class="py-2.5 sm:py-3 h-screen overflow-hidden flex flex-col bg-gray-50/60">
        <div class="w-full px-2 sm:px-4 lg:px-5 2xl:max-w-[1880px] 2xl:mx-auto flex-1 flex flex-col md:flex-row gap-3 sm:gap-4 overflow-hidden">
            
            <!-- Left Column: Catalog & Categories (65% width) -->
            <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-0">
                <!-- Search & Category Filters -->
                <div class="p-3 sm:p-4 border-b border-gray-100 flex flex-col gap-2.5 sm:gap-3 bg-[#f0f8f5]/60">
                    <div class="flex items-center justify-between gap-3 w-full">
                        <!-- Search Bar -->
                        <div class="relative flex-1 max-w-md">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#0e703c]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" id="search-input" onkeyup="filterProducts()" placeholder="Search menu, coffee, pastries..." class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] focus:border-[#0e703c] outline-none transition shadow-2xs font-medium" />
                        </div>

                        <!-- Top Header Info: Cashier & Branch (Phase 1 #1) -->
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200/80 rounded-xl text-xs shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-extrabold text-gray-900">{{ $activeShift ? $activeShift->opened_by : Auth::user()->name }}</span>
                            <span class="text-gray-300">·</span>
                            <span class="font-semibold text-gray-600">📍 {{ $activeShift?->branch?->name ?? ($branches->first()?->name ?? 'Main Branch') }}</span>
                        </div>

                        <!-- Mobile-only Quick Jump to Cart Button -->
                        <button type="button" 
                                onclick="scrollToCart()" 
                                title="View Current Order"
                                class="md:hidden relative shrink-0 p-2.5 bg-white hover:bg-emerald-50 text-[#0e703c] border border-gray-200 hover:border-[#0e703c] rounded-xl shadow-2xs transition-all flex items-center justify-center active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span id="mobile-cart-header-badge" class="hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-rose-600 text-white text-[10px] font-black rounded-full items-center justify-center shadow-sm ring-2 ring-white leading-none">
                                0
                            </span>
                        </button>
                    </div>

                    <!-- Category Pills (Phase 3 #10 & #11: Flex wrap row, Best Sellers first, shortened labels) -->
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 w-full pt-0.5">
                        <button onclick="selectCategory('popular', this)" class="category-btn px-3 py-1.5 rounded-xl text-xs font-black whitespace-nowrap bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 transition shadow-2xs flex items-center gap-1.5">
                            <span>⭐ Best Sellers</span>
                        </button>
                        <button onclick="selectCategory('all', this)" class="category-btn active px-3.5 py-1.5 rounded-xl text-xs font-black whitespace-nowrap bg-[#0e703c] text-white shadow-2xs transition">
                            All Menu
                        </button>
                        @foreach($categories as $category)
                            @php
                                $cName = $category->name;
                                $cLower = strtolower($cName);
                                if (str_contains($cLower, 'hot')) $cLabel = 'Hot (' . $category->activeProducts->count() . ')';
                                elseif (str_contains($cLower, 'iced')) $cLabel = 'Iced (' . $category->activeProducts->count() . ')';
                                elseif (str_contains($cLower, 'frappe')) $cLabel = 'Frappe (' . $category->activeProducts->count() . ')';
                                elseif (str_contains($cLower, 'snack') || str_contains($cLower, 'pastr')) $cLabel = 'Pastries (' . $category->activeProducts->count() . ')';
                                elseif (str_contains($cLower, 'non')) $cLabel = 'Non-Coffee (' . $category->activeProducts->count() . ')';
                                else $cLabel = $category->name . ' (' . $category->activeProducts->count() . ')';
                            @endphp
                            <button onclick="selectCategory('{{ $category->id }}', this)" class="category-btn px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap bg-white text-gray-700 hover:bg-emerald-50 hover:text-[#0e703c] border border-gray-200 transition shadow-2xs">
                                {{ $cLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid (Scrollable, Phase 2 & 3: category tints, tap feedback, whole card clickable) -->
                <div class="flex-1 p-3 sm:p-4 overflow-y-auto">
                    <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-3.5">
                        @foreach($categories as $category)
                            @php
                                $catLower = strtolower($category->name);
                                if (str_contains($catLower, 'hot')) {
                                    $cardTheme = ['bg' => 'bg-[#fffaf4]', 'border' => 'border-[#fae8d2]', 'headerBg' => 'bg-gradient-to-br from-amber-100/90 to-amber-200/60', 'icon' => '☕'];
                                } elseif (str_contains($catLower, 'iced')) {
                                    $cardTheme = ['bg' => 'bg-[#f4faff]', 'border' => 'border-[#cbe7fd]', 'headerBg' => 'bg-gradient-to-br from-sky-100/90 to-sky-200/60', 'icon' => '🧊'];
                                } elseif (str_contains($catLower, 'frappe')) {
                                    $cardTheme = ['bg' => 'bg-[#fffdf5]', 'border' => 'border-[#faeec5]', 'headerBg' => 'bg-gradient-to-br from-amber-100/90 to-yellow-200/60', 'icon' => '🥤'];
                                } elseif (str_contains($catLower, 'snack') || str_contains($catLower, 'pastr') || str_contains($catLower, 'bake')) {
                                    $cardTheme = ['bg' => 'bg-[#fffdf2]', 'border' => 'border-[#faefbe]', 'headerBg' => 'bg-gradient-to-br from-yellow-100/90 to-amber-200/60', 'icon' => '🥐'];
                                } elseif (str_contains($catLower, 'non') || str_contains($catLower, 'tea')) {
                                    $cardTheme = ['bg' => 'bg-[#f4fbf7]', 'border' => 'border-[#c7edd9]', 'headerBg' => 'bg-gradient-to-br from-emerald-100/90 to-teal-200/60', 'icon' => '🍵'];
                                } else {
                                    $cardTheme = ['bg' => 'bg-[#f9fafb]', 'border' => 'border-gray-200', 'headerBg' => 'bg-gradient-to-br from-gray-100 to-gray-200', 'icon' => '☕'];
                                }
                            @endphp
                            @foreach($category->activeProducts as $product)
                                @php
                                    $minPrice = $product->sizes->min('pivot.price');
                                    $maxPrice = $product->sizes->max('pivot.price');
                                    $priceDisplay = $minPrice == $maxPrice ? "₱" . number_format($minPrice, 2) : "₱" . number_format($minPrice, 0) . " - ₱" . number_format($maxPrice, 0);
                                    $isPopular = in_array($product->id, $popularProductIds ?? []);
                                @endphp
                                <div class="product-card {{ $cardTheme['bg'] }} border {{ $cardTheme['border'] }} hover:border-[#0e703c] rounded-2xl p-3 flex flex-col justify-between cursor-pointer transition-all duration-150 shadow-2xs hover:shadow-md group relative overflow-hidden select-none active:scale-[0.97]"
                                     data-category="{{ $category->id }}"
                                     data-name="{{ strtolower($product->name) }}"
                                     data-product-id="{{ $product->id }}"
                                     data-popular="{{ $isPopular ? '1' : '0' }}"
                                     onclick="handleProductCardClick({{ json_encode($product) }}, this)">
                                    
                                    <!-- In-cart Badge (Phase 2 #8) -->
                                    <div id="card-badge-{{ $product->id }}" class="hidden absolute top-2 right-2 px-2 py-0.5 rounded-full bg-[#0e703c] text-white text-[11px] font-black shadow-md ring-2 ring-white z-10 animate-fade-in pointer-events-none">
                                        ×0
                                    </div>

                                    @if($isPopular)
                                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-black shadow-2xs z-10 pointer-events-none flex items-center gap-1">
                                        ★ Popular
                                    </div>
                                    @endif

                                    <div>
                                        <div class="w-full h-24 rounded-xl {{ $cardTheme['headerBg'] }} flex items-center justify-center text-3xl group-hover:scale-105 transition-transform duration-200 shadow-inner">
                                            {{ $cardTheme['icon'] }}
                                        </div>
                                        <h4 class="font-bold text-gray-900 text-sm mt-2.5 line-clamp-1 group-hover:text-[#0e703c] transition">{{ $product->name }}</h4>
                                        <p class="text-xs text-gray-600 font-semibold capitalize mt-0.5">{{ $category->name }}</p>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-black/5 flex items-center justify-between">
                                        <span class="font-black text-[#0e703c] text-sm">{{ $priceDisplay }}</span>
                                        <span class="p-1.5 bg-white/90 text-[#0e703c] rounded-lg border border-black/5 shadow-2xs group-hover:bg-[#0e703c] group-hover:text-white transition pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Cart Panel (35% width, Phase 1 decluttered column layout) -->
            <div id="pos-cart-panel" class="w-full md:w-[420px] flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden shrink-0">
                <!-- Cart Header (Phase 1 #1 & #2: Shift info/time moved out, minimalist) -->
                <div class="p-3.5 border-b border-gray-100 bg-[#f0f8f5]/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full overflow-hidden bg-[#0e703c] border border-white shrink-0">
                                <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                            </div>
                            <h3 class="font-bold text-gray-900 text-base">Heim Order Cart</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Clear All (Phase 5 #15: Protected with confirmation modal) -->
                            <button type="button" onclick="promptClearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-bold transition flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-rose-50" title="Clear all items in cart">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Clear All</span>
                            </button>

                            <!-- Kiosk Fullscreen Mode for Tablets -->
                            <button type="button" onclick="toggleKioskFullscreen()" id="kiosk-fullscreen-btn" title="Toggle Tablet Kiosk Fullscreen" class="h-8 px-2.5 rounded-xl bg-white hover:bg-emerald-50 text-gray-700 hover:text-[#0e703c] border border-gray-200 flex items-center gap-1.5 font-bold text-xs transition shadow-2xs active:scale-95 touch-manipulation select-none">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                <span class="hidden sm:inline" id="fullscreen-btn-text">Kiosk</span>
                            </button>

                            <!-- Inventify 3-dot Menu ⋮ -->
                            <div class="relative" id="pos-menu-container">
                                <button type="button" onclick="togglePosMenu(event)" id="pos-three-dots-btn" title="Shift & POS Menu" class="w-8 h-8 rounded-xl bg-white hover:bg-emerald-50 text-gray-700 hover:text-[#0e703c] border border-gray-200 flex items-center justify-center font-bold text-lg leading-none transition shadow-2xs">
                                    ⋮
                                </button>
                                <!-- Dropdown -->
                                <div id="pos-menu-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-1.5 z-50 animate-fade-in text-sm font-medium">
                                    <button type="button" onclick="triggerOpenDrawer()" class="w-full px-4 py-2.5 text-left text-gray-700 hover:bg-[#f0f8f5] hover:text-[#0e703c] flex items-center gap-2.5 transition">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        <span>Open Drawer</span>
                                    </button>
                                    <button type="button" onclick="handleShiftMenuClick()" class="w-full px-4 py-2.5 text-left text-gray-700 hover:bg-[#f0f8f5] hover:text-[#0e703c] flex items-center gap-2.5 transition">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Shifts</span>
                                    </button>
                                    @if(auth()->user()->isManager())
                                    <button type="button" onclick="openSettingsModal()" class="w-full px-4 py-2.5 text-left text-gray-700 hover:bg-[#f0f8f5] hover:text-[#0e703c] flex items-center gap-2.5 transition">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Settings</span>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Single Shift Indicator Bar (Phase 1 #1: Keep single shift indicator) -->
                    <div id="pos-shift-status-bar" onclick="handleShiftMenuClick()" class="cursor-pointer flex items-center justify-between px-3 py-2 rounded-xl border text-[11px] font-semibold transition hover:shadow-2xs {{ $activeShift ? 'bg-emerald-50/90 border-emerald-200 text-emerald-800' : 'bg-amber-50/90 border-amber-200 text-amber-800' }}">
                        <div class="flex items-center gap-2">
                            <span id="pos-shift-indicator-dot" class="w-2.5 h-2.5 rounded-full {{ $activeShift ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' }}"></span>
                            <span id="pos-shift-status-text" class="font-bold">{{ $activeShift ? 'Shift Active: ' . $activeShift->opened_by : 'No Shift Active (Required)' }}</span>
                        </div>
                        <span id="pos-shift-action-label" class="text-[10px] font-extrabold uppercase tracking-wider text-[#0e703c] hover:underline flex items-center gap-1">
                            <span>{{ $activeShift ? 'Reconcile / End' : 'Start Shift' }}</span>
                            <span>➔</span>
                        </span>
                    </div>

                    <!-- Hidden Inputs for Form/AJAX Submissions -->
                    <input type="hidden" id="cashier-name" value="{{ $activeShift ? $activeShift->opened_by : Auth::user()->name }}" />
                    <input type="hidden" id="pos-branch-select" value="{{ $activeShift?->branch_id ?? ($branches->first()?->id ?? '') }}" />

                    <!-- Order Type Tabs & Grab Details -->
                    <div class="space-y-2 pt-0.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-gray-700">Order Mode</label>
                            <span class="text-[11px] font-medium text-gray-500">📍 {{ $activeShift?->branch?->name ?? ($branches->first()?->name ?? 'Main Branch') }}</span>
                        </div>
                        
                        <!-- Segmented Tab Bar -->
                        <div class="flex items-center bg-white p-1 rounded-2xl border border-gray-200 shadow-2xs select-none">
                            <button type="button" id="btn-ot-dine_in" onclick="setOrderType('dine_in')" class="flex-1 py-2 text-center text-xs font-black rounded-xl transition bg-[#0e703c] text-white shadow-xs">
                                Dine-in
                            </button>
                            <button type="button" id="btn-ot-takeout" onclick="setOrderType('takeout')" class="flex-1 py-2 text-center text-xs font-bold rounded-xl text-gray-700 hover:text-[#0e703c] hover:bg-emerald-50/50 transition">
                                Take-out
                            </button>
                            <button type="button" id="btn-ot-grab_delivery" onclick="setOrderType('grab_delivery')" class="flex-1 py-2 text-center text-xs font-bold rounded-xl text-gray-700 hover:text-[#0e703c] hover:bg-emerald-50/50 transition">
                                Grab
                            </button>
                        </div>

                        <!-- GrabFood Order Details Box -->
                        <div id="grab-details-box" class="hidden p-3 bg-[#f0f9f4] rounded-2xl border border-[#9ae1c3] space-y-2 transition">
                            <div class="text-[11px] font-black tracking-wide text-[#0e703c] uppercase">
                                GRABFOOD ORDER DETAILS
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-[#0e703c] mb-1">
                                        Grab Order Code <span class="text-rose-500 font-black">*</span>
                                    </label>
                                    <input type="text" id="grab-order-code" placeholder="GF-20260927-0012" class="w-full px-2.5 py-1.5 text-xs bg-white border border-[#34d399] rounded-xl focus:ring-2 focus:ring-[#0e703c] outline-none font-semibold text-gray-800 placeholder-gray-400 shadow-2xs" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-[#0e703c] mb-1">
                                        Rider Code <span class="text-rose-500 font-black">*</span>
                                    </label>
                                    <input type="text" id="rider-code" placeholder="RDR-025" class="w-full px-2.5 py-1.5 text-xs bg-white border border-[#34d399] rounded-xl focus:ring-2 focus:ring-[#0e703c] outline-none font-semibold text-gray-800 placeholder-gray-400 shadow-2xs" />
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-[10px] font-semibold text-[#0e703c]">
                                <svg class="w-3.5 h-3.5 shrink-0 text-[#0e703c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16v-4m0-4h.01" />
                                </svg>
                                <span>Grab pricing applies automatically for this order</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Items List (Scrollable, Phase 1 #3: flex: 1; overflow-y: auto; min-height: 240px) -->
                <div id="cart-items" class="flex-1 min-h-[240px] p-3.5 overflow-y-auto divide-y divide-gray-100 space-y-2.5">
                    <div id="empty-cart-msg" class="h-full flex flex-col items-center justify-center text-gray-500 py-12 select-none">
                        <div class="w-16 h-16 rounded-full bg-[#f0f8f5] border border-emerald-100 flex items-center justify-center text-3xl mb-3 text-[#0e703c] shadow-inner">
                            🛒
                        </div>
                        <p class="text-sm font-extrabold text-gray-800">No items yet</p>
                        <p class="text-xs text-gray-500 mt-1 font-medium">Tap an item to add it</p>
                    </div>
                </div>

                <!-- Cart Footer & Checkout -->
                <div class="p-3.5 border-t border-gray-100 bg-[#f0f8f5]/40 space-y-3">
                    <!-- Expandable Line Item Discounts Row (Phase 1 #4: Hidden when cart empty) -->
                    <div id="cart-discounts-wrapper" class="hidden flex items-center justify-between text-xs pb-2 border-b border-gray-100">
                        <span class="font-extrabold text-gray-700 uppercase tracking-wider text-[10px]">Line Discounts</span>
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="openBulkSeniorModal()" class="px-2.5 py-1 rounded-lg font-bold text-[11px] bg-amber-100 hover:bg-amber-200 text-amber-900 transition" title="Apply Senior/PWD 20% to all items in cart">
                                + Senior to All
                            </button>
                            <button type="button" onclick="clearAllCartDiscounts()" class="px-2.5 py-1 rounded-lg font-bold text-[11px] bg-gray-100 hover:bg-gray-200 text-gray-600 transition" title="Reset all line discounts">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Totals Breakdown (Phase 4 #13: WCAG AA contrast > 4.5:1) -->
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-gray-600 font-semibold">
                            <span>Total Items</span>
                            <span id="cart-item-count" class="font-black text-gray-900">0</span>
                        </div>
                        <div class="flex justify-between text-gray-600 font-semibold">
                            <span>Subtotal (Gross)</span>
                            <span id="cart-subtotal" class="font-black text-gray-900">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-rose-600 font-semibold">
                            <span>Line Discounts</span>
                            <span id="cart-discount" class="font-black">-₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600 font-semibold">
                            <span>Vatable Sales</span>
                            <span id="cart-taxable" class="font-black text-gray-900">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-600 font-semibold">
                            <span>VAT (12%)</span>
                            <span id="cart-tax" class="font-black text-gray-900">₱0.00</span>
                        </div>
                        <div id="cart-vat-exempt-row" class="hidden flex justify-between text-amber-900 font-semibold bg-amber-50/90 px-2 py-0.5 rounded-md border border-amber-200/60">
                            <span>VAT-Exempt Sales (RA 9994/10754)</span>
                            <span id="cart-vat-exempt" class="font-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Total Due</span>
                            <span id="cart-total" class="text-2xl font-black text-[#0e703c]">₱0.00</span>
                        </div>
                    </div>

                    <!-- 56px Tall Payment Button (Phase 5 #16) -->
                    <button id="checkout-btn" onclick="openPaymentModal()" disabled class="w-full h-14 min-h-[56px] rounded-2xl bg-gray-200 text-gray-500 font-bold flex items-center justify-center gap-2 cursor-not-allowed select-none transition-all">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Add items to start</span>
                    </button>
                    <span id="btn-total" class="hidden">0.00</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Clear Cart Confirmation Modal (Phase 5 #15) -->
    <div id="clear-cart-confirm-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-4" style="display: none;" onclick="if(event.target === this) closeClearConfirmModal()">
        <div class="shift-modal-box bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border border-gray-100">
            <div class="w-14 h-14 rounded-full bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 mx-auto mb-4 text-2xl shadow-inner">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="font-extrabold text-gray-900 text-lg">Clear Order Cart?</h3>
            <p id="clear-cart-modal-msg" class="text-xs text-gray-600 mt-2 leading-relaxed">
                Are you sure you want to clear this order? All items and discounts will be removed.
            </p>
            <div class="grid grid-cols-2 gap-3 mt-6">
                <button type="button" onclick="closeClearConfirmModal()" class="py-2.5 px-4 rounded-xl border border-gray-300 font-bold text-xs text-gray-700 hover:bg-gray-100 transition">
                    Keep Items
                </button>
                <button type="button" onclick="confirmClearCart()" class="py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition">
                    Clear Order
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- INVENTIFY SHIFT MANAGEMENT MODALS (VIDEO DUPLICATE) -->
    <!-- ========================================== -->

    <!-- 1. Settings Modal (Matching video 00:02-00:03) -->
    <div id="pos-settings-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-4 overflow-hidden" style="display: none;" onclick="if(event.target === this) closeSettingsModal()">
        <div class="shift-modal-box bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-100 flex flex-col my-auto" style="max-height: 90vh;">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]/60 shrink-0">
                <h3 class="font-bold text-gray-900 text-lg">Settings</h3>
                <button type="button" onclick="closeSettingsModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-6 overflow-y-auto flex-1 custom-modal-scrollbar" style="min-height: 0;">
                <!-- User Shift Toggle -->
                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-900 text-sm">User Shift</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Require users to start a shift before accessing POS</p>
                        </div>
                        <!-- Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="setting-user-shift" class="sr-only peer" onchange="toggleUserShiftLabel(this)">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#155d49]"></div>
                        </label>
                    </div>
                    <div class="mt-1 text-right">
                        <span id="setting-user-shift-text" class="text-xs font-semibold text-[#155d49]">Enabled</span>
                    </div>
                </div>

                <!-- Shift Report Permission -->
                <div class="border-t border-gray-100 pt-5">
                    <h4 class="font-bold text-gray-900 text-sm">Shift Report Permission</h4>
                    <p class="text-xs text-gray-500 mt-0.5 mb-3">Choose which data non-admin users can view</p>
                    <div class="space-y-2.5">
                        <label class="flex items-center gap-2.5 text-xs font-semibold text-gray-700 cursor-pointer">
                            <input type="checkbox" id="perm-cash-movement" checked class="rounded text-[#155d49] focus:ring-[#155d49] w-4 h-4">
                            <span>Cash Movement</span>
                        </label>
                        <label class="flex items-center gap-2.5 text-xs font-semibold text-gray-700 cursor-pointer">
                            <input type="checkbox" id="perm-sales-summary" checked class="rounded text-[#155d49] focus:ring-[#155d49] w-4 h-4">
                            <span>Sales Summary</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" onclick="closeSettingsModal()" class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                <button type="button" onclick="saveSettings()" id="btn-save-settings" class="px-6 py-2.5 rounded-xl bg-[#155d49] hover:bg-[#114a3b] text-xs font-bold text-white shadow-sm transition">Save</button>
            </div>
        </div>
    </div>

    <!-- 2. Start Shift Modal (Matching video 00:04-00:05) -->
    <div id="start-shift-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-3 sm:p-4 overflow-hidden" style="display: none;" onclick="if(event.target === this) closeStartShiftModal()">
        <div class="shift-modal-box bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-100 flex flex-col my-auto" style="max-height: 90vh;">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]/60 shrink-0">
                <h3 class="font-bold text-gray-900 text-lg">Shift</h3>
                <button type="button" onclick="closeStartShiftModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-5 overflow-y-auto flex-1 custom-modal-scrollbar" style="min-height: 0;">
                <p class="text-xs text-gray-500 leading-relaxed">
                    Set the starting cash amount and compare it with the actual cash at the end of the shift
                </p>

                <!-- Starting Cash Input Box -->
                <div class="border border-gray-300 focus-within:border-[#155d49] focus-within:ring-2 focus-within:ring-[#155d49]/20 rounded-2xl p-3 bg-white transition">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Starting Cash</label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-lg font-bold text-gray-400">₱</span>
                        <input type="number" step="0.01" min="0" id="start-shift-cash" value="2000" class="w-full p-0 border-0 text-2xl font-black text-gray-900 focus:ring-0 outline-none" placeholder="0.00" required />
                    </div>
                </div>

                <!-- Quick Denomination Chips -->
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setStartingCash(500)" class="px-3 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] text-gray-700 text-xs font-bold rounded-xl border border-gray-200 transition">₱500</button>
                    <button type="button" onclick="setStartingCash(1000)" class="px-3 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] text-gray-700 text-xs font-bold rounded-xl border border-gray-200 transition">₱1,000</button>
                    <button type="button" onclick="setStartingCash(2000)" class="px-3 py-1 bg-emerald-50 text-[#155d49] border border-emerald-200 text-xs font-bold rounded-xl transition">₱2,000</button>
                    <button type="button" onclick="setStartingCash(5000)" class="px-3 py-1 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] text-gray-700 text-xs font-bold rounded-xl border border-gray-200 transition">₱5,000</button>
                    <button type="button" onclick="setStartingCash(0)" class="px-2.5 py-1 text-gray-400 hover:text-gray-600 text-xs font-semibold transition">Clear</button>
                </div>

                <!-- Open Cash Drawer Checkbox -->
                <div class="flex items-center gap-2.5 pt-1">
                    <input type="checkbox" id="start-shift-drawer-checkbox" checked class="rounded text-[#155d49] focus:ring-[#155d49] w-4 h-4">
                    <label for="start-shift-drawer-checkbox" class="text-xs font-semibold text-gray-700 cursor-pointer">Open Cash Drawer</label>
                </div>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" onclick="closeStartShiftModal()" class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                <button type="button" onclick="submitStartShift()" id="btn-submit-start-shift" class="px-6 py-2.5 rounded-xl bg-[#155d49] hover:bg-[#114a3b] text-xs font-bold text-white shadow-sm transition flex items-center gap-2">
                    <span>Start Shift</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Active Shift & End Shift Reconciliation Modal (Matching video 00:07-00:22) -->
    <div id="shift-reconcile-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-2 sm:p-4 overflow-hidden" style="display: none;" onclick="if(event.target === this) closeReconcileModal()">
        <div class="shift-modal-box bg-white rounded-2xl sm:rounded-3xl w-full shadow-2xl border border-gray-100 flex flex-col overflow-hidden my-auto relative" style="max-width: 520px; height: 86vh; max-height: 720px;">
            <!-- Modal Header (Fixed at top) -->
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]/80 shrink-0 select-none">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-white border border-emerald-100 flex items-center justify-center text-emerald-800 shadow-xs text-sm">⏱️</span>
                    <h3 class="font-bold text-gray-900 text-lg">Shift</h3>
                </div>
                <button type="button" onclick="closeReconcileModal()" class="text-gray-400 hover:text-gray-700 p-1.5 rounded-xl hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body (Scrollable with min-h-0) -->
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 custom-modal-scrollbar" style="min-height: 0; overscroll-behavior: contain;">
                <!-- Shift Started By & Datetime (Matching video 00:07) -->
                <div class="flex items-center justify-between text-xs text-gray-600 border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-500">Shift Started By :</span>
                        <span id="reconcile-opened-by" class="font-bold text-gray-900">Admin</span>
                    </div>
                    <div id="reconcile-opened-at" class="font-semibold text-gray-500">
                        --/--/----, --:-- --
                    </div>
                </div>

                <!-- Cash Drawer Summary Box (Matching video 00:07) -->
                <div class="bg-gray-50/80 rounded-2xl p-4 border border-gray-200/80 space-y-2 text-xs">
                    <div class="font-bold text-gray-900 uppercase tracking-wider text-[11px] mb-2">Cash Drawer</div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500 font-medium">Starting Cash</span>
                        <span id="reconcile-starting-cash" class="font-bold text-gray-900">: ₱ 0.00</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500 font-medium">Cash In</span>
                        <span id="reconcile-cash-in" class="font-bold text-gray-900">: ₱ 0.00</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500 font-medium">Cash Out</span>
                        <span id="reconcile-cash-out" class="font-bold text-gray-900">: ₱ 0.00</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-t border-gray-200/60 pt-2 font-bold">
                        <span class="text-gray-800">Expected Cash</span>
                        <span id="reconcile-expected-cash" class="text-gray-900 text-sm">: ₱ 0.00</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500 font-medium">Difference</span>
                        <span id="reconcile-difference" class="font-bold text-gray-900">: ₱ 0.00</span>
                    </div>
                    <div class="flex justify-between items-center py-1">
                        <span class="text-gray-500 font-medium">Status</span>
                        <div class="flex items-center gap-1.5">
                            <span>:</span>
                            <span id="reconcile-status-badge" class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800">Cash Balanced</span>
                        </div>
                    </div>
                </div>

                <!-- Actual Cash Input Box (Matching video 00:09) -->
                <div class="space-y-2">
                    <div class="border border-gray-300 focus-within:border-[#155d49] focus-within:ring-2 focus-within:ring-[#155d49]/20 rounded-2xl p-3 bg-white transition">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1">Actual Cash</label>
                        <div class="flex items-center gap-1.5">
                            <span class="text-lg font-bold text-gray-400">₱</span>
                            <input type="number" step="0.01" min="0" id="reconcile-actual-cash" oninput="liveUpdateReconciliation()" class="w-full p-0 border-0 text-2xl font-black text-gray-900 focus:ring-0 outline-none" placeholder="Enter actual cash" />
                        </div>
                    </div>

                    <!-- Sub-row: Open Cash Drawer Checkbox & Live Status Indicator -->
                    <div class="flex items-center justify-between px-1">
                        <button type="button" onclick="triggerOpenDrawer()" class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-[#155d49] transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            <span>Open Cash Drawer</span>
                        </button>
                        <span id="reconcile-live-indicator" class="text-xs font-bold px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800">Cash Balanced</span>
                    </div>
                </div>

                <!-- Accordion 1: Cash Movement (Matching video 00:15) -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition">
                    <div onclick="toggleAccordion('acc-cash-movement')" class="w-full p-3.5 bg-gray-50/70 hover:bg-gray-100/70 flex items-center justify-between text-xs font-bold text-gray-800 transition cursor-pointer select-none">
                        <span class="flex items-center gap-1.5">
                            <span id="acc-cash-movement-caret" class="text-emerald-700 text-[10px] transition-transform">▼</span>
                            <span>Cash Movement</span>
                        </span>
                        <button type="button" onclick="event.stopPropagation(); openCashMovementModal()" class="px-2.5 py-1 text-[11px] bg-white border border-gray-200 rounded-lg text-[#155d49] hover:bg-emerald-50 transition shadow-xs">+ Add Cash In/Out</button>
                    </div>
                    <div id="acc-cash-movement" class="p-4 space-y-3 border-t border-gray-100 bg-white">
                        <div class="flex gap-2">
                            <button type="button" onclick="setCashMovementTab('in')" id="tab-cm-in" class="px-3 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg shadow-xs">Cash In</button>
                            <button type="button" onclick="setCashMovementTab('out')" id="tab-cm-out" class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200">Cash Out</button>
                        </div>
                        <div id="cm-tab-in-content" class="space-y-2 text-xs">
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600 font-medium">POS Transaction</span>
                                <span id="reconcile-pos-cash-sales" class="font-bold text-gray-900">: ₱ 0.00</span>
                            </div>
                            <div id="reconcile-manual-in-list" class="space-y-1.5 pt-1 border-t border-gray-100"></div>
                        </div>
                        <div id="cm-tab-out-content" class="hidden space-y-2 text-xs">
                            <div id="reconcile-manual-out-list" class="space-y-1.5">
                                <p class="text-gray-400 text-center py-2">No Cash Out recorded</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion 2: Sales Summary (Matching video 00:18) -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition">
                    <div onclick="toggleAccordion('acc-sales-summary')" class="w-full p-3.5 bg-gray-50/70 hover:bg-gray-100/70 flex items-center justify-between text-xs font-bold text-gray-800 transition cursor-pointer select-none">
                        <span class="flex items-center gap-1.5">
                            <span id="acc-sales-summary-caret" class="text-emerald-700 text-[10px] transition-transform">▼</span>
                            <span>Sales Summary</span>
                        </span>
                    </div>
                    <div id="acc-sales-summary" class="p-4 space-y-3 border-t border-gray-100 bg-white">
                        <div class="flex gap-2">
                            <button type="button" onclick="setSalesSummaryTab('sales')" id="tab-ss-sales" class="px-3 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg shadow-xs">Sales</button>
                            <button type="button" onclick="setSalesSummaryTab('tx')" id="tab-ss-tx" class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200">Transactions</button>
                        </div>
                        <div id="ss-tab-sales-content" class="space-y-1.5 text-xs">
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600 font-medium">Gross Sales</span>
                                <span id="reconcile-gross-sales" class="font-bold text-gray-900">: ₱ 0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600 font-medium">Discounts</span>
                                <span id="reconcile-discounts" class="font-bold text-gray-900">: ₱ 0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600 font-medium">Service Fee</span>
                                <span class="font-bold text-gray-900">: ₱ 0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600 font-medium">Delivery Fee</span>
                                <span class="font-bold text-gray-900">: ₱ 0.00</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-t border-gray-100 pt-2 font-bold">
                                <span class="text-gray-900">Net Sales</span>
                                <span id="reconcile-net-sales" class="text-emerald-700 font-black">: ₱ 0.00</span>
                            </div>
                        </div>
                        <div id="ss-tab-tx-content" class="hidden space-y-2 text-xs">
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600 font-medium">Completed Transactions</span>
                                <span id="reconcile-tx-count" class="font-bold text-gray-900">: 0</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600 font-medium">Voids & Refunds</span>
                                <span id="reconcile-void-count" class="font-bold text-rose-600">: 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion 3: Sales Breakdown (Matching video 00:21) -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition">
                    <div onclick="toggleAccordion('acc-sales-breakdown')" class="w-full p-3.5 bg-gray-50/70 hover:bg-gray-100/70 flex items-center justify-between text-xs font-bold text-gray-800 transition cursor-pointer select-none">
                        <span class="flex items-center gap-1.5">
                            <span id="acc-sales-breakdown-caret" class="text-emerald-700 text-[10px] transition-transform">▼</span>
                            <span>Sales Breakdown</span>
                        </span>
                        <span class="text-[11px] font-semibold text-gray-500">Payment Method</span>
                    </div>
                    <div id="acc-sales-breakdown" class="p-4 space-y-2 border-t border-gray-100 bg-white text-xs">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 font-medium">Cash</span>
                            <span id="reconcile-breakdown-cash" class="font-bold text-gray-900">: ₱ 0.00</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 font-medium">Online Payment</span>
                            <span id="reconcile-breakdown-online" class="font-bold text-gray-900">: ₱ 0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Discrepancy Reason Input (Shown if difference != 0) -->
                <div id="reconcile-discrepancy-box" class="hidden space-y-1.5 pt-2">
                    <label class="block text-xs font-bold text-rose-700">Reason for Cash Discrepancy (Optional)</label>
                    <input type="text" id="reconcile-discrepancy-reason" placeholder="e.g. Cash is short ₱150 / change discrepancy" class="w-full px-3 py-2 text-xs bg-rose-50/40 border border-rose-200 rounded-xl focus:ring-2 focus:ring-rose-500 outline-none text-gray-800 font-medium" />
                </div>
            </div>

            <!-- Modal Footer (Fixed at bottom) -->
            <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2.5 shrink-0 select-none">
                <button type="button" onclick="printShiftThermalReport()" class="px-3.5 py-2.5 rounded-xl border border-gray-300 bg-white text-xs font-bold text-gray-700 hover:bg-gray-100 transition flex items-center gap-1.5 shadow-xs">
                    <span>🖨️</span>
                    <span>Print Summary</span>
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeReconcileModal()" class="px-4 sm:px-5 py-2.5 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                    <button type="button" onclick="promptEndShiftSecurityAuth()" id="btn-submit-end-shift" class="px-5 sm:px-6 py-2.5 rounded-xl bg-[#155d49] hover:bg-[#114a3b] text-xs font-bold text-white shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>End Shift</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3b. Security Authorization Modal for Ending Shift -->
    <div id="shift-auth-modal" class="fixed inset-0 bg-black/70 backdrop-blur-xs z-[70] flex items-center justify-center p-3 sm:p-4 overflow-hidden" style="display: none;" onclick="if(event.target === this) closeShiftAuthModal()">
        <div class="shift-modal-box bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-gray-100 flex flex-col my-auto" style="max-height: 90vh;">
            <!-- Header -->
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]/80 shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-emerald-100/80 border border-emerald-200 text-emerald-800 flex items-center justify-center text-lg">🛡️</span>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base leading-tight">Supervisor Authorization</h3>
                        <p class="text-[11px] text-gray-500 font-medium">Supervisor, Manager, or Owner required</p>
                    </div>
                </div>
                <button type="button" onclick="closeShiftAuthModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4 overflow-y-auto flex-1 custom-modal-scrollbar" style="min-height: 0;">
                <!-- Summary preview box -->
                <div class="bg-gray-50 border border-gray-200/80 rounded-2xl p-3.5 space-y-1.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Cashier Ending Shift:</span>
                        <span id="shift-auth-summary-cashier" class="font-bold text-gray-800">Admin</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 font-medium">Actual Cash Declared:</span>
                        <span id="shift-auth-summary-actual" class="font-black text-gray-900">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center pt-1 border-t border-gray-200/60">
                        <span class="text-gray-500 font-medium">Drawer Reconciliation:</span>
                        <span id="shift-auth-summary-status" class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">Cash Balanced</span>
                    </div>
                </div>

                <!-- Error message alert -->
                <div id="shift-auth-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold"></div>

                <!-- Credential Inputs -->
                <div class="space-y-3 pt-1">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Supervisor / Manager / Owner Email</label>
                        <input type="email" id="shift-auth-email" placeholder="e.g. supervisor@coffee.com" autocomplete="username" class="w-full px-3.5 py-2.5 text-xs bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] font-medium text-gray-900 outline-none transition" required />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Password</label>
                        <input type="password" id="shift-auth-password" placeholder="Enter supervisor or manager password" autocomplete="current-password" class="w-full px-3.5 py-2.5 text-xs bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] font-medium text-gray-900 outline-none transition" required />
                    </div>
                </div>

                <p class="text-[11px] text-gray-400 leading-relaxed italic">
                    🔒 An audit log entry with the authorizing supervisor's name and role will be recorded upon closing this shift.
                </p>
            </div>

            <!-- Footer -->
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2.5 shrink-0">
                <button type="button" onclick="closeShiftAuthModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                <button type="button" onclick="confirmEndShiftWithAuth()" id="btn-confirm-auth-end-shift" class="px-5 py-2.5 rounded-xl bg-[#155d49] hover:bg-[#114a3b] text-xs font-bold text-white shadow-sm transition flex items-center gap-1.5">
                    <span>Authorize & End Shift</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 4. Record Cash Movement Modal (Pay-In / Pay-Out) -->
    <div id="cash-movement-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[60] flex items-center justify-center p-3 sm:p-4 overflow-hidden" style="display: none;" onclick="if(event.target === this) closeCashMovementModal()">
        <div class="shift-modal-box bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl border border-gray-100 flex flex-col my-auto" style="max-height: 90vh;">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]/60 shrink-0">
                <h3 class="font-bold text-gray-900 text-base">Record Cash Movement</h3>
                <button type="button" onclick="closeCashMovementModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4 overflow-y-auto flex-1 custom-modal-scrollbar" style="min-height: 0;">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Movement Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="selectCmType('cash_in')" id="cm-btn-in" class="py-2 rounded-xl text-xs font-bold border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] transition">Cash In (Pay-in)</button>
                        <button type="button" onclick="selectCmType('cash_out')" id="cm-btn-out" class="py-2 rounded-xl text-xs font-bold border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 transition">Cash Out (Drop)</button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Amount (₱)</label>
                    <input type="number" step="0.01" min="0.01" id="cm-amount" placeholder="0.00" class="w-full px-3 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] font-bold text-gray-900 outline-none" required />
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Reason / Description</label>
                    <input type="text" id="cm-reason" placeholder="e.g. Added change fund / Ice purchase payout" class="w-full px-3 py-2 text-xs bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] font-medium text-gray-800 outline-none" required />
                </div>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-2 shrink-0">
                <button type="button" onclick="closeCashMovementModal()" class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition">Cancel</button>
                <button type="button" onclick="submitCashMovement()" id="btn-save-cm" class="px-5 py-2 rounded-xl bg-[#155d49] hover:bg-[#114a3b] text-xs font-bold text-white shadow-sm transition">Save Movement</button>
            </div>
        </div>
    </div>

    <!-- Product Customization Modal (Size & Add-ons) -->
    <div id="product-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-fade-in border border-emerald-100">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-xl">☕</div>
                    <div>
                        <h3 id="modal-product-name" class="font-extrabold text-gray-900 text-lg">Product Name</h3>
                        <p id="modal-product-category" class="text-xs font-semibold text-[#155d49] uppercase">Category</p>
                    </div>
                </div>
                <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                <!-- Size Selection -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2.5">Select Cup Size</label>
                    <div id="modal-sizes" class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <!-- Add-ons Selection -->
                @if($addOns->count() > 0)
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2.5">Add-ons (Optional)</label>
                        <div class="space-y-2">
                            @foreach($addOns as $addOn)
                                <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-[#f0f8f5] cursor-pointer transition">
                                    <div class="flex items-center gap-2.5">
                                        <input type="checkbox" name="modal_addon" value="{{ $addOn->id }}" data-name="{{ $addOn->name }}" data-price="{{ $addOn->price }}" class="rounded text-[#155d49] focus:ring-[#155d49]">
                                        <span class="text-sm font-semibold text-gray-800">{{ $addOn->name }}</span>
                                    </div>
                                    <span class="text-xs font-bold text-[#155d49]">+₱{{ number_format($addOn->price, 2) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity -->
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <span class="text-sm font-bold text-gray-800">Quantity</span>
                    <div class="flex items-center gap-2">
                        <button onclick="adjustModalQty(-1)" class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 flex items-center justify-center transition">-</button>
                        <span id="modal-qty" class="w-8 text-center font-extrabold text-gray-900 text-base">1</span>
                        <button onclick="adjustModalQty(1)" class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 font-bold text-gray-700 flex items-center justify-center transition">+</button>
                    </div>
                </div>
            </div>

            <div class="p-5 bg-[#f0f8f5] border-t border-gray-100">
                <button onclick="addCurrentModalItemToCart()" class="w-full py-3.5 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold rounded-2xl shadow transition flex items-center justify-center gap-2">
                    <span>Add to Order</span>
                    <span>•</span>
                    <span id="modal-calculated-price">₱0.00</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Line Item Discount Modal (RA 9994 / RA 10754 Compliant) -->
    <div id="line-discount-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl animate-fade-in border border-emerald-100 flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5] shrink-0">
                <div class="flex items-center gap-2 text-[#155d49]">
                    <span class="text-xl">🏷️</span>
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base">Line Item Discount</h3>
                        <p id="line-disc-item-name" class="text-xs text-gray-500 font-semibold truncate max-w-[260px]">Product Name</p>
                    </div>
                </div>
                <button type="button" onclick="closeLineDiscountModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto flex-1 custom-modal-scrollbar">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Select Discount Type</label>

                    <!-- None / Regular -->
                    <label class="flex items-center justify-between p-3 rounded-2xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition line-disc-opt" id="opt-disc-none">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="line_discount_choice" value="none" onchange="onLineDiscountChoiceChange('none')" class="text-[#155d49] focus:ring-[#155d49]">
                            <div>
                                <div class="font-bold text-sm text-gray-900">Regular (No Discount)</div>
                                <div class="text-[11px] text-gray-500">Standard 12% VAT applies</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-gray-400">0%</span>
                    </label>

                    <!-- Senior / PWD -->
                    <label class="flex items-center justify-between p-3 rounded-2xl border-2 border-amber-200 bg-amber-50/50 hover:bg-amber-50 cursor-pointer transition line-disc-opt" id="opt-disc-pwd_senior">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="line_discount_choice" value="pwd_senior" onchange="onLineDiscountChoiceChange('pwd_senior')" class="text-[#155d49] focus:ring-[#155d49]">
                            <div>
                                <div class="font-bold text-sm text-amber-950 flex items-center gap-1.5">
                                    <span>Senior Citizen / PWD</span>
                                    <span class="px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 text-[10px] font-black uppercase">RA 9994/10754</span>
                                </div>
                                <div class="text-[11px] text-amber-800">20% Discount + 100% VAT Exemption</div>
                            </div>
                        </div>
                        <span class="text-xs font-black text-amber-900">20%</span>
                    </label>

                    <!-- Senior / PWD ID Input (Visible when Senior/PWD is chosen) -->
                    <div id="line-disc-id-section" class="hidden p-3 space-y-1 bg-amber-50/90 rounded-xl border border-amber-200">
                        <label class="block text-[11px] font-bold text-amber-950">Senior Citizen / PWD ID Number (OSCA / LGU)</label>
                        <input type="text" id="line-disc-id-input" placeholder="e.g. OSCA-2024-00123 / PWD-0987" class="w-full px-3 py-1.5 text-xs bg-white border border-amber-300 rounded-lg focus:ring-2 focus:ring-amber-500 outline-none font-mono" />
                        <p class="text-[10px] text-amber-700">Records OSCA/PWD ID for Philippine regulatory audit compliance.</p>
                    </div>

                    <!-- Staff / Employee -->
                    <label class="flex items-center justify-between p-3 rounded-2xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition line-disc-opt" id="opt-disc-employee">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="line_discount_choice" value="employee" onchange="onLineDiscountChoiceChange('employee')" class="text-[#155d49] focus:ring-[#155d49]">
                            <div>
                                <div class="font-bold text-sm text-gray-900">Staff / Employee Discount</div>
                                <div class="text-[11px] text-gray-500">10% Discount (Vatable)</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-[#155d49]">10%</span>
                    </label>

                    <!-- Custom Percentage -->
                    <label class="flex items-center justify-between p-3 rounded-2xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition line-disc-opt" id="opt-disc-custom_pct">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="line_discount_choice" value="custom_pct" onchange="onLineDiscountChoiceChange('custom_pct')" class="text-[#155d49] focus:ring-[#155d49]">
                            <div>
                                <div class="font-bold text-sm text-gray-900">Custom Percentage (%)</div>
                                <div class="text-[11px] text-gray-500">Promo / Manager special discount</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-purple-700">%</span>
                    </label>

                    <div id="line-disc-custom-pct-section" class="hidden p-3 space-y-1 bg-purple-50 rounded-xl border border-purple-200">
                        <label class="block text-[11px] font-bold text-purple-950">Discount Percentage (%)</label>
                        <input type="number" id="line-disc-pct-input" min="0" max="100" step="1" placeholder="e.g. 15" oninput="updateLineDiscountPreview()" class="w-full px-3 py-1.5 text-xs bg-white border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none font-bold text-gray-900" />
                    </div>

                    <!-- Custom Fixed Amount -->
                    <label class="flex items-center justify-between p-3 rounded-2xl border-2 border-gray-200 hover:border-gray-300 cursor-pointer transition line-disc-opt" id="opt-disc-custom_fixed">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="line_discount_choice" value="custom_fixed" onchange="onLineDiscountChoiceChange('custom_fixed')" class="text-[#155d49] focus:ring-[#155d49]">
                            <div>
                                <div class="font-bold text-sm text-gray-900">Custom Fixed Amount (₱)</div>
                                <div class="text-[11px] text-gray-500">Deduct fixed peso amount</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-purple-700">₱</span>
                    </label>

                    <div id="line-disc-custom-fixed-section" class="hidden p-3 space-y-1 bg-purple-50 rounded-xl border border-purple-200">
                        <label class="block text-[11px] font-bold text-purple-950">Discount Amount (₱)</label>
                        <input type="number" id="line-disc-fixed-input" min="0" step="0.01" placeholder="e.g. 50.00" oninput="updateLineDiscountPreview()" class="w-full px-3 py-1.5 text-xs bg-white border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none font-bold text-gray-900" />
                    </div>
                </div>

                <!-- Live Preview Card -->
                <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-200 text-xs space-y-1.5">
                    <div class="flex justify-between text-gray-500">
                        <span>Item Subtotal:</span>
                        <span id="line-prev-subtotal" class="font-bold text-gray-900">₱0.00</span>
                    </div>
                    <div class="flex justify-between text-rose-600">
                        <span>Line Discount:</span>
                        <span id="line-prev-discount" class="font-bold">-₱0.00</span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Tax Status:</span>
                        <span id="line-prev-tax-status" class="font-bold text-emerald-800">Vatable (12%)</span>
                    </div>
                    <div class="flex justify-between text-gray-900 font-black text-sm pt-1.5 border-t border-gray-200">
                        <span>Line Total:</span>
                        <span id="line-prev-total" class="text-base text-[#155d49]">₱0.00</span>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2 shrink-0">
                <button type="button" onclick="applyLineDiscountToAll()" class="px-3 py-2 rounded-xl border border-emerald-300 bg-white hover:bg-emerald-50 text-[11px] font-bold text-[#155d49] transition">
                    Apply to All Items
                </button>
                <div class="flex gap-2">
                    <button type="button" onclick="closeLineDiscountModal()" class="px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="button" onclick="saveLineDiscount()" class="px-4 py-2 rounded-xl bg-[#155d49] hover:bg-[#114a3b] text-xs font-bold text-white shadow-sm transition">
                        Apply Discount
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal (Optimized for Touchscreen Tablets & Baristas) -->
    <div id="payment-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-emerald-100 flex flex-col max-h-[94vh]">
            <div class="p-4 sm:p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5] shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-[#155d49] border border-white shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base sm:text-lg">Checkout & Payment</h3>
                        <p class="text-xs text-gray-500">Touch presets or enter tendered amount</p>
                    </div>
                </div>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-4 sm:p-5 space-y-3.5 overflow-y-auto custom-modal-scrollbar flex-1">
                <!-- Total Amount Banner -->
                <div class="bg-[#f0f8f5] border border-emerald-200 rounded-2xl p-3.5 text-center space-y-1.5">
                    <span class="text-[11px] font-bold text-[#155d49] uppercase tracking-wider">Total Amount Due</span>
                    <h2 id="pay-modal-total" class="text-3xl sm:text-4xl font-black text-[#155d49]">₱0.00</h2>
                    <div class="flex flex-wrap justify-center items-center gap-x-4 gap-y-1 text-xs text-gray-600 pt-2 border-t border-emerald-200/60 font-medium">
                        <span>Subtotal: <strong id="pay-modal-subtotal" class="text-gray-900">₱0.00</strong></span>
                        <span id="pay-modal-discount-wrap">Line Discounts: <strong id="pay-modal-discount" class="text-rose-600">-₱0.00</strong></span>
                        <span>Vatable Sales: <strong id="pay-modal-vatable" class="text-gray-900">₱0.00</strong></span>
                        <span>VAT (12%): <strong id="pay-modal-tax" class="text-gray-900">₱0.00</strong></span>
                        <span id="pay-modal-exempt-wrap" class="hidden">VAT-Exempt: <strong id="pay-modal-exempt" class="text-amber-800">₱0.00</strong></span>
                    </div>
                </div>

                <!-- Payment Method Tabs -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="setPaymentMethod('cash')" id="pm-cash" class="pm-btn active py-2.5 px-2 border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition shadow-xs touch-manipulation select-none active:scale-98">
                            <span class="text-base sm:text-lg">💵</span>
                            <span>Cash</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('online')" id="pm-online" class="pm-btn py-2.5 px-2 border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition touch-manipulation select-none active:scale-98">
                            <span class="text-base sm:text-lg">📲</span>
                            <span>Online</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('split')" id="pm-split" class="pm-btn py-2.5 px-2 border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition touch-manipulation select-none active:scale-98">
                            <span class="text-base sm:text-lg">⚖️</span>
                            <span>Split</span>
                        </button>
                    </div>
                </div>

                <!-- Cash Section with On-Screen Touch Numpad -->
                <div id="cash-section" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Amount Tendered</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-black text-lg pointer-events-none">₱</span>
                                <input type="number" step="0.01" id="amount-tendered" oninput="calculateChange()" class="w-full pl-8 pr-3 py-2 text-2xl font-black text-gray-900 border-2 border-emerald-300 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none text-right font-mono" placeholder="0.00" />
                            </div>
                        </div>

                        <!-- Change Display -->
                        <div class="flex flex-col justify-between p-2.5 bg-[#f0f8f5] rounded-xl border border-emerald-200">
                            <span class="text-xs font-bold text-[#155d49] uppercase tracking-wider">Change to Return</span>
                            <span id="change-display" class="text-2xl font-black text-[#155d49] text-right font-mono">₱0.00</span>
                        </div>
                    </div>

                    <!-- Quick Cash Presets -->
                    <div>
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">Quick Cash Presets</span>
                        <div class="grid grid-cols-5 gap-1.5 sm:gap-2">
                            <button type="button" onclick="setQuickCash('exact')" class="py-2.5 px-1 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 border border-gray-200 hover:border-[#155d49] rounded-xl text-xs sm:text-sm font-extrabold transition shadow-2xs active:scale-95 text-center touch-manipulation select-none">Exact</button>
                            <button type="button" onclick="setQuickCash(100)" class="py-2.5 px-1 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 border border-gray-200 hover:border-[#155d49] rounded-xl text-xs sm:text-sm font-extrabold transition shadow-2xs active:scale-95 text-center touch-manipulation select-none">₱100</button>
                            <button type="button" onclick="setQuickCash(200)" class="py-2.5 px-1 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 border border-gray-200 hover:border-[#155d49] rounded-xl text-xs sm:text-sm font-extrabold transition shadow-2xs active:scale-95 text-center touch-manipulation select-none">₱200</button>
                            <button type="button" onclick="setQuickCash(500)" class="py-2.5 px-1 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 border border-gray-200 hover:border-[#155d49] rounded-xl text-xs sm:text-sm font-extrabold transition shadow-2xs active:scale-95 text-center touch-manipulation select-none">₱500</button>
                            <button type="button" onclick="setQuickCash(1000)" class="py-2.5 px-1 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 border border-gray-200 hover:border-[#155d49] rounded-xl text-xs sm:text-sm font-extrabold transition shadow-2xs active:scale-95 text-center touch-manipulation select-none">₱1,000</button>
                        </div>
                    </div>

                    <!-- On-Screen Touch Numpad (Toast / Square / Loyverse Standard for Tablets) -->
                    <div class="bg-gray-50/90 p-2 sm:p-2.5 rounded-2xl border border-gray-200">
                        <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                            <button type="button" onclick="posNumpadInput('1')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">1</button>
                            <button type="button" onclick="posNumpadInput('2')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">2</button>
                            <button type="button" onclick="posNumpadInput('3')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">3</button>

                            <button type="button" onclick="posNumpadInput('4')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">4</button>
                            <button type="button" onclick="posNumpadInput('5')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">5</button>
                            <button type="button" onclick="posNumpadInput('6')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">6</button>

                            <button type="button" onclick="posNumpadInput('7')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">7</button>
                            <button type="button" onclick="posNumpadInput('8')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">8</button>
                            <button type="button" onclick="posNumpadInput('9')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">9</button>

                            <button type="button" onclick="posNumpadInput('C')" class="h-10 sm:h-11 bg-rose-50 hover:bg-rose-100 active:bg-rose-600 active:text-white text-rose-700 rounded-xl text-xs sm:text-sm font-black border border-rose-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">CLEAR</button>
                            <button type="button" onclick="posNumpadInput('0')" class="h-10 sm:h-11 bg-white hover:bg-emerald-50 active:bg-[#155d49] active:text-white text-gray-800 rounded-xl text-lg font-black border border-gray-200 shadow-2xs transition active:scale-95 touch-manipulation select-none">0</button>
                            <button type="button" onclick="posNumpadInput('backspace')" class="h-10 sm:h-11 bg-amber-50 hover:bg-amber-100 active:bg-amber-600 active:text-white text-amber-800 rounded-xl text-lg font-black border border-amber-200 shadow-2xs transition active:scale-95 touch-manipulation select-none flex items-center justify-center" title="Backspace">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414a2 2 0 011.414-.586H19a2 2 0 012 2v10a2 2 0 01-2 2H10.828a2 2 0 01-1.414-.586L3 12z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reference Number Section (For Online Payment - Enforced per System Analysis Paper) -->
                <div id="reference-section" class="hidden space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-gray-700">Digital Reference / Approval Number</label>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-900 border border-amber-300">Required</span>
                    </div>
                    <input type="text" id="reference-number" class="w-full px-4 py-2.5 text-sm border-2 border-emerald-300 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-mono font-bold" placeholder="e.g. GCash / Maya / QRPh Ref # (e.g. 1029384756)" />
                    <p class="text-[11px] text-gray-500">Checkout is strictly verified against an external transaction reference ID.</p>
                </div>

                <!-- Split Payment Section -->
                <div id="split-section" class="hidden space-y-3 p-3.5 bg-amber-50/70 rounded-2xl border border-amber-200">
                    <div class="flex justify-between items-center text-xs font-bold text-amber-900 border-b border-amber-200/60 pb-1.5">
                        <span class="flex items-center gap-1.5"><span>⚖️</span> Split Payment Tender</span>
                        <span id="split-remaining-label" class="font-mono font-black text-amber-800">Due: ₱0.00</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">💵 Cash Amount</label>
                            <input type="number" step="0.01" min="0" id="split-cash-amount" oninput="calculateSplitChange()" placeholder="0.00" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-xl font-bold font-mono focus:ring-2 focus:ring-[#155d49] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">📲 Online Amount</label>
                            <input type="number" step="0.01" min="0" id="split-online-amount" oninput="calculateSplitChange()" placeholder="0.00" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-xl font-bold font-mono focus:ring-2 focus:ring-[#155d49] outline-none" />
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-gray-700">Online Reference #</label>
                            <span class="text-[10px] font-bold text-gray-500">For digital portion</span>
                        </div>
                        <input type="text" id="split-reference-number" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-xl font-mono focus:ring-2 focus:ring-[#155d49] outline-none" placeholder="GCash / Maya transaction ref #" />
                    </div>
                    <div class="flex justify-between items-center text-xs font-black text-gray-800 pt-1 border-t border-amber-200/60">
                        <span>Total Tendered / Change:</span>
                        <span id="split-change-display" class="font-mono text-[#155d49] font-black text-sm">₱0.00</span>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5 bg-[#f0f8f5] border-t border-gray-100 flex gap-3 shrink-0">
                <button type="button" onclick="closePaymentModal()" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition touch-manipulation active:scale-98">Cancel</button>
                <button type="button" id="submit-order-btn" onclick="submitOrder()" class="flex-1 py-3 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold rounded-xl shadow transition flex items-center justify-center gap-2 touch-manipulation active:scale-98">
                    <span id="submit-spinner" class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                    <span id="submit-text">Complete Sale</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Receipt / Order Complete Modal (Loyverse-Style Thermal Receipt) -->
    <div id="receipt-modal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl animate-fade-in flex flex-col max-h-[92vh] border border-gray-200 my-auto">
            <!-- Modal Header -->
            <div class="p-3.5 sm:p-4 bg-[#f0f8f5] border-b border-emerald-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-[#155d49]">
                    <span class="w-6 h-6 rounded-full bg-[#155d49] text-white flex items-center justify-center text-xs font-black">✓</span>
                    <h3 class="font-extrabold text-sm sm:text-base">Order Completed!</h3>
                </div>
                <!-- Paper Width Selector (80mm default / 58mm portable) -->
                <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-emerald-200 text-[11px] font-bold">
                    <button type="button" id="btn-paper-80" onclick="setReceiptPaperWidth('80mm')" class="px-2 py-0.5 rounded-lg bg-[#155d49] text-white transition">80mm</button>
                    <button type="button" id="btn-paper-58" onclick="setReceiptPaperWidth('58mm')" class="px-2 py-0.5 rounded-lg text-gray-500 hover:text-gray-900 transition">58mm</button>
                </div>
            </div>

            <!-- Receipt Content (Thermal Slip Preview) -->
            <div class="p-3 sm:p-4 bg-gray-100 overflow-y-auto flex justify-center">
                <div id="receipt-area" class="w-full max-w-[310px] bg-white p-4 sm:p-5 shadow-md border border-gray-200 rounded-sm font-mono text-[11px] leading-tight text-black select-text transition-all">
                    <!-- Store Brand Header -->
                    <div class="text-center space-y-1">
                        <div class="w-12 h-12 rounded-full overflow-hidden bg-[#155d49] mx-auto border border-gray-300 shadow-sm flex items-center justify-center">
                            <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover" />
                        </div>
                        <h2 class="text-base font-black tracking-wider text-black">HEIM COFFEE</h2>
                        <p class="text-[10px] text-gray-700 font-semibold uppercase tracking-wider">Fresh Brews & Pastries</p>
                        <p id="rec-branch-name" class="text-[10px] text-gray-800 font-bold">Bangkal Branch</p>
                        <p id="rec-branch-address" class="text-[9px] text-gray-600">MacArthur Hwy, Bangkal, Davao City, PH</p>
                    </div>

                    <div class="border-t border-dashed border-gray-400 my-2.5"></div>

                    <!-- Order Metadata -->
                    <div class="space-y-1 text-[11px]">
                        <div class="flex justify-between font-bold text-xs text-black">
                            <span>ORDER NO:</span>
                            <span id="rec-order-no" class="font-black text-black">-</span>
                        </div>
                        <div class="flex justify-between font-bold text-[11px] text-[#155d49] bg-emerald-50 px-1 py-0.5 rounded">
                            <span>ORDER TYPE:</span>
                            <span id="rec-order-type" class="font-black uppercase tracking-wider">DINE-IN</span>
                        </div>
                        <div id="rec-grab-code-row" class="hidden flex justify-between font-bold text-[11px] text-[#0e703c] bg-emerald-50 px-1 py-0.5 rounded">
                            <span>GRAB ORDER:</span>
                            <span id="rec-grab-order-code" class="font-mono font-black">-</span>
                        </div>
                        <div id="rec-rider-code-row" class="hidden flex justify-between font-bold text-[11px] text-[#0e703c] bg-emerald-50 px-1 py-0.5 rounded">
                            <span>RIDER CODE:</span>
                            <span id="rec-rider-code" class="font-mono font-black">-</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>DATE:</span>
                            <span id="rec-date">-</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>CASHIER:</span>
                            <span id="rec-cashier">-</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-gray-400 my-2.5"></div>

                    <!-- Column Header -->
                    <div class="flex justify-between font-bold text-[10px] text-gray-700 uppercase tracking-wider pb-1">
                        <span>QTY ITEM</span>
                        <span>PRICE</span>
                    </div>
                    <div class="border-t border-dashed border-gray-300 mb-2"></div>

                    <!-- Dynamically Populated Line Items -->
                    <div id="rec-items-list" class="space-y-1.5 py-0.5">
                        <!-- Items & Add-ons injected here -->
                    </div>

                    <div class="border-t border-dashed border-gray-400 my-2.5"></div>

                    <!-- Totals Breakdown -->
                    <div class="space-y-1 text-[11px]">
                        <div class="flex justify-between text-gray-700">
                            <span>Subtotal (Gross):</span>
                            <span id="rec-subtotal" class="font-semibold text-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Total Discounts:</span>
                            <span id="rec-discount" class="font-semibold text-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Vatable Sales:</span>
                            <span id="rec-taxable" class="font-semibold text-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>VAT (12%):</span>
                            <span id="rec-tax" class="font-semibold text-black">₱0.00</span>
                        </div>
                        <div id="rec-exempt-row" class="hidden flex justify-between text-gray-700">
                            <span>VAT-Exempt Sales:</span>
                            <span id="rec-vat-exempt" class="font-semibold text-black">₱0.00</span>
                        </div>
                        
                        <div class="border-t-2 border-dashed border-black my-1.5"></div>
                        
                        <div class="flex justify-between items-baseline font-black text-sm text-black">
                            <span>TOTAL DUE:</span>
                            <span id="rec-total" class="text-base text-black">₱0.00</span>
                        </div>

                        <div class="border-t border-dashed border-gray-400 my-1.5"></div>

                        <div class="flex justify-between text-gray-700">
                            <span id="rec-pm-label">Payment:</span>
                            <span id="rec-payment-method" class="font-bold text-black">CASH</span>
                        </div>
                        <div id="rec-ref-container" class="flex justify-between text-gray-700 hidden">
                            <span>Ref #:</span>
                            <span id="rec-ref-no" class="font-mono font-bold text-black">-</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Tendered:</span>
                            <span id="rec-tendered" class="font-semibold text-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span class="font-bold">Change:</span>
                            <span id="rec-change" class="font-bold text-black">₱0.00</span>
                        </div>
                    </div>

                    <div class="border-t border-dashed border-gray-400 my-3"></div>

                    <!-- Footer Notes -->
                    <div class="text-center space-y-0.5 text-[10px] text-gray-600">
                        <p class="font-bold text-black">THANK YOU FOR CHOOSING HEIM!</p>
                        <p>Please come again.</p>
                        <p class="pt-1 text-[9px] text-gray-400">Wi-Fi: HeimGuest | PW: heimcoffee</p>
                        <p class="text-[9px] text-gray-400 font-mono tracking-tight">*** Thermal Receipt ***</p>
                    </div>
                </div>
            </div>

            <!-- Receipt Actions -->
            <div class="p-3.5 sm:p-4 bg-[#f0f8f5] border-t border-emerald-100 flex gap-2">
                <button type="button" onclick="printReceipt()" class="flex-1 py-3 bg-gray-900 hover:bg-black text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Receipt
                </button>
                <button type="button" onclick="startNewOrder()" class="flex-1 py-3 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold rounded-xl text-xs shadow transition">
                    New Order
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // State
        let cart = [];
        let currentModalProduct = null;
        let selectedModalSize = null;
        let modalQuantity = 1;
        let paymentMethod = 'cash';
        let currentOrderType = 'dine_in';
        let currentBranchId = {{ $activeShift?->branch_id ?? ($branches->first()?->id ?? 'null') }};

        function setOrderType(type) {
            currentOrderType = type;
            const types = ['dine_in', 'takeout', 'grab_delivery'];
            types.forEach(t => {
                const btn = document.getElementById(`btn-ot-${t}`);
                if (btn) {
                    if (t === type) {
                        btn.className = 'flex-1 py-2 text-center text-xs font-extrabold rounded-xl transition bg-[#0e703c] text-white shadow-xs';
                    } else {
                        btn.className = 'flex-1 py-2 text-center text-xs font-bold rounded-xl text-gray-700 hover:text-[#0e703c] hover:bg-emerald-50/50 transition';
                    }
                }
            });

            const grabBox = document.getElementById('grab-details-box');
            if (grabBox) {
                if (type === 'grab_delivery') {
                    grabBox.classList.remove('hidden');
                    const grabInput = document.getElementById('grab-order-code');
                    if (grabInput && !grabInput.value) {
                        setTimeout(() => grabInput.focus(), 50);
                    }
                } else {
                    grabBox.classList.add('hidden');
                }
            }
        }

        // Filter products by category (Phase 3 #10 & #11: Best Sellers tab & wrap pills)
        function selectCategory(categoryId, btn) {
            document.querySelectorAll('.category-btn').forEach(b => {
                b.classList.remove('active', 'bg-[#0e703c]', 'text-white', 'shadow-2xs');
                b.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-200');
            });
            if (btn) {
                btn.classList.add('active', 'bg-[#0e703c]', 'text-white', 'shadow-2xs');
                btn.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-200');
            }

            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                if (categoryId === 'all') {
                    card.classList.remove('hidden');
                } else if (categoryId === 'popular') {
                    if (card.dataset.popular === '1') {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                } else if (card.dataset.category === String(categoryId)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        // Tactile micro-interaction feedback on product card tap (Phase 2 #6, #8)
        function handleProductCardClick(product, cardEl) {
            if (cardEl) {
                cardEl.classList.add('ring-2', 'ring-[#0e703c]', 'ring-offset-1');
                setTimeout(() => {
                    cardEl.classList.remove('ring-2', 'ring-[#0e703c]', 'ring-offset-1');
                }, 150);
            }
            openProductModal(product);
        }

        // Search products
        function filterProducts() {
            const query = document.getElementById('search-input').value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                const name = card.dataset.name;
                if (name.includes(query)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        // Product Modal Logic
        function openProductModal(product) {
            currentModalProduct = product;
            modalQuantity = 1;
            document.getElementById('modal-qty').innerText = modalQuantity;
            document.getElementById('modal-product-name').innerText = product.name;
            document.getElementById('modal-product-category').innerText = product.category ? product.category.name : '';

            // Reset add-on checkboxes
            document.querySelectorAll('input[name="modal_addon"]').forEach(cb => cb.checked = false);

            // Populate Sizes
            const sizesContainer = document.getElementById('modal-sizes');
            sizesContainer.innerHTML = '';

            if (product.sizes && product.sizes.length > 0) {
                selectedModalSize = product.sizes[0];
                product.sizes.forEach((size, idx) => {
                    const isFirst = idx === 0;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = `size-choice-btn p-3 rounded-2xl border-2 text-left transition flex flex-col justify-between ${
                        isFirst ? 'border-[#155d49] bg-[#f0f8f5] text-[#155d49]' : 'border-gray-200 hover:border-gray-300 text-gray-700'
                    }`;
                    btn.innerHTML = `
                        <span class="text-[11px] font-bold uppercase">${size.name}</span>
                        <span class="text-sm font-black mt-1">₱${parseFloat(size.pivot.price).toFixed(2)}</span>
                    `;
                    btn.onclick = () => selectModalSize(size, btn);
                    sizesContainer.appendChild(btn);
                });
            }

            updateModalPrice();
            document.getElementById('product-modal').classList.remove('hidden');
        }

        function selectModalSize(size, btn) {
            selectedModalSize = size;
            document.querySelectorAll('.size-choice-btn').forEach(b => {
                b.className = 'size-choice-btn p-3 rounded-2xl border-2 border-gray-200 hover:border-gray-300 text-gray-700 text-left transition flex flex-col justify-between';
            });
            btn.className = 'size-choice-btn p-3 rounded-2xl border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] text-left transition flex flex-col justify-between';
            updateModalPrice();
        }

        function adjustModalQty(delta) {
            modalQuantity = Math.max(1, modalQuantity + delta);
            document.getElementById('modal-qty').innerText = modalQuantity;
            updateModalPrice();
        }

        function updateModalPrice() {
            if (!selectedModalSize) return;
            let unitPrice = parseFloat(selectedModalSize.pivot.price);

            // Add checked add-ons
            document.querySelectorAll('input[name="modal_addon"]:checked').forEach(cb => {
                unitPrice += parseFloat(cb.dataset.price);
            });

            const total = unitPrice * modalQuantity;
            document.getElementById('modal-calculated-price').innerText = `₱${total.toFixed(2)}`;
        }

        // Listen to addon checkbox changes
        document.querySelectorAll('input[name="modal_addon"]').forEach(cb => {
            cb.addEventListener('change', updateModalPrice);
        });

        function closeProductModal() {
            document.getElementById('product-modal').classList.add('hidden');
        }

        function addCurrentModalItemToCart() {
            if (!currentModalProduct || !selectedModalSize) return;

            const selectedAddOns = [];
            document.querySelectorAll('input[name="modal_addon"]:checked').forEach(cb => {
                selectedAddOns.push({
                    id: parseInt(cb.value),
                    name: cb.dataset.name,
                    price: parseFloat(cb.dataset.price)
                });
            });

            const unitPrice = parseFloat(selectedModalSize.pivot.price);
            const addOnTotal = selectedAddOns.reduce((sum, a) => sum + a.price, 0);
            const itemTotal = (unitPrice + addOnTotal) * modalQuantity;

            // Add to cart array with per-line discount fields
            cart.push({
                product_id: currentModalProduct.id,
                product_name: currentModalProduct.name,
                size_id: selectedModalSize.id,
                size_name: selectedModalSize.name,
                unit_price: unitPrice,
                add_ons: selectedAddOns,
                quantity: modalQuantity,
                subtotal: itemTotal,
                discount_type: 'none',
                discount_rate: 0,
                discount_value: 0,
                discount: 0,
                is_vat_exempt: false,
                tax: 0,
                total: itemTotal,
                id_number: ''
            });

            renderCart();
            closeProductModal();
        }

        // HCI: Fitts's Law & Immediate Visual Feedback for Mobile View
        function scrollToCart() {
            const cartEl = document.getElementById('pos-cart-panel');
            if (cartEl) {
                cartEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                cartEl.classList.add('ring-2', 'ring-[#155d49]', 'ring-offset-2');
                setTimeout(() => {
                    cartEl.classList.remove('ring-2', 'ring-[#155d49]', 'ring-offset-2');
                }, 1200);
            }
        }

        // Cart State & Per-Line Calculations (VAT-Inclusive Philippine Coffee Shop Standard)
        const TAX_RATE = {{ (float) \App\Models\PosSetting::get('tax_rate', 12.00) }};
        const TAX_DIVISOR = 1 + (TAX_RATE / 100);
        let activeDiscountItemIndex = null;

        function calculateCartTotals() {
            let subtotal = 0;
            let totalDiscount = 0;
            let vatableSales = 0;
            let vatExemptSales = 0;
            let totalTax = 0;
            let totalDue = 0;

            cart.forEach(item => {
                const itemGross = item.subtotal;
                subtotal += itemGross;

                let itemDisc = 0;
                let isExempt = false;
                let itemTotal = 0;

                if (item.discount_type === 'pwd_senior') {
                    // RA 9994 / RA 10754: 20% discount on Net of VAT price + VAT Exemption
                    const netOfVat = itemGross / TAX_DIVISOR;
                    itemDisc = Math.round(netOfVat * 0.20 * 100) / 100;
                    isExempt = true;
                    itemTotal = Math.round((netOfVat - itemDisc) * 100) / 100;
                } else if (item.discount_type === 'employee') {
                    itemDisc = Math.round(itemGross * 0.10 * 100) / 100;
                    itemTotal = Math.round(Math.max(0, itemGross - itemDisc) * 100) / 100;
                } else if (item.discount_type === 'custom_pct') {
                    const pct = Math.min(100, Math.max(0, parseFloat(item.discount_value) || 0));
                    itemDisc = Math.round(itemGross * (pct / 100) * 100) / 100;
                    itemTotal = Math.round(Math.max(0, itemGross - itemDisc) * 100) / 100;
                } else if (item.discount_type === 'custom_fixed') {
                    itemDisc = Math.min(itemGross, Math.max(0, parseFloat(item.discount_value) || 0));
                    itemTotal = Math.round(Math.max(0, itemGross - itemDisc) * 100) / 100;
                } else {
                    itemDisc = 0;
                    itemTotal = Math.round(itemGross * 100) / 100;
                }

                itemDisc = Math.round(itemDisc * 100) / 100;
                item.discount = itemDisc;
                totalDiscount += itemDisc;

                if (isExempt) {
                    item.is_vat_exempt = true;
                    item.tax = 0.00;
                    item.total = itemTotal;
                    vatExemptSales += itemTotal;
                } else {
                    item.is_vat_exempt = false;
                    const itemVatable = Math.round((itemTotal / TAX_DIVISOR) * 100) / 100;
                    const itemTax = Math.round((itemTotal - itemVatable) * 100) / 100;
                    item.tax = itemTax;
                    item.total = itemTotal;
                    vatableSales += itemVatable;
                    totalTax += itemTax;
                }
                totalDue += item.total;
            });

            return {
                subtotal: Math.round(subtotal * 100) / 100,
                discount: Math.round(totalDiscount * 100) / 100,
                vatableSales: Math.round(vatableSales * 100) / 100,
                vatExemptSales: Math.round(vatExemptSales * 100) / 100,
                tax: Math.round(totalTax * 100) / 100,
                taxRate: TAX_RATE,
                totalDue: Math.round(totalDue * 100) / 100
            };
        }

        // Line Item Discount Modal Handlers
        function openLineDiscountModal(index) {
            activeDiscountItemIndex = index;
            const item = cart[index];
            if (!item) return;

            document.getElementById('line-disc-item-name').innerText = `${item.quantity}x ${item.product_name} (${item.size_name})`;

            const type = item.discount_type || 'none';
            setLineDiscountRadio(type);

            const idInput = document.getElementById('line-disc-id-input');
            if (idInput) idInput.value = item.id_number || '';

            const pctInput = document.getElementById('line-disc-pct-input');
            if (pctInput) pctInput.value = (type === 'custom_pct' ? (item.discount_value || '') : '');

            const fixedInput = document.getElementById('line-disc-fixed-input');
            if (fixedInput) fixedInput.value = (type === 'custom_fixed' ? (item.discount_value || '') : '');

            onLineDiscountChoiceChange(type);
            updateLineDiscountPreview();

            document.getElementById('line-discount-modal').classList.remove('hidden');
        }

        function closeLineDiscountModal() {
            document.getElementById('line-discount-modal').classList.add('hidden');
            activeDiscountItemIndex = null;
        }

        function setLineDiscountRadio(type) {
            const rad = document.querySelector(`input[name="line_discount_choice"][value="${type}"]`);
            if (rad) rad.checked = true;
        }

        function onLineDiscountChoiceChange(type) {
            document.querySelectorAll('.line-disc-opt').forEach(el => {
                el.classList.remove('border-[#155d49]', 'bg-[#f0f8f5]');
                el.classList.add('border-gray-200');
            });
            const opt = document.getElementById(`opt-disc-${type}`);
            if (opt) {
                opt.classList.remove('border-gray-200');
                opt.classList.add('border-[#155d49]', 'bg-[#f0f8f5]');
            }

            const idSec = document.getElementById('line-disc-id-section');
            const pctSec = document.getElementById('line-disc-custom-pct-section');
            const fixSec = document.getElementById('line-disc-custom-fixed-section');

            if (idSec) idSec.classList.toggle('hidden', type !== 'pwd_senior');
            if (pctSec) pctSec.classList.toggle('hidden', type !== 'custom_pct');
            if (fixSec) fixSec.classList.toggle('hidden', type !== 'custom_fixed');

            updateLineDiscountPreview();
        }

        function updateLineDiscountPreview() {
            if (activeDiscountItemIndex === null || !cart[activeDiscountItemIndex]) return;
            const item = cart[activeDiscountItemIndex];
            const itemGross = item.subtotal;

            const rad = document.querySelector('input[name="line_discount_choice"]:checked');
            const type = rad ? rad.value : 'none';

            let disc = 0;
            let isExempt = false;
            let itemTotal = 0;

            if (type === 'pwd_senior') {
                const netOfVat = itemGross / TAX_DIVISOR;
                disc = Math.round(netOfVat * 0.20 * 100) / 100;
                isExempt = true;
                itemTotal = Math.round((netOfVat - disc) * 100) / 100;
            } else if (type === 'employee') {
                disc = Math.round(itemGross * 0.10 * 100) / 100;
                itemTotal = Math.round(Math.max(0, itemGross - disc) * 100) / 100;
            } else if (type === 'custom_pct') {
                const pct = Math.min(100, Math.max(0, parseFloat(document.getElementById('line-disc-pct-input')?.value) || 0));
                disc = Math.round(itemGross * (pct / 100) * 100) / 100;
                itemTotal = Math.round(Math.max(0, itemGross - disc) * 100) / 100;
            } else if (type === 'custom_fixed') {
                const val = parseFloat(document.getElementById('line-disc-fixed-input')?.value) || 0;
                disc = Math.min(itemGross, Math.max(0, val));
                itemTotal = Math.round(Math.max(0, itemGross - disc) * 100) / 100;
            } else {
                itemTotal = Math.round(itemGross * 100) / 100;
            }

            const vatable = isExempt ? 0 : Math.round((itemTotal / TAX_DIVISOR) * 100) / 100;
            const tax = isExempt ? 0 : Math.round((itemTotal - vatable) * 100) / 100;

            document.getElementById('line-prev-subtotal').innerText = `₱${itemGross.toFixed(2)}`;
            document.getElementById('line-prev-discount').innerText = `-₱${disc.toFixed(2)}`;
            const taxEl = document.getElementById('line-prev-tax-status');
            if (taxEl) {
                taxEl.innerText = isExempt ? 'VAT-Exempt (0%) • RA 9994/10754' : `Vatable (12% Included: ₱${tax.toFixed(2)})`;
                taxEl.className = isExempt ? 'font-bold text-amber-800' : 'font-bold text-emerald-800';
            }
            document.getElementById('line-prev-total').innerText = `₱${itemTotal.toFixed(2)}`;
        }

        function saveLineDiscount() {
            if (activeDiscountItemIndex === null || !cart[activeDiscountItemIndex]) return;
            const item = cart[activeDiscountItemIndex];

            const rad = document.querySelector('input[name="line_discount_choice"]:checked');
            const type = rad ? rad.value : 'none';

            item.discount_type = type;

            if (type === 'pwd_senior') {
                item.discount_rate = 20.00;
                item.discount_value = 20.00;
                item.id_number = document.getElementById('line-disc-id-input')?.value.trim() || '';
            } else if (type === 'employee') {
                item.discount_rate = 10.00;
                item.discount_value = 10.00;
                item.id_number = '';
            } else if (type === 'custom_pct') {
                const pct = Math.min(100, Math.max(0, parseFloat(document.getElementById('line-disc-pct-input')?.value) || 0));
                item.discount_rate = pct;
                item.discount_value = pct;
                item.id_number = '';
            } else if (type === 'custom_fixed') {
                const val = parseFloat(document.getElementById('line-disc-fixed-input')?.value) || 0;
                item.discount_rate = item.subtotal > 0 ? (val / item.subtotal) * 100 : 0;
                item.discount_value = val;
                item.id_number = '';
            } else {
                item.discount_type = 'none';
                item.discount_rate = 0;
                item.discount_value = 0;
                item.id_number = '';
            }

            closeLineDiscountModal();
            renderCart();
        }

        function applyLineDiscountToAll() {
            const rad = document.querySelector('input[name="line_discount_choice"]:checked');
            const type = rad ? rad.value : 'none';
            const idNo = document.getElementById('line-disc-id-input')?.value.trim() || '';
            const pctVal = parseFloat(document.getElementById('line-disc-pct-input')?.value) || 0;
            const fixVal = parseFloat(document.getElementById('line-disc-fixed-input')?.value) || 0;

            cart.forEach(item => {
                item.discount_type = type;
                if (type === 'pwd_senior') {
                    item.discount_rate = 20.00;
                    item.discount_value = 20.00;
                    item.id_number = idNo;
                } else if (type === 'employee') {
                    item.discount_rate = 10.00;
                    item.discount_value = 10.00;
                    item.id_number = '';
                } else if (type === 'custom_pct') {
                    item.discount_rate = pctVal;
                    item.discount_value = pctVal;
                    item.id_number = '';
                } else if (type === 'custom_fixed') {
                    item.discount_rate = 0;
                    item.discount_value = fixVal;
                    item.id_number = '';
                } else {
                    item.discount_type = 'none';
                    item.discount_rate = 0;
                    item.discount_value = 0;
                    item.id_number = '';
                }
            });

            closeLineDiscountModal();
            renderCart();
        }

        function removeLineDiscount(index) {
            if (cart[index]) {
                cart[index].discount_type = 'none';
                cart[index].discount_rate = 0;
                cart[index].discount_value = 0;
                cart[index].id_number = '';
                renderCart();
            }
        }

        function openBulkSeniorModal() {
            if (cart.length === 0) {
                alert('Add items to cart before applying discounts.');
                return;
            }
            openLineDiscountModal(0);
            setLineDiscountRadio('pwd_senior');
            onLineDiscountChoiceChange('pwd_senior');
        }

        function clearAllCartDiscounts() {
            cart.forEach(item => {
                item.discount_type = 'none';
                item.discount_rate = 0;
                item.discount_value = 0;
                item.id_number = '';
            });
            renderCart();
        }

        // Cart Rendering (Phases 1, 2, 4, 5)
        function renderCart() {
            const container = document.getElementById('cart-items');
            const mobileBadge = document.getElementById('mobile-cart-header-badge');
            const discWrapper = document.getElementById('cart-discounts-wrapper');
            const checkoutBtn = document.getElementById('checkout-btn');

            if (cart.length === 0) {
                container.innerHTML = `
                    <div id="empty-cart-msg" class="h-full flex flex-col items-center justify-center text-gray-500 py-12 select-none">
                        <div class="w-16 h-16 rounded-full bg-[#f0f8f5] border border-emerald-100 flex items-center justify-center text-3xl mb-3 text-[#0e703c] shadow-inner">
                            🛒
                        </div>
                        <p class="text-sm font-extrabold text-gray-800">No items yet</p>
                        <p class="text-xs text-gray-500 mt-1 font-medium">Tap an item to add it</p>
                    </div>
                `;
                document.getElementById('cart-item-count').innerText = '0';
                document.getElementById('cart-subtotal').innerText = '₱0.00';
                document.getElementById('cart-discount').innerText = '-₱0.00';
                document.getElementById('cart-taxable').innerText = '₱0.00';
                document.getElementById('cart-tax').innerText = '₱0.00';
                const vatExemptRow = document.getElementById('cart-vat-exempt-row');
                if (vatExemptRow) vatExemptRow.classList.add('hidden');
                document.getElementById('cart-total').innerText = '₱0.00';
                const btnTotalEl = document.getElementById('btn-total');
                if (btnTotalEl) btnTotalEl.innerText = '0.00';

                // Hide discounts row when cart is empty (Phase 1 #4)
                if (discWrapper) discWrapper.classList.add('hidden');

                // Disabled 56px payment button (Phase 4 #14 & Phase 5 #16)
                if (checkoutBtn) {
                    checkoutBtn.disabled = true;
                    checkoutBtn.className = "w-full h-14 min-h-[56px] rounded-2xl bg-gray-200 text-gray-500 font-bold flex items-center justify-center gap-2 cursor-not-allowed select-none transition-all";
                    checkoutBtn.innerHTML = `
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Add items to start</span>
                    `;
                }

                // Reset all card badges (Phase 2 #8)
                document.querySelectorAll('[id^="card-badge-"]').forEach(badge => {
                    badge.classList.add('hidden');
                    badge.innerText = '×0';
                });

                if (mobileBadge) {
                    mobileBadge.innerText = '0';
                    mobileBadge.classList.add('hidden');
                    mobileBadge.classList.remove('flex');
                }
                return;
            }

            container.innerHTML = '';
            let count = 0;
            const productQuantities = {};

            cart.forEach((item, index) => {
                count += item.quantity;
                productQuantities[item.product_id] = (productQuantities[item.product_id] || 0) + item.quantity;

                const itemRow = document.createElement('div');
                itemRow.className = 'py-2.5 flex flex-col gap-1.5 text-sm border-b border-gray-100 last:border-b-0';
                
                let addOnsHtml = '';
                if (item.add_ons && item.add_ons.length > 0) {
                    addOnsHtml = `<div class="text-[11px] text-[#0e703c] pl-2 border-l-2 border-[#0e703c]/30 font-medium">
                        ${item.add_ons.map(a => `+ ${a.name} (₱${a.price.toFixed(2)})`).join('<br>')}
                    </div>`;
                }

                // Line discount badge / button
                let discBadge = '';
                const discType = item.discount_type || 'none';
                const discAmt = item.discount || 0;

                if (discType === 'pwd_senior') {
                    discBadge = `
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="openLineDiscountModal(${index})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-50 border border-amber-300 text-amber-900 text-[10px] font-bold cursor-pointer hover:bg-amber-100 transition">
                                <span>🏷️ Senior/PWD 20% (-₱${discAmt.toFixed(2)}) [VAT-Exempt]</span>
                                ${item.id_number ? `<span class="font-mono text-amber-700 font-semibold">• ID: ${item.id_number}</span>` : ''}
                            </button>
                            <button type="button" onclick="removeLineDiscount(${index})" class="text-rose-500 hover:text-rose-700 text-xs font-black p-0.5" title="Remove discount">✕</button>
                        </div>
                    `;
                } else if (discType === 'employee') {
                    discBadge = `
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="openLineDiscountModal(${index})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-50 border border-blue-300 text-blue-900 text-[10px] font-bold cursor-pointer hover:bg-blue-100 transition">
                                <span>🏷️ Staff 10% (-₱${discAmt.toFixed(2)})</span>
                            </button>
                            <button type="button" onclick="removeLineDiscount(${index})" class="text-rose-500 hover:text-rose-700 text-xs font-black p-0.5" title="Remove discount">✕</button>
                        </div>
                    `;
                } else if (discType === 'custom_pct' || discType === 'custom_fixed') {
                    const tag = discType === 'custom_pct' ? `${item.discount_value}%` : `₱${item.discount_value}`;
                    discBadge = `
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="openLineDiscountModal(${index})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-purple-50 border border-purple-300 text-purple-900 text-[10px] font-bold cursor-pointer hover:bg-purple-100 transition">
                                <span>🏷️ Custom ${tag} (-₱${discAmt.toFixed(2)})</span>
                            </button>
                            <button type="button" onclick="removeLineDiscount(${index})" class="text-rose-500 hover:text-rose-700 text-xs font-black p-0.5" title="Remove discount">✕</button>
                        </div>
                    `;
                } else {
                    discBadge = `
                        <button type="button" onclick="openLineDiscountModal(${index})" class="self-start inline-flex items-center gap-1 px-2 py-0.5 rounded-lg border border-dashed border-gray-300 hover:border-[#0e703c] text-[10px] text-gray-500 hover:text-[#0e703c] bg-white hover:bg-emerald-50 font-semibold transition cursor-pointer">
                            <span>+ Line Discount</span>
                        </button>
                    `;
                }

                // Phase 2 #7: Enlarge touch targets (buttons >= 40px with >= 8px gap)
                itemRow.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-900 text-sm">${item.product_name}</p>
                            <span class="inline-block px-2 py-0.5 bg-[#f0f8f5] text-[#0e703c] rounded-md text-[10px] font-bold uppercase border border-emerald-100">${item.size_name}</span>
                        </div>
                        <span class="font-bold text-gray-900 text-sm">₱${item.subtotal.toFixed(2)}</span>
                    </div>
                    ${addOnsHtml}
                    ${discBadge}
                    <div class="flex justify-between items-center mt-2 pt-1 border-t border-gray-100/80">
                        <div class="flex items-center gap-2.5">
                            <button type="button" onclick="updateCartQty(${index}, -1)" class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-xl bg-gray-100 hover:bg-emerald-100 text-gray-800 hover:text-[#0e703c] border border-gray-200 font-black text-lg flex items-center justify-center transition active:scale-90 shadow-2xs touch-manipulation select-none" title="Decrease Quantity">-</button>
                            <span class="w-8 text-center text-sm font-black text-gray-900 select-none">${item.quantity}</span>
                            <button type="button" onclick="updateCartQty(${index}, 1)" class="w-10 h-10 min-w-[40px] min-h-[40px] rounded-xl bg-gray-100 hover:bg-emerald-100 text-gray-800 hover:text-[#0e703c] border border-gray-200 font-black text-lg flex items-center justify-center transition active:scale-90 shadow-2xs touch-manipulation select-none" title="Increase Quantity">+</button>
                        </div>
                        <button type="button" onclick="removeFromCart(${index})" class="h-10 min-h-[40px] px-3.5 bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 hover:border-rose-600 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer active:scale-95 touch-manipulation select-none" title="Remove item from order">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Remove</span>
                        </button>
                    </div>
                `;
                container.appendChild(itemRow);
            });

            // Update in-cart badges on product cards (Phase 2 #8)
            document.querySelectorAll('[id^="card-badge-"]').forEach(badge => {
                const pId = badge.id.replace('card-badge-', '');
                const q = productQuantities[pId] || 0;
                if (q > 0) {
                    badge.innerText = `×${q}`;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            });

            // Show line discounts row when cart has items (Phase 1 #4)
            if (discWrapper) discWrapper.classList.remove('hidden');

            const totals = calculateCartTotals();
            document.getElementById('cart-item-count').innerText = count;
            document.getElementById('cart-subtotal').innerText = `₱${totals.subtotal.toFixed(2)}`;
            document.getElementById('cart-discount').innerText = `-₱${totals.discount.toFixed(2)}`;
            document.getElementById('cart-taxable').innerText = `₱${totals.vatableSales.toFixed(2)}`;
            document.getElementById('cart-tax').innerText = `₱${totals.tax.toFixed(2)}`;

            const vatExemptRow = document.getElementById('cart-vat-exempt-row');
            const vatExemptEl = document.getElementById('cart-vat-exempt');
            if (totals.vatExemptSales > 0) {
                if (vatExemptRow) vatExemptRow.classList.remove('hidden');
                if (vatExemptEl) vatExemptEl.innerText = `₱${totals.vatExemptSales.toFixed(2)}`;
            } else {
                if (vatExemptRow) vatExemptRow.classList.add('hidden');
            }

            document.getElementById('cart-total').innerText = `₱${totals.totalDue.toFixed(2)}`;
            const btnTotalEl = document.getElementById('btn-total');
            if (btnTotalEl) btnTotalEl.innerText = totals.totalDue.toFixed(2);

            // Enabled 56px Tall Payment Button (Phase 5 #16)
            if (checkoutBtn) {
                checkoutBtn.disabled = false;
                checkoutBtn.className = "w-full h-14 min-h-[56px] rounded-2xl bg-[#0e703c] hover:bg-[#0b5930] text-white font-black text-base flex items-center justify-center gap-2.5 cursor-pointer shadow-md hover:shadow-lg active:scale-[0.99] transition-all select-none";
                checkoutBtn.innerHTML = `
                    <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Charge ₱${totals.totalDue.toFixed(2)}</span>
                `;
            }

            if (mobileBadge) {
                mobileBadge.innerText = count;
                mobileBadge.classList.remove('hidden');
                mobileBadge.classList.add('flex');
            }
        }

        function updateCartQty(index, delta) {
            cart[index].quantity += delta;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            } else {
                const unitPrice = cart[index].unit_price;
                const addOnTotal = cart[index].add_ons.reduce((sum, a) => sum + a.price, 0);
                cart[index].subtotal = (unitPrice + addOnTotal) * cart[index].quantity;
            }
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        // Phase 5 #15: Safe Clear Cart Confirmation Modal
        function promptClearCart() {
            if (cart.length === 0) return;
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            const msgEl = document.getElementById('clear-cart-modal-msg');
            if (msgEl) {
                msgEl.innerText = `Are you sure you want to clear ${count} ${count === 1 ? 'item' : 'items'} from this order? All items and discounts will be removed.`;
            }
            const modal = document.getElementById('clear-cart-confirm-modal');
            if (modal) modal.style.display = 'flex';
        }

        function closeClearConfirmModal() {
            const modal = document.getElementById('clear-cart-confirm-modal');
            if (modal) modal.style.display = 'none';
        }

        function confirmClearCart() {
            cart = [];
            currentDiscountType = 'none';
            currentDiscountVal = 0;
            setCartDiscount('none');
            renderCart();
            closeClearConfirmModal();
        }

        function clearCart() {
            promptClearCart();
        }

        // Payment Modal & Methods
        function openPaymentModal() {
            if (window.requireUserShift && !window.activeShift) {
                if (confirm('A shift must be started before processing orders.\n\nWould you like to start a shift now?')) {
                    openStartShiftModal();
                }
                return;
            }

            const cashierName = document.getElementById('cashier-name').value.trim();
            if (!cashierName) {
                alert('Please enter cashier name before proceeding.');
                document.getElementById('cashier-name').focus();
                return;
            }

            const totals = calculateCartTotals();
            document.getElementById('pay-modal-total').innerText = `₱${totals.totalDue.toFixed(2)}`;
            document.getElementById('pay-modal-subtotal').innerText = `₱${totals.subtotal.toFixed(2)}`;
            document.getElementById('pay-modal-discount').innerText = `-₱${totals.discount.toFixed(2)}`;
            document.getElementById('pay-modal-vatable').innerText = `₱${totals.vatableSales.toFixed(2)}`;
            document.getElementById('pay-modal-tax').innerText = `₱${totals.tax.toFixed(2)}`;

            const exemptWrap = document.getElementById('pay-modal-exempt-wrap');
            const exemptEl = document.getElementById('pay-modal-exempt');
            if (totals.vatExemptSales > 0) {
                if (exemptWrap) exemptWrap.classList.remove('hidden');
                if (exemptEl) exemptEl.innerText = `₱${totals.vatExemptSales.toFixed(2)}`;
            } else {
                if (exemptWrap) exemptWrap.classList.add('hidden');
            }

            numpadFresh = true;
            setPaymentMethod('cash');
            document.getElementById('amount-tendered').value = totals.totalDue.toFixed(2);
            calculateChange();
            document.getElementById('payment-modal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }

        let numpadFresh = true;

        function posNumpadInput(key) {
            const input = document.getElementById('amount-tendered');
            if (!input) return;

            let currentVal = (input.value || '').trim();
            if (numpadFresh) {
                currentVal = '';
                numpadFresh = false;
            }

            if (key === 'C') {
                input.value = '';
            } else if (key === 'backspace') {
                input.value = currentVal.slice(0, -1);
            } else if (key === '.') {
                if (!currentVal.includes('.')) {
                    input.value = currentVal === '' ? '0.' : currentVal + '.';
                }
            } else {
                // Digits 0-9
                if (currentVal.includes('.')) {
                    const parts = currentVal.split('.');
                    if (parts[1] && parts[1].length >= 2) {
                        return; // Disallow more than 2 decimal places
                    }
                }
                if (currentVal === '0' && key !== '.') {
                    input.value = key;
                } else {
                    input.value = currentVal + key;
                }
            }
            calculateChange();
        }

        function setPaymentMethod(method) {
            paymentMethod = method;
            document.querySelectorAll('.pm-btn').forEach(btn => {
                btn.className = 'pm-btn py-2.5 px-2 border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition touch-manipulation select-none active:scale-98';
            });
            const activeBtn = document.getElementById(`pm-${method}`);
            if (activeBtn) {
                activeBtn.className = 'pm-btn active py-2.5 px-2 border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition shadow-xs touch-manipulation select-none active:scale-98';
            }

            const totals = calculateCartTotals();
            const cashSection = document.getElementById('cash-section');
            const refSection = document.getElementById('reference-section');
            const splitSection = document.getElementById('split-section');

            if (method === 'cash') {
                cashSection.classList.remove('hidden');
                refSection.classList.add('hidden');
                splitSection.classList.add('hidden');
                numpadFresh = true;
                document.getElementById('amount-tendered').value = totals.totalDue.toFixed(2);
                calculateChange();
            } else if (method === 'online') {
                cashSection.classList.add('hidden');
                refSection.classList.remove('hidden');
                splitSection.classList.add('hidden');
                document.getElementById('amount-tendered').value = totals.totalDue.toFixed(2);
                calculateChange();
                setTimeout(() => {
                    const refInput = document.getElementById('reference-number');
                    if (refInput) refInput.focus();
                }, 50);
            } else if (method === 'split') {
                cashSection.classList.add('hidden');
                refSection.classList.add('hidden');
                splitSection.classList.remove('hidden');
                document.getElementById('split-remaining-label').innerText = `Due: ₱${totals.totalDue.toFixed(2)}`;
                // Default split half or 0
                if (!document.getElementById('split-cash-amount').value) {
                    document.getElementById('split-cash-amount').value = '';
                }
                if (!document.getElementById('split-online-amount').value) {
                    document.getElementById('split-online-amount').value = '';
                }
                calculateSplitChange();
            }
        }

        function calculateSplitChange() {
            const totals = calculateCartTotals();
            const splitCash = parseFloat(document.getElementById('split-cash-amount').value) || 0;
            const splitOnline = parseFloat(document.getElementById('split-online-amount').value) || 0;
            const totalSplit = splitCash + splitOnline;
            const diff = totalSplit - totals.totalDue;

            const changeDisplay = document.getElementById('split-change-display');
            if (diff >= 0) {
                changeDisplay.className = 'font-mono text-emerald-700 font-black text-sm';
                changeDisplay.innerText = `Change: ₱${diff.toFixed(2)}`;
            } else {
                changeDisplay.className = 'font-mono text-rose-600 font-black text-sm';
                changeDisplay.innerText = `Remaining: ₱${Math.abs(diff).toFixed(2)}`;
            }
        }

        function setQuickCash(amount) {
            numpadFresh = false;
            const totals = calculateCartTotals();
            if (amount === 'exact') {
                document.getElementById('amount-tendered').value = totals.totalDue.toFixed(2);
            } else {
                document.getElementById('amount-tendered').value = parseFloat(amount).toFixed(2);
            }
            calculateChange();
        }

        function calculateChange() {
            const totals = calculateCartTotals();
            const tendered = parseFloat(document.getElementById('amount-tendered').value) || 0;
            const change = Math.max(0, tendered - totals.totalDue);
            document.getElementById('change-display').innerText = `₱${change.toFixed(2)}`;
        }

        function toggleKioskFullscreen() {
            if (!document.fullscreenElement) {
                const docEl = document.documentElement;
                if (docEl.requestFullscreen) {
                    docEl.requestFullscreen().catch(e => console.log('Fullscreen rejected:', e));
                } else if (docEl.webkitRequestFullscreen) {
                    docEl.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(e => console.log('Exit fullscreen rejected:', e));
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            }
        }
        document.addEventListener('fullscreenchange', () => {
            const btnText = document.getElementById('fullscreen-btn-text');
            if (btnText) {
                btnText.innerText = document.fullscreenElement ? 'Exit' : 'Kiosk';
            }
        });

        // Refresh CSRF token from server (prevents stale token after long sessions)
        async function refreshCsrfToken() {
            try {
                const response = await fetch("{{ route('csrf.refresh') }}", {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                    return data.token;
                }
            } catch (e) {
                console.warn('CSRF refresh failed:', e);
            }
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // Submit Order via AJAX (with automatic CSRF token refresh)
        async function submitOrder() {
            const cashierName = document.getElementById('cashier-name').value.trim();
            const totals = calculateCartTotals();
            const referenceNumber = document.getElementById('reference-number').value.trim();
            const splitCash = parseFloat(document.getElementById('split-cash-amount').value) || 0;
            const splitOnline = parseFloat(document.getElementById('split-online-amount').value) || 0;
            const splitRef = document.getElementById('split-reference-number').value.trim();

            let amountTendered = totals.totalDue;

            if (paymentMethod === 'cash') {
                amountTendered = parseFloat(document.getElementById('amount-tendered').value) || 0;
                if (amountTendered < totals.totalDue) {
                    alert('Amount tendered is less than the total due (₱' + totals.totalDue.toFixed(2) + ').');
                    return;
                }
            } else if (paymentMethod === 'online') {
                amountTendered = totals.totalDue;
                if (!referenceNumber) {
                    alert('Verification Required: Online payments strictly require an external transaction reference / confirmation number.');
                    document.getElementById('reference-number').focus();
                    return;
                }
            } else if (paymentMethod === 'split') {
                amountTendered = splitCash + splitOnline;
                if (amountTendered < totals.totalDue) {
                    alert('Total split payment tendered (₱' + amountTendered.toFixed(2) + ') is less than the total due (₱' + totals.totalDue.toFixed(2) + ').');
                    return;
                }
                if (splitOnline > 0 && !splitRef) {
                    alert('Digital Verification Required: Please enter the reference number for the digital/online portion of this split payment.');
                    document.getElementById('split-reference-number').focus();
                    return;
                }
            }

            const branchSelect = document.getElementById('pos-branch-select');
            const branchId = branchSelect ? branchSelect.value : currentBranchId;

            let grabOrderCode = null;
            let riderCode = null;
            if (currentOrderType === 'grab_delivery') {
                const grabInput = document.getElementById('grab-order-code');
                const riderInput = document.getElementById('rider-code');
                grabOrderCode = grabInput ? grabInput.value.trim() : '';
                riderCode = riderInput ? riderInput.value.trim() : '';

                if (!grabOrderCode) {
                    alert('GrabFood Order Code is required. (e.g. GF-20260927-0012)');
                    if (grabInput) grabInput.focus();
                    return;
                }
                if (!riderCode) {
                    alert('Rider Code is required. (e.g. RDR-025)');
                    if (riderInput) riderInput.focus();
                    return;
                }
            }

            const payload = {
                cashier_name: cashierName,
                branch_id: branchId,
                order_type: currentOrderType,
                grab_order_code: grabOrderCode,
                rider_code: riderCode,
                payment_method: paymentMethod,
                discount: totals.discount,
                amount_tendered: amountTendered,
                reference_number: referenceNumber,
                split_cash_amount: splitCash,
                split_online_amount: splitOnline,
                split_reference_number: splitRef,
                items: cart.map(item => ({
                    product_id: item.product_id,
                    size_id: item.size_id,
                    quantity: item.quantity,
                    add_ons: item.add_ons.map(a => a.id),
                    discount_type: item.discount_type || 'none',
                    discount_rate: item.discount_rate || 0,
                    discount: item.discount || 0,
                    id_number: item.id_number || ''
                }))
            };

            const submitBtn = document.getElementById('submit-order-btn');
            const spinner = document.getElementById('submit-spinner');
            const submitText = document.getElementById('submit-text');

            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            submitText.innerText = 'Processing...';

            try {
                // Always refresh CSRF token before submitting (prevents stale token errors)
                const freshToken = await refreshCsrfToken();

                let response = await fetch("{{ route('pos.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': freshToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                // If still 419 (session fully expired), retry once after re-login redirect
                if (response.status === 419) {
                    alert('Your session has expired. The page will reload — please log in again.');
                    window.location.reload();
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    closePaymentModal();
                    cart = [];
                    clearAllCartDiscounts();
                    showReceiptModal(data.order);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Checkout Error: ' + err.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                submitText.innerText = 'Complete Sale';
            }
        }

        // Thermal Receipt State & Logic
        let currentReceiptOrder = null;
        let currentPaperWidth = localStorage.getItem('heim_receipt_paper_width') || '80mm';

        function setReceiptPaperWidth(width) {
            currentPaperWidth = width;
            localStorage.setItem('heim_receipt_paper_width', width);

            const btn80 = document.getElementById('btn-paper-80');
            const btn58 = document.getElementById('btn-paper-58');
            const receiptArea = document.getElementById('receipt-area');

            if (width === '58mm') {
                if (btn58) {
                    btn58.className = 'px-2 py-0.5 rounded-lg bg-[#155d49] text-white transition';
                }
                if (btn80) {
                    btn80.className = 'px-2 py-0.5 rounded-lg text-gray-500 hover:text-gray-900 transition';
                }
                if (receiptArea) {
                    receiptArea.style.maxWidth = '230px';
                    receiptArea.style.fontSize = '10px';
                }
            } else {
                if (btn80) {
                    btn80.className = 'px-2 py-0.5 rounded-lg bg-[#155d49] text-white transition';
                }
                if (btn58) {
                    btn58.className = 'px-2 py-0.5 rounded-lg text-gray-500 hover:text-gray-900 transition';
                }
                if (receiptArea) {
                    receiptArea.style.maxWidth = '310px';
                    receiptArea.style.fontSize = '11px';
                }
            }
        }

        // Receipt Modal
        function showReceiptModal(order) {
            currentReceiptOrder = order;

            // Apply saved paper width preference
            setReceiptPaperWidth(currentPaperWidth);

            document.getElementById('rec-order-no').innerText = order.order_number;
            const branchName = order.branch ? order.branch.name : (document.getElementById('pos-branch-select')?.selectedOptions[0]?.text?.replace('📍 ', '') || 'Bangkal Branch');
            const branchAddress = order.branch ? order.branch.address : 'MacArthur Hwy, Bangkal, Davao City, PH';
            const bNameEl = document.getElementById('rec-branch-name');
            const bAddrEl = document.getElementById('rec-branch-address');
            if (bNameEl) bNameEl.innerText = branchName;
            if (bAddrEl) bAddrEl.innerText = branchAddress;

            const otEl = document.getElementById('rec-order-type');
            if (otEl) {
                const ot = order.order_type || 'dine_in';
                otEl.innerText = ot === 'takeout' ? 'TAKEOUT' : (ot === 'grab_delivery' ? 'GRAB DELIVERY' : 'DINE-IN');
            }

            const recGrabRow = document.getElementById('rec-grab-code-row');
            const recGrabCode = document.getElementById('rec-grab-order-code');
            const recRiderRow = document.getElementById('rec-rider-code-row');
            const recRiderCode = document.getElementById('rec-rider-code');
            if (order.grab_order_code) {
                if (recGrabRow) recGrabRow.classList.remove('hidden');
                if (recGrabCode) recGrabCode.innerText = order.grab_order_code;
            } else {
                if (recGrabRow) recGrabRow.classList.add('hidden');
            }
            if (order.rider_code) {
                if (recRiderRow) recRiderRow.classList.remove('hidden');
                if (recRiderCode) recRiderCode.innerText = order.rider_code;
            } else {
                if (recRiderRow) recRiderRow.classList.add('hidden');
            }

            const orderDate = new Date(order.created_at);
            document.getElementById('rec-date').innerText = isNaN(orderDate.getTime())
                ? new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })
                : orderDate.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
            document.getElementById('rec-cashier').innerText = order.cashier_name;
            const subtotal = parseFloat(order.subtotal || 0);
            const discount = parseFloat(order.discount || 0);
            const vatableSales = parseFloat(order.vatable_sales !== undefined ? order.vatable_sales : Math.max(0, subtotal - discount));
            const vatExemptSales = parseFloat(order.vat_exempt_sales || 0);
            const tax = parseFloat(order.tax || 0);

            document.getElementById('rec-subtotal').innerText = `₱${subtotal.toFixed(2)}`;
            document.getElementById('rec-discount').innerText = `-₱${discount.toFixed(2)}`;
            const taxableEl = document.getElementById('rec-taxable');
            if (taxableEl) taxableEl.innerText = `₱${vatableSales.toFixed(2)}`;
            const taxEl = document.getElementById('rec-tax');
            if (taxEl) taxEl.innerText = `₱${tax.toFixed(2)}`;

            const exemptRow = document.getElementById('rec-exempt-row');
            const exemptEl = document.getElementById('rec-vat-exempt');
            if (vatExemptSales > 0) {
                if (exemptRow) exemptRow.classList.remove('hidden');
                if (exemptEl) exemptEl.innerText = `₱${vatExemptSales.toFixed(2)}`;
            } else {
                if (exemptRow) exemptRow.classList.add('hidden');
            }

            document.getElementById('rec-total').innerText = `₱${parseFloat(order.total).toFixed(2)}`;
            
            const payments = order.payments || [];
            const payment = order.payment || {};
            let pmLabel = 'CASH';
            let tenderedTotal = parseFloat(payment.amount_tendered || order.total);
            let changeTotal = parseFloat(payment.change || 0);
            let refText = payment.reference_number || '';

            if (payments.length > 1) {
                pmLabel = 'SPLIT PAYMENT';
                tenderedTotal = payments.reduce((acc, p) => acc + parseFloat(p.amount_tendered || 0), 0);
                changeTotal = payments.reduce((acc, p) => acc + parseFloat(p.change || 0), 0);
                const onlinePart = payments.find(p => p.method !== 'cash');
                if (onlinePart && onlinePart.reference_number) {
                    refText = onlinePart.reference_number;
                }
            } else {
                const isOnline = ['online', 'gcash', 'card'].includes((payment.method || '').toLowerCase());
                pmLabel = isOnline ? 'ONLINE PAYMENT' : 'CASH';
            }

            document.getElementById('rec-payment-method').innerText = pmLabel;
            document.getElementById('rec-tendered').innerText = `₱${tenderedTotal.toFixed(2)}`;
            document.getElementById('rec-change').innerText = `₱${changeTotal.toFixed(2)}`;

            const refContainer = document.getElementById('rec-ref-container');
            const refNo = document.getElementById('rec-ref-no');
            if (refText && refText.trim() !== '') {
                refNo.innerText = refText;
                refContainer.classList.remove('hidden');
            } else {
                refContainer.classList.add('hidden');
            }

            const itemsContainer = document.getElementById('rec-items-list');
            itemsContainer.innerHTML = '';
            order.items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'space-y-0.5';

                let addonsHtml = '';
                if (item.add_ons && item.add_ons.length > 0) {
                    addonsHtml = item.add_ons.map(a => `
                        <div class="flex justify-between pl-3 text-[10px] text-gray-600">
                            <span>+ ${a.add_on_name}</span>
                            <span>₱${parseFloat(a.add_on_price || 0).toFixed(2)}</span>
                        </div>
                    `).join('');
                }

                let discHtml = '';
                if (parseFloat(item.discount || 0) > 0) {
                    const dTypeLabel = item.discount_type === 'pwd_senior' 
                        ? 'Senior/PWD 20% (VAT-Exempt)' 
                        : (item.discount_type === 'employee' ? 'Staff 10%' : 'Discount');
                    discHtml = `
                        <div class="flex justify-between pl-3 text-[10px] text-gray-700 italic">
                            <span>> ${dTypeLabel}${item.id_number ? ` • ID: ${item.id_number}` : ''}</span>
                            <span>-₱${parseFloat(item.discount).toFixed(2)}</span>
                        </div>
                    `;
                }

                itemDiv.innerHTML = `
                    <div class="flex justify-between font-bold text-black">
                        <span class="truncate pr-2">${item.quantity}x ${item.product_name} (${item.size_name})</span>
                        <span class="font-mono">₱${parseFloat(item.subtotal).toFixed(2)}</span>
                    </div>
                    ${addonsHtml}
                    ${discHtml}
                `;
                itemsContainer.appendChild(itemDiv);
            });

            document.getElementById('receipt-modal').classList.remove('hidden');
        }

        // Non-destructive thermal print via hidden isolated iframe
        function printReceipt() {
            if (!currentReceiptOrder) {
                alert('No receipt to print.');
                return;
            }

            const is58 = currentPaperWidth === '58mm';
            const pageMargin = '0mm';
            const paperCssWidth = is58 ? '58mm' : '80mm';
            const bodyWidth = is58 ? '48mm' : '72mm';
            const baseFontSize = is58 ? '10px' : '12px';

            const itemsRows = currentReceiptOrder.items.map(item => {
                let addons = '';
                if (item.add_ons && item.add_ons.length > 0) {
                    addons = item.add_ons.map(a => `
                        <div style="display:flex; justify-content:space-between; padding-left:10px; font-size:0.9em; color:#222;">
                            <span>+ ${a.add_on_name}</span>
                            <span>₱${parseFloat(a.add_on_price || 0).toFixed(2)}</span>
                        </div>
                    `).join('');
                }

                let discRow = '';
                if (parseFloat(item.discount || 0) > 0) {
                    const dLabel = item.discount_type === 'pwd_senior' 
                        ? 'SC/PWD 20% (VAT-Exempt)' 
                        : (item.discount_type === 'employee' ? 'Staff 10%' : 'Disc');
                    discRow = `
                        <div style="display:flex; justify-content:space-between; padding-left:10px; font-size:0.85em; color:#333; font-style:italic;">
                            <span>> ${dLabel}${item.id_number ? ' ID:' + item.id_number : ''}</span>
                            <span>-₱${parseFloat(item.discount).toFixed(2)}</span>
                        </div>
                    `;
                }

                return `
                    <div style="margin-bottom: 3px;">
                        <div style="display:flex; justify-content:space-between; font-weight:bold;">
                            <span>${item.quantity}x ${item.product_name} (${item.size_name})</span>
                            <span>₱${parseFloat(item.subtotal).toFixed(2)}</span>
                        </div>
                        ${addons}
                        ${discRow}
                    </div>
                `;
            }).join('');

            const payments = currentReceiptOrder.payments || [];
            const payment = currentReceiptOrder.payment || {};
            let paymentName = 'CASH';
            let printTendered = parseFloat(payment.amount_tendered || currentReceiptOrder.total).toFixed(2);
            let printChange = parseFloat(payment.change || 0).toFixed(2);
            let refHtml = '';

            if (payments.length > 1) {
                paymentName = 'SPLIT PAYMENT';
                printTendered = payments.reduce((acc, p) => acc + parseFloat(p.amount_tendered || 0), 0).toFixed(2);
                printChange = payments.reduce((acc, p) => acc + parseFloat(p.change || 0), 0).toFixed(2);
                const onlinePart = payments.find(p => p.method !== 'cash');
                if (onlinePart && onlinePart.reference_number) {
                    refHtml = `
                        <div style="display:flex; justify-content:space-between; font-size:0.85em;">
                            <span>Online Ref #:</span>
                            <span style="font-weight:bold;">${onlinePart.reference_number}</span>
                        </div>
                    `;
                }
            } else {
                const isOnline = ['online', 'gcash', 'card'].includes((payment.method || '').toLowerCase());
                paymentName = isOnline ? 'ONLINE PAYMENT' : 'CASH';
                if (payment.reference_number) {
                    refHtml = `
                        <div style="display:flex; justify-content:space-between; font-size:0.85em;">
                            <span>Ref #:</span>
                            <span style="font-weight:bold;">${payment.reference_number}</span>
                        </div>
                    `;
                }
            }

            const printBranch = currentReceiptOrder.branch ? currentReceiptOrder.branch.name : (document.getElementById('pos-branch-select')?.selectedOptions[0]?.text?.replace('📍 ', '') || 'Bangkal Branch');
            const printAddress = currentReceiptOrder.branch ? currentReceiptOrder.branch.address : 'MacArthur Hwy, Bangkal, Davao City, PH';
            const ot = currentReceiptOrder.order_type || 'dine_in';
            const printOrderType = ot === 'takeout' ? 'TAKEOUT' : (ot === 'grab_delivery' ? 'GRAB DELIVERY' : 'DINE-IN');

            const dateFormatted = new Date(currentReceiptOrder.created_at).toLocaleString('en-US', {
                month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true
            });

            const vatablePrint = parseFloat(currentReceiptOrder.vatable_sales !== undefined && currentReceiptOrder.vatable_sales !== null ? currentReceiptOrder.vatable_sales : ((parseFloat(currentReceiptOrder.total || 0) - parseFloat(currentReceiptOrder.vat_exempt_sales || 0)) / 1.12));
            const vatExemptPrint = parseFloat(currentReceiptOrder.vat_exempt_sales || 0);

            const receiptHtml = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt ${currentReceiptOrder.order_number}</title>
    <style>
        @page {
            size: portrait;
            margin: 0mm;
        }
        @media print {
            html, body {
                width: ${bodyWidth} !important;
                max-width: ${bodyWidth} !important;
                margin: 0 auto !important;
                padding: 2mm 1mm 4mm 1mm !important;
                background: #fff !important;
                color: #000 !important;
                font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace !important;
                font-size: ${baseFontSize} !important;
                line-height: 1.3 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            .receipt-wrapper {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
        body {
            width: ${bodyWidth};
            max-width: ${bodyWidth};
            margin: 0 auto;
            padding: 2mm 1mm 4mm 1mm;
            background: #fff;
            color: #000;
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            font-size: ${baseFontSize};
            line-height: 1.3;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .receipt-wrapper {
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .center { text-align: center; }
        .dashed { border-top: 1px dashed #000; margin: 4px 0; }
        .double-dashed { border-top: 2px dashed #000; margin: 5px 0; }
        .row { display: flex; justify-content: space-between; }
        .bold { font-weight: bold; }
        .store-name { font-size: ${is58 ? '14px' : '16px'}; font-weight: 900; letter-spacing: 1px; }
        .total-amount { font-size: ${is58 ? '14px' : '16px'}; font-weight: 900; }
    </style>
</head>
<body>
<div class="receipt-wrapper">
    <div class="center">
        <div class="store-name">HEIM COFFEE</div>
        <div style="font-size: 0.9em; font-weight: bold;">FRESH BREWS & PASTRIES</div>
        <div style="font-size: 0.85em; font-weight: bold;">${printBranch}</div>
        <div style="font-size: 0.8em;">${printAddress}</div>
    </div>

    <div class="dashed"></div>

    <div class="row bold">
        <span>ORDER:</span>
        <span>${currentReceiptOrder.order_number}</span>
    </div>
    <div class="row bold" style="background:#eee; padding:1px 0;">
        <span>TYPE:</span>
        <span>${printOrderType}</span>
    </div>
    ${currentReceiptOrder.grab_order_code ? `
    <div class="row bold" style="color:#0e703c;">
        <span>GRAB ORDER:</span>
        <span>${currentReceiptOrder.grab_order_code}</span>
    </div>
    ` : ''}
    ${currentReceiptOrder.rider_code ? `
    <div class="row bold" style="color:#0e703c;">
        <span>RIDER CODE:</span>
        <span>${currentReceiptOrder.rider_code}</span>
    </div>
    ` : ''}
    <div class="row">
        <span>DATE:</span>
        <span>${dateFormatted}</span>
    </div>
    <div class="row">
        <span>CASHIER:</span>
        <span>${currentReceiptOrder.cashier_name}</span>
    </div>

    <div class="dashed"></div>

    <div class="row bold" style="font-size: 0.9em;">
        <span>QTY ITEM</span>
        <span>PRICE</span>
    </div>
    <div class="dashed" style="border-top-style: dotted;"></div>

    <div>
        ${itemsRows}
    </div>

    <div class="dashed"></div>

    <div class="row">
        <span>Subtotal (Gross):</span>
        <span>₱${parseFloat(currentReceiptOrder.subtotal).toFixed(2)}</span>
    </div>
    <div class="row">
        <span>Total Discounts:</span>
        <span>-₱${parseFloat(currentReceiptOrder.discount || 0).toFixed(2)}</span>
    </div>
    <div class="row">
        <span>Vatable Sales:</span>
        <span>₱${vatablePrint.toFixed(2)}</span>
    </div>
    <div class="row">
        <span>VAT (${parseFloat(currentReceiptOrder.tax_rate || 12).toFixed(0)}%):</span>
        <span>₱${parseFloat(currentReceiptOrder.tax || 0).toFixed(2)}</span>
    </div>
    ${vatExemptPrint > 0 ? `
    <div class="row">
        <span>VAT-Exempt Sales:</span>
        <span>₱${vatExemptPrint.toFixed(2)}</span>
    </div>
    ` : ''}

    <div class="double-dashed"></div>

    <div class="row total-amount">
        <span>TOTAL DUE:</span>
        <span>₱${parseFloat(currentReceiptOrder.total).toFixed(2)}</span>
    </div>

    <div class="dashed"></div>

    <div class="row">
        <span>Payment:</span>
        <span class="bold">${paymentName}</span>
    </div>
    ${refHtml}
    <div class="row">
        <span>Tendered:</span>
        <span>₱${parseFloat(payment.amount_tendered || currentReceiptOrder.total).toFixed(2)}</span>
    </div>
    <div class="row">
        <span class="bold">Change:</span>
        <span class="bold">₱${parseFloat(payment.change || 0).toFixed(2)}</span>
    </div>

    <div class="dashed"></div>

    <div class="center" style="font-size: 0.85em; margin-top: 4px;">
        <div class="bold">THANK YOU FOR CHOOSING HEIM!</div>
        <div>Please come again.</div>
        <div style="font-size: 0.9em; margin-top: 2px;">Wi-Fi: HeimGuest</div>
        <div style="font-size: 0.8em; margin-top: 4px;">*** Heim POS Thermal Slip ***</div>
    </div>
</div>
</body>
</html>
            `;

            let printFrame = document.getElementById('heim-thermal-frame');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'heim-thermal-frame';
                printFrame.style.position = 'fixed';
                printFrame.style.right = '0';
                printFrame.style.bottom = '0';
                printFrame.style.width = '0';
                printFrame.style.height = '0';
                printFrame.style.border = '0';
                document.body.appendChild(printFrame);
            }

            const doc = printFrame.contentWindow.document;
            doc.open();
            doc.write(receiptHtml);
            doc.close();

            setTimeout(() => {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            }, 300);
        }

        function startNewOrder() {
            document.getElementById('receipt-modal').classList.add('hidden');
            currentReceiptOrder = null;
            const grabInput = document.getElementById('grab-order-code');
            const riderInput = document.getElementById('rider-code');
            if (grabInput) grabInput.value = '';
            if (riderInput) riderInput.value = '';
        }

        // Live Clock Updater
        function updatePosLiveClock() {
            const clockEl = document.getElementById('pos-live-clock');
            if (clockEl) {
                const now = new Date();
                clockEl.innerText = now.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            }
        }
        setInterval(updatePosLiveClock, 1000);
        updatePosLiveClock();

        // =======================================================
        // INVENTIFY SHIFT MANAGEMENT LOGIC (VIDEO DUPLICATE)
        // =======================================================
        window.activeShift = @json($activeShift);
        window.requireUserShift = @json($requireShift);
        window.shiftMetrics = null;
        window.recentMovements = [];
        window.currentCmType = 'cash_in';
        window.currentCmTab = 'in';
        window.currentSsTab = 'sales';

        // 1. Three-dot menu toggle (Matching video 00:01)
        function togglePosMenu(e) {
            if (e) e.stopPropagation();
            const dropdown = document.getElementById('pos-menu-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        document.addEventListener('click', function(e) {
            const container = document.getElementById('pos-menu-container');
            const dropdown = document.getElementById('pos-menu-dropdown');
            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // 2. Open Cash Drawer
        async function triggerOpenDrawer() {
            const dropdown = document.getElementById('pos-menu-dropdown');
            if (dropdown) dropdown.classList.add('hidden');

            try {
                const res = await fetch("{{ route('shifts.open-drawer') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.success) {
                    showToast('🗄️ Cash Drawer opened');
                }
            } catch (err) {
                console.error(err);
                showToast('🗄️ Cash Drawer kicked');
            }
        }

        function showToast(msg) {
            let toast = document.getElementById('pos-toast-msg');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'pos-toast-msg';
                toast.className = 'fixed bottom-5 left-1/2 -translate-x-1/2 z-[100] px-4 py-2.5 bg-gray-900 text-white font-bold text-xs rounded-xl shadow-xl transition-all duration-300 pointer-events-none opacity-0 translate-y-2';
                document.body.appendChild(toast);
            }
            toast.innerText = msg;
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', 'translate-y-2');
            }, 2500);
        }

        // 3. Shift Menu click (Routes to Start Shift or View/End Shift)
        function handleShiftMenuClick() {
            const dropdown = document.getElementById('pos-menu-dropdown');
            if (dropdown) dropdown.classList.add('hidden');

            if (window.activeShift) {
                openReconcileModal();
            } else {
                openStartShiftModal();
            }
        }

        // 4. Settings Modal (Matching video 00:02-00:03)
        function openSettingsModal() {
            const dropdown = document.getElementById('pos-menu-dropdown');
            if (dropdown) dropdown.classList.add('hidden');

            const toggle = document.getElementById('setting-user-shift');
            if (toggle) {
                toggle.checked = !!window.requireUserShift;
                toggleUserShiftLabel(toggle);
            }
            const m = document.getElementById('pos-settings-modal');
            m.classList.remove('hidden');
            m.style.display = 'flex';
        }

        function closeSettingsModal() {
            const m = document.getElementById('pos-settings-modal');
            m.classList.add('hidden');
            m.style.display = 'none';
        }

        function toggleUserShiftLabel(cb) {
            const lbl = document.getElementById('setting-user-shift-text');
            if (lbl) {
                if (cb.checked) {
                    lbl.innerText = 'Enabled';
                    lbl.className = 'text-xs font-semibold text-[#155d49]';
                } else {
                    lbl.innerText = 'Disabled';
                    lbl.className = 'text-xs font-semibold text-gray-400';
                }
            }
        }

        async function saveSettings() {
            const btn = document.getElementById('btn-save-settings');
            btn.disabled = true;
            btn.innerText = 'Saving...';

            const userShift = document.getElementById('setting-user-shift').checked;
            const cashMov = document.getElementById('perm-cash-movement').checked;
            const salesSum = document.getElementById('perm-sales-summary').checked;

            try {
                const res = await fetch("{{ route('shifts.settings') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        require_user_shift: userShift,
                        shift_report_permission: {
                            cash_movement: cashMov,
                            sales_summary: salesSum
                        }
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.requireUserShift = userShift;
                    updateShiftStatusUI();
                    closeSettingsModal();
                    showToast('✓ Settings saved successfully');
                } else {
                    alert(data.message || 'Failed to save settings');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred while saving settings.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Save';
            }
        }

        // 5. Start Shift Modal (Matching video 00:04-00:05)
        function openStartShiftModal() {
            document.getElementById('start-shift-cash').value = '2000';
            const m = document.getElementById('start-shift-modal');
            m.classList.remove('hidden');
            m.style.display = 'flex';
        }

        function closeStartShiftModal() {
            const m = document.getElementById('start-shift-modal');
            m.classList.add('hidden');
            m.style.display = 'none';
        }

        function setStartingCash(val) {
            document.getElementById('start-shift-cash').value = val > 0 ? val.toFixed(2) : '';
        }

        async function submitStartShift() {
            const btn = document.getElementById('btn-submit-start-shift');
            const cashVal = parseFloat(document.getElementById('start-shift-cash').value || 0);
            const cashierName = document.getElementById('cashier-name').value.trim() || 'Admin';
            const openDrawer = document.getElementById('start-shift-drawer-checkbox').checked;

            if (isNaN(cashVal) || cashVal < 0) {
                alert('Please enter a valid starting cash amount.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span>Starting...</span>';

            try {
                const res = await fetch("{{ route('shifts.start') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        starting_cash: cashVal,
                        cashier_name: cashierName,
                        open_cash_drawer: openDrawer
                    })
                });

                const data = await res.json();
                if (data.success) {
                    window.activeShift = data.shift;
                    window.shiftMetrics = data.metrics;
                    updateShiftStatusUI();
                    closeStartShiftModal();
                    showToast(`✓ Shift started with ₱${cashVal.toLocaleString(undefined, {minimumFractionDigits: 2})}`);

                    if (openDrawer) {
                        triggerOpenDrawer();
                    }
                } else {
                    alert(data.message || 'Failed to start shift');
                }
            } catch (err) {
                console.error(err);
                alert('Error starting shift. Please try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Start Shift</span>';
            }
        }

        // 6. Active Shift / Reconciliation Modal (Matching video 00:07-00:22)
        async function openReconcileModal() {
            const m = document.getElementById('shift-reconcile-modal');
            m.classList.remove('hidden');
            m.style.display = 'flex';
            await fetchCurrentShift();
        }

        function closeReconcileModal() {
            const m = document.getElementById('shift-reconcile-modal');
            m.classList.add('hidden');
            m.style.display = 'none';
        }

        async function fetchCurrentShift() {
            try {
                const res = await fetch("{{ route('shifts.current') }}");
                const data = await res.json();
                if (data.success && data.has_active_shift) {
                    window.activeShift = data.shift;
                    window.shiftMetrics = data.metrics;
                    window.recentMovements = data.recent_movements || [];
                    renderReconcileModal();
                    updateShiftStatusUI();
                } else if (!data.has_active_shift) {
                    window.activeShift = null;
                    window.shiftMetrics = null;
                    updateShiftStatusUI();
                    closeReconcileModal();
                }
            } catch (err) {
                console.error('Error fetching shift:', err);
            }
        }

        function renderReconcileModal() {
            if (!window.activeShift || !window.shiftMetrics) return;

            const shift = window.activeShift;
            const m = window.shiftMetrics;

            document.getElementById('reconcile-opened-by').innerText = shift.opened_by || 'Admin';
            
            // Format datetime matching video: 12/14/2025, 07:59 PM
            const openDate = new Date(shift.opened_at);
            document.getElementById('reconcile-opened-at').innerText = openDate.toLocaleString('en-US', {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            document.getElementById('reconcile-starting-cash').innerText = `: ₱ ${m.starting_cash.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('reconcile-cash-in').innerText = `: ₱ ${m.cash_in.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('reconcile-cash-out').innerText = `: ₱ ${m.cash_out.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('reconcile-expected-cash').innerText = `: ₱ ${m.expected_cash.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            // Cash movements
            document.getElementById('reconcile-pos-cash-sales').innerText = `: ₱ ${m.pos_cash_sales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            // Render list
            renderCashMovementsList();

            // Sales Summary
            document.getElementById('reconcile-gross-sales').innerText = `: ₱ ${m.gross_sales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('reconcile-discounts').innerText = `: ₱ ${m.discounts.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('reconcile-net-sales').innerText = `: ₱ ${m.net_sales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('reconcile-tx-count').innerText = `: ${m.transactions_count}`;
            document.getElementById('reconcile-void-count').innerText = `: ${m.voids_count}`;

            // Breakdown
            document.getElementById('reconcile-breakdown-cash').innerText = `: ₱ ${m.pos_cash_sales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('reconcile-breakdown-online').innerText = `: ₱ ${m.online_sales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            // Set actual cash default to expected if empty
            const actualInput = document.getElementById('reconcile-actual-cash');
            if (!actualInput.value) {
                actualInput.value = m.expected_cash;
            }
            liveUpdateReconciliation();
        }

        function renderCashMovementsList() {
            const inContainer = document.getElementById('reconcile-manual-in-list');
            const outContainer = document.getElementById('reconcile-manual-out-list');
            if (!inContainer || !outContainer) return;

            const ins = (window.recentMovements || []).filter(m => m.type === 'cash_in');
            const outs = (window.recentMovements || []).filter(m => m.type === 'cash_out');

            if (ins.length > 0) {
                inContainer.innerHTML = ins.map(i => `
                    <div class="flex justify-between items-center text-gray-700 py-0.5">
                        <span class="truncate max-w-[200px] text-gray-500">• ${i.reason}</span>
                        <span class="font-bold text-gray-900">+₱${parseFloat(i.amount).toFixed(2)}</span>
                    </div>
                `).join('');
            } else {
                inContainer.innerHTML = '';
            }

            if (outs.length > 0) {
                outContainer.innerHTML = outs.map(o => `
                    <div class="flex justify-between items-center text-gray-700 py-0.5">
                        <span class="truncate max-w-[200px] text-gray-500">• ${o.reason}</span>
                        <span class="font-bold text-rose-600">-₱${parseFloat(o.amount).toFixed(2)}</span>
                    </div>
                `).join('');
            } else {
                outContainer.innerHTML = '<p class="text-gray-400 text-center py-2">No Cash Out recorded</p>';
            }
        }

        // Live calculation when typing actual cash (Matching video 00:09 - 00:14)
        function liveUpdateReconciliation() {
            if (!window.shiftMetrics) return;

            const expected = window.shiftMetrics.expected_cash;
            const actualRaw = document.getElementById('reconcile-actual-cash').value;
            const actual = actualRaw !== '' ? parseFloat(actualRaw) : 0;
            const diff = actual - expected;

            const diffEl = document.getElementById('reconcile-difference');
            const badgeEl = document.getElementById('reconcile-status-badge');
            const liveInd = document.getElementById('reconcile-live-indicator');
            const discBox = document.getElementById('reconcile-discrepancy-box');

            const diffFormatted = Math.abs(diff).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

            if (Math.abs(diff) < 0.01) {
                diffEl.innerText = `: ₱ 0.00`;
                diffEl.className = 'font-bold text-gray-900';

                badgeEl.innerText = 'Cash Balanced';
                badgeEl.className = 'px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800';

                liveInd.innerText = 'Cash Balanced';
                liveInd.className = 'text-xs font-bold px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800';

                discBox.classList.add('hidden');
            } else if (diff < 0) {
                diffEl.innerText = `: - ₱ ${diffFormatted}`;
                diffEl.className = 'font-bold text-rose-600';

                badgeEl.innerText = 'Cash Short';
                badgeEl.className = 'px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-700';

                liveInd.innerText = `Cash Short (₱${diffFormatted})`;
                liveInd.className = 'text-xs font-bold px-2.5 py-0.5 rounded-md bg-rose-100 text-rose-700';

                discBox.classList.remove('hidden');
            } else {
                diffEl.innerText = `: + ₱ ${diffFormatted}`;
                diffEl.className = 'font-bold text-emerald-700';

                badgeEl.innerText = 'Cash Over';
                badgeEl.className = 'px-2 py-0.5 rounded-md text-[11px] font-bold bg-teal-100 text-teal-800';

                liveInd.innerText = `Cash Over (+₱${diffFormatted})`;
                liveInd.className = 'text-xs font-bold px-2.5 py-0.5 rounded-md bg-teal-100 text-teal-800';

                discBox.classList.remove('hidden');
            }
        }

        // Accordion Helpers
        function toggleAccordion(id) {
            const el = document.getElementById(id);
            const caret = document.getElementById(id + '-caret');
            if (el) {
                const isHidden = el.classList.toggle('hidden');
                if (caret) {
                    caret.style.transform = isHidden ? 'rotate(-90deg)' : 'rotate(0deg)';
                }
            }
        }

        function setCashMovementTab(tab) {
            window.currentCmTab = tab;
            const btnIn = document.getElementById('tab-cm-in');
            const btnOut = document.getElementById('tab-cm-out');
            const cIn = document.getElementById('cm-tab-in-content');
            const cOut = document.getElementById('cm-tab-out-content');

            if (tab === 'in') {
                btnIn.className = 'px-3 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg shadow-xs';
                btnOut.className = 'px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200';
                cIn.classList.remove('hidden');
                cOut.classList.add('hidden');
            } else {
                btnOut.className = 'px-3 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg shadow-xs';
                btnIn.className = 'px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200';
                cOut.classList.remove('hidden');
                cIn.classList.add('hidden');
            }
        }

        function setSalesSummaryTab(tab) {
            window.currentSsTab = tab;
            const btnSales = document.getElementById('tab-ss-sales');
            const btnTx = document.getElementById('tab-ss-tx');
            const cSales = document.getElementById('ss-tab-sales-content');
            const cTx = document.getElementById('ss-tab-tx-content');

            if (tab === 'sales') {
                btnSales.className = 'px-3 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg shadow-xs';
                btnTx.className = 'px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200';
                cSales.classList.remove('hidden');
                cTx.classList.add('hidden');
            } else {
                btnTx.className = 'px-3 py-1 bg-[#155d49] text-white text-xs font-bold rounded-lg shadow-xs';
                btnSales.className = 'px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-200';
                cTx.classList.remove('hidden');
                cSales.classList.add('hidden');
            }
        }

        // 7. Manual Cash Movement (Pay In / Out)
        function openCashMovementModal() {
            selectCmType('cash_in');
            document.getElementById('cm-amount').value = '';
            document.getElementById('cm-reason').value = '';
            const m = document.getElementById('cash-movement-modal');
            m.classList.remove('hidden');
            m.style.display = 'flex';
        }

        function closeCashMovementModal() {
            const m = document.getElementById('cash-movement-modal');
            m.classList.add('hidden');
            m.style.display = 'none';
        }

        function selectCmType(type) {
            window.currentCmType = type;
            const btnIn = document.getElementById('cm-btn-in');
            const btnOut = document.getElementById('cm-btn-out');

            if (type === 'cash_in') {
                btnIn.className = 'py-2 rounded-xl text-xs font-bold border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] transition';
                btnOut.className = 'py-2 rounded-xl text-xs font-bold border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 transition';
            } else {
                btnOut.className = 'py-2 rounded-xl text-xs font-bold border-2 border-rose-600 bg-rose-50 text-rose-700 transition';
                btnIn.className = 'py-2 rounded-xl text-xs font-bold border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 transition';
            }
        }

        async function submitCashMovement() {
            const btn = document.getElementById('btn-save-cm');
            const amount = parseFloat(document.getElementById('cm-amount').value || 0);
            const reason = document.getElementById('cm-reason').value.trim();

            if (isNaN(amount) || amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }
            if (!reason) {
                alert('Please enter a reason or description.');
                return;
            }

            btn.disabled = true;
            btn.innerText = 'Saving...';

            try {
                const res = await fetch("{{ route('shifts.cash-movement') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        type: window.currentCmType,
                        amount: amount,
                        reason: reason,
                    })
                });

                const data = await res.json();
                if (data.success) {
                    window.shiftMetrics = data.metrics;
                    closeCashMovementModal();
                    await fetchCurrentShift();
                    showToast(`✓ Cash movement saved: ₱${amount.toFixed(2)}`);
                } else {
                    alert(data.message || 'Failed to save cash movement');
                }
            } catch (err) {
                console.error(err);
                alert('Error saving cash movement');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Save Movement';
            }
        }

        // 8. End Shift with Supervisor Authorization Popup
        function promptEndShiftSecurityAuth() {
            const actualInput = document.getElementById('reconcile-actual-cash');
            const actualVal = parseFloat(actualInput.value || 0);

            if (isNaN(actualVal) || actualVal < 0 || actualInput.value.trim() === '') {
                alert('Please enter a valid Actual Cash amount in the shift drawer before ending the shift.');
                actualInput.focus();
                return;
            }

            // Populate preview details in the authorization modal
            const shift = window.activeShift || {};
            const cashier = shift.opened_by || 'Cashier';
            document.getElementById('shift-auth-summary-cashier').innerText = cashier;
            document.getElementById('shift-auth-summary-actual').innerText = `₱${actualVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            const badge = document.getElementById('reconcile-status-badge');
            const authStatus = document.getElementById('shift-auth-summary-status');
            if (badge && authStatus) {
                authStatus.innerText = badge.innerText;
                authStatus.className = badge.className;
            }

            // Reset credentials & errors
            document.getElementById('shift-auth-email').value = '';
            document.getElementById('shift-auth-password').value = '';
            const errDiv = document.getElementById('shift-auth-error');
            errDiv.innerText = '';
            errDiv.classList.add('hidden');

            // Open auth popup modal
            const authModal = document.getElementById('shift-auth-modal');
            authModal.classList.remove('hidden');
            authModal.style.display = 'flex';
            setTimeout(() => {
                document.getElementById('shift-auth-email').focus();
            }, 100);
        }

        function closeShiftAuthModal() {
            const authModal = document.getElementById('shift-auth-modal');
            authModal.classList.add('hidden');
            authModal.style.display = 'none';
        }

        async function confirmEndShiftWithAuth() {
            const email = document.getElementById('shift-auth-email').value.trim();
            const password = document.getElementById('shift-auth-password').value;
            const errDiv = document.getElementById('shift-auth-error');
            const btn = document.getElementById('btn-confirm-auth-end-shift');

            if (!email || !password) {
                errDiv.innerText = 'Please enter both supervisor/manager email and password.';
                errDiv.classList.remove('hidden');
                return;
            }

            const actualVal = parseFloat(document.getElementById('reconcile-actual-cash').value || 0);
            const reason = document.getElementById('reconcile-discrepancy-reason').value.trim();

            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span>Verifying Authorization...</span>
            `;
            errDiv.classList.add('hidden');

            try {
                const res = await fetch("{{ route('shifts.end') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        actual_cash: actualVal,
                        discrepancy_reason: reason,
                        auth_email: email,
                        auth_password: password
                    })
                });

                const data = await res.json();
                if (data.success) {
                    const closedShift = data.shift;
                    const metrics = data.metrics;

                    // Trigger thermal summary receipt print
                    printShiftSummarySlip(closedShift, metrics);

                    window.activeShift = null;
                    window.shiftMetrics = null;
                    updateShiftStatusUI();
                    closeShiftAuthModal();
                    closeReconcileModal();

                    alert(`✓ Shift successfully ended!\nStatus: ${data.status_label}\nAuthorized By: ${data.authorized_by} (${data.authorizer_role})\nActual Cash: ₱${actualVal.toFixed(2)}\nDifference: ₱${parseFloat(closedShift.difference).toFixed(2)}`);
                } else {
                    errDiv.innerText = data.message || 'Authorization failed. Please try again.';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                errDiv.innerText = 'Network or server error verifying authorization.';
                errDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Authorize & End Shift</span>';
            }
        }

        // 9. Update UI Status Bar
        function updateShiftStatusUI() {
            const bar = document.getElementById('pos-shift-status-bar');
            const dot = document.getElementById('pos-shift-indicator-dot');
            const text = document.getElementById('pos-shift-status-text');
            const actLabel = document.getElementById('pos-shift-action-label');
            const cashierInput = document.getElementById('cashier-name');

            if (!bar) return;

            if (window.activeShift) {
                bar.className = 'cursor-pointer flex items-center justify-between px-3 py-2 rounded-xl border text-[11px] font-semibold transition hover:shadow-xs bg-emerald-50/90 border-emerald-200 text-emerald-800';
                if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse';
                if (text) text.innerText = 'Shift Active: ' + (window.activeShift.opened_by || 'Admin');
                if (actLabel) actLabel.innerHTML = '<span>Reconcile / End</span> <span>➔</span>';
                if (cashierInput && window.activeShift.opened_by) {
                    cashierInput.value = window.activeShift.opened_by;
                }
            } else {
                bar.className = 'cursor-pointer flex items-center justify-between px-3 py-2 rounded-xl border text-[11px] font-semibold transition hover:shadow-xs bg-amber-50/90 border-amber-200 text-amber-800';
                if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-amber-500';
                if (text) text.innerText = window.requireUserShift ? 'No Shift Active (Required)' : 'No Shift Active';
                if (actLabel) actLabel.innerHTML = '<span>Start Shift</span> <span>➔</span>';
            }
        }

        // 10. Thermal Shift Summary Slip Printing (Loyverse / Inventify Style)
        function printShiftThermalReport() {
            if (!window.activeShift || !window.shiftMetrics) {
                alert('No active shift data available to print.');
                return;
            }
            printShiftSummarySlip(window.activeShift, window.shiftMetrics);
        }

        function printShiftSummarySlip(shift, metrics) {
            const expected = metrics.expected_cash;
            const actual = parseFloat(document.getElementById('reconcile-actual-cash')?.value || shift.actual_cash || expected);
            const diff = actual - expected;
            const statusLabel = Math.abs(diff) < 0.01 ? 'CASH BALANCED' : (diff < 0 ? 'CASH SHORT' : 'CASH OVER');

            const openedAtStr = new Date(shift.opened_at).toLocaleString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            });
            const closedAtStr = shift.closed_at ? new Date(shift.closed_at).toLocaleString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true
            }) : 'Current (Active)';

            const receiptHtml = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Heim Shift Report</title>
    <style>
        @page { size: 58mm auto; margin: 0; }
        @media print { body { width: 58mm; margin: 0; padding: 4px 6px; } }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.25;
            color: #000;
            background: #fff;
            width: 58mm;
            margin: 0 auto;
            padding: 6px;
            box-sizing: border-box;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .dashed { border-top: 1px dashed #000; margin: 5px 0; }
        .double { border-top: 2px solid #000; margin: 5px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .badge { display: inline-block; padding: 2px 4px; border: 1px solid #000; font-weight: bold; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="center">
        <div class="bold" style="font-size: 1.2em;">HEIM COFFEE SHOP</div>
        <div class="bold" style="letter-spacing: 1px; margin-top: 2px;">*** SHIFT REPORT ***</div>
    </div>

    <div class="dashed"></div>

    <div class="row"><span>Cashier:</span><span class="bold">${shift.opened_by || 'Admin'}</span></div>
    <div class="row"><span>Opened:</span><span>${openedAtStr}</span></div>
    <div class="row"><span>Closed:</span><span>${closedAtStr}</span></div>

    <div class="double"></div>
    <div class="bold center" style="margin-bottom: 4px;">CASH RECONCILIATION</div>

    <div class="row"><span>Starting Cash:</span><span>₱${parseFloat(metrics.starting_cash).toFixed(2)}</span></div>
    <div class="row"><span>Cash In (POS):</span><span>₱${parseFloat(metrics.pos_cash_sales).toFixed(2)}</span></div>
    ${metrics.manual_cash_in > 0 ? `<div class="row"><span>Manual Cash In:</span><span>₱${parseFloat(metrics.manual_cash_in).toFixed(2)}</span></div>` : ''}
    <div class="row"><span>Cash Out:</span><span>₱${parseFloat(metrics.cash_out).toFixed(2)}</span></div>
    
    <div class="dashed"></div>
    <div class="row bold"><span>Expected Cash:</span><span>₱${parseFloat(expected).toFixed(2)}</span></div>
    <div class="row bold"><span>Actual Cash:</span><span>₱${parseFloat(actual).toFixed(2)}</span></div>
    <div class="row bold"><span>Difference:</span><span>${diff < 0 ? '-' : '+'}₱${Math.abs(diff).toFixed(2)}</span></div>

    <div class="center" style="margin-top: 4px;">
        <span class="badge">${statusLabel}</span>
    </div>

    <div class="dashed"></div>
    <div class="bold center" style="margin-bottom: 4px;">SALES SUMMARY</div>
    <div class="row"><span>Gross Sales:</span><span>₱${parseFloat(metrics.gross_sales).toFixed(2)}</span></div>
    <div class="row"><span>Discounts:</span><span>₱${parseFloat(metrics.discounts).toFixed(2)}</span></div>
    <div class="row bold"><span>Net Sales:</span><span>₱${parseFloat(metrics.net_sales).toFixed(2)}</span></div>
    <div class="row"><span>Completed Trans:</span><span>${metrics.transactions_count}</span></div>
    <div class="row"><span>Voids / Refunds:</span><span>${metrics.voids_count}</span></div>

    <div class="dashed"></div>
    <div class="bold center" style="margin-bottom: 4px;">PAYMENT BREAKDOWN</div>
    <div class="row"><span>Cash Sales:</span><span>₱${parseFloat(metrics.pos_cash_sales).toFixed(2)}</span></div>
    <div class="row"><span>Online Payment:</span><span>₱${parseFloat(metrics.online_sales).toFixed(2)}</span></div>

    <div class="dashed"></div>
    <div class="center" style="font-size: 0.85em; margin-top: 6px;">
        <div>*** End of Shift Report ***</div>
        <div style="margin-top: 4px;">Verified by: _________________</div>
    </div>
</body>
</html>
            `;

            let printFrame = document.getElementById('heim-thermal-frame');
            if (!printFrame) {
                printFrame = document.createElement('iframe');
                printFrame.id = 'heim-thermal-frame';
                printFrame.style.position = 'fixed';
                printFrame.style.right = '0';
                printFrame.style.bottom = '0';
                printFrame.style.width = '0';
                printFrame.style.height = '0';
                printFrame.style.border = '0';
                document.body.appendChild(printFrame);
            }

            const doc = printFrame.contentWindow.document;
            doc.open();
            doc.write(receiptHtml);
            doc.close();

            setTimeout(() => {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            }, 300);
        }

        // Initialize shift on load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.activeShift) {
                fetchCurrentShift();
            }
            updateShiftStatusUI();
        });
    </script>
    @endpush
</x-app-layout>
