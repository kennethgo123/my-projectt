<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-center">
            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h2 class="mt-2 text-lg font-medium text-gray-900">Account Deactivated</h2>
        </div>

        <div class="mb-4 text-sm text-gray-600">
            <p>Your account has been deactivated by the administrator. Please see the reason below:</p>
        </div>

        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-gray-900">{{ auth()->user()->deactivation_reason }}</p>
        </div>

        <div class="mb-4 text-sm text-gray-600">
            <p>If you believe this is a mistake or would like to reactivate your account, please contact the administrator.</p>
        </div>

        <div class="flex justify-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 disabled:opacity-25 transition">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </x-authentication-card>
</x-guest-layout> 