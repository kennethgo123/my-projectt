<!-- Law Firm Card -->
<div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow duration-300">
    <div class="p-6">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                @if($lawFirm->photo_path)
                    <img class="h-20 w-20 rounded-full object-cover border-2 border-emerald-100" 
                         src="{{ Storage::url($lawFirm->photo_path) }}" 
                         alt="{{ $lawFirm->firm_name }}">
                @elseif($lawFirm->user && $lawFirm->user->profile_photo_path)
                    <img class="h-20 w-20 rounded-full object-cover border-2 border-emerald-100" 
                         src="{{ Storage::url($lawFirm->user->profile_photo_path) }}" 
                         alt="{{ $lawFirm->firm_name }}">
                @else
                    <div class="h-20 w-20 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border-2 border-emerald-100">
                        <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M5 9h14M5 13h14M5 17h14" 
                                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                @endif
            </div>
            
            <div class="flex-1 min-w-0 relative">
                <!-- Book Consultation button at top right -->
                <div class="absolute top-0 right-0">
                    <a href="{{ route('client.book-consultation', ['lawyer_id' => $lawFirm->user->id]) }}" 
                       class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-full text-white bg-green-500 hover:bg-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        Book Consultation
                    </a>
                </div>
                <div class="mb-2">
                    <div class="flex items-center">
                        <h3 class="text-lg font-semibold text-gray-900 font-raleway">
                            {{ $lawFirm->firm_name }} 
                            <span class="text-sm font-normal text-gray-500">(Law Firm)</span>
                        </h3>
                            
                        <!-- Subscription badge for law firms -->
                        {!! renderSubscriptionBadge($lawFirm->user) !!}
                    </div>
                    <div class="flex items-center mt-1 text-sm text-gray-500">
                        @php 
                            $avg_lf = $lawFirm->user->law_firm_average_rating ?? 0; 
                            $fs_lf = floor($avg_lf); 
                            $hs_lf = $avg_lf - $fs_lf >= 0.5; 
                            $es_lf = 5 - $fs_lf - ($hs_lf ? 1:0); 
                        @endphp
                        @for ($i = 0; $i < $fs_lf; $i++)
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                        @if ($hs_lf)
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <defs>
                                    <linearGradient id="halfStarLawFirmList{{$lawFirm->user->id}}" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="50%" stop-color="currentColor"></stop>
                                        <stop offset="50%" stop-color="#D1D5DB"></stop>
                                    </linearGradient>
                                </defs>
                                <path fill="url(#halfStarLawFirmList{{$lawFirm->user->id}})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endif
                        @for ($i = 0; $i < $es_lf; $i++)
                            <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                        <span class="ml-1.5 mr-2">
                            {{ $lawFirm->user->law_firm_average_rating > 0 ? number_format($lawFirm->user->law_firm_average_rating, 1) : 'New' }}
                            @if($lawFirm->user->law_firm_rating_count > 0)
                                ({{ $lawFirm->user->law_firm_rating_count }})
                            @endif
                        </span>
                        <span class="text-gray-400">•</span>
                        <span class="ml-2">{{ $lawFirm->city }}</span>
                        
                        <!-- Office Address - Only shown when show_office_address is true and coordinates exist -->
                        @if($lawFirm->show_office_address && $lawFirm->office_address && $lawFirm->lat && $lawFirm->lng)
                            <div class="mt-1 text-sm text-gray-600">
                                <a href="https://www.openstreetmap.org/?mlat={{ $lawFirm->lat }}&mlon={{ $lawFirm->lng }}#map=16/{{ $lawFirm->lat }}/{{ $lawFirm->lng }}" 
                                   target="_blank" 
                                   class="flex items-center text-emerald-600 hover:text-emerald-800 hover:underline"
                                   title="View on OpenStreetMap">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ Str::limit($lawFirm->office_address, 60) }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                
                <p class="mt-3 text-sm text-gray-700 font-open-sans leading-relaxed line-clamp-3 h-[4.5rem]">
                    @if($lawFirm->about)
                        {{ Str::limit($lawFirm->about, 200) }}
                    @elseif($lawFirm->description)
                        {{ Str::limit($lawFirm->description, 200) }}
                    @else
                        No detailed description available.
                    @endif
                </p>
                
                @if($lawFirm->services->count() > 0)
                    <div class="mt-4 flex flex-wrap gap-2 min-h-[2rem]">
                        @foreach($lawFirm->services->take(5) as $service)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                {{ $service->name }}
                            </span>
                        @endforeach
                        @if($lawFirm->services->count() > 5)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                +{{ $lawFirm->services->count() - 5 }} more
                            </span>
                        @endif
                    </div>
                @else
                    <div class="mt-4 min-h-[2rem]"></div>
                @endif
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2">
                        <span class="text-base font-semibold text-gray-900">₱{{ number_format($lawFirm->min_budget) }} - ₱{{ number_format($lawFirm->max_budget) }}</span>
                        <span class="text-sm text-gray-500">(est. fee)</span>
                    </div>
                    <div class="flex items-center space-x-2 mb-2">
                        @if($lawFirm->offers_online_consultation)
                            <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 font-medium border border-gray-200">
                                <svg class="h-3.5 w-3.5 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                </svg>
                                Online
                            </span>
                        @endif
                        @if($lawFirm->offers_inhouse_consultation)
                            <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 font-medium border border-gray-200">
                                <svg class="h-3.5 w-3.5 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                In-House
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Message and View Profile buttons at bottom right -->
                <div class="flex space-x-2 mt-2 sm:mt-0">
                    <a href="{{ route('messages.chat', ['userId' => $lawFirm->user->id]) }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-green-500 hover:bg-green-400 transition-colors duration-200 sm:w-auto w-1/2">
                        Message
                    </a>
                    <a href="javascript:void(0);" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-full text-white bg-green-500 hover:bg-green-400 transition-colors duration-200 sm:w-auto w-1/2"
                       @click.prevent="(() => {
                           const lawFirmData = {{ json_encode($lawFirm->load(['user.receivedLawFirmRatings', 'user.activeSubscription.plan', 'lawyers', 'services'])) }};
                           console.log('Showing law firm detail modal with data:', lawFirmData);
                           $dispatch('show-lawyer-detail', {
                               lawyer: lawFirmData, 
                               type: 'lawFirm'
                           });
                       })()">
                        View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div> 