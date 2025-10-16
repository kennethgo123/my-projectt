<div class="relative inline-flex items-center px-1 pt-1">
    <x-navbarrr align="left" width="64" contentClasses="p-4 bg-white">
        <x-slot name="trigger">
            <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                <div>{{ __('Find Legal Help') }}</div>
                <div class="ml-1">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </button>
        </x-slot>
        
        <x-slot name="content">
            <div>
                <h3 class="text-base font-medium text-gray-900 mb-2">Find Legal Help</h3>
                <p class="text-sm text-gray-600 mb-4">Find experienced legal professionals near you.</p>
                
                <div class="space-y-2 mb-3">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-indigo-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                        </svg>
                        <a href="{{ route('client.nearby-lawyers') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Search by Location
                        </a>
                    </div>
                    
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-indigo-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                        </svg>
                        <a href="{{ route('client.nearby-lawyers') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Browse by Service
                        </a>
                    </div>
                </div>
                
                <a href="{{ route('client.nearby-lawyers') }}" class="block px-3 py-2 rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 text-center">
                    View All Lawyers
                </a>
                
                @guest
                <p class="text-xs text-gray-500 mt-3">Login or register to manage cases and message lawyers directly.</p>
                @endguest
            </div>
        </x-slot>
    </x-navbarrr>
</div> 