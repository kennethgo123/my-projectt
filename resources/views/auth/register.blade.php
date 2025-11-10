<x-guest-layout>
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
        <!-- Hero/Image side -->
        <div class="hidden lg:block relative">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-indigo-700 to-indigo-500 opacity-90"></div>
            <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('{{ asset('img/hero.jpg') }}');"></div>
            <div class="relative z-10 h-full w-full flex items-center justify-center px-10">
                <div class="text-center text-white max-w-lg">
                    <x-application-logo class="mx-auto h-14 w-auto mb-6" />
                    <h2 class="text-3xl font-bold">Join LexCav</h2>
                    <p class="mt-3 text-indigo-100">Create an account to book consultations and manage your cases.</p>
                </div>
            </div>
        </div>

        <!-- Form side -->
        <div class="flex items-center justify-center py-12 px-6 lg:px-12 bg-gray-50">
            <div class="w-full max-w-md">
                <div class="bg-white overflow-hidden shadow-xl rounded-lg">
                    <div class="p-6">
                        <x-authentication-card>
                            <x-slot name="logo">
                                <x-authentication-card-logo />
                            </x-slot>

                            <x-validation-errors class="mb-4" />

                            <form method="POST" action="{{ route('register') }}">
                                @csrf

                                <div>
                                    <x-label for="name" value="{{ __('Name') }}" />
                                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                                </div>

                                <div class="mt-4">
                                    <x-label for="email" value="{{ __('Email') }}" />
                                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                                </div>

                        <div class="mt-4">
                            <x-label for="password" value="{{ __('Password') }}" />
                            <div class="relative">
                                <input id="password" class="block mt-1 w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password" required autocomplete="new-password" />
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

                        <div class="mt-4">
                            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                            <div class="relative">
                                <input id="password_confirmation" class="block mt-1 w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password_confirmation" required autocomplete="new-password" />
                                <button type="button" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="(function(btn){var i=document.getElementById('password_confirmation');var o=btn.querySelector('.eye-open');var c=btn.querySelector('.eye-closed');if(i.type==='password'){i.type='text';o.classList.add('hidden');c.classList.remove('hidden');}else{i.type='password';o.classList.remove('hidden');c.classList.add('hidden');}})(this)">
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

                                <div class="mt-6 flex items-center justify-end">
                                    <x-button>
                                        {{ __('Register') }}
                                    </x-button>
                                </div>
                            </form>
                        </x-authentication-card>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
