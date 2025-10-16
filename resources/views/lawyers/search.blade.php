<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Search Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Lawyers Section -->
                @if($lawyers->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Lawyers</h3>
                        <div class="space-y-4">
                            @foreach($lawyers as $lawyer)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-lg font-medium">
                                                {{ $lawyer->getFullNameAttribute() }}
                                            </h4>
                                            <p class="text-sm text-gray-600">{{ $lawyer->city }}</p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Budget Range: ₱{{ number_format($lawyer->min_budget) }} - ₱{{ number_format($lawyer->max_budget) }}
                                            </p>
                                            
                                            @if($lawyer->services->count() > 0)
                                                <div class="mt-2">
                                                    <span class="text-sm font-medium text-gray-700">Services:</span>
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        @foreach($lawyer->services as $service)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                                {{ $service->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <a href="{{ route('lawyers.show', $lawyer->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            View Profile
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $lawyers->links() }}
                        </div>
                    </div>
                @endif

                <!-- Law Firms Section -->
                @if($lawFirms->count() > 0)
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Law Firms</h3>
                        <div class="space-y-4">
                            @foreach($lawFirms as $firm)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-lg font-medium">
                                                {{ $firm->firm_name }}
                                            </h4>
                                            <p class="text-sm text-gray-600">{{ $firm->city }}</p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Budget Range: ₱{{ number_format($firm->min_budget) }} - ₱{{ number_format($firm->max_budget) }}
                                            </p>
                                            
                                            @if($firm->services->count() > 0)
                                                <div class="mt-2">
                                                    <span class="text-sm font-medium text-gray-700">Services:</span>
                                                    <div class="mt-1 flex flex-wrap gap-1">
                                                        @foreach($firm->services as $service)
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                                {{ $service->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <a href="{{ route('law-firms.show', $firm->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                            View Profile
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $lawFirms->links() }}
                        </div>
                    </div>
                @endif

                @if($lawyers->count() === 0 && $lawFirms->count() === 0)
                    <div class="text-center py-8">
                        <p class="text-gray-500">No results found for your search criteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout> 