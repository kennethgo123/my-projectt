<x-guest-layout>
    <div class="min-h-screen relative">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-center bg-cover" style="background-image: url('/images/bg.png');"></div>
        <!-- Dark overlay -->
        <div class="absolute inset-0 bg-black/55"></div>

        <!-- Centered content -->
        <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-md">
                <div class="bg-white shadow-2xl rounded-xl overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="mb-4 flex items-center justify-center">
                            <x-application-logo class="h-10 w-auto" />
                        </div>
                        <h2 class="text-2xl font-semibold text-gray-900 text-center mb-3">
                            {{ __('Reset Password') }}
                        </h2>
                        <p class="mb-6 text-sm text-gray-600 text-center">
                            {{ __('Create a new password that is at least 12 characters with an uppercase letter, number, and special character.') }}
                        </p>

                        <x-validation-errors class="mb-4" />

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div>
                                <x-label for="email" value="{{ __('Email') }}" />
                                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                            </div>

                            <div class="mt-4">
                                <x-label for="password" value="{{ __('New Password') }}" />
                                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            </div>

                            <div class="mt-4">
                                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                            </div>

                            <div class="mt-6">
                                <x-button class="w-full justify-center">
                                    {{ __('Reset Password') }}
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
