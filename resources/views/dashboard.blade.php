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
            <div class="flex items-center gap-3">
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
        <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

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
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900 text-lg">Recent Orders</h3>
                        <a href="{{ route('orders.index') }}" class="text-xs font-bold text-[#155d49] hover:underline">View all orders &rarr;</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg">Order #</th>
                                    <th class="py-3 px-4">Cashier</th>
                                    <th class="py-3 px-4">Items</th>
                                    <th class="py-3 px-4">Total</th>
                                    <th class="py-3 px-4">Payment</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 rounded-r-lg text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentOrders as $order)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4 font-mono font-bold text-gray-900">{{ $order->order_number }}</td>
                                        <td class="py-3 px-4 text-gray-600 font-medium">{{ $order->cashier_name }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ $order->items->sum('quantity') }}</td>
                                        <td class="py-3 px-4 font-extrabold text-gray-900">₱{{ number_format($order->total, 2) }}</td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 text-xs rounded-full uppercase font-bold
                                                {{ $order->payment?->method === 'cash' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                                {{ $order->payment?->method === 'gcash' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $order->payment?->method === 'card' ? 'bg-purple-100 text-purple-800' : '' }}
                                            ">
                                                {{ $order->payment?->method ?? 'N/A' }}
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
                                        <td colspan="7" class="py-6 text-center text-gray-400">No orders recorded yet today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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
