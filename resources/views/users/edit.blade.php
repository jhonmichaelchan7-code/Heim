<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Edit Staff Member: {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Update staff member profile, email, role, assigned branch, or reset password
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('users.update', $user) }}" x-data="{ selectedRole: '{{ old('role', $user->role) }}' }" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none" />
                        @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none" />
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Role / Permissions</label>
                        <select name="role" x-model="selectedRole" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none">
                            <option value="cashier" {{ old('role', $user->role) === 'cashier' ? 'selected' : '' }}>Cashier — Shared POS Terminal access</option>
                            <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager — Products + Recipes + Inventory + Reports + Audit Logs</option>
                            <option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>Owner — Full System Access + User Management</option>
                        </select>
                        @error('role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Assigned Branch Select (Required for Cashier, All branches for Manager/Owner) -->
                    <div x-show="selectedRole === 'cashier'" class="space-y-1" x-cloak>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Assigned branch <span class="text-rose-500">*</span>
                        </label>
                        <select name="branch_id" :required="selectedRole === 'cashier'" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none">
                            <option value="">Select branch...</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                    📍 {{ $branch->name }} ({{ $branch->code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-500">Cashiers are locked to their assigned branch for shift opening, POS transactions, and thermal receipts.</p>
                        @error('branch_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div x-show="['manager', 'owner'].includes(selectedRole)" class="space-y-1" x-cloak>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Assigned branch</label>
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600 flex items-center gap-2">
                            <span>🌐</span>
                            <span class="font-medium">All branches (owner access) — Managers and Owners have global access across all branches.</span>
                        </div>
                    </div>

                    @if($user->id !== auth()->id())
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Account Status</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition {{ old('is_active', $user->is_active) ? 'border-[#155d49] bg-[#f0f8f5]' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="text-[#155d49] focus:ring-[#155d49]">
                                <div>
                                    <span class="text-sm font-bold text-gray-900 block">Active Account</span>
                                    <span class="text-xs text-gray-500">Can log in and operate POS or back-office</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition {{ !old('is_active', $user->is_active) ? 'border-rose-500 bg-rose-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                <input type="radio" name="is_active" value="0" {{ !old('is_active', $user->is_active) ? 'checked' : '' }} class="text-rose-600 focus:ring-rose-500">
                                <div>
                                    <span class="text-sm font-bold text-gray-900 block">Deactivated / Inactive</span>
                                    <span class="text-xs text-gray-500">Account login blocked without deleting</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    @else
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-[#155d49] font-medium">
                        <strong>Account Status:</strong> Active (You cannot deactivate your own current login session).
                    </div>
                    @endif

                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Change Password (Optional)</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">New Password</label>
                                <input type="password" name="password" placeholder="Leave blank to keep current" class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none" />
                                @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Confirm New Password</label>
                                <input type="password" name="password_confirmation" placeholder="Leave blank to keep current" class="w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-4 flex justify-end gap-3">
                        <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow transition">
                            Update Staff Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
