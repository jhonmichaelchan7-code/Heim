<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5 text-center">
        <h2 class="text-xl font-bold text-gray-900">Staff Portal Login</h2>
        <p class="text-xs text-gray-500 mt-1">Sign in to your Heim terminal or management dashboard</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-xs font-bold text-gray-700 uppercase tracking-wider" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-200 focus:border-[#155d49] focus:ring-[#155d49] rounded-xl text-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@coffee.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-xs font-bold text-gray-700 uppercase tracking-wider" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-200 focus:border-[#155d49] focus:ring-[#155d49] rounded-xl text-sm"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#155d49] shadow-sm focus:ring-[#155d49]" name="remember">
                <span class="ms-2 text-xs text-gray-600 font-medium">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-semibold text-[#155d49] hover:underline" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full py-3 bg-[#155d49] hover:bg-[#114a3b] text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-900/20 transition flex items-center justify-center gap-2">
                <span>Sign In to Terminal</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>
</x-guest-layout>
