<x-app-layout>
    <div class="min-h-screen bg-gray-50 relative z-0 pt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <!-- Welcome Text -->
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    Welcome, {{ auth()->user()->clientProfile->first_name ?? auth()->user()->name }}
                </h1>
                <h2 class="text-3xl font-semibold text-gray-900 mb-8">
                    Let's get you the legal help you need
                </h2>
                <h3 class="text-4xl font-bold text-gray-900 mb-12">
                    We'll help you find lawyers nearby
                </h3>

                <!-- Description -->
                <p class="text-lg text-gray-600 mb-12">
                    Find experienced legal professionals in your area that can help with your specific needs.
                </p>

                <!-- Find Lawyers Button -->
                <a href="{{ route('client.nearby-lawyers') }}" class="bg-green-600 text-white inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    FIND LEGAL HELP
                </a>
            </div>
        </div>
        
        <!-- Featured Professionals Section -->
        <div class="mt-8">
            <livewire:featured-professionals />
        </div>
    </div>
</x-app-layout> 