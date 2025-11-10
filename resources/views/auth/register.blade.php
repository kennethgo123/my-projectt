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
                                <button type="button" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="(function(btn){var i=document.getElementById('password');var o=btn.querySelector('.eye-open');var c=btn.querySelector('.eye-closed');if(i.type==='password'){i.type='text';o.classList.add('hidden');c.classList.remove('hidden');c.classList.add('opacity-0');requestAnimationFrame(()=>{c.classList.remove('opacity-0');});}else{i.type='password';o.classList.remove('hidden');o.classList.add('opacity-0');requestAnimationFrame(()=>{o.classList.remove('opacity-0');});c.classList.add('hidden');}})(this)">
                                    <svg class="h-5 w-5 eye-open transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12c1.5-4.5 6-7.5 9.75-7.5S20.25 7.5 21.75 12c-1.5 4.5-6 7.5-9.75 7.5S3.75 16.5 2.25 12z" />
                                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                                    </svg>
                                    <svg class="h-5 w-5 eye-closed hidden transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 7.418A7.93 7.93 0 0112 7.5c3.75 0 8.25 3 9.75 7.5a13.16 13.16 0 01-3.18 4.59M6.34 6.34C4.3 7.67 2.87 9.56 2.25 12c.54 1.65 1.56 3.15 2.88 4.41M9.88 14.12A3 3 0 0012 15c1.657 0 3-1.343 3-3 0-.42-.084-.82-.236-1.184" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                            <div class="relative">
                                <input id="password_confirmation" class="block mt-1 w-full pr-10 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password_confirmation" required autocomplete="new-password" />
                                <button type="button" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700" onclick="(function(btn){var i=document.getElementById('password_confirmation');var o=btn.querySelector('.eye-open');var c=btn.querySelector('.eye-closed');if(i.type==='password'){i.type='text';o.classList.add('hidden');c.classList.remove('hidden');c.classList.add('opacity-0');requestAnimationFrame(()=>{c.classList.remove('opacity-0');});}else{i.type='password';o.classList.remove('hidden');o.classList.add('opacity-0');requestAnimationFrame(()=>{o.classList.remove('opacity-0');});c.classList.add('hidden');}})(this)">
                                    <svg class="h-5 w-5 eye-open transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12c1.5-4.5 6-7.5 9.75-7.5S20.25 7.5 21.75 12c-1.5 4.5-6 7.5-9.75 7.5S3.75 16.5 2.25 12z" />
                                        <circle cx="12" cy="12" r="3" stroke-width="2" />
                                    </svg>
                                    <svg class="h-5 w-5 eye-closed hidden transition-opacity duration-150" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18M10.584 7.418A7.93 7.93 0 0112 7.5c3.75 0 8.25 3 9.75 7.5a13.16 13.16 0 01-3.18 4.59M6.34 6.34C4.3 7.67 2.87 9.56 2.25 12c.54 1.65 1.56 3.15 2.88 4.41M9.88 14.12A3 3 0 0012 15c1.657 0 3-1.343 3-3 0-.42-.084-.82-.236-1.184" />
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
