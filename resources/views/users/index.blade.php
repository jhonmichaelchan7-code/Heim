<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight">
                    {{ __('Staff & User Management') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Manage system staff, shared cashier logins, supervisor permissions, and managerial access
                </p>
            </div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-xs rounded-xl shadow-sm transition gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Staff Member
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3.5 px-4">Staff Member</th>
                                <th class="py-3.5 px-4">Email Address</th>
                                <th class="py-3.5 px-4">System Role</th>
                                <th class="py-3.5 px-4 text-center">Account Status</th>
                                <th class="py-3.5 px-4">Created Date</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-4 px-4 font-bold text-gray-900 flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-[#f0f8f5] text-[#155d49] flex items-center justify-center font-bold text-xs border border-emerald-200 shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <span>{{ $user->name }}</span>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600 font-medium">
                                        {{ $user->email }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase
                                            {{ $user->role === 'owner' ? 'bg-emerald-100 text-[#155d49]' : '' }}
                                            {{ $user->role === 'manager' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $user->role === 'supervisor' ? 'bg-teal-100 text-teal-800' : '' }}
                                            {{ $user->role === 'cashier' ? 'bg-gray-100 text-gray-800' : '' }}
                                        ">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 rounded-full text-xs font-bold transition
                                                    {{ $user->is_active ? 'bg-emerald-100 text-[#155d49] hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}
                                                ">
                                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-[#155d49]">
                                                Active (You)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-xs text-gray-500 font-mono">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="py-4 px-4 text-right space-x-2">
                                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-bold transition">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        No users registered.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
