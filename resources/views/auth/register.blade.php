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
                                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                                </div>

                                <div class="mt-4">
                                    <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
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
