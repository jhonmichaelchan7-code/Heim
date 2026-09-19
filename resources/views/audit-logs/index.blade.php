<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Audit Trails & Activity Logs') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Immutable activity log of user actions, financial transactions, inventory movements, and system changes
                </p>
            </div>
            <button onclick="window.print()" class="px-3.5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Logs
            </button>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-[1650px] mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Search Actor / Action</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. John, Order #..." class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Module</label>
                        <select name="module" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Modules</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-sm">
                            Filter
                        </button>
                        <a href="{{ route('audit-logs.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm rounded-xl transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Timestamp</th>
                                <th class="py-3.5 px-4">Actor</th>
                                <th class="py-3.5 px-4">Module</th>
                                <th class="py-3.5 px-4">Action</th>
                                <th class="py-3.5 px-4">Description</th>
                                <th class="py-3.5 px-4">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-xs text-gray-500 font-mono whitespace-nowrap">
                                        {{ $log->created_at->format('M d, Y h:i:s A') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-gray-900">{{ $log->actor_name }}</div>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] uppercase font-bold
                                            {{ $log->actor_role === 'owner' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                            {{ $log->actor_role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $log->actor_role === 'supervisor' ? 'bg-teal-100 text-teal-800' : '' }}
                                            {{ $log->actor_role === 'cashier' ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ $log->actor_role ?? 'System' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#f0f8f5] text-[#155d49] uppercase">
                                            {{ $log->module }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                                            {{ str_contains($log->action, 'order') ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                            {{ str_contains($log->action, 'refund') ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ str_contains($log->action, 'stock') ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ str_contains($log->action, 'create') ? 'bg-teal-100 text-teal-800' : '' }}
                                            {{ str_contains($log->action, 'update') ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-700 text-xs">
                                        {{ $log->description }}
                                        @if($log->metadata)
                                            <div class="mt-1 font-mono text-[10px] text-gray-400 bg-gray-50 p-1.5 rounded-lg border border-gray-200">
                                                {{ json_encode($log->metadata) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-mono text-gray-400">
                                        {{ $log->ip_address ?? '127.0.0.1' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400">
                                        No audit records found matching your filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $logs->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
