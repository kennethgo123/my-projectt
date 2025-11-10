<x-guest-layout>
    <div class="min-h-screen relative">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('/images/bg.png');"></div>
        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black/55"></div>

        <!-- Centered form -->
        <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-md">
                <div class="bg-white shadow-2xl rounded-xl overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="mb-4 flex items-center justify-center">
                            <x-application-logo class="h-10 w-auto" />
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-900 text-center mb-6">Login</h2>

                        <x-validation-errors class="mb-4" />

                        @session('status')
                            <div class="mb-4 font-medium text-sm text-green-600 text-center">
                                {{ $value }}
                            </div>
                        @endsession

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div>
                                <x-label for="email" value="{{ __('Email') }}" />
                                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                            </div>

                        <div class="mt-4">
                            <x-label for="password" value="{{ __('Password') }}" />
                            <div class="relative">
                                <input id="password" class="block mt-1 w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password" required autocomplete="current-password" />
                                <button type="button" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="(function(btn){var i=document.getElementById('password');var o=btn.querySelector('.eye-open');var c=btn.querySelector('.eye-closed');if(i.type==='password'){i.type='text';o.classList.add('hidden');c.classList.remove('hidden');}else{i.type='password';o.classList.remove('hidden');c.classList.add('hidden');}})(this)">
                                    <svg class="h-5 w-5 eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="h-5 w-5 eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.142-3.442M6.219 6.219A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.042 5.132M15 12a3 3 0 00-3-3m0 0a3 3 0 013 3m-3-3L3 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                            <div class="block mt-4">
                                <label for="remember_me" class="flex items-center">
                                    <x-checkbox id="remember_me" name="remember" />
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-between mt-6">
                                <a class="underline text-sm text-gray-700 hover:text-gray-900" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                                <x-button>
                                    {{ __('Log in') }}
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
