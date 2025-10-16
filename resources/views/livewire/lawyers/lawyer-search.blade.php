<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Search Filters -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6 lg:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" wire:model.live="search" placeholder="Search lawyers..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select wire:model.live="selectedCategory"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- City Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <select wire:model.live="city"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Cities</option>
                            @foreach($cities as $cityOption)
                                <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Budget Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Professional Fee Range</label>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            <input type="number" wire:model.live="minBudget" placeholder="Min"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="number" wire:model.live="maxBudget" placeholder="Max"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 lg:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($lawyers as $lawyer)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $lawyer->full_name }}</h3>
                                <p class="mt-2 text-gray-600">{{ $lawyer->city }}</p>
                                
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500">Professional Fee Range:</p>
                                    <p class="font-medium text-gray-900">₱{{ number_format($lawyer->min_budget) }} - ₱{{ number_format($lawyer->max_budget) }}</p>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @foreach($lawyer->services as $service)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $service->name }}
                                        </span>
                                    @endforeach
                                </div>

                                <div class="mt-6 flex space-x-2">
                                    <a href="{{ route('lawyers.profile', $lawyer) }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                        View Profile
                                    </a>
                                    
                                    @auth
                                        @if(auth()->user()->isClient())
                                            <a href="{{ route('messages.chat', ['userId' => $lawyer->user_id]) }}"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                                </svg>
                                                Message
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No lawyers found</h3>
                            <p class="mt-1 text-sm text-gray-500">Try adjusting your search filters.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $lawyers->links() }}
                </div>
            </div>
        </div>
    </div>
</div> 