<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Staff & User Management') }}
                </h2>
                <p class="text-xs text-gray-600 font-medium mt-0.5">
                    Manage system staff, shared cashier logins, supervisor permissions, and managerial access
                </p>
            </div>
            
            <!-- Single Primary Solid Green Button (G1, G3) -->
            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 py-2 bg-[#0e703c] hover:bg-[#0b5930] text-white font-semibold text-xs sm:text-sm rounded-xl shadow-xs transition active:scale-[0.98]">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add staff member</span>
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:max-w-[1880px] 2xl:mx-auto space-y-4 sm:space-y-6">

            <!-- Scale & Filter Toolbar (Staff #4, G2) -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                <form id="users-filter-form" method="GET" action="{{ route('users.index') }}" class="flex-1 flex flex-col sm:flex-row gap-3 items-center w-full">
                    <div class="relative flex-1 w-full max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="users-search-input"
                               value="{{ request('search') }}" 
                               placeholder="Search staff by name or email..." 
                               class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] focus:border-[#0e703c] outline-none font-medium placeholder-gray-500 shadow-2xs" />
                    </div>

                    <div class="w-full sm:w-48">
                        <select name="role" 
                                onchange="document.getElementById('users-filter-form').submit()" 
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0e703c] outline-none font-medium text-gray-700 shadow-2xs">
                            <option value="">All roles</option>
                            <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                            <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="cashier" {{ request('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                        </select>
                    </div>

                    @if(request('search') || request('role'))
                        <a href="{{ route('users.index') }}" class="text-xs font-bold text-gray-600 hover:text-rose-600 transition underline whitespace-nowrap">
                            Reset
                        </a>
                    @endif
                </form>

                <div class="text-xs font-semibold text-gray-600 whitespace-nowrap self-end sm:self-center">
                    Showing {{ $users->total() }} {{ $users->total() === 1 ? 'staff member' : 'staff members' }}
                </div>
            </div>

            <!-- Users Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/80 text-gray-600 text-xs font-semibold border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-4 font-bold">Staff member</th>
                                <th class="py-3.5 px-4 font-bold">Email address</th>
                                <th class="py-3.5 px-4 font-bold">System role</th>
                                <th class="py-3.5 px-4 font-bold">Branch</th>
                                <th class="py-3.5 px-4 text-center font-bold">Account status</th>
                                <th class="py-3.5 px-4 font-bold">Member since</th>
                                <th class="py-3.5 px-4 text-right font-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                @php
                                    $isOwner = $user->role === 'owner';
                                    $isSelf = $user->id === auth()->id();
                                    $nameCapitalized = ucwords(strtolower($user->name));
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition {{ !$user->is_active ? 'bg-gray-50/40 text-gray-500' : '' }}">
                                    <!-- Name & Avatar (Normalized Capitalization - Staff #5) -->
                                    <td class="py-3.5 px-4 font-bold text-gray-900">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs shrink-0 shadow-2xs border
                                                {{ $isOwner ? 'bg-emerald-100 text-[#0e703c] border-emerald-200' : '' }}
                                                {{ $user->role === 'manager' ? 'bg-indigo-100 text-indigo-700 border-indigo-200' : '' }}
                                                {{ $user->role === 'cashier' ? 'bg-cyan-100 text-cyan-800 border-cyan-200' : '' }}
                                            ">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-extrabold text-gray-900">{{ $nameCapitalized }}</span>
                                                    @if($isSelf)
                                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-gray-100 text-gray-600">You</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-gray-600 font-medium">
                                        {{ $user->email }}
                                    </td>

                                    <!-- Distinct Role Badges -->
                                    <td class="py-3.5 px-4">
                                        @if($user->role === 'owner')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-[#0e703c] border border-emerald-200/80 shadow-2xs">
                                                <span>👑</span>
                                                <span>Owner</span>
                                            </span>
                                        @elseif($user->role === 'manager')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-100 text-indigo-800 border border-indigo-200/80 shadow-2xs">
                                                <span>🛡️</span>
                                                <span>Manager</span>
                                            </span>
                                        @elseif($user->role === 'cashier')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-black bg-cyan-100 text-cyan-900 border border-cyan-200/80 shadow-2xs">
                                                <span>🏷️</span>
                                                <span>Cashier</span>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Assigned Branch Column -->
                                    <td class="py-3.5 px-4 font-medium text-xs">
                                        @if($user->branch)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#0e703c] border border-emerald-200">
                                                📍 {{ $user->branch->name }}
                                            </span>
                                        @elseif(in_array($user->role, ['owner', 'manager']))
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                                🌐 All branches (owner access)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                ⚠️ Unassigned
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Account Status & Safe Toggle (Staff #1, Staff #3) -->
                                    <td class="py-3.5 px-4 text-center">
                                        @if($isOwner)
                                            <!-- Owner lock explanation tooltip (Staff #3) -->
                                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#0e703c] border border-emerald-200/60" title="The owner account cannot be deactivated.">
                                                <svg class="w-3.5 h-3.5 text-[#0e703c]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                <span>Active (Permanent)</span>
                                            </div>
                                        @elseif($isSelf)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#0e703c] border border-emerald-200/60">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                <span>Active (You)</span>
                                            </span>
                                        @else
                                            <!-- Toggle Switch with Confirmation Dialog (Staff #1) -->
                                            <form id="toggle-form-{{ $user->id }}" method="POST" action="{{ route('users.toggle-active', $user) }}" class="inline-block">
                                                @csrf
                                                <button type="button" 
                                                        onclick="handleToggleUser({{ $user->id }}, '{{ addslashes($nameCapitalized) }}', {{ $user->is_active ? 'true' : 'false' }})" 
                                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold transition shadow-2xs cursor-pointer border {{ $user->is_active ? 'bg-emerald-50 text-[#0e703c] border-emerald-200 hover:bg-emerald-100' : 'bg-gray-100 text-gray-600 border-gray-200 hover:bg-gray-200' }}"
                                                        title="Click to toggle status (currently {{ $user->is_active ? 'Active' : 'Inactive' }})">
                                                    <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                                    <span>{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                                </button>
                                            </form>
                                        @endif
                                    </td>

                                    <!-- Consistent Date Format (G5) -->
                                    <td class="py-3.5 px-4 text-xs font-semibold text-gray-600">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>

                                    <!-- Actions: Secondary Edit Button Only (G1 - Red Deactivate Removed!) -->
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('users.edit', $user) }}" 
                                           class="inline-flex items-center justify-center h-9 min-h-[36px] px-3.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 rounded-xl text-xs font-semibold shadow-2xs transition active:scale-95" 
                                           title="Edit staff member profile">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-500 font-medium">
                                        No staff members found matching your filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="p-4 border-t border-gray-100 bg-gray-50/40">
                        {{ $users->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- User Deactivate Confirmation Modal (Staff #1, Error Prevention) -->
    <div id="user-status-confirm-modal" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4" style="display: none;" onclick="if(event.target === this) closeUserStatusModal()">
        <div class="shift-modal-box bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border border-gray-100 animate-scale-up">
            <div id="user-modal-icon-bg" class="w-14 h-14 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 mx-auto mb-4 text-2xl shadow-inner">
                ⚠️
            </div>
            <h3 id="user-modal-title" class="font-extrabold text-gray-900 text-lg">Change User Status</h3>
            <p id="user-modal-msg" class="text-xs text-gray-600 mt-2 leading-relaxed">
                Are you sure you want to change this staff member's status?
            </p>
            <div class="grid grid-cols-2 gap-3 mt-6">
                <button type="button" onclick="closeUserStatusModal()" class="py-2.5 px-4 rounded-xl border border-gray-200 font-bold text-xs text-gray-700 hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="button" id="user-modal-confirm-btn" onclick="submitUserStatusToggle()" class="py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let pendingToggleUserId = null;

        function handleToggleUser(userId, userName, isActive) {
            if (isActive) {
                // Confirm dialog when deactivating (Staff #1)
                pendingToggleUserId = userId;
                document.getElementById('user-modal-title').innerText = `Deactivate ${userName}?`;
                document.getElementById('user-modal-msg').innerText = `Deactivate ${userName}? She won't be able to log in to the POS or system.`;
                document.getElementById('user-modal-confirm-btn').innerText = 'Deactivate';
                document.getElementById('user-modal-confirm-btn').className = 'py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition';
                document.getElementById('user-status-confirm-modal').style.display = 'flex';
            } else {
                // Re-activating: directly submit
                document.getElementById(`toggle-form-${userId}`).submit();
            }
        }

        function closeUserStatusModal() {
            document.getElementById('user-status-confirm-modal').style.display = 'none';
            pendingToggleUserId = null;
        }

        function submitUserStatusToggle() {
            if (pendingToggleUserId) {
                document.getElementById(`toggle-form-${pendingToggleUserId}`).submit();
            }
        }

        // Live search with 300ms debounce (G2)
        let searchTimeout = null;
        const searchInput = document.getElementById('users-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('users-filter-form').submit();
                }, 350);
            });
        }
    </script>
    @endpush
</x-app-layout>
