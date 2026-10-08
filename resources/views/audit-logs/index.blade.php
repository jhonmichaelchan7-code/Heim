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
            <!-- Consolidated Export Dropdown (Secondary Outline) -->
            <div class="flex flex-wrap items-center gap-2 no-print">
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.outside="open = false" class="inline-flex items-center gap-2 h-10 px-4 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-semibold text-xs sm:text-sm rounded-xl shadow-2xs transition">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Export</span>
                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-1.5 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-30">
                        <button type="button" @click="open = false; exportCompleteAuditLogsExcel()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Export Excel (.csv)</span>
                        </button>
                        <button type="button" @click="open = false; printAuditLogsDataOnly()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print Logs</span>
                        </button>
                        <div class="border-t border-gray-100 my-1"></div>
                        <button type="button" @click="open = false; openGoogleSheetModal()" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 text-left">
                            <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14H6v-2h6v2zm4-4H6v-2h10v2zm0-4H6V7h10v2z"/></svg>
                            <span>Sync Google Sheet</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Filter Card -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <form id="audit-filter-form" method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Search actor or action</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. John, Order #..." oninput="debounceAuditSearch()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium placeholder:text-gray-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Module</label>
                        <select name="module" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium text-gray-700">
                            <option value="">All Modules</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date from</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1">Date to</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="w-full h-10 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#155d49] outline-none font-medium" />
                    </div>

                    <div class="flex items-center pb-1">
                        <a href="{{ route('audit-logs.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900 transition underline underline-offset-4">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm min-w-[1050px]">
                        <thead class="bg-gray-50 text-gray-700 text-xs font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4 w-44">Timestamp</th>
                                <th class="py-3.5 px-4 w-48">Actor</th>
                                <th class="py-3.5 px-4 w-28">Module</th>
                                <th class="py-3.5 px-4 w-36">Action</th>
                                <th class="py-3.5 px-4">Description</th>
                                <th class="py-3.5 px-4 w-32 text-right">IP address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3.5 px-4 text-xs text-gray-500 font-mono whitespace-nowrap">
                                        {{ $log->created_at->format('M d, Y h:i:s A') }}
                                    </td>
                                    <!-- Actor Cell: Nowrap on one line (Requirement 5) -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">{{ $log->actor_name }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] uppercase font-bold
                                                {{ $log->actor_role === 'owner' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                                {{ $log->actor_role === 'manager' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                                {{ $log->actor_role === 'supervisor' ? 'bg-amber-100 text-amber-800' : '' }}
                                                {{ $log->actor_role === 'cashier' ? 'bg-cyan-100 text-cyan-800' : '' }}
                                                {{ !in_array($log->actor_role, ['owner', 'manager', 'supervisor', 'cashier']) ? 'bg-gray-100 text-gray-700' : '' }}
                                            ">
                                                {{ $log->actor_role ?? 'System' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#f0f8f5] text-[#155d49] uppercase">
                                            {{ $log->module }}
                                        </span>
                                    </td>
                                    <!-- Humanized Action Names (Requirement 5) -->
                                    @php
                                        $actionLabels = [
                                            'user_created' => 'User Created',
                                            'user_updated' => 'User Updated',
                                            'user_activated' => 'User Activated',
                                            'user_deactivated' => 'User Deactivated',
                                            'stock_in' => 'Stock In',
                                            'stock_out' => 'Stock Out',
                                            'waste_recorded' => 'Waste Recorded',
                                            'stock_adjusted' => 'Stock Adjusted',
                                            'order_completed' => 'Order Completed',
                                            'order_cancelled' => 'Order Cancelled',
                                            'order_refunded' => 'Order Refunded',
                                            'shift_opened' => 'Shift Opened',
                                            'shift_closed' => 'Shift Closed',
                                            'recipe_created' => 'Recipe Created',
                                            'recipe_updated' => 'Recipe Updated',
                                            'recipe_deleted' => 'Recipe Deleted',
                                            'report_generated' => 'Report Generated',
                                            'google_sheet_synced' => 'Google Sheet Synced',
                                        ];
                                        $humanAction = $actionLabels[$log->action] ?? ucwords(str_replace(['_', '-'], ' ', $log->action));
                                    @endphp
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                                            {{ str_contains($log->action, 'order') || str_contains($log->action, 'stock_in') ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                            {{ str_contains($log->action, 'refund') || str_contains($log->action, 'waste') || str_contains($log->action, 'deactivat') ? 'bg-rose-100 text-rose-800' : '' }}
                                            {{ str_contains($log->action, 'shift') || str_contains($log->action, 'stock_out') ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ str_contains($log->action, 'create') || str_contains($log->action, 'activat') ? 'bg-teal-100 text-teal-800' : '' }}
                                            {{ str_contains($log->action, 'update') || str_contains($log->action, 'report') ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ $humanAction }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-700 text-xs">
                                        {{ $log->description }}
                                        <!-- Before / After Structured Metadata Display (Requirement 5) -->
                                        @if($log->metadata)
                                            @php
                                                $meta = $log->metadata;
                                                $hasBeforeAfter = (isset($meta['before']) && isset($meta['after'])) || (isset($meta['old']) && isset($meta['new']));
                                            @endphp
                                            @if($hasBeforeAfter)
                                                @php
                                                    $before = $meta['before'] ?? $meta['old'] ?? [];
                                                    $after = $meta['after'] ?? $meta['new'] ?? [];
                                                @endphp
                                                <div class="mt-1.5 flex flex-wrap gap-2 text-[11px]">
                                                    <div class="px-2 py-1 bg-rose-50 border border-rose-200/80 rounded-lg text-rose-800">
                                                        <span class="font-bold uppercase text-[9px] text-rose-600 block">Before</span>
                                                        <span class="font-mono">{{ is_array($before) ? json_encode($before) : $before }}</span>
                                                    </div>
                                                    <div class="px-2 py-1 bg-emerald-50 border border-emerald-200/80 rounded-lg text-emerald-800">
                                                        <span class="font-bold uppercase text-[9px] text-emerald-700 block">After</span>
                                                        <span class="font-mono">{{ is_array($after) ? json_encode($after) : $after }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-1.5 flex flex-wrap gap-1.5 text-[10px]">
                                                    @foreach($meta as $k => $v)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 border border-gray-200/80 text-gray-700">
                                                            <span class="font-semibold text-gray-500">{{ ucwords(str_replace('_', ' ', $k)) }}:</span>
                                                            <span class="font-mono font-medium">{{ is_array($v) ? json_encode($v) : $v }}</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <!-- IP Column: Guaranteed visible without overflow clipping (Requirement 5) -->
                                    <td class="py-3.5 px-4 text-xs font-mono text-gray-600 whitespace-nowrap text-right">
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
                let auditSearchTimer = null;
                window.debounceAuditSearch = function() {
                    clearTimeout(auditSearchTimer);
                    auditSearchTimer = setTimeout(() => {
                        document.getElementById('audit-filter-form').submit();
                    }, 300);
                };

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

                window.printAuditLogsDataOnly = function() {
                    const printWindow = window.open('', '_blank', 'width=1050,height=750');
                    if (!printWindow) {
                        alert('Please allow popups to print report data.');
                        return;
                    }

                    const printHtml = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Heim Coffee - Audit Trails & Activity Logs</title>
                        <style>
                            @page { size: landscape; margin: 10mm; }
                            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #111827; padding: 10px; margin: 0; font-size: 11px; }
                            .header { border-bottom: 2px solid #155d49; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
                            .store-title { font-size: 18px; font-weight: 900; color: #155d49; letter-spacing: 0.5px; }
                            .report-title { font-size: 13px; font-weight: 700; color: #374151; margin-top: 3px; }
                            .meta { font-size: 10px; color: #6b7280; text-align: right; }
                            table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10.5px; }
                            th, td { border: 1px solid #d1d5db; padding: 5px 8px; text-align: left; }
                            th { background: #f3f4f6; font-weight: 700; color: #374151; text-transform: uppercase; font-size: 9.5px; }
                            .footer { margin-top: 16px; border-top: 1px dashed #d1d5db; padding-top: 6px; font-size: 9px; color: #9ca3af; text-align: center; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <div>
                                <div class="store-title">HEIM COFFEE</div>
                                <div class="report-title">Audit Trails & Activity Logs</div>
                            </div>
                            <div class="meta">
                                <div><strong>Records:</strong> {{ $logs->total() }} events</div>
                                <div><strong>Printed:</strong> ${new Date().toLocaleString()}</div>
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Timestamp</th>
                                    <th style="width: 120px;">Actor Name</th>
                                    <th style="width: 80px;">Role</th>
                                    <th style="width: 80px;">Module</th>
                                    <th style="width: 100px;">Action</th>
                                    <th>Description</th>
                                    <th style="width: 90px;">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td><strong>{{ $log->actor_name }}</strong></td>
                                        <td>{{ $log->actor_role ?? 'System' }}</td>
                                        <td>{{ $log->module }}</td>
                                        <td>{{ $log->action }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="footer">
                            *** Heim POS Official Audit Trail Ledger &bull; Printed strictly for business records ***
                        </div>
                    </body>
                    </html>
                    `;

                    printWindow.document.open();
                    printWindow.document.write(printHtml);
                    printWindow.document.close();
                    setTimeout(() => {
                        printWindow.focus();
                        printWindow.print();
                    }, 350);
                };

                // Register Google Sheet Sync Payload Getter
                document.addEventListener('DOMContentLoaded', () => {
                    if (window.registerCurrentReportSyncGetter) {
                        window.registerCurrentReportSyncGetter(() => ({
                            action: 'sync_audit_logs',
                            report_title: 'Audit Trails & Activity Logs',
                            period: new Date().toISOString().slice(0, 10),
                            data: {
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
                            }
                        }));
                    }
                });
            </script>
        </div>
    </div>

    @include('reports.google-sheet-modal')
</x-app-layout>
