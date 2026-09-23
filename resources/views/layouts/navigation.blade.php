<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center min-w-0">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full overflow-hidden shadow-sm border border-emerald-700/20 bg-[#155d49] flex items-center justify-center transition-transform group-hover:scale-105 shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-base sm:text-lg text-gray-900 tracking-tight leading-none">Heim</span>
                            <span class="text-[9px] sm:text-[10px] uppercase tracking-wider text-[#155d49] font-bold">POS & Inventory</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (HCI: Law of Proximity, Miller's Law chunking, Adaptive spacing) -->
                <div class="hidden sm:flex items-center gap-1 md:gap-1.5 lg:gap-2 sm:ms-4 lg:ms-6 xl:ms-8 overflow-x-auto scrollbar-none py-1">
                    <!-- Core Operations -->
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('dashboard') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        Dashboard
                    </a>

                    <a href="{{ route('pos.index') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('pos.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        POS
                    </a>

                    <a href="{{ route('orders.index') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('orders.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        Orders
                    </a>

                    <!-- Catalog & Inventory Management -->
                    @if(auth()->user()->isAtLeast('supervisor'))
                        <span class="hidden xl:inline-block w-px h-4 bg-gray-200 mx-0.5 shrink-0" aria-hidden="true"></span>

                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Products
                        </a>

                        <a href="{{ route('recipes.index') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('recipes.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Recipes
                        </a>

                        <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('inventory.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Inventory
                        </a>
                    @endif

                    <!-- Reports & Governance -->
                    @if(auth()->user()->isAtLeast('manager'))
                        <span class="hidden xl:inline-block w-px h-4 bg-gray-200 mx-0.5 shrink-0" aria-hidden="true"></span>

                        <a href="{{ route('consumption.index') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('consumption.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Consumption
                        </a>

                        <a href="{{ route('reports.sales') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('reports.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Reports
                        </a>

                        <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs lg:text-sm font-semibold whitespace-nowrap transition {{ request()->routeIs('audit-logs.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Audit Logs
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right section: Notifications & User Profile -->
            <div class="hidden sm:flex sm:items-center sm:ms-3 lg:ms-6 gap-2 lg:gap-3 shrink-0">
                <!-- Live Real-Time Clock Badge -->
                <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 bg-[#f0f8f5] text-[#155d49] border border-emerald-100 rounded-xl text-xs font-semibold shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="nav-live-clock" class="font-mono font-bold">--:--:-- --</span>
                </div>

                <!-- Notifications Bell -->
                @if(auth()->user()->isAtLeast('manager'))
                    @php
                        $unreadCount = \App\Models\SystemNotification::unread()
                            ->forRole(auth()->user()->role)
                            ->count();
                    @endphp
                    <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-500 hover:text-[#155d49] hover:bg-[#f0f8f5] rounded-xl transition">
                        <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] rounded-full h-4.5 min-w-[18px] px-1 flex items-center justify-center font-bold shadow-sm animate-pulse">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                @endif

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-2.5 py-1.5 border border-gray-200 text-xs sm:text-sm leading-4 font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] sm:text-xs font-bold uppercase
                                    {{ auth()->user()->role === 'owner' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                    {{ auth()->user()->role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ auth()->user()->role === 'supervisor' ? 'bg-teal-100 text-teal-800' : '' }}
                                    {{ auth()->user()->role === 'cashier' ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst(auth()->user()->role) }}
                                </span>
                                <span class="font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                            </div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if(auth()->user()->isOwner())
                            <x-dropdown-link :href="route('users.index')">
                                User Management
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-400 hover:text-[#155d49] hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile Drawer) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-100 bg-white shadow-lg">
        <div class="pt-2 pb-3 px-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pos.index')" :active="request()->routeIs('pos.*')">POS</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">Orders</x-responsive-nav-link>

            @if(auth()->user()->isAtLeast('supervisor'))
                <div class="pt-2 pb-1 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catalog & Stock</div>
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*') || request()->routeIs('categories.*')">Products</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('recipes.index')" :active="request()->routeIs('recipes.*')">Recipes</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">Inventory</x-responsive-nav-link>
            @endif

            @if(auth()->user()->isAtLeast('manager'))
                <div class="pt-2 pb-1 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Management</div>
                <x-responsive-nav-link :href="route('consumption.index')" :active="request()->routeIs('consumption.*')">Consumption</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.sales')" :active="request()->routeIs('reports.*')">Reports</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')">Audit Logs</x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-3 pb-3 px-4 border-t border-gray-100 bg-gray-50/50">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-bold text-sm text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-xs text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase
                    {{ auth()->user()->role === 'owner' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                    {{ auth()->user()->role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ auth()->user()->role === 'supervisor' ? 'bg-teal-100 text-teal-800' : '' }}
                    {{ auth()->user()->role === 'cashier' ? 'bg-gray-100 text-gray-800' : '' }}
                ">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                @if(auth()->user()->isOwner())
                    <x-responsive-nav-link :href="route('users.index')">User Management</x-responsive-nav-link>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
