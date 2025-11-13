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
                            <a href="#about-us" class="text-white hover:text-green-300 transition">About us</a>
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
                            <a href="#about-us" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">About us</a>
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

    <!-- About Us Section -->
    <div id="about-us" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    About us
                </h2>
                <div class="w-24 h-1 bg-yellow-500 mx-auto my-6"></div>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <p class="text-lg text-gray-700 leading-relaxed text-center">
                    At Lexcav, we believe that access to legal assistance should be simple, transparent, and within everyone's reach. Our platform bridges the gap between clients and qualified legal professionals, making it easier to find trusted lawyers who can address your specific needs. Whether you're seeking legal advice, consultation, or representation, Lexcav provides a secure and convenient space to connect with experts who care. Guided by our mission of making legal services accessible, we're redefining how people access justice—one connection at a time.
                </p>
            </div>
        </div>
    </div>

</div>
</div>