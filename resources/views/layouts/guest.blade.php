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
    </head>
    <body>
        <!-- Navigation Bar -->
        <div class="absolute top-0 left-0 right-0 z-20">
            <nav class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-white text-2xl font-bold">LexCav</a>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex flex-grow justify-between items-center">
                        <div class="flex space-x-8 ml-8">
                            <a href="{{ route('home') }}" class="text-white hover:text-green-300 transition">Home</a>
                            <a href="{{ route('home') }}#services" class="text-white hover:text-green-300 transition">Services</a>
                            <a href="{{ route('home') }}#featured-lawyers" class="text-white hover:text-green-300 transition">Featured Lawyers</a>
                        </div>
                        <div class="flex space-x-8">
                            <a href="{{ route('register') }}" class="text-white hover:text-green-300 transition {{ request()->routeIs('register') ? 'text-green-300' : '' }}">Register</a>
                            <a href="{{ route('login') }}" class="text-white hover:text-green-300 transition {{ request()->routeIs('login') ? 'text-green-300' : '' }}">Login</a>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden" x-data="{ open: false }">
                        <button @click="open = !open" class="text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <!-- Mobile Menu -->
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                            <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Home</a>
                            <a href="{{ route('home') }}#services" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Services</a>
                            <a href="{{ route('home') }}#featured-lawyers" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Featured Lawyers</a>
                            <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Register</a>
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Login</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
