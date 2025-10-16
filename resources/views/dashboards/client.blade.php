<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(!auth()->user()->profile_completed)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Your account information has not yet been completed. Please complete your profile to avail law services.
                            </p>
                            <div class="mt-4">
                                <a href="{{ route('profile.complete') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Complete Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h1 class="text-2xl font-bold mb-6">Find Legal Help</h1>
                
                <div class="mb-8">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Welcome to your client dashboard! Use the search below to find lawyers or law firms, or click "Find Lawyers Near Me" in the navigation bar to discover legal services in your area.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('lawyers.search') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                                <input type="text" name="search" id="search" placeholder="Search by name, service, or location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="service" class="block text-sm font-medium text-gray-700">Service</label>
                                <select name="service" id="service" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">All Services</option>
                                    @foreach(\App\Models\LegalService::active()->get() as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="budget" class="block text-sm font-medium text-gray-700">Budget Range</label>
                                <select name="budget" id="budget" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Any Budget</option>
                                    <option value="0-5000">₱0 - ₱5,000</option>
                                    <option value="5000-10000">₱5,000 - ₱10,000</option>
                                    <option value="10000-20000">₱10,000 - ₱20,000</option>
                                    <option value="20000-50000">₱20,000 - ₱50,000</option>
                                    <option value="50000-1000000">₱50,000+</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                                <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="relevance">Relevance</option>
                                    <option value="budget_low">Budget: Low to High</option>
                                    <option value="budget_high">Budget: High to Low</option>
                                    <option value="rating">Rating</option>
                                    <option value="name">Name</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-900 disabled:opacity-25 transition">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold mb-4">Featured Lawyers</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach(\App\Models\User::whereHas('role', function($q) { $q->where('name', 'lawyer'); })
                                ->where('status', 'approved')
                                ->where('is_featured', true)
                                ->with('lawyerProfile')
                                ->take(3)
                                ->get() as $featuredLawyer)
                            <div class="bg-white border rounded-lg overflow-hidden shadow-md">
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold mb-2">
                                        {{ $featuredLawyer->lawyerProfile->first_name }} {{ $featuredLawyer->lawyerProfile->last_name }}
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-2">
                                        {{ $featuredLawyer->lawyerProfile->city }}
                                    </p>
                                    <p class="text-gray-600 text-sm mb-4">
                                        ₱{{ number_format($featuredLawyer->lawyerProfile->min_budget) }} - ₱{{ number_format($featuredLawyer->lawyerProfile->max_budget) }}
                                    </p>
                                    <a href="{{ route('lawyers.profile', $featuredLawyer) }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 