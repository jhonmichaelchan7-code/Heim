<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-full overflow-hidden shadow-sm border border-emerald-700/20 bg-[#155d49] flex items-center justify-center transition-transform group-hover:scale-105">
                            <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover rounded-full" />
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-lg text-gray-900 tracking-tight leading-none">Heim</span>
                            <span class="text-[10px] uppercase tracking-wider text-[#155d49] font-bold">POS & Inventory</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:ms-8 sm:flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        Dashboard
                    </a>

                    <a href="{{ route('pos.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('pos.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        POS
                    </a>

                    <a href="{{ route('orders.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('orders.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        Orders
                    </a>

                    @if(auth()->user()->isAtLeast('supervisor'))
                        <a href="{{ route('products.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Products
                        </a>

                        <a href="{{ route('recipes.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('recipes.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Recipes
                        </a>

                        <a href="{{ route('inventory.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('inventory.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Inventory
                        </a>
                    @endif

                    @if(auth()->user()->isAtLeast('manager'))
                        <a href="{{ route('consumption.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('consumption.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Consumption
                        </a>

                        <a href="{{ route('reports.sales') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Reports
                        </a>

                        <a href="{{ route('audit-logs.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold transition {{ request()->routeIs('audit-logs.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                            Audit Logs
                        </a>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <!-- Notifications Bell -->
                @if(auth()->user()->isAtLeast('manager'))
                    @php
                        $unreadCount = \App\Models\SystemNotification::unread()
                            ->forRole(auth()->user()->role)
                            ->count();
                    @endphp
                    <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-500 hover:text-[#155d49] hover:bg-[#f0f8f5] rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium shadow-sm animate-pulse">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                @endif

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase
                                    {{ auth()->user()->role === 'owner' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                    {{ auth()->user()->role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ auth()->user()->role === 'supervisor' ? 'bg-teal-100 text-teal-800' : '' }}
                                    {{ auth()->user()->role === 'cashier' ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst(auth()->user()->role) }}
                                </span>
                                <span class="font-semibold">{{ Auth::user()->name }}</span>
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
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-[#155d49] hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pos.index')" :active="request()->routeIs('pos.*')">POS</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">Orders</x-responsive-nav-link>

            @if(auth()->user()->isAtLeast('supervisor'))
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">Products</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('recipes.index')" :active="request()->routeIs('recipes.*')">Recipes</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')">Inventory</x-responsive-nav-link>
            @endif

            @if(auth()->user()->isAtLeast('manager'))
                <x-responsive-nav-link :href="route('consumption.index')" :active="request()->routeIs('consumption.*')">Consumption</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.sales')" :active="request()->routeIs('reports.*')">Reports</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')">Audit Logs</x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
