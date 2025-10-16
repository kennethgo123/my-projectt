<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? config('app.name') }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            [x-cloak] { display: none !important; }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            @auth
                                @if(auth()->user()->isLawyer())
                                    <a href="{{ route('lawyer.welcome') }}" class="flex items-center">
                                        <x-application-logo />
                                    </a>
                                @else
                                    <a href="{{ route('home') }}" class="flex items-center">
                                        <x-application-logo />
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('home') }}" class="flex items-center">
                                    <x-application-logo />
                                </a>
                            @endauth
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-8">
                            @auth
                                @if(auth()->user()->isLawyer())
                                    <a href="{{ route('lawyer.welcome') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('lawyer.welcome') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
                                        Home
                                    </a>
                                    <a href="{{ route('profile.optimize') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                        Optimize Your Profile
                                    </a>
                                    <a href="{{ route('lawyer.consultations') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                        Manage Consultations
                                    </a>
                                    <a href="{{ route('lawyer.cases') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                        Manage Cases
                                    </a>
                                    <a href="{{ route('lawyer.invoices') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('lawyer.invoices') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
                                        Invoices
                                    </a>
                                    <a href="{{ route('messages') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                        Messages
                                    </a>
                                    {{-- <x-find-legal-help-dropdown />
                                    <a href="{{ route('services') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                        Services
                                    </a> --}}
                                @elseif(auth()->user()->isClient())
                                    <a href="{{ route('client.welcome') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.welcome') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
                                        Home
                                    </a>
                                    <x-find-legal-help-dropdown />
                                    <a href="{{ route('client.cases') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.cases*') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
                                        Manage My Cases
                                    </a>
                                    <a href="{{ route('client.consultations') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.consultations') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
                                        My Consultations
                                    </a>
                                    <a href="{{ route('messages') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('messages*') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
                                        Messages
                                    </a>
                                    <a href="{{ route('client.invoices') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out {{ request()->routeIs('client.invoices') ? 'border-indigo-500 text-gray-900' : 'border-transparent' }}">
                                        My Invoices
                                    </a>
                                @else
                                    <x-find-legal-help-dropdown />
                                    <a href="{{ route('services') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                        Services
                                    </a>
                                    @if(auth()->user()->isLawFirm())
                                        <a href="{{ route('dashboard') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                            Dashboard
                                        </a>
                                    @endif
                                @endif
                            @else
                                <x-find-legal-help-dropdown />
                                <a href="{{ route('services') }}" class="inline-flex items-center h-16 px-1 border-b-2 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                                    Services
                                </a>
                            @endauth
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        @auth
                            @if(auth()->user()->isClient())
                                <div class="w-64 mr-4">
                                    @livewire('navbar-search')
                                </div>
                            @endif
                            <div class="ml-3 flex items-center space-x-4">
                                <!-- Notification Dropdown -->
                                @if(auth()->user()->isClient() || auth()->user()->isLawyer())
                                    <livewire:components.notification-dropdown />
                                @endif

                                <!-- User Menu -->
                                <div class="relative">
                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                @if(auth()->user()->isLawyer())
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->lawyerProfile->first_name ?? '' }} {{ auth()->user()->lawyerProfile->last_name ?? '' }}">
                                                @else
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">
                                                @endif
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            <x-dropdown-link href="{{ route('profile.show') }}">
                                                {{ __('Profile') }}
                                            </x-dropdown-link>

                                            @if(auth()->user()->isAdmin())
                                                <x-dropdown-link href="{{ route('admin.dashboard') }}">
                                                    {{ __('Admin Panel') }}
                                                </x-dropdown-link>
                                            @endif

                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <x-dropdown-link href="{{ route('logout') }}"
                                                        onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                                    {{ __('Log Out') }}
                                                </x-dropdown-link>
                                            </form>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">Log in</a>
                            <a href="{{ route('register') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Register</a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Mobile navigation menu -->
            <div class="block sm:hidden">
                <div class="p-4 space-y-3">
                    @auth
                        @if(auth()->user()->isLawyer())
                            <a href="{{ route('lawyer.welcome') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('lawyer.welcome') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Home
                            </a>
                            <a href="{{ route('lawyer.consultations') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('lawyer.consultations') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Manage Consultations
                            </a>
                            <a href="{{ route('lawyer.cases') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('lawyer.cases') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Manage Cases
                            </a>
                            <a href="{{ route('lawyer.invoices') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('lawyer.invoices') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Invoices
                            </a>
                            <a href="{{ route('messages') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('messages') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Messages
                            </a>
                            <a href="{{ route('profile.optimize') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('profile.optimize') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Optimize Profile
                            </a>
                        @elseif(auth()->user()->isClient())
                            <a href="{{ route('client.welcome') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('client.welcome') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Home
                            </a>
                            <div class="px-3 py-2">
                                <div class="font-medium text-base text-gray-800 mb-1">Find Legal Help</div>
                                <div class="ml-1 mt-1">
                                    <a href="{{ route('client.nearby-lawyers') }}" class="block py-1 text-sm text-gray-600 hover:text-gray-900">
                                        Search by Location
                                    </a>
                                    <a href="{{ route('client.nearby-lawyers') }}" class="block py-1 text-sm text-gray-600 hover:text-gray-900">
                                        Browse by Service
                                    </a>
                                </div>
                            </div>
                            <a href="{{ route('client.cases') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('client.cases*') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Manage My Cases
                            </a>
                            <a href="{{ route('client.consultations') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('client.consultations') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                My Consultations
                            </a>
                            <a href="{{ route('messages') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('messages*') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                Messages
                            </a>
                            <a href="{{ route('client.invoices') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 {{ request()->routeIs('client.invoices') ? 'bg-gray-100 border-l-4 border-indigo-500 font-semibold' : '' }}">
                                My Invoices
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        @if(isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <x-page-header :header="$header" />
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @if (session()->has('message'))
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="p-4 mb-4 text-base bg-white border-l-4 border-green-500 shadow-sm rounded" role="alert">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-gray-900 font-medium">{{ session('message') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center space-x-6 md:order-2">
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Terms</span>
                        Terms of Service
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Privacy</span>
                        Privacy Policy
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Contact</span>
                        Contact Us
                    </a>
                </div>
                <div class="mt-8 md:mt-0 md:order-1">
                    <p class="text-center text-base text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>

        @livewireScripts
        @stack('scripts')
        
        <script>
            // Listen for notification events and update the indicator
            document.addEventListener('livewire:initialized', function () {
                // Check for unread notifications on page load
                updateNotificationIndicator();
                
                // Listen for Livewire event when a new notification is received
                Livewire.on('notification-received', function () {
                    updateNotificationIndicator();
                });
                
                function updateNotificationIndicator() {
                    // If we're on a client page
                    if (document.getElementById('notification-indicator')) {
                        const unreadCount = document.querySelector('.notification-count')?.textContent;
                        const indicator = document.getElementById('notification-indicator');
                        
                        if (unreadCount && parseInt(unreadCount) > 0) {
                            indicator.classList.remove('hidden');
                        } else {
                            indicator.classList.add('hidden');
                        }
                    }
                    
                    // If we're on a lawyer page
                    if (document.getElementById('lawyer-notification-indicator')) {
                        const unreadCount = document.querySelector('.notification-count')?.textContent;
                        const indicator = document.getElementById('lawyer-notification-indicator');
                        
                        if (unreadCount && parseInt(unreadCount) > 0) {
                            indicator.classList.remove('hidden');
                        } else {
                            indicator.classList.add('hidden');
                        }
                    }
                }
            });
        </script>
    </body>
</html> 