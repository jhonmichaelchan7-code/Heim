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
            <!-- HCI Theory: Single prominent primary action button -->
            <div class="flex items-center no-print">
                <button type="button" 
                        onclick="exportCompleteAuditLogsExcel()" 
                        class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#107c41] hover:bg-[#0c6133] text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs hover:shadow transition-all duration-150 active:scale-[0.98]">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6C4.89 2 4 2.89 4 4v16c0 1.11.89 2 2 2h12c1.11 0 2-.89 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                    <span>Export Logs (Excel)</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Search Actor / Action</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. John, Order #..." class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Module</label>
                        <select name="module" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium">
                            <option value="">All Modules</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none" />
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 h-10 px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            <span>Filter</span>
                        </button>
                        <a href="{{ route('audit-logs.index') }}" class="h-10 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition flex items-center justify-center">
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

            <script>
                window.exportCompleteAuditLogsExcel = function() {
                    const reportData = {
                        title: "HEIM COFFEE - AUDIT TRAILS & ACTIVITY LOGS",
                        generatedAt: new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }),
                        items: [
                            @foreach($logs as $log)
                            [
                                "{{ $log->created_at->format('Y-m-d H:i:s') }}",
                                "{{ addslashes($log->actor_name) }}",
                                "{{ $log->actor_role ?? 'System' }}",
                                "{{ $log->module }}",
                                "{{ $log->action }}",
                                "{{ addslashes(str_replace(["\r", "\n"], ' ', $log->description)) }}",
                                "{{ $log->ip_address ?? '127.0.0.1' }}"
                            ],
                            @endforeach
                        ]
                    };

                    const escapeCsv = (str) => '"' + String(str ?? '').replace(/"/g, '""') + '"';

                    let csv = "";
                    csv += escapeCsv(reportData.title) + "\n";
                    csv += escapeCsv("Generated At:") + "," + escapeCsv(reportData.generatedAt) + "\n\n";

                    csv += escapeCsv("Timestamp") + "," + escapeCsv("Actor Name") + "," + escapeCsv("Actor Role") + "," + escapeCsv("Module") + "," + escapeCsv("Action") + "," + escapeCsv("Description") + "," + escapeCsv("IP Address") + "\n";

                    reportData.items.forEach(row => {
                        csv += escapeCsv(row[0]) + "," + escapeCsv(row[1]) + "," + escapeCsv(row[2]) + "," + escapeCsv(row[3]) + "," + escapeCsv(row[4]) + "," + escapeCsv(row[5]) + "," + escapeCsv(row[6]) + "\n";
                    });

                    // Add UTF-8 BOM so Excel opens with proper encoding
                    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', `Heim_Audit_Logs_${new Date().toISOString().slice(0, 10)}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    setTimeout(() => URL.revokeObjectURL(url), 1000);
                };
            </script>
        </div>
    </div>
</x-app-layout>
