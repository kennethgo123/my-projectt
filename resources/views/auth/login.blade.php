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
                                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
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
