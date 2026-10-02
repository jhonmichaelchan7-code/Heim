<!-- Navigation Layout: Modern Sidebar (Desktop) + Slide-over Drawer (Mobile) -->
<div>
    <!-- Desktop Sidebar (Visible on lg screens and up) -->
    <aside class="hidden lg:flex flex-col w-64 xl:w-72 bg-white border-r border-gray-200/80 h-screen sticky top-0 shrink-0 z-30 shadow-xs select-none">
        <!-- Brand Header -->
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl overflow-hidden shadow-sm border border-emerald-700/20 bg-[#155d49] flex items-center justify-center transition-transform group-hover:scale-105 shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover" />
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-lg text-gray-900 tracking-tight leading-tight">Heim</span>
                    <span class="text-[10px] uppercase tracking-wider text-[#155d49] font-bold">POS & Inventory</span>
                </div>
            </a>
        </div>

        <!-- Live Clock & Shift Badge -->
        <div class="px-5 py-3 bg-[#f0f8f5]/60 border-b border-gray-100 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 text-[#155d49] font-semibold">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span id="nav-live-clock" class="font-mono font-bold">--:--:-- --</span>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                {{ auth()->user()->role === 'owner' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                {{ auth()->user()->role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ auth()->user()->role === 'supervisor' ? 'bg-teal-100 text-teal-800' : '' }}
                {{ auth()->user()->role === 'cashier' ? 'bg-gray-100 text-gray-700' : '' }}
            ">
                {{ ucfirst(auth()->user()->role) }}
            </span>
        </div>

        <!-- Scrollable Navigation Items -->
        <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6 custom-scrollbar text-sm">
            <!-- Operations Group -->
            <div class="space-y-1">
                <div class="px-3 pb-1 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                    Operations
                </div>

                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('pos.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('pos.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('pos.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <div class="flex-1 flex items-center justify-between">
                        <span>POS Terminal</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ request()->routeIs('pos.*') ? 'bg-white/20 text-white' : 'bg-emerald-100 text-[#155d49]' }}">SALE</span>
                    </div>
                </a>

                <a href="{{ route('orders.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('orders.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('orders.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Orders History</span>
                </a>
            </div>

            <!-- Management Group (Supervisor+) -->
            @if(auth()->user()->isAtLeast('supervisor'))
                <div class="space-y-1">
                    <div class="px-3 pb-1 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                        Catalog & Inventory
                    </div>

                    <a href="{{ route('products.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Products & Menu</span>
                    </a>

                    <a href="{{ route('recipes.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('recipes.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('recipes.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>Recipes</span>
                    </a>

                    <a href="{{ route('inventory.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('inventory.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('inventory.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span>Inventory</span>
                    </a>
                </div>
            @endif

            <!-- Analytics & Governance (Manager+) -->
            @if(auth()->user()->isAtLeast('manager'))
                <div class="space-y-1">
                    <div class="px-3 pb-1 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                        Analytics & Reports
                    </div>

                    <a href="{{ route('consumption.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('consumption.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('consumption.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span>Consumption</span>
                    </a>

                    <a href="{{ route('reports.sales') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Sales Reports</span>
                    </a>

                    <a href="{{ route('audit-logs.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('audit-logs.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('audit-logs.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Audit Logs</span>
                    </a>
                </div>
            @endif

            <!-- Administration (Owner) -->
            @if(auth()->user()->isOwner())
                <div class="space-y-1">
                    <div class="px-3 pb-1 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">
                        Administration
                    </div>

                    <a href="{{ route('users.index') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('users.*') ? 'bg-[#155d49] text-white shadow-sm' : 'text-gray-700 hover:text-[#155d49] hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('users.*') ? 'text-white' : 'text-gray-400 group-hover:text-[#155d49]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span>User Management</span>
                    </a>
                </div>
            @endif
        </nav>

        <!-- User Profile & Notifications Footer -->
        <div class="p-3.5 border-t border-gray-100 bg-gray-50/70 space-y-2">
            @if(auth()->user()->isAtLeast('manager'))
                @php
                    $unreadCount = \App\Models\SystemNotification::unread()
                        ->forRole(auth()->user()->role)
                        ->count();
                @endphp
                <a href="{{ route('notifications.index') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('notifications.*') ? 'bg-white shadow-xs text-[#155d49]' : 'text-gray-600 hover:bg-white hover:text-gray-900' }}">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span>Notifications</span>
                    </div>
                    @if($unreadCount > 0)
                        <span class="bg-rose-500 text-white text-[10px] rounded-full h-4.5 min-w-[18px] px-1 flex items-center justify-center font-bold">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
            @endif

            <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-gray-100 shadow-xs">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 min-w-0 group">
                    <div class="w-8 h-8 rounded-lg bg-[#155d49] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs group-hover:ring-2 group-hover:ring-emerald-300 transition">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs font-bold text-gray-900 truncate leading-tight group-hover:text-[#155d49] transition">
                            {{ Auth::user()->name }}
                        </span>
                        <span class="text-[10px] text-gray-400 truncate">
                            {{ Auth::user()->email }}
                        </span>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" 
                            title="Log Out"
                            class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Mobile Top Navigation Bar (Visible on mobile/tablet < lg) -->
    <header class="lg:hidden bg-white border-b border-gray-200/80 h-16 px-4 flex items-center justify-between sticky top-0 z-40 shadow-xs select-none">
        <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle Button -->
            <button @click="mobileOpen = true" 
                    type="button" 
                    class="p-2 -ml-1 text-gray-600 hover:text-[#155d49] hover:bg-[#f0f8f5] rounded-xl transition"
                    aria-label="Open sidebar menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Brand Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl overflow-hidden bg-[#155d49] flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover" />
                </div>
                <span class="font-black text-base text-gray-900 tracking-tight">Heim</span>
            </a>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Mobile Clock -->
            <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 bg-[#f0f8f5] text-[#155d49] border border-emerald-100 rounded-lg text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span id="nav-live-clock-mobile" class="font-mono text-[11px] font-bold">--:-- --</span>
            </div>

            <!-- Mobile Notifications -->
            @if(auth()->user()->isAtLeast('manager'))
                @php
                    $unreadCount = \App\Models\SystemNotification::unread()
                        ->forRole(auth()->user()->role)
                        ->count();
                @endphp
                <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-500 hover:text-[#155d49] rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($unreadCount > 0)
                        <span class="absolute top-1 right-1 bg-red-500 text-white text-[10px] rounded-full h-4 min-w-[16px] px-0.5 flex items-center justify-center font-bold">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
            @endif

            <!-- Mobile Avatar / Profile Link -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-1.5 p-1 rounded-xl hover:bg-gray-50 transition">
                <div class="w-8 h-8 rounded-lg bg-[#155d49] text-white flex items-center justify-center font-bold text-xs shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </a>
        </div>
    </header>

    <!-- Mobile Slide-Out Drawer (Off-Canvas Overlay) -->
    <div x-show="mobileOpen" 
         x-cloak
         class="fixed inset-0 z-50 lg:hidden" 
         aria-modal="true" 
         role="dialog">
        <!-- Backdrop Blur Overlay -->
        <div x-show="mobileOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileOpen = false"
             class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <!-- Slide Drawer Container -->
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative flex flex-col w-4/5 max-w-xs h-full bg-white shadow-2xl select-none">
            
            <!-- Drawer Header -->
            <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl overflow-hidden bg-[#155d49] flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="Heim Logo" class="w-full h-full object-cover" />
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-base text-gray-900 leading-tight">Heim</span>
                        <span class="text-[9px] uppercase tracking-wider text-[#155d49] font-bold">POS & Inventory</span>
                    </div>
                </a>

                <!-- Close Button -->
                <button @click="mobileOpen = false" 
                        type="button" 
                        class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Drawer Navigation Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-5 text-sm">
                <!-- Operations -->
                <div class="space-y-1">
                    <div class="px-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Operations</div>
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('pos.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('pos.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('pos.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>POS Terminal</span>
                    </a>
                    <a href="{{ route('orders.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('orders.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('orders.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Orders History</span>
                    </a>
                </div>

                <!-- Catalog & Stock (Supervisor+) -->
                @if(auth()->user()->isAtLeast('supervisor'))
                    <div class="space-y-1">
                        <div class="px-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Catalog & Inventory</div>
                        <a href="{{ route('products.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('products.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span>Products</span>
                        </a>
                        <a href="{{ route('recipes.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('recipes.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('recipes.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>Recipes</span>
                        </a>
                        <a href="{{ route('inventory.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('inventory.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('inventory.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Inventory</span>
                        </a>
                    </div>
                @endif

                <!-- Analytics (Manager+) -->
                @if(auth()->user()->isAtLeast('manager'))
                    <div class="space-y-1">
                        <div class="px-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Analytics & Reports</div>
                        <a href="{{ route('consumption.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('consumption.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('consumption.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Consumption</span>
                        </a>
                        <a href="{{ route('reports.sales') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('reports.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Sales Reports</span>
                        </a>
                        <a href="{{ route('audit-logs.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('audit-logs.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('audit-logs.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Audit Logs</span>
                        </a>
                    </div>
                @endif

                <!-- Administration (Owner) -->
                @if(auth()->user()->isOwner())
                    <div class="space-y-1">
                        <div class="px-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Administration</div>
                        <a href="{{ route('users.index') }}" 
                           class="flex items-center gap-3 px-3 py-2 rounded-xl font-semibold transition {{ request()->routeIs('users.*') ? 'bg-[#155d49] text-white shadow-xs' : 'text-gray-700 hover:bg-[#f0f8f5]' }}">
                            <svg class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>User Management</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Drawer Footer (User Info & Logout) -->
            <div class="p-4 border-t border-gray-100 bg-gray-50/70 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#155d49] text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name }}</span>
                        <span class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                    <a href="{{ route('profile.edit') }}" class="py-2 text-center bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 text-center bg-rose-50 border border-rose-100 text-rose-700 font-bold rounded-xl hover:bg-rose-100 transition">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
