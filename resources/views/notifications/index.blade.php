<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('System Notifications & Alerts') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Low stock thresholds, out-of-stock notices, and operational warnings
                </p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs rounded-xl border border-gray-300 shadow-sm transition gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark All as Read
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="space-y-3">
                @forelse($notifications as $notif)
                    <div class="bg-white rounded-2xl shadow-sm border p-4 transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 {{ !$notif->is_read ? 'border-emerald-300 bg-[#f0f8f5]/40' : 'border-gray-100' }}">
                        <div class="flex items-start gap-3">
                            <div class="p-2.5 rounded-xl mt-0.5 {{ $notif->type === 'out_of_stock' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 text-[10px] rounded-full font-bold uppercase {{ $notif->type === 'out_of_stock' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ str_replace('_', ' ', $notif->type) }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                                    @if(!$notif->is_read)
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#155d49]"></span>
                                    @endif
                                </div>
                                <p class="font-bold text-gray-900 text-sm mt-1">{{ $notif->message }}</p>
                                @if($notif->resolved_at)
                                    <p class="text-xs text-[#155d49] font-bold mt-1">
                                        ✓ Resolved on {{ $notif->resolved_at->format('M d, Y h:i A') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if(!$notif->is_read)
                                <form method="POST" action="{{ route('notifications.read', $notif) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition">
                                        Mark Read
                                    </button>
                                </form>
                            @endif

                            @if(!$notif->resolved_at)
                                <form method="POST" action="{{ route('notifications.resolve', $notif) }}">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 bg-[#155d49] hover:bg-[#114a3b] text-white rounded-xl text-xs font-bold shadow-sm transition">
                                        Resolve
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-[#155d49] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-bold text-gray-800">No active notifications</p>
                        <p class="text-xs text-gray-400 mt-0.5">All Heim inventory thresholds and operations are healthy.</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="p-4 bg-white rounded-2xl shadow-sm border border-gray-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
