<div>
    <!-- Hero Section -->
    <div id="home" class="relative h-screen flex items-center justify-center overflow-hidden">
        <img src="/images/bg.png" class="absolute w-full h-full object-fill" alt="Background">
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black opacity-30"></div>

        <!-- Navigation Bar -->
        <div class="absolute top-0 left-0 right-0 z-20">
            <nav class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <a href="/" class="text-white text-2xl font-bold">LexCav</a>
                    
                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex flex-grow justify-between items-center">
                        <div class="flex space-x-8 ml-8">
                            <a href="#home" class="text-white hover:text-green-300 transition">Home</a>
                            <a href="#services" class="text-white hover:text-green-300 transition">Services</a>
                            <a href="#featured-lawyers" class="text-white hover:text-green-300 transition">Featured Lawyers</a>
                        </div>
                        <div class="flex space-x-8">
                            <a href="{{ route('register') }}" class="text-white hover:text-green-300 transition">Register</a>
                            <a href="{{ route('login') }}" class="text-white hover:text-green-300 transition">Login</a>
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
                            <a href="#home" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Home</a>
                            <a href="#services" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Services</a>
                            <a href="#featured-lawyers" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Featured Lawyers</a>
                            <a href="{{ route('register') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Register</a>
                            <a href="{{ route('login') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Login</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Content Container -->
        <div class="relative z-10 text-center px-4">
            <!-- Main Hero Content -->
            <div class="max-w-2xl mx-auto">
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl md:text-6xl mb-6">
                    Making legal services accessible
                </h1>
                <p class="mt-3 max-w-md mx-auto text-base text-white sm:text-lg md:mt-5 md:text-xl md:max-w-3xl mb-8">
                    Connect with qualified legal professionals
                </p>
                <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                    <div class="rounded-md shadow">
                        <a href="{{ route('register') }}" style="background-color: #2e7d32;" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-xl font-medium rounded-md text-white hover:opacity-90 md:py-4 md:text-2xl md:px-10 transition duration-150 ease-in-out">
                            Seek Legal Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div id="services" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Legal Services
                </h2>
                <div class="w-24 h-1 bg-yellow-500 mx-auto my-6"></div>
                <p class="mt-4 text-lg text-gray-500">
                    Explore our comprehensive range of legal services designed to meet your specific needs across various practice areas.
                </p>
            </div>

            <div x-data="{
                activePracticeArea: '{{ $practiceAreas->keys()->first() }}',
                expandedCategories: {}
            }" class="flex flex-col md:flex-row gap-8 bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Practice Areas List -->
                <div class="md:w-1/3 bg-gray-50 p-6">
                    <h3 class="text-xl font-semibold mb-6 text-gray-900">Practice Areas</h3>
                    <ul class="space-y-2">
                        @foreach($practiceAreas as $category => $services)
                            <li>
                                <button 
                                    @click="activePracticeArea = '{{ $category }}'" 
                                    class="w-full text-left py-3 px-4 rounded transition"
                                    :class="activePracticeArea === '{{ $category }}' ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-100'"
                                >
                                    {{ ucfirst($category) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Categories List -->
                <div class="md:w-2/3 p-6">
                    @foreach($practiceAreas as $category => $services)
                        <div x-show="activePracticeArea === '{{ $category }}'">
                            <h3 class="text-2xl font-bold mb-6 text-gray-900">{{ ucfirst($category) }} Cases</h3>
                            <div class="space-y-2">
                                @foreach($services as $service)
                                    @if($service->categories->count() > 0)
                                        @foreach($service->categories as $category)
                                            <div x-data="{ open: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                                <button 
                                                    @click="open = !open" 
                                                    class="w-full flex justify-between items-center p-4 text-left hover:bg-gray-50 transition"
                                                >
                                                    <h4 class="text-lg font-medium text-gray-800">{{ $category->name }}</h4>
                                                    <svg 
                                                        xmlns="http://www.w3.org/2000/svg" 
                                                        class="h-5 w-5 text-gray-500 transition-transform" 
                                                        :class="open ? 'transform rotate-180' : ''"
                                                        fill="none" 
                                                        viewBox="0 0 24 24" 
                                                        stroke="currentColor"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <div 
                                                    x-show="open" 
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0"
                                                    x-transition:enter-end="opacity-100"
                                                    class="p-4 bg-gray-50 border-t border-gray-200"
                                                >
                                                    <p class="text-gray-600">{{ $category->description }}</p>
                                                    <div class="mt-4">
                                                        <a href="{{ route('register') }}" class="inline-flex items-center text-green-600 hover:text-green-800">
                                                            Find a lawyer
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div x-data="{ open: false }" class="border border-gray-200 rounded-lg overflow-hidden">
                                            <button 
                                                @click="open = !open" 
                                                class="w-full flex justify-between items-center p-4 text-left hover:bg-gray-50 transition"
                                            >
                                                <h4 class="text-lg font-medium text-gray-800">{{ $service->name }}</h4>
                                                <svg 
                                                    xmlns="http://www.w3.org/2000/svg" 
                                                    class="h-5 w-5 text-gray-500 transition-transform" 
                                                    :class="open ? 'transform rotate-180' : ''"
                                                    fill="none" 
                                                    viewBox="0 0 24 24" 
                                                    stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div 
                                                x-show="open" 
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                class="p-4 bg-gray-50 border-t border-gray-200"
                                            >
                                                <p class="text-gray-600">{{ $service->description }}</p>
                                                <div class="mt-4">
                                                    <a href="{{ route('register') }}" class="inline-flex items-center text-green-600 hover:text-green-800">
                                                        Find a lawyer
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Lawyers Section -->
    <div id="featured-lawyers" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Featured Legal Professionals
                </h2>
                <p class="mt-4 text-lg text-gray-500">
                    Meet our highly qualified legal experts ready to help with your case
                </p>
            </div>
            
            <!-- Featured Lawyers Placeholder -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @for($i = 0; $i < 4; $i++)
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="h-12 w-12 rounded-full bg-gray-200"></div>
                                <div class="ml-4">
                                    <div class="h-4 w-32 bg-gray-200 rounded mb-2"></div>
                                    <div class="h-3 w-24 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center">
                                    @for($j = 0; $j < 5; $j++)
                                        <svg class="h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <div class="ml-2 h-3 w-16 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            
            <div class="text-center mt-8">
                <p class="text-sm text-gray-500 italic">Featured lawyers will be available soon</p>
                <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-700 hover:bg-green-800">
                    Register as a Lawyer
                </a>
            </div>
        </div>
    </div>

    <!-- Provider Listings -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach($providers as $provider)
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center">
                                <img class="h-12 w-12 rounded-full" src="{{ $provider->profile_photo_url }}" alt="{{ $provider->name }}">
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ $provider->profile->first_name ?? '' }} {{ $provider->profile->last_name ?? '' }}
                                        {{ $provider->profile->company_name ?? '' }}
                                    </h3>
                                    <p class="text-sm text-gray-500">{{ ucfirst($provider->role->name) }}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <p class="text-sm text-gray-500">{{ Str::limit($provider->profile->bio ?? '', 150) }}</p>
                            </div>
                            <div class="mt-4">
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $provider->years_of_experience ?? 0 }} years experience
                                    </div>
                                    <a href="{{ route('providers.show', $provider) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $providers->links() }}
            </div>
        </div>
    </div>
</div> 
</div> 