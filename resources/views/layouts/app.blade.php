<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
        @stack('styles')
        
        {{-- Custom CSS to hide Find Legal Help and Services links for lawyers --}}
        @if(auth()->check() && auth()->user()->role->name === 'lawyer')
        <style>
            /* Hide "Find Legal Help" and "Services" links for lawyers */
            .nav-lawyer-hide {
                display: none !important;
            }
        </style>
        @endif
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100">
            {{-- Load the appropriate navigation menu based on user role --}}
            @if(auth()->check() && auth()->user()->role->name === 'lawyer')
                @include('lawyer.navigation-menu')
            @elseif(auth()->check() && auth()->user()->role->name === 'client')
                <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between h-16">
                            <x-client-navbar />
                            
                            <!-- Right Side Navigation -->
                            <div class="hidden sm:flex sm:items-center sm:ml-auto">
                                <div class="w-64 mr-4">
                                    @livewire('navbar-search')
                                </div>
                                @livewire('components.notification-dropdown')
                                
                                <!-- Settings Dropdown -->
                                <div class="ml-3 relative">
                                    <x-dropdown align="right" width="48">
                                        <x-slot name="trigger">
                                            <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                                <img class="h-8 w-8 rounded-full object-cover" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            <!-- Account Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                {{ __('Manage Account') }}
                                            </div>

                                            <x-dropdown-link href="{{ route('profile.show') }}">
                                                {{ __('Profile') }}
                                            </x-dropdown-link>

                                            <!-- Authentication -->
                                            <form method="POST" action="{{ route('logout') }}" x-data>
                                                @csrf

                                                <x-dropdown-link href="{{ route('logout') }}"
                                                    @click.prevent="$root.submit();">
                                                    {{ __('Log Out') }}
                                                </x-dropdown-link>
                                            </form>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            </div>
                            
                            <!-- Hamburger -->
                            <div class="-mr-2 flex items-center sm:hidden">
                                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Responsive Navigation Menu -->
                        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
                            <x-client-navbar />
                            
                            <!-- Responsive Settings Options -->
                            <div class="pt-4 pb-1 border-t border-gray-200">
                                <div class="flex items-center px-4">
                                    <div class="flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="{{ auth()->user()->profile_photo_url }}" alt="{{ auth()->user()->name }}">
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium text-base text-gray-800">{{ auth()->user()->name }}</div>
                                        <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                                    </div>
                                </div>

                                <div class="mt-3 space-y-1">
                                    <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                                        {{ __('Profile') }}
                                    </x-responsive-nav-link>
                                    
                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}" x-data>
                                        @csrf

                                        <x-responsive-nav-link href="{{ route('logout') }}"
                                            @click.prevent="$root.submit();">
                                            {{ __('Log Out') }}
                                        </x-responsive-nav-link>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            @elseif(auth()->check() && auth()->user()->role->name === 'law_firm')
                <!-- For law firm users, always use the standard navigation as a Blade include -->
                @include('navigation-menu')
            @else
                <!-- For guests, use the standard navigation as a Blade include instead of Livewire -->
                @include('navigation-menu')
            @endif

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{-- Flash Messages --}}
                @if (session()->has('message'))
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('message') }}</span>
                        </div>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

        @stack('modals')

        @livewireScripts
        @stack('scripts')
        
        {{-- JavaScript to hide unwanted links in lawyer navigation --}}
        @if(auth()->check() && auth()->user()->role->name === 'lawyer')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Hide "Find Legal Help" in navbar
                const findLegalHelpLinks = document.querySelectorAll('a, button, div')
                findLegalHelpLinks.forEach(el => {
                    if (el.textContent.includes('Find Legal Help')) {
                        el.closest('.relative, li, div:not(.min-h-screen)').classList.add('nav-lawyer-hide');
                    }
                });
                
                // Hide "Services" in navbar
                const servicesLinks = document.querySelectorAll('a, button, div')
                servicesLinks.forEach(el => {
                    if (el.textContent.trim() === 'Services') {
                        el.closest('.relative, li, div:not(.min-h-screen)').classList.add('nav-lawyer-hide');
                    }
                });
            });
        </script>
        @endif
    </body>
</html>
