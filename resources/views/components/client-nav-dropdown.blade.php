<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
        <div>Find Legal Help</div>
        
        <div class="ml-1">
            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>
    </button>
    
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 left-0 mt-2 w-screen max-w-6xl shadow-lg origin-top-right rounded-md">
        <div class="rounded-md bg-white shadow-xs p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <!-- Browse By Legal Services -->
                <div>
                    <h3 class="text-lg font-bold mb-3 text-gray-900">Browse By Legal Services</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @php
                            $services = \App\Models\LegalService::active()->orderBy('name')->get();
                        @endphp
                        
                        @foreach($services as $service)
                            <a href="{{ route('search.lawyers', ['service' => $service->id]) }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded-md">
                                {{ $service->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                
                <!-- Browse By City -->
                <div>
                    <h3 class="text-lg font-bold mb-3 text-gray-900">Browse By City</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @php
                            $lawyerCities = \App\Models\LawyerProfile::select('city')
                                ->distinct()
                                ->whereHas('user', function($q) {
                                    $q->where('profile_completed', true)
                                        ->where('status', 'approved');
                                })
                                ->whereNotNull('city')
                                ->pluck('city')
                                ->toArray();
                                
                            $lawFirmCities = \App\Models\LawFirmProfile::select('city')
                                ->distinct()
                                ->whereHas('user', function($q) {
                                    $q->where('profile_completed', true)
                                        ->where('status', 'approved');
                                })
                                ->whereNotNull('city')
                                ->pluck('city')
                                ->toArray();
                            
                            $allCities = array_unique(array_merge($lawyerCities, $lawFirmCities));
                            sort($allCities);
                        @endphp
                        
                        @foreach($allCities as $city)
                            <a href="{{ route('search.lawyers', ['city' => $city]) }}" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded-md">
                                {{ $city }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 