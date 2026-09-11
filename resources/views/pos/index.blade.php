<x-app-layout>
    <div class="py-3 h-[calc(100vh-4.5rem)] flex flex-col">
        <div class="max-w-[1650px] w-full mx-auto px-4 sm:px-6 lg:px-8 flex-1 flex flex-col md:flex-row gap-4 overflow-hidden">
            
            <!-- Left Column: Catalog & Categories (65% width) -->
            <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Search & Category Filters -->
                <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between bg-[#f0f8f5]/60">
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#155d49]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" id="search-input" onkeyup="filterProducts()" placeholder="Search menu, coffee, pastries..." class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none transition shadow-sm font-medium" />
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
            <div class="w-full md:w-[420px] flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div id="cart-items" class="flex-1 p-4 overflow-y-auto divide-y divide-gray-100 space-y-3">
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
                    <div class="grid grid-cols-3 gap-2.5">
                        <button type="button" onclick="setPaymentMethod('cash')" id="pm-cash" class="pm-btn active py-3 px-3 border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] rounded-xl font-bold text-sm flex flex-col items-center gap-1 transition">
                            <span class="text-base">💵</span>
                            <span>Cash</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('gcash')" id="pm-gcash" class="pm-btn py-3 px-3 border-2 border-gray-200 bg-white text-gray-700 rounded-xl font-bold text-sm flex flex-col items-center gap-1 transition">
                            <span class="text-base">📱</span>
                            <span>GCash</span>
                        </button>
                        <button type="button" onclick="setPaymentMethod('card')" id="pm-card" class="pm-btn py-3 px-3 border-2 border-gray-200 bg-white text-gray-700 rounded-xl font-bold text-sm flex flex-col items-center gap-1 transition">
                            <span class="text-base">💳</span>
                            <span>Card</span>
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

                <!-- Reference Number Section (For GCash/Card) -->
                <div id="reference-section" class="hidden space-y-2">
                    <label class="block text-xs font-bold text-gray-700">Reference / Approval Code</label>
                    <input type="text" id="reference-number" class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-mono" placeholder="e.g. GCash Ref # or Card Auth Code" />
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

    <!-- Receipt / Order Complete Modal -->
    <div id="receipt-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl animate-fade-in flex flex-col max-h-[90vh] border border-emerald-100">
            <div class="p-4 bg-[#f0f8f5] border-b border-emerald-100 flex items-center gap-2 text-[#155d49]">
                <svg class="w-6 h-6 text-[#155d49]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <h3 class="font-extrabold text-base">Payment Completed!</h3>
            </div>

            <!-- Receipt Content (Printable) -->
            <div id="receipt-area" class="p-6 overflow-y-auto font-mono text-xs text-gray-800 space-y-4 bg-white">
                <div class="text-center space-y-1.5 flex flex-col items-center">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-[#155d49] border-2 border-gray-200 shadow-sm">
                        <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                    </div>
                    <h2 class="text-base font-black tracking-wider">HEIM COFFEE</h2>
                    <p class="text-[11px] text-gray-500">Fresh Brews & Pastries</p>
                    <p class="text-[10px] text-gray-400">Tel: (02) 8123-4567</p>
                </div>
                
                <div class="border-t border-b border-dashed border-gray-300 py-2 space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span>Order #:</span>
                        <span id="rec-order-no" class="font-bold">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Date:</span>
                        <span id="rec-date">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Cashier:</span>
                        <span id="rec-cashier">-</span>
                    </div>
                </div>

                <div id="rec-items-list" class="space-y-1.5 py-1">
                    <!-- Populated dynamically -->
                </div>

                <div class="border-t border-dashed border-gray-300 pt-2 space-y-1 text-[11px]">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span id="rec-subtotal">₱0.00</span>
                    </div>
                    <div class="flex justify-between font-bold text-sm text-black">
                        <span>TOTAL:</span>
                        <span id="rec-total">₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span id="rec-pm-label">Payment:</span>
                        <span id="rec-tendered">₱0.00</span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>Change:</span>
                        <span id="rec-change">₱0.00</span>
                    </div>
                </div>

                <div class="text-center pt-3 border-t border-dashed border-gray-300 space-y-0.5 text-[10px] text-gray-500">
                    <p>Thank you for choosing Heim!</p>
                    <p>Please come again.</p>
                </div>
            </div>

            <!-- Receipt Actions -->
            <div class="p-4 bg-[#f0f8f5] border-t border-gray-100 flex gap-2">
                <button onclick="printReceipt()" class="flex-1 py-3 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Receipt
                </button>
                <button onclick="startNewOrder()" class="flex-1 py-3 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold rounded-xl text-xs shadow">
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

        // Cart Rendering
        function renderCart() {
            const container = document.getElementById('cart-items');
            const emptyMsg = document.getElementById('empty-cart-msg');

            if (cart.length === 0) {
                container.innerHTML = '';
                container.appendChild(emptyMsg);
                document.getElementById('cart-item-count').innerText = '0';
                document.getElementById('cart-subtotal').innerText = '₱0.00';
                document.getElementById('cart-total').innerText = '₱0.00';
                document.getElementById('btn-total').innerText = '0.00';
                document.getElementById('checkout-btn').disabled = true;
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
                btn.className = 'pm-btn py-3 px-3 border-2 border-gray-200 bg-white text-gray-700 rounded-xl font-bold text-sm flex flex-col items-center gap-1 transition';
            });
            const activeBtn = document.getElementById(`pm-${method}`);
            activeBtn.className = 'pm-btn py-3 px-3 border-2 border-[#155d49] bg-[#f0f8f5] text-[#155d49] rounded-xl font-bold text-sm flex flex-col items-center gap-1 transition';

            if (method === 'cash') {
                document.getElementById('cash-section').classList.remove('hidden');
                document.getElementById('reference-section').classList.add('hidden');
            } else {
                document.getElementById('cash-section').classList.add('hidden');
                document.getElementById('reference-section').classList.remove('hidden');
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

        // Submit Order via AJAX
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
                const response = await fetch("{{ route('pos.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.success) {
                    closePaymentModal();
                    showReceiptModal(data.order);
                    cart = [];
                    renderCart();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Failed to connect to server: ' + err.message);
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                submitText.innerText = 'Complete Sale';
            }
        }

        // Receipt Modal
        function showReceiptModal(order) {
            document.getElementById('rec-order-no').innerText = order.order_number;
            document.getElementById('rec-date').innerText = new Date(order.created_at).toLocaleString();
            document.getElementById('rec-cashier').innerText = order.cashier_name;
            document.getElementById('rec-subtotal').innerText = `₱${parseFloat(order.subtotal).toFixed(2)}`;
            document.getElementById('rec-total').innerText = `₱${parseFloat(order.total).toFixed(2)}`;
            
            const payment = order.payment;
            document.getElementById('rec-pm-label').innerText = `Payment (${payment.method.toUpperCase()}):`;
            document.getElementById('rec-tendered').innerText = `₱${parseFloat(payment.amount_tendered).toFixed(2)}`;
            document.getElementById('rec-change').innerText = `₱${parseFloat(payment.change).toFixed(2)}`;

            const itemsContainer = document.getElementById('rec-items-list');
            itemsContainer.innerHTML = '';
            order.items.forEach(item => {
                const div = document.createElement('div');
                div.className = 'space-y-0.5';
                
                let addons = '';
                if (item.add_ons && item.add_ons.length > 0) {
                    addons = `<div class="text-[9px] text-[#155d49] pl-2">
                        ${item.add_ons.map(a => `+ ${a.add_on_name}`).join(', ')}
                    </div>`;
                }

                div.innerHTML = `
                    <div class="flex justify-between">
                        <span>${item.quantity}x ${item.product_name} (${item.size_name})</span>
                        <span>₱${parseFloat(item.subtotal).toFixed(2)}</span>
                    </div>
                    ${addons}
                `;
                itemsContainer.appendChild(div);
            });

            document.getElementById('receipt-modal').classList.remove('hidden');
        }

        function printReceipt() {
            const printContent = document.getElementById('receipt-area').innerHTML;
            const originalContent = document.body.innerHTML;
            document.body.innerHTML = `<div style="width: 300px; margin: auto; padding: 20px; font-family: monospace;">${printContent}</div>`;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }

        function startNewOrder() {
            document.getElementById('receipt-modal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
