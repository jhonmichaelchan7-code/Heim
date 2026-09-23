<x-app-layout>
    <div class="py-3 min-h-[calc(100vh-4.5rem)] md:h-[calc(100vh-4.5rem)] flex flex-col">
        <div class="max-w-[1650px] w-full mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col md:flex-row gap-4 md:overflow-hidden overflow-visible">
            
            <!-- Left Column: Catalog & Categories (65% width) -->
            <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden min-h-[480px] md:min-h-0">
                <!-- Search & Category Filters -->
                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between bg-[#f0f8f5]/60">
                    <div class="flex items-center gap-2 w-full sm:w-72">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#155d49]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" id="search-input" onkeyup="filterProducts()" placeholder="Search menu, coffee, pastries..." class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none transition shadow-sm font-medium" />
                        </div>

                        <!-- Mobile-only Quick Jump to Cart Button (HCI: Visibility of System Status & Fitts's Law) -->
                        <button type="button" 
                                onclick="scrollToCart()" 
                                title="View Current Order"
                                class="md:hidden relative shrink-0 p-2.5 bg-white hover:bg-emerald-50 text-[#155d49] border border-gray-200 hover:border-[#155d49] rounded-xl shadow-sm transition-all flex items-center justify-center active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span id="mobile-cart-header-badge" class="hidden absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-rose-600 text-white text-[10px] font-black rounded-full items-center justify-center shadow-sm ring-2 ring-white leading-none">
                                0
                            </span>
                        </button>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 scrollbar-none">
                        <button onclick="selectCategory('all', this)" class="category-btn active px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-[#155d49] text-white shadow-sm transition">
                            All Menu
                        </button>
                        @foreach($categories as $category)
                            <button onclick="selectCategory('{{ $category->id }}', this)" class="category-btn px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-white text-gray-700 hover:bg-emerald-50 hover:text-[#155d49] border border-gray-200 transition">
                                {{ $category->name }} ({{ $category->activeProducts->count() }})
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid (Scrollable) -->
                <div class="flex-1 p-4 overflow-y-auto">
                    <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5">
                        @foreach($categories as $category)
                            @foreach($category->activeProducts as $product)
                                @php
                                    $minPrice = $product->sizes->min('pivot.price');
                                    $maxPrice = $product->sizes->max('pivot.price');
                                    $priceDisplay = $minPrice == $maxPrice ? "₱" . number_format($minPrice, 2) : "₱" . number_format($minPrice, 0) . " - ₱" . number_format($maxPrice, 0);
                                @endphp
                                <div class="product-card bg-white border border-gray-100 hover:border-[#155d49] rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition-all duration-150 shadow-sm hover:shadow-md group relative overflow-hidden"
                                     data-category="{{ $category->id }}"
                                     data-name="{{ strtolower($product->name) }}"
                                     onclick="openProductModal({{ json_encode($product) }})">
                                    <div>
                                        <div class="w-full h-24 rounded-xl bg-gradient-to-br from-[#f0f8f5] to-[#dcf0e9] flex items-center justify-center text-3xl group-hover:scale-105 transition-transform duration-200">
                                            ☕
                                        </div>
                                        <h4 class="font-bold text-gray-900 text-sm mt-2.5 line-clamp-1 group-hover:text-[#155d49] transition">{{ $product->name }}</h4>
                                        <p class="text-xs text-gray-400 capitalize">{{ $category->name }}</p>
                                    </div>
                                    <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
                                        <span class="font-black text-[#155d49] text-sm">{{ $priceDisplay }}</span>
                                        <span class="p-1.5 bg-[#f0f8f5] text-[#155d49] rounded-lg group-hover:bg-[#155d49] group-hover:text-white transition">
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

            <!-- Right Column: Order Cart Panel (35% width) -->
            <div id="pos-cart-panel" class="w-full md:w-[420px] flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden shrink-0">
                <!-- Cart Header & Cashier Assignment -->
                <div class="p-4 border-b border-gray-100 bg-[#f0f8f5]/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full overflow-hidden bg-[#155d49] border border-white shrink-0">
                                <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                            </div>
                            <h3 class="font-bold text-gray-900 text-base">Heim Order Cart</h3>
                        </div>
                        <button onclick="clearCart()" class="text-xs text-rose-600 hover:text-rose-800 font-bold transition">Clear All</button>
                    </div>

                    <!-- Cashier Name Field (Shared login support) -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Cashier on Shift</label>
                        <input type="text" id="cashier-name" value="{{ Auth::user()->name }}" class="w-full px-3 py-1.5 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-semibold text-gray-800" placeholder="Cashier Name..." required />
                    </div>

                    <!-- Real-time Live Clock -->
                    <div class="flex items-center justify-between px-3 py-1.5 bg-white rounded-xl border border-gray-200 text-[11px] font-semibold text-gray-600 shadow-xs">
                        <span class="flex items-center gap-1.5 text-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="uppercase tracking-wider text-[10px] font-bold">Live Time</span>
                        </span>
                        <span id="pos-live-clock" class="font-mono text-gray-900 font-bold">--:--:-- --</span>
                    </div>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div id="cart-items" class="flex-1 p-4 overflow-y-auto divide-y divide-gray-100 space-y-3 max-h-[350px] md:max-h-none">
                    <div id="empty-cart-msg" class="h-full flex flex-col items-center justify-center text-gray-400 py-16">
                        <div class="w-16 h-16 rounded-full bg-[#f0f8f5] flex items-center justify-center text-2xl mb-3 text-[#155d49]">
                            🛒
                        </div>
                        <p class="text-sm font-bold text-gray-700">Order Cart is Empty</p>
                        <p class="text-xs text-gray-400 mt-1">Select items from the Heim menu</p>
                    </div>
                </div>

                <!-- Cart Footer & Checkout -->
                <div class="p-4 border-t border-gray-100 bg-[#f0f8f5]/40 space-y-3">
                    <div class="space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-500 text-xs font-medium">
                            <span>Total Drinks/Items</span>
                            <span id="cart-item-count" class="font-bold text-gray-800">0</span>
                        </div>
                        <div class="flex justify-between text-gray-500 text-xs font-medium">
                            <span>Subtotal</span>
                            <span id="cart-subtotal" class="font-bold text-gray-800">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Total Due</span>
                            <span id="cart-total" class="text-2xl font-black text-[#155d49]">₱0.00</span>
                        </div>
                    </div>

                    <button id="checkout-btn" onclick="openPaymentModal()" disabled class="w-full py-3.5 bg-[#155d49] hover:bg-[#114a3b] disabled:bg-gray-300 text-white font-bold rounded-xl shadow transition duration-150 flex items-center justify-center gap-2 cursor-pointer disabled:cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Proceed to Payment (₱<span id="btn-total">0.00</span>)
                    </button>
                </div>
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

    <!-- Payment Modal -->
    <div id="payment-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-emerald-100">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-[#f0f8f5]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-[#155d49] border border-white shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Checkout & Payment</h3>
                        <p class="text-xs text-gray-500">Select customer payment method</p>
                    </div>
                </div>
                <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-5">
                <!-- Total Amount Banner -->
                <div class="bg-[#f0f8f5] border border-emerald-200 rounded-2xl p-5 text-center">
                    <span class="text-xs font-bold text-[#155d49] uppercase tracking-wider">Total Amount Due</span>
                    <h2 id="pay-modal-total" class="text-4xl font-black text-[#155d49] mt-0.5">₱0.00</h2>
                </div>

                <!-- Payment Method Tabs -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Payment Method</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="setPaymentMethod('cash')" id="pm-cash" class="pm-btn active py-3.5 px-4 border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm">
                            <span class="text-xl">💵</span>
                            <span>Cash</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('online')" id="pm-online" class="pm-btn py-3.5 px-4 border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition">
                            <span class="text-xl">📲</span>
                            <span>Online Payment</span>
                        </button>
                    </div>
                </div>

                <!-- Cash Section -->
                <div id="cash-section" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Amount Tendered</label>
                        <input type="number" id="amount-tendered" step="0.01" oninput="calculateChange()" class="w-full px-4 py-2.5 text-xl font-black text-gray-900 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" placeholder="0.00" />
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setQuickCash('exact')" class="px-3.5 py-2 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] rounded-xl text-xs font-bold text-gray-700 transition">Exact</button>
                        <button type="button" onclick="setQuickCash(100)" class="px-3.5 py-2 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] rounded-xl text-xs font-bold text-gray-700 transition">₱100</button>
                        <button type="button" onclick="setQuickCash(200)" class="px-3.5 py-2 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] rounded-xl text-xs font-bold text-gray-700 transition">₱200</button>
                        <button type="button" onclick="setQuickCash(500)" class="px-3.5 py-2 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] rounded-xl text-xs font-bold text-gray-700 transition">₱500</button>
                        <button type="button" onclick="setQuickCash(1000)" class="px-3.5 py-2 bg-gray-100 hover:bg-emerald-50 hover:text-[#155d49] rounded-xl text-xs font-bold text-gray-700 transition">₱1,000</button>
                    </div>

                    <!-- Change Display -->
                    <div class="flex justify-between items-center p-3.5 bg-[#f0f8f5] rounded-xl border border-emerald-200">
                        <span class="text-sm font-bold text-[#155d49]">Change to Return</span>
                        <span id="change-display" class="text-2xl font-black text-[#155d49]">₱0.00</span>
                    </div>
                </div>

                <!-- Reference Number Section (For Online Payment) -->
                <div id="reference-section" class="hidden space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Reference / Transaction Number (Optional)</label>
                    <input type="text" id="reference-number" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-mono" placeholder="e.g. GCash / Maya / QRPh Ref #" />
                </div>
            </div>

            <div class="p-5 bg-[#f0f8f5] border-t border-gray-100 flex gap-3">
                <button type="button" onclick="closePaymentModal()" class="flex-1 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-xl transition">Cancel</button>
                <button type="button" id="submit-order-btn" onclick="submitOrder()" class="flex-1 py-3 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold rounded-xl shadow transition flex items-center justify-center gap-2">
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
                        <p class="text-[10px] text-gray-600">Main Branch • Manila, PH</p>
                        <p class="text-[10px] text-gray-600">Tel: (02) 8123-4567</p>
                    </div>

                    <div class="border-t border-dashed border-gray-400 my-2.5"></div>

                    <!-- Order Metadata -->
                    <div class="space-y-1 text-[11px]">
                        <div class="flex justify-between font-bold text-xs text-black">
                            <span>ORDER NO:</span>
                            <span id="rec-order-no" class="font-black text-black">-</span>
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
                            <span>Subtotal:</span>
                            <span id="rec-subtotal" class="font-semibold text-black">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Discount:</span>
                            <span id="rec-discount" class="font-semibold text-black">₱0.00</span>
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

        // Filter products by category
        function selectCategory(categoryId, btn) {
            document.querySelectorAll('.category-btn').forEach(b => {
                b.classList.remove('active', 'bg-[#155d49]', 'text-white');
                b.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-200');
            });
            btn.classList.add('active', 'bg-[#155d49]', 'text-white');
            btn.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-200');

            const cards = document.querySelectorAll('.product-card');
            cards.forEach(card => {
                if (categoryId === 'all' || card.dataset.category === categoryId) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
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

            // Add to cart array
            cart.push({
                product_id: currentModalProduct.id,
                product_name: currentModalProduct.name,
                size_id: selectedModalSize.id,
                size_name: selectedModalSize.name,
                unit_price: unitPrice,
                add_ons: selectedAddOns,
                quantity: modalQuantity,
                subtotal: itemTotal
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

        // Cart Rendering
        function renderCart() {
            const container = document.getElementById('cart-items');
            const mobileBadge = document.getElementById('mobile-cart-header-badge');

            if (cart.length === 0) {
                container.innerHTML = `
                    <div id="empty-cart-msg" class="h-full flex flex-col items-center justify-center text-gray-400 py-16">
                        <div class="w-16 h-16 rounded-full bg-[#f0f8f5] flex items-center justify-center text-2xl mb-3 text-[#155d49]">
                            🛒
                        </div>
                        <p class="text-sm font-bold text-gray-700">Order Cart is Empty</p>
                        <p class="text-xs text-gray-400 mt-1">Select items from the Heim menu</p>
                    </div>
                `;
                document.getElementById('cart-item-count').innerText = '0';
                document.getElementById('cart-subtotal').innerText = '₱0.00';
                document.getElementById('cart-total').innerText = '₱0.00';
                document.getElementById('btn-total').innerText = '0.00';
                document.getElementById('checkout-btn').disabled = true;

                if (mobileBadge) {
                    mobileBadge.innerText = '0';
                    mobileBadge.classList.add('hidden');
                    mobileBadge.classList.remove('flex');
                }
                return;
            }

            container.innerHTML = '';
            let total = 0;
            let count = 0;

            cart.forEach((item, index) => {
                total += item.subtotal;
                count += item.quantity;

                const itemRow = document.createElement('div');
                itemRow.className = 'py-2.5 flex flex-col gap-1 text-sm';
                
                let addOnsHtml = '';
                if (item.add_ons.length > 0) {
                    addOnsHtml = `<div class="text-[11px] text-[#155d49] pl-2 border-l-2 border-[#155d49]/30">
                        ${item.add_ons.map(a => `+ ${a.name} (₱${a.price.toFixed(2)})`).join('<br>')}
                    </div>`;
                }

                itemRow.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-900 text-sm">${item.product_name}</p>
                            <span class="inline-block px-2 py-0.5 bg-[#f0f8f5] text-[#155d49] rounded-md text-[10px] font-bold uppercase border border-emerald-100">${item.size_name}</span>
                        </div>
                        <span class="font-bold text-gray-900 text-sm">₱${item.subtotal.toFixed(2)}</span>
                    </div>
                    ${addOnsHtml}
                    <div class="flex justify-between items-center mt-1.5">
                        <div class="flex items-center gap-1.5">
                            <button onclick="updateCartQty(${index}, -1)" class="w-6 h-6 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-xs flex items-center justify-center">-</button>
                            <span class="w-6 text-center text-xs font-black text-gray-900">${item.quantity}</span>
                            <button onclick="updateCartQty(${index}, 1)" class="w-6 h-6 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-xs flex items-center justify-center">+</button>
                        </div>
                        <button onclick="removeFromCart(${index})" class="text-xs text-rose-500 hover:text-rose-700 font-semibold">Remove</button>
                    </div>
                `;
                container.appendChild(itemRow);
            });

            document.getElementById('cart-item-count').innerText = count;
            document.getElementById('cart-subtotal').innerText = `₱${total.toFixed(2)}`;
            document.getElementById('cart-total').innerText = `₱${total.toFixed(2)}`;
            document.getElementById('btn-total').innerText = total.toFixed(2);
            document.getElementById('checkout-btn').disabled = false;

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

        function clearCart() {
            if (cart.length === 0) return;
            if (confirm('Are you sure you want to clear the entire cart?')) {
                cart = [];
                renderCart();
            }
        }

        // Payment Modal & Methods
        function openPaymentModal() {
            const cashierName = document.getElementById('cashier-name').value.trim();
            if (!cashierName) {
                alert('Please enter cashier name before proceeding.');
                document.getElementById('cashier-name').focus();
                return;
            }

            const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
            document.getElementById('pay-modal-total').innerText = `₱${total.toFixed(2)}`;
            setPaymentMethod('cash');
            document.getElementById('amount-tendered').value = total.toFixed(2);
            calculateChange();
            document.getElementById('payment-modal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }

        function setPaymentMethod(method) {
            paymentMethod = method;
            document.querySelectorAll('.pm-btn').forEach(btn => {
                btn.className = 'pm-btn py-3.5 px-4 border-2 border-gray-200 bg-white text-gray-700 hover:border-gray-300 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition';
            });
            const activeBtn = document.getElementById(`pm-${method}`);
            if (activeBtn) {
                activeBtn.className = 'pm-btn active py-3.5 px-4 border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition shadow-sm';
            }

            if (method === 'cash') {
                document.getElementById('cash-section').classList.remove('hidden');
                document.getElementById('reference-section').classList.add('hidden');
            } else {
                // Online Payment (GCash / Maya / QRPh)
                document.getElementById('cash-section').classList.add('hidden');
                document.getElementById('reference-section').classList.remove('hidden');
                const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
                document.getElementById('amount-tendered').value = total.toFixed(2);
                calculateChange();
                setTimeout(() => {
                    const refInput = document.getElementById('reference-number');
                    if (refInput) refInput.focus();
                }, 50);
            }
        }

        function setQuickCash(amount) {
            const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
            if (amount === 'exact') {
                document.getElementById('amount-tendered').value = total.toFixed(2);
            } else {
                document.getElementById('amount-tendered').value = amount;
            }
            calculateChange();
        }

        function calculateChange() {
            const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
            const tendered = parseFloat(document.getElementById('amount-tendered').value) || 0;
            const change = Math.max(0, tendered - total);
            document.getElementById('change-display').innerText = `₱${change.toFixed(2)}`;
        }

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
            const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
            const amountTendered = paymentMethod === 'cash' ? (parseFloat(document.getElementById('amount-tendered').value) || 0) : total;
            const referenceNumber = document.getElementById('reference-number').value.trim();

            if (paymentMethod === 'cash' && amountTendered < total) {
                alert('Amount tendered is less than the total due.');
                return;
            }

            const payload = {
                cashier_name: cashierName,
                payment_method: paymentMethod,
                amount_tendered: amountTendered,
                reference_number: referenceNumber,
                items: cart.map(item => ({
                    product_id: item.product_id,
                    size_id: item.size_id,
                    quantity: item.quantity,
                    add_ons: item.add_ons.map(a => a.id)
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
                    renderCart();
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
            const orderDate = new Date(order.created_at);
            document.getElementById('rec-date').innerText = isNaN(orderDate.getTime())
                ? new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true })
                : orderDate.toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });
            document.getElementById('rec-cashier').innerText = order.cashier_name;
            document.getElementById('rec-subtotal').innerText = `₱${parseFloat(order.subtotal).toFixed(2)}`;
            const discount = parseFloat(order.discount || 0);
            document.getElementById('rec-discount').innerText = `₱${discount.toFixed(2)}`;
            document.getElementById('rec-total').innerText = `₱${parseFloat(order.total).toFixed(2)}`;
            
            const payment = order.payment || {};
            const isOnline = ['online', 'gcash'].includes((payment.method || '').toLowerCase());
            document.getElementById('rec-payment-method').innerText = isOnline ? 'ONLINE PAYMENT' : 'CASH';
            document.getElementById('rec-tendered').innerText = `₱${parseFloat(payment.amount_tendered || order.total).toFixed(2)}`;
            document.getElementById('rec-change').innerText = `₱${parseFloat(payment.change || 0).toFixed(2)}`;

            const refContainer = document.getElementById('rec-ref-container');
            const refNo = document.getElementById('rec-ref-no');
            if (payment.reference_number && payment.reference_number.trim() !== '') {
                refNo.innerText = payment.reference_number;
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

                itemDiv.innerHTML = `
                    <div class="flex justify-between font-bold text-black">
                        <span class="truncate pr-2">${item.quantity}x ${item.product_name} (${item.size_name})</span>
                        <span class="font-mono">₱${parseFloat(item.subtotal).toFixed(2)}</span>
                    </div>
                    ${addonsHtml}
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
                return `
                    <div style="margin-bottom: 3px;">
                        <div style="display:flex; justify-content:space-between; font-weight:bold;">
                            <span>${item.quantity}x ${item.product_name} (${item.size_name})</span>
                            <span>₱${parseFloat(item.subtotal).toFixed(2)}</span>
                        </div>
                        ${addons}
                    </div>
                `;
            }).join('');

            const payment = currentReceiptOrder.payment || {};
            const isOnline = ['online', 'gcash'].includes((payment.method || '').toLowerCase());
            const paymentName = isOnline ? 'ONLINE PAYMENT' : 'CASH';
            const refHtml = payment.reference_number ? `
                <div style="display:flex; justify-content:space-between;">
                    <span>Ref #:</span>
                    <span style="font-weight:bold;">${payment.reference_number}</span>
                </div>
            ` : '';

            const dateFormatted = new Date(currentReceiptOrder.created_at).toLocaleString('en-US', {
                month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true
            });

            const receiptHtml = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt ${currentReceiptOrder.order_number}</title>
    <style>
        @page {
            size: ${paperCssWidth} auto;
            margin: ${pageMargin};
        }
        @media print {
            html, body {
                width: ${bodyWidth};
                margin: 0 auto !important;
                padding: 3mm 2mm 8mm 2mm !important;
                background: #fff !important;
                color: #000 !important;
                font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace !important;
                font-size: ${baseFontSize} !important;
                line-height: 1.35 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        body {
            width: ${bodyWidth};
            margin: 0 auto;
            padding: 3mm 2mm 8mm 2mm;
            background: #fff;
            color: #000;
            font-family: 'Courier New', Courier, 'Lucida Console', Monaco, monospace;
            font-size: ${baseFontSize};
            line-height: 1.35;
        }
        .center { text-align: center; }
        .dashed { border-top: 1px dashed #000; margin: 5px 0; }
        .double-dashed { border-top: 2px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; }
        .bold { font-weight: bold; }
        .store-name { font-size: ${is58 ? '14px' : '16px'}; font-weight: 900; letter-spacing: 1px; }
        .total-amount { font-size: ${is58 ? '14px' : '16px'}; font-weight: 900; }
    </style>
</head>
<body>
    <div class="center">
        <div class="store-name">HEIM COFFEE</div>
        <div style="font-size: 0.9em; font-weight: bold;">FRESH BREWS & PASTRIES</div>
        <div style="font-size: 0.85em;">Main Branch • Manila, PH</div>
        <div style="font-size: 0.85em;">Tel: (02) 8123-4567</div>
    </div>

    <div class="dashed"></div>

    <div class="row bold">
        <span>ORDER:</span>
        <span>${currentReceiptOrder.order_number}</span>
    </div>
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
        <span>Subtotal:</span>
        <span>₱${parseFloat(currentReceiptOrder.subtotal).toFixed(2)}</span>
    </div>
    <div class="row">
        <span>Discount:</span>
        <span>₱${parseFloat(currentReceiptOrder.discount || 0).toFixed(2)}</span>
    </div>

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
    </script>
    @endpush
</x-app-layout>
