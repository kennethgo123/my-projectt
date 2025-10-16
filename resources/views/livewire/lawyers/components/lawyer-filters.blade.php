<!-- Left Sidebar Filters (Column 1) -->
<div class="w-full md:w-1/4 bg-white rounded-2xl p-6 shadow-xl border border-gray-200 h-fit">
    <h2 class="text-xl font-semibold mb-6 font-raleway text-gray-800 border-b-2 border-gray-200 pb-2">Filters</h2>
    
    <div class="space-y-6">
        <div>
            <h3 class="text-sm uppercase font-semibold mb-2 font-raleway text-gray-700 tracking-wider">City</h3>
            <div class="relative">
                <select id="city" wire:model.live="selectedCity" class="appearance-none w-full rounded-lg border-2 border-gray-200 bg-white px-4 py-2.5 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50 font-open-sans text-gray-700">
                @foreach($allCities as $city)
                    <option value="{{ $city }}">{{ $city }}</option>
                @endforeach
            </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-sm uppercase font-semibold mb-2 font-raleway text-gray-700 tracking-wider">Services</h3>
            <div class="relative">
                <select id="service" wire:model.live="selectedService" class="appearance-none w-full rounded-lg border-2 border-gray-200 bg-white px-4 py-2.5 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50 font-open-sans text-gray-700">
                <option value="">All Services</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-sm uppercase font-semibold mb-2 font-raleway text-gray-700 tracking-wider">Price Range</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-medium">₱</span>
                    </div>
                    <input type="number" id="min_budget" wire:model.live="minBudget" placeholder="Min" 
                        class="w-full pl-8 rounded-lg border-2 border-gray-200 py-2.5 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50 font-open-sans text-gray-700">
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-medium">₱</span>
                    </div>
                    <input type="number" id="max_budget" wire:model.live="maxBudget" placeholder="Max" 
                        class="w-full pl-8 rounded-lg border-2 border-gray-200 py-2.5 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50 font-open-sans text-gray-700">
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-sm uppercase font-semibold mb-2 font-raleway text-gray-700 tracking-wider">Minimum Rating</h3>
            <div class="flex items-center space-x-2">
                <select wire:model.live="minRating" class="w-full rounded-lg border-2 border-gray-200 bg-white px-4 py-2.5 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50 font-open-sans text-gray-700">
                    <option value="0">Any Rating</option>
                    <option value="5">★★★★★ (5 stars)</option>
                    <option value="4">★★★★☆ (4+ stars)</option>
                    <option value="3">★★★☆☆ (3+ stars)</option>
                    <option value="2">★★☆☆☆ (2+ stars)</option>
                    <option value="1">★☆☆☆☆ (1+ stars)</option>
                </select>
            </div>
        </div>
        
        <div>
            <h3 class="text-sm uppercase font-semibold mb-3 font-raleway text-gray-700 tracking-wider">Consultation Type</h3>
            <div class="space-y-3">
                <div class="flex items-center bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <input type="checkbox" id="online_consultation" wire:model.live="onlineConsultation" 
                        class="h-5 w-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <label for="online_consultation" class="ml-3 text-sm text-gray-700 font-medium font-open-sans">Online Consultation</label>
                </div>
                <div class="flex items-center bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <input type="checkbox" id="inhouse_consultation" wire:model.live="inhouseConsultation" 
                        class="h-5 w-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <label for="inhouse_consultation" class="ml-3 text-sm text-gray-700 font-medium font-open-sans">In-House Consultation</label>
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="text-sm uppercase font-semibold mb-2 font-raleway text-gray-700 tracking-wider">Language</h3>
            <div class="relative">
                <select wire:model.live="selectedLanguage" class="appearance-none w-full rounded-lg border-2 border-gray-200 bg-white px-4 py-2.5 shadow-sm focus:border-gray-500 focus:ring focus:ring-gray-200 focus:ring-opacity-50 font-open-sans text-gray-700">
                <option value="">All Languages</option>
                <option value="English">English</option>
                <option value="Filipino (Tagalog)">Filipino (Tagalog)</option>
                <option value="Cebuano">Cebuano</option>
                <option value="Ilocano">Ilocano</option>
                <option value="Waray">Waray</option>
                <option value="Kapampangan">Kapampangan</option>
                <option value="Pangasinan">Pangasinan</option>
            </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="pt-4">
            <button wire:click="resetFilters" class="w-full px-6 py-3 bg-white border-2 border-gray-400 text-gray-700 font-medium rounded-lg hover:bg-gray-50 hover:border-gray-500 transition-all duration-200 flex items-center justify-center font-raleway shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset Filters
            </button>
        </div>
    </div>
</div> 