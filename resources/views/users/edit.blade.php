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
                    Update staff member profile, email, role, or reset password
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
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
                        <select name="role" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#155d49] focus:border-[#155d49] outline-none">
                            <option value="cashier" {{ old('role', $user->role) === 'cashier' ? 'selected' : '' }}>Cashier — Shared POS Terminal access</option>
                            <option value="supervisor" {{ old('role', $user->role) === 'supervisor' ? 'selected' : '' }}>Supervisor — POS + Products + Recipes + Stock In/Waste + Refund Authorization</option>
                            <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager — Supervisor + Daily Consumption + Sales/Inventory Reports + Audit Logs</option>
                            <option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>Owner — Full System Access + User Management</option>
                        </select>
                        @error('role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

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
