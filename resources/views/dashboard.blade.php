<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full overflow-hidden shadow-sm border-2 border-[#155d49] bg-[#155d49] shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                        Heim Management Dashboard
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Welcome back, <span class="font-bold text-[#155d49]">{{ Auth::user()->name }}</span> (<span class="uppercase font-semibold text-xs">{{ Auth::user()->role }}</span>)
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center">
                    <select name="branch_id" onchange="this.form.submit()" class="h-10 px-3 py-1.5 text-xs font-bold rounded-xl border border-gray-200 bg-white text-gray-800 shadow-xs focus:ring-2 focus:ring-[#155d49] outline-none">
                        <option value="">🏢 All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                📍 {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow transition duration-150 ease-in-out gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Launch POS Terminal
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Cashier Shift Operations Widget -->
            @if($activeShift)
                <div class="bg-gradient-to-r from-[#155d49] to-[#1a6e57] rounded-3xl p-5 sm:p-6 text-white shadow-lg shadow-emerald-950/15 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 border border-emerald-600/30">
                    <div class="space-y-1.5">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-bold text-emerald-100">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Active Terminal Shift</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight">Shift in Progress: {{ $activeShift->opened_by }}</h3>
                        <p class="text-xs text-emerald-100/90 font-medium">
                            Started {{ $activeShift->opened_at->format('M d, Y • h:i A') }} • Initial Starting Cash: <span class="font-bold text-white">₱{{ number_format($activeShift->starting_cash, 2) }}</span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:gap-6 bg-white/10 backdrop-blur-md px-5 py-3.5 rounded-2xl border border-white/15 w-full lg:w-auto justify-between sm:justify-start">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-200">Cash In (POS)</p>
                            <p class="text-base sm:text-lg font-black text-white">₱{{ number_format($activeShiftMetrics['pos_cash_sales'] ?? 0, 2) }}</p>
                        </div>
                        <div class="hidden sm:block w-px h-8 bg-white/20"></div>
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-200">Expected Drawer</p>
                            <p class="text-base sm:text-lg font-black text-emerald-300">₱{{ number_format($activeShiftMetrics['expected_cash'] ?? $activeShift->starting_cash, 2) }}</p>
                        </div>
                        <div class="hidden sm:block w-px h-8 bg-white/20"></div>
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-200">Transactions</p>
                            <p class="text-base sm:text-lg font-black text-white">{{ $activeShiftMetrics['transactions_count'] ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 w-full lg:w-auto">
                        <a href="{{ route('pos.index') }}" class="flex-1 lg:flex-none px-6 py-3 bg-white text-[#155d49] hover:bg-emerald-50 font-bold text-xs rounded-xl shadow transition text-center flex items-center justify-center gap-1.5">
                            <span>Open POS & Reconcile</span>
                            <span>➔</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-amber-50/90 border border-amber-200/90 rounded-3xl p-5 sm:p-6 text-amber-900 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 border border-amber-200 text-amber-800 flex items-center justify-center text-2xl shrink-0">
                            ⏱️
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Cashier Notice</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-gray-900 mt-0.5">No Active Shift Opened</h3>
                            <p class="text-xs text-gray-600 mt-0.5">Start your morning or afternoon shift float (e.g. ₱2,000) on the POS before taking orders.</p>
                        </div>
                    </div>
                    <a href="{{ route('pos.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow transition gap-2 shrink-0">
                        <span>Launch POS to Start Shift</span>
                        <span>➔</span>
                    </a>
                </div>
            @endif

            <!-- KPI Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Today's Revenue -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Today's Net Sales</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-1">₱{{ number_format($todayRevenue, 2) }}</h3>
                        <p class="text-xs text-[#155d49] font-semibold mt-1 flex items-center gap-1">
                            <span>Completed today</span>
                        </p>
                    </div>
                    <div class="p-3.5 bg-[#f0f8f5] text-[#155d49] rounded-2xl border border-emerald-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Today's Orders -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Orders Served</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-1">{{ $todayOrderCount }}</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">
                            {{ $todayItemsSold }} drinks / items
                        </p>
                    </div>
                    <div class="p-3.5 bg-blue-50 text-blue-600 rounded-2xl border border-blue-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                </div>

                <!-- 7-Day Revenue -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">7-Day Rolling Sales</p>
                        <h3 class="text-2xl font-black text-gray-900 mt-1">₱{{ number_format($weeklyRevenue, 2) }}</h3>
                        <p class="text-xs text-emerald-600 font-semibold mt-1">
                            Weekly trend
                        </p>
                    </div>
                    <div class="p-3.5 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>

                <!-- Low Stock Alert Counter -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Stock Alerts</p>
                        <h3 class="text-2xl font-black {{ $lowStockIngredients->count() > 0 ? 'text-rose-600' : 'text-gray-900' }} mt-1">
                            {{ $lowStockIngredients->count() }}
                        </h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">
                            {{ $lowStockIngredients->count() > 0 ? 'Needs replenishment' : 'All stocks healthy' }}
                        </p>
                    </div>
                    <div class="p-3.5 {{ $lowStockIngredients->count() > 0 ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-gray-50 text-gray-400' }} rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Order Channels & Tender Breakdown Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2 py-0.5 rounded">🍽️ Dine-In Today</span>
                    <h4 class="text-xl font-extrabold text-gray-900 mt-1">₱{{ number_format($orderTypesBreakdown['dine_in']['total'] ?? 0, 2) }}</h4>
                    <p class="text-xs text-gray-500 font-medium">{{ $orderTypesBreakdown['dine_in']['count'] ?? 0 }} orders</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded">🛍️ Takeout Today</span>
                    <h4 class="text-xl font-extrabold text-gray-900 mt-1">₱{{ number_format($orderTypesBreakdown['takeout']['total'] ?? 0, 2) }}</h4>
                    <p class="text-xs text-gray-500 font-medium">{{ $orderTypesBreakdown['takeout']['count'] ?? 0 }} orders</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">🛵 Grab Delivery</span>
                    <h4 class="text-xl font-extrabold text-gray-900 mt-1">₱{{ number_format($orderTypesBreakdown['grab_delivery']['total'] ?? 0, 2) }}</h4>
                    <p class="text-xs text-gray-500 font-medium">{{ $orderTypesBreakdown['grab_delivery']['count'] ?? 0 }} orders</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 bg-purple-50 px-2 py-0.5 rounded">💳 Tender Settlement</span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-bold text-emerald-700">Cash: ₱{{ number_format($cashTotal ?? 0, 2) }}</span>
                        <span class="text-gray-300">|</span>
                        <span class="text-xs font-bold text-sky-700">Online: ₱{{ number_format($onlineTotal ?? 0, 2) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">Reconciled register totals</p>
                </div>
            </div>

            <!-- Notifications Card (if Manager+) -->
            @if(auth()->user()->isAtLeast('manager') && count($notifications) > 0)
                <div class="bg-[#f0f8f5] border border-emerald-200 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-[#155d49] text-white rounded-lg">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <h4 class="font-bold text-[#155d49]">System Inventory Notifications & Alerts</h4>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-[#155d49] hover:underline">View all</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($notifications as $notif)
                            <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-emerald-100 text-sm shadow-sm">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $notif->type === 'out_of_stock' ? 'bg-rose-500' : 'bg-amber-500' }}"></span>
                                    <span class="font-semibold text-gray-800">{{ $notif->message }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Orders (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ tab: '{{ $activeShift ? 'current' : 'all' }}' }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-gray-900 text-lg">
                                    {{ !auth()->user()->isAtLeast('supervisor') ? 'Current Shift Orders' : 'Recent Orders' }}
                                </h3>
                                @if(!auth()->user()->isAtLeast('supervisor'))
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-[#155d49]">Active Shift</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if(!auth()->user()->isAtLeast('supervisor'))
                                    Showing orders from your current active shift • Matches your POS cash drawer
                                @else
                                    <span x-show="tab === 'current'">Showing orders from current active shift (matches Cash in POS)</span>
                                    <span x-show="tab === 'all'" style="display: none;">Showing all staff orders recorded today</span>
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            {{-- Toggle Tabs visible ONLY for Manager, Supervisor, and Owner --}}
                            @if(auth()->user()->isAtLeast('supervisor') && $activeShift)
                                <div class="inline-flex p-1 bg-gray-100 rounded-xl">
                                    <button type="button" 
                                        @click="tab = 'current'" 
                                        :class="tab === 'current' ? 'bg-white text-[#155d49] font-bold shadow-xs' : 'text-gray-500 hover:text-gray-900 font-medium'"
                                        class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5 cursor-pointer">
                                        <span>Current Shift</span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                            :class="tab === 'current' ? 'bg-emerald-100 text-[#155d49]' : 'bg-gray-200 text-gray-600'">
                                            {{ $recentOrders->where('shift_id', $activeShift->id)->count() }}
                                        </span>
                                    </button>
                                    <button type="button" 
                                        @click="tab = 'all'" 
                                        :class="tab === 'all' ? 'bg-white text-[#155d49] font-bold shadow-xs' : 'text-gray-500 hover:text-gray-900 font-medium'"
                                        class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5 cursor-pointer">
                                        <span>All Today</span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold"
                                            :class="tab === 'all' ? 'bg-emerald-100 text-[#155d49]' : 'bg-gray-200 text-gray-600'">
                                            {{ $recentOrders->count() }}
                                        </span>
                                    </button>
                                </div>
                            @endif
                            <a href="{{ route('orders.index') }}" class="text-xs font-bold text-[#155d49] hover:underline whitespace-nowrap">View all orders &rarr;</a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg">Order #</th>
                                    <th class="py-3 px-4">Cashier</th>
                                    <th class="py-3 px-4">Branch & Channel</th>
                                    <th class="py-3 px-4">Items</th>
                                    <th class="py-3 px-4">Total</th>
                                    <th class="py-3 px-4">Payment</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 rounded-r-lg text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentOrders as $order)
                                    @php
                                        $isCurrent = $activeShift && $order->shift_id === $activeShift->id;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition" 
                                        @if(auth()->user()->isAtLeast('supervisor'))
                                            x-show="tab === 'all' || {{ $isCurrent ? 'true' : 'false' }}"
                                            @if(!$isCurrent) style="display: none;" @endif
                                        @endif
                                    >
                                        <td class="py-3 px-4 font-mono font-bold text-gray-900">
                                            <div class="flex items-center gap-1.5">
                                                <span>{{ $order->order_number }}</span>
                                                @if(auth()->user()->isAtLeast('supervisor') && $activeShift && !$isCurrent)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-gray-100 text-gray-500 border border-gray-200">Prev Shift</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600 font-medium">{{ $order->cashier_name }}</td>
                                        <td class="py-3 px-4">
                                            <div class="flex flex-col gap-0.5">
                                                <span class="text-xs font-semibold text-gray-800">{{ $order->branch?->name ?? 'Main' }}</span>
                                                <span class="text-[10px] font-bold text-gray-500">{{ $order->order_type_label }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600">{{ $order->items->sum('quantity') }}</td>
                                        <td class="py-3 px-4 font-extrabold text-gray-900">₱{{ number_format($order->total, 2) }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 text-xs rounded-full font-bold
                                                {{ $order->payment?->method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                                {{ in_array($order->payment?->method, ['online', 'gcash']) ? 'bg-sky-100 text-sky-800' : '' }}
                                                {{ $order->payment?->method === 'card' ? 'bg-purple-100 text-purple-800' : '' }}
                                            ">
                                                {{ in_array($order->payment?->method, ['online', 'gcash']) ? 'Online' : ($order->payment?->method ? ucfirst($order->payment->method) : 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 text-xs rounded-full font-bold
                                                {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ $order->status === 'refunded' ? 'bg-rose-100 text-rose-800' : '' }}
                                            ">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('orders.show', $order) }}" class="text-[#155d49] hover:underline font-bold text-xs">Details</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-gray-400">
                                            {{ !auth()->user()->isAtLeast('supervisor') ? 'No orders recorded in your current shift yet.' : 'No orders recorded yet today.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Sub-total reconciliation helper -->
                    @if($activeShift)
                        <div class="mt-4 pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-gray-500">
                            <div>
                                @if(!auth()->user()->isAtLeast('supervisor'))
                                    <span>Current Shift Orders Total: </span>
                                    <span class="font-extrabold text-[#155d49] text-sm">₱{{ number_format($recentOrders->sum('total'), 2) }}</span>
                                    <span class="text-gray-400">({{ $recentOrders->count() }} orders)</span>
                                @else
                                    <div x-show="tab === 'current'">
                                        <span>Current Shift Orders Total: </span>
                                        <span class="font-extrabold text-[#155d49] text-sm">₱{{ number_format($recentOrders->where('shift_id', $activeShift->id)->sum('total'), 2) }}</span>
                                        <span class="text-gray-400">({{ $recentOrders->where('shift_id', $activeShift->id)->count() }} orders)</span>
                                    </div>
                                    <div x-show="tab === 'all'" style="display: none;">
                                        <span>All Recorded Orders Today: </span>
                                        <span class="font-extrabold text-gray-900 text-sm">₱{{ number_format($recentOrders->sum('total'), 2) }}</span>
                                        <span class="text-gray-400">({{ $recentOrders->count() }} orders)</span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-400">
                                <span class="inline-flex items-center gap-1 text-emerald-700 font-medium">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Matches POS Cash in Drawer
                                </span>
                            </div>
                        </div>
                    @endif

                    <!-- Recent Shift History Card (Visible only to Manager and Owner) -->
                    @if(in_array(auth()->user()->role, ['manager', 'owner']) || auth()->user()->isManager())
                    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg">Recent Shift Records</h3>
                                <p class="text-xs text-gray-500">Summary of cashier float settlements and drawer balances</p>
                            </div>
                            <span class="text-xs font-bold text-gray-400">Showing last 5 shifts</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                    <tr>
                                        <th class="py-3 px-4 rounded-l-lg">Cashier</th>
                                        <th class="py-3 px-4">Opened</th>
                                        <th class="py-3 px-4">Starting</th>
                                        <th class="py-3 px-4">Actual Cash</th>
                                        <th class="py-3 px-4">Difference</th>
                                        <th class="py-3 px-4 rounded-r-lg">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($recentShifts as $s)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 font-semibold text-gray-800">{{ $s->opened_by }}</td>
                                            <td class="py-3 px-4 text-xs text-gray-500">{{ $s->opened_at->format('M d, h:i A') }}</td>
                                            <td class="py-3 px-4 font-medium text-gray-800">₱{{ number_format($s->starting_cash, 2) }}</td>
                                            <td class="py-3 px-4 font-bold text-gray-900">{{ $s->actual_cash !== null ? '₱' . number_format($s->actual_cash, 2) : '—' }}</td>
                                            <td class="py-3 px-4 font-bold {{ $s->difference < 0 ? 'text-rose-600' : ($s->difference > 0 ? 'text-emerald-700' : 'text-gray-900') }}">
                                                {{ $s->difference !== null ? ($s->difference < 0 ? '-' : ($s->difference > 0 ? '+' : '')) . '₱' . number_format(abs($s->difference), 2) : '—' }}
                                            </td>
                                            <td class="py-3 px-4">
                                                @if($s->status === 'open')
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 animate-pulse">Open</span>
                                                @elseif($s->status === 'balanced')
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Balanced</span>
                                                @elseif($s->status === 'short')
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Cash Short</span>
                                                @elseif($s->status === 'over')
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-teal-100 text-teal-800">Cash Over</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ ucfirst($s->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-6 text-center text-gray-400">No shift records recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column: Stock Alerts & Best Sellers -->
                <div class="space-y-6">
                    <!-- Low Stock Alert Widget -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-900 text-base">Ingredient Alerts</h3>
                            @if(auth()->user()->isAtLeast('supervisor'))
                                <a href="{{ route('inventory.index') }}" class="text-xs font-bold text-[#155d49] hover:underline">Manage</a>
                            @endif
                        </div>

                        <div class="space-y-2.5">
                            @forelse($lowStockIngredients as $ing)
                                <div class="flex items-center justify-between p-3 rounded-xl border {{ $ing->current_stock <= 0 ? 'bg-rose-50 border-rose-200' : 'bg-amber-50 border-amber-200' }}">
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">{{ $ing->name }}</p>
                                        <p class="text-xs text-gray-500">Min: {{ $ing->minimum_stock }} {{ $ing->unit }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-black {{ $ing->current_stock <= 0 ? 'text-rose-600' : 'text-amber-800' }}">
                                            {{ $ing->current_stock }} {{ $ing->unit }}
                                        </span>
                                        <span class="block text-[10px] uppercase font-bold {{ $ing->current_stock <= 0 ? 'text-rose-700' : 'text-amber-800' }}">
                                            {{ $ing->current_stock <= 0 ? 'Out of Stock' : 'Low Stock' }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-gray-400 bg-gray-50 rounded-xl text-sm">
                                    <svg class="w-8 h-8 text-emerald-600 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    All ingredient stocks in good standing!
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Best Sellers Today -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 text-base mb-4">Top Drinks Today</h3>
                        <div class="space-y-2.5">
                            @forelse($bestSellers as $item)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full bg-[#155d49] text-white text-xs font-bold flex items-center justify-center shadow-sm">
                                            {{ $loop->iteration }}
                                        </span>
                                        <span class="text-sm font-bold text-gray-800">{{ $item->product_name }}</span>
                                    </div>
                                    <span class="text-sm font-extrabold text-[#155d49]">{{ $item->total_qty }} sold</span>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 text-center py-4">No product sales yet today.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
