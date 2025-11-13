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
                            {{ __('Forgot Password') }}
                        </h2>
                        <p class="mb-6 text-sm text-gray-600 text-center">
                            {{ __('No problem. Enter your email address and we will send you a password reset link.') }}
                        </p>

                        @session('status')
                            <div class="mb-4 font-medium text-sm text-green-600 text-center">
                                {{ $value }}
                            </div>
                        @endsession

                        <x-validation-errors class="mb-4" />

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div>
                                <x-label for="email" value="{{ __('Email') }}" />
                                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                            </div>

                            <div class="mt-6">
                                <x-button class="w-full justify-center">
                                    {{ __('Email Password Reset Link') }}
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
