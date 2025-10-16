<div>
    {{-- Page Header --}}
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
                <a href="{{ route('client.nearby-lawyers') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    <svg class="flex-shrink-0 -ml-1 mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Back to Lawyers
                </a>
                <h1 class="ml-4 text-3xl font-bold text-gray-900">
                    @if($type === 'lawFirm')
                        {{ $lawyer->firm_name }}
                    @else
                        {{ $lawyer->first_name }} {{ $lawyer->last_name }}
                    @endif
                </h1>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Profile header --}}
        <div class="flex flex-col md:flex-row md:items-start space-y-6 md:space-y-0 md:space-x-6 mb-10 bg-emerald-50 p-6 rounded-2xl border border-emerald-100 shadow-md">
            {{-- Profile photo --}}
            <div class="flex-shrink-0 flex justify-center">
                <div class="h-32 w-32 rounded-full bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center text-emerald-600 border-4 border-emerald-400 overflow-hidden shadow-lg">
                    @if($lawyer->photo_path)
                        <img class="h-full w-full object-cover" src="{{ Storage::url($lawyer->photo_path) }}" alt="Profile photo">
                    @else
                        <svg class="h-16 w-16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a10 10 0 00-10 10h20a10 10 0 00-10-10z" />
                        </svg>
                    @endif
                </div>
            </div>
            
            {{-- Basic info --}}
            <div class="flex-1 space-y-4">
                <div>
                    <div class="flex items-center">
                        <h3 class="text-2xl font-bold font-raleway text-emerald-800 mb-2">
                            @if($type === 'lawFirm')
                                {{ $lawyer->firm_name }}
                            @else
                                {{ $lawyer->first_name }} {{ $lawyer->last_name }}
                            @endif
                        </h3>
                    </div>
                    
                    <div class="flex items-center mt-1 text-sm text-gray-500">
                        {{-- Star Rating Display --}}
                        @if($lawyer->user)
                            <div class="flex items-center">
                                @php
                                    $ratings = $type === 'lawFirm' ? 
                                        $lawyer->user->receivedLawFirmRatings : 
                                        $lawyer->user->receivedRatings;
                                    $averageRating = $ratings->count() > 0 ? $ratings->avg('rating') : 0;
                                    $ratingCount = $ratings->count();
                                @endphp
                                
                                {{-- Full stars --}}
                                @for($i = 1; $i <= floor($averageRating); $i++)
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                                
                                {{-- Half star --}}
                                @if(($averageRating - floor($averageRating)) >= 0.5)
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <defs>
                                            <linearGradient id="halfStarProfile" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="50%" stop-color="currentColor"></stop>
                                                <stop offset="50%" stop-color="#D1D5DB"></stop>
                                            </linearGradient>
                                        </defs>
                                        <path fill="url(#halfStarProfile)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endif
                                
                                {{-- Empty stars --}}
                                @for($i = ceil($averageRating) + 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                                
                                {{-- Rating value --}}
                                <span class="ml-1.5 mr-2">
                                    @if($averageRating > 0)
                                        {{ number_format($averageRating, 1) }}
                                    @else
                                        New
                                    @endif
                                    @if($ratingCount > 0)
                                        ({{ $ratingCount }})
                                    @endif
                                </span>
                            </div>
                        @endif
                        <span class="text-gray-400 ml-2">•</span>
                        <span class="ml-2">{{ $lawyer->city }}</span>
                    </div>
                    
                    <div class="mt-3 flex items-center text-sm text-gray-700">
                        @if($type !== 'lawFirm')
                            @if(!$lawyer->law_firm_id)
                                <span class="font-medium">Solo Practitioner</span>
                            @elseif($lawyer->lawFirm)
                                <span>Associated with <span class="font-medium">{{ $lawyer->lawFirm->firm_name }}</span></span>
                            @endif
                        @endif
                    </div>

                    {{-- Office Address Section --}}
                    @if($lawyer->office_address && $lawyer->lat && $lawyer->lng)
                        <div class="mt-3 flex items-center text-sm text-gray-700">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <a href="https://www.openstreetmap.org/?mlat={{ $lawyer->lat }}&mlon={{ $lawyer->lng }}#map=16/{{ $lawyer->lat }}/{{ $lawyer->lng }}" 
                               target="_blank" 
                               class="text-emerald-600 hover:text-emerald-800 hover:underline transition-colors"
                               title="View on OpenStreetMap">
                                {{ $lawyer->office_address }}
                                <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
                
                @auth
                    @if(auth()->user()->role->name === 'client')
                        <div class="flex flex-wrap gap-4">
                            {{-- Book Consultation button --}}
                            <a href="{{ route('client.book-consultation', ['lawyer_id' => $lawyer->user->id]) }}" 
                               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-semibold rounded-lg shadow-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-raleway transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                </svg>
                                Book a Consultation
                            </a>
                            <a href="{{ route('messages.chat', ['userId' => $lawyer->user->id]) }}"
                               class="inline-flex items-center px-6 py-3 border border-emerald-600 text-base font-semibold rounded-lg shadow-md text-emerald-700 bg-white hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-raleway transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                </svg>
                                Message
                            </a>
                            {{-- Report This Lawyer button --}}
                            <button wire:click="openReportModal"
                                    class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg shadow-sm text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 font-raleway transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
                                </svg>
                                @if($type === 'lawFirm')
                                    Report This Law Firm
                                @else
                                    Report This Lawyer
                                @endif
                            </button>
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        {{-- Detailed sections --}}
        <div class="space-y-8">
            {{-- Pricing Description --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    PRICING INFORMATION
                </h4>
                <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed">
                    @if($type === 'lawFirm')
                        {!! $lawyer->pricing_description ?: 'Pricing information not available.' !!}
                    @else
                        @if($lawyer->law_firm_id && $lawyer->lawFirm)
                            {{-- Show law firm's pricing description for associated lawyers --}}
                            {!! $lawyer->lawFirm->pricing_description ?: 'Pricing information not available.' !!}
                        @else
                            {{-- Show lawyer's own pricing description for independent lawyers --}}
                            {!! $lawyer->pricing_description ?: 'Pricing information not available.' !!}
                        @endif
                    @endif
                    
                    <div class="mt-4 text-sm text-gray-600">
                        @if($type !== 'lawFirm' && $lawyer->law_firm_id && $lawyer->lawFirm)
                            {{-- Show law firm's budget range for associated lawyers --}}
                            @if($lawyer->lawFirm->min_budget && $lawyer->lawFirm->max_budget)
                                <p>Professional Fee Range: ₱{{ number_format($lawyer->lawFirm->min_budget) }} - ₱{{ number_format($lawyer->lawFirm->max_budget) }}</p>
                            @endif
                        @elseif((!$lawyer->law_firm_id || $type === 'lawFirm') && $lawyer->min_budget && $lawyer->max_budget)
                            {{-- Show individual budget range for independent lawyers or law firms --}}
                            <p>Professional Fee Range: ₱{{ number_format($lawyer->min_budget) }} - ₱{{ number_format($lawyer->max_budget) }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- About section --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    ABOUT
                </h4>
                <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed">
                    {!! $lawyer->about ?: ($lawyer->description ?? 'No description available.') !!}
                </div>
            </div>
            
            {{-- Education section (only for lawyers, not law firms) --}}
            @if($type !== 'lawFirm')
                <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                    <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                        </svg>
                        EDUCATION
                    </h4>
                    <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed">
                        {!! $lawyer->education ?: 'No education information available.' !!}
                    </div>
                </div>
            @endif
            
            {{-- Professional Experience section --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-3.76 0-7.26-.84-10-2.308z" />
                    </svg>
                    PROFESSIONAL EXPERIENCE
                </h4>
                <div class="prose max-w-none text-gray-700 font-open-sans leading-relaxed">
                    {!! $lawyer->experience ?: 'No professional experience information available.' !!}
                </div>
            </div>
            
            {{-- Services section --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V8z" clip-rule="evenodd" />
                    </svg>
                    SERVICES
                </h4>
                <div class="flex flex-wrap gap-2">
                    @if($lawyer->services && $lawyer->services->count() > 0)
                        @foreach($lawyer->services as $service)
                            <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-800 font-open-sans border border-emerald-200">
                                {{ $service->name }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-gray-600 font-open-sans">No services listed.</p>
                    @endif
                </div>
            </div>
            
            {{-- Client Reviews Section --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-emerald-200 hover:shadow-md transition-all duration-300">
                <h4 class="text-lg font-semibold text-emerald-800 mb-4 font-raleway flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    CLIENT REVIEWS
                </h4>
                
                {{-- Individual Reviews --}}
                <div>
                    @if($lawyer->user)
                        @php
                            $reviews = $type === 'lawFirm' ? 
                                $lawyer->user->receivedLawFirmRatings()->where('is_visible', true)->with('client.clientProfile')->latest()->get() : 
                                $lawyer->user->receivedRatings()->where('is_visible', true)->with('client.clientProfile')->latest()->get();
                        @endphp
                        
                        @if($reviews->count() > 0)
                            @foreach($reviews as $review)
                                <div class="border-b border-gray-200 py-4 last:border-b-0">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                                <span class="text-emerald-600 font-medium text-sm">
                                                    {{ substr($review->client->clientProfile->first_name ?? $review->client->name, 0, 1) }}{{ substr($review->client->clientProfile->last_name ?? '', 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if($review->client->clientProfile)
                                                        {{ $review->client->clientProfile->first_name }} {{ $review->client->clientProfile->last_name }}
                                                    @else
                                                        {{ $review->client->name }}
                                                    @endif
                                                </p>
                                                <div class="flex items-center">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="text-xs text-gray-500">{{ $review->created_at->format('M d, Y') }}</span>
                                            </div>
                                            @if($review->comment)
                                                <p class="text-sm text-gray-700">{{ $review->comment }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-600 font-open-sans">No reviews yet.</p>
                        @endif
                    @else
                        <p class="text-gray-600 font-open-sans">No reviews available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Report Modal --}}
    @if($showReportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeReportModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit.prevent="submitReport">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        @if($type === 'lawFirm')
                                            Report {{ $lawyer->firm_name }}
                                        @else
                                            Report {{ $lawyer->first_name }} {{ $lawyer->last_name }}
                                        @endif
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        {{-- Basic Information Section --}}
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Your Information</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="reporterName" class="block text-sm font-medium text-gray-700">Your Name *</label>
                                                    <input type="text" id="reporterName" wire:model="reporterName" 
                                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                                    @error('reporterName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label for="reporterEmail" class="block text-sm font-medium text-gray-700">Your Email *</label>
                                                    <input type="email" id="reporterEmail" wire:model="reporterEmail" 
                                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                                    @error('reporterEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label for="reporterPhone" class="block text-sm font-medium text-gray-700">Your Phone</label>
                                                    <input type="text" id="reporterPhone" wire:model="reporterPhone" 
                                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                                    @error('reporterPhone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                <div>
                                                    <label for="serviceDate" class="block text-sm font-medium text-gray-700">Date of Service/Interaction</label>
                                                    <input type="date" id="serviceDate" wire:model="serviceDate" 
                                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                                    @error('serviceDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <label for="legalMatterType" class="block text-sm font-medium text-gray-700">Type of Legal Matter</label>
                                                <input type="text" id="legalMatterType" wire:model="legalMatterType" 
                                                       placeholder="e.g., Contract Review, Criminal Defense, Family Law, etc."
                                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                                @error('legalMatterType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        {{-- Incident Details Section --}}
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Incident Details</h4>
                                            <div class="space-y-4">
                                                <div>
                                                    <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                                    <select id="category" wire:model="category" 
                                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                                        <option value="">Select a category</option>
                                                        <option value="professional_misconduct">Professional Misconduct</option>
                                                        <option value="billing_disputes">Billing Disputes</option>
                                                        <option value="communication_issues">Communication Issues</option>
                                                        <option value="ethical_violations">Ethical Violations</option>
                                                        <option value="competency_concerns">Competency Concerns</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div>
                                                    <label for="description" class="block text-sm font-medium text-gray-700">Detailed Description *</label>
                                                    <textarea id="description" wire:model="description" rows="4" 
                                                              placeholder="Please provide a detailed description of the incident (minimum 50 characters)"
                                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"></textarea>
                                                    <p class="mt-1 text-xs text-gray-500">{{ strlen($description) }}/2000 characters (minimum 50 required)</p>
                                                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div>
                                                    <label for="timelineOfEvents" class="block text-sm font-medium text-gray-700">Timeline of Events</label>
                                                    <textarea id="timelineOfEvents" wire:model="timelineOfEvents" rows="3" 
                                                              placeholder="Please provide a chronological timeline of events (optional)"
                                                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"></textarea>
                                                    @error('timelineOfEvents') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div>
                                                    <label for="supportingDocuments" class="block text-sm font-medium text-gray-700">Supporting Documents</label>
                                                    <input type="file" id="supportingDocuments" wire:model="supportingDocuments" multiple 
                                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                                                    <p class="mt-1 text-xs text-gray-500">Upload contracts, emails, receipts, or other relevant documents (PDF, DOC, DOCX, JPG, PNG - max 10MB each)</p>
                                                    @error('supportingDocuments.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                    
                                                    <div wire:loading wire:target="supportingDocuments" class="mt-2">
                                                        <div class="flex items-center">
                                                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                            <span class="text-sm text-gray-600">Uploading files...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    wire:target="submitReport"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                                <span wire:loading.remove wire:target="submitReport">Submit Report</span>
                                <span wire:loading wire:target="submitReport">Submitting...</span>
                            </button>
                            <button type="button" 
                                    wire:click="closeReportModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div> 