
<div>
<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Session Messages -->
                @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Lawyer Profile Header -->
                <div class="bg-gradient-to-r from-blue-800 to-indigo-900 px-6 py-8 sm:px-10">
                    <div class="flex flex-col md:flex-row items-center md:items-start">
                        <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                            @if($lawyer->lawyerProfile && $lawyer->lawyerProfile->photo_path)
                                <img class="h-24 w-24 object-cover rounded-full border-4 border-white shadow-md" 
                                     src="{{ Storage::url($lawyer->lawyerProfile->photo_path) }}" 
                                     alt="{{ $lawyerName }}">
                            @elseif($lawyer->profile_photo_path)
                                <img class="h-24 w-24 object-cover rounded-full border-4 border-white shadow-md" 
                                     src="{{ Storage::url($lawyer->profile_photo_path) }}" 
                                     alt="{{ $lawyerName }}">
                            @else
                                <div class="h-24 w-24 rounded-full bg-blue-100 border-4 border-white shadow-md flex items-center justify-center">
                                    <svg class="h-12 w-12 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 14.25c3.73 0 6.75-3.02 6.75-6.75S15.73.75 12 .75 5.25 3.77 5.25 7.5 8.27 14.25 12 14.25zm0 1.5C7.44 15.75 3.75 19.44 3.75 24h16.5c0-4.56-3.69-8.25-8.25-8.25z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="text-center md:text-left">
                            <h2 class="text-2xl font-bold text-yellow-200 font-raleway">{{ $lawyerName }}</h2>
                            @if($lawyer->lawyerProfile)
                                <p class="mt-1 text-yellow-200 font-medium font-open-sans">
                                    {{ $lawyer->lawyerProfile->practice_areas ?? 'Legal Professional' }}
                                </p>
                                @if($lawyer->lawyerProfile->city && $lawyer->lawyerProfile->state)
                                    <p class="mt-1 text-blue-200 font-open-sans">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $lawyer->lawyerProfile->city }}, {{ $lawyer->lawyerProfile->state }}
                                    </p>
                                @endif
                            @endif
                            <div class="mt-3 flex flex-wrap justify-center md:justify-start gap-2">
                            @if($lawyer->lawyerProfile?->offers_online_consultation || $lawyer->lawFirmProfile?->offers_online_consultation)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    Online Consultation
                                </span>
                            @endif
                            @if($lawyer->lawyerProfile?->offers_inhouse_consultation || $lawyer->lawFirmProfile?->offers_inhouse_consultation)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    In-House Consultation
                                </span>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Consultation Booking Form -->
                <div class="p-6 sm:p-10">
                    <h3 class="text-xl font-semibold text-gray-900 mb-6 font-raleway">Book a Consultation</h3>

                <form wire:submit.prevent="submitConsultation" class="space-y-6">
                    <!-- Consultation Type -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3 font-raleway">Consultation Type</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @if(($lawyer->lawyerProfile && $lawyer->lawyerProfile->offers_online_consultation) || 
                                ($lawyer->lawFirmProfile && $lawyer->lawFirmProfile->offers_online_consultation) ||
                                ($lawyer->isLawyer() && $lawyer->firm_id && 
                                    \App\Models\LawFirmLawyer::where('user_id', $lawyer->id)->where('offers_online_consultation', true)->exists()))
                                    <label class="relative p-4 bg-white border rounded-lg shadow-sm cursor-pointer hover:border-indigo-500 flex 
                                           {{ $consultation_type === 'Online Consultation' ? 'ring-2 ring-indigo-500' : '' }}">
                                        <input type="radio" name="consultation_type" wire:model.live="consultation_type" value="Online Consultation" class="sr-only">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center h-10 w-10 bg-blue-100 rounded-full mr-3">
                                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900 font-raleway">Online Consultation</span>
                                                <span class="text-xs text-gray-500 mt-1 font-open-sans">Meet virtually via video call</span>
                                            </div>
                                        </div>
                                        @if($consultation_type === "Online Consultation")
                                            <div class="absolute inset-0 border-2 border-indigo-500 rounded-lg pointer-events-none"></div>
                                        @endif
                                    </label>
                            @endif
                                
                            @if(($lawyer->lawyerProfile && $lawyer->lawyerProfile->offers_inhouse_consultation) || 
                                ($lawyer->lawFirmProfile && $lawyer->lawFirmProfile->offers_inhouse_consultation) ||
                                ($lawyer->isLawyer() && $lawyer->firm_id && 
                                    \App\Models\LawFirmLawyer::where('user_id', $lawyer->id)->where('offers_inhouse_consultation', true)->exists()))
                                    <label class="relative p-4 bg-white border rounded-lg shadow-sm cursor-pointer hover:border-indigo-500 flex
                                           {{ $consultation_type === 'In-House Consultation' ? 'ring-2 ring-indigo-500' : '' }}">
                                        <input type="radio" name="consultation_type" wire:model.live="consultation_type" value="In-House Consultation" class="sr-only">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center h-10 w-10 bg-green-100 rounded-full mr-3">
                                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900 font-raleway">In-House Consultation</span>
                                                <span class="text-xs text-gray-500 mt-1 font-open-sans">Meet in person at their office</span>
                                            </div>
                                        </div>
                                        @if($consultation_type === "In-House Consultation")
                                            <div class="absolute inset-0 border-2 border-indigo-500 rounded-lg pointer-events-none"></div>
                                        @endif
                                    </label>
                            @endif
                            </div>
                            @error('consultation_type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Law Firm Lawyer Selection (only shown for law firms) -->
                        @if($isLawFirm)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3 font-raleway">Select Lawyer</h4>
                            <p class="text-sm text-gray-500 mb-4 font-open-sans">
                                Choose a specific lawyer from {{ $lawyerName }} for your consultation or let the firm decide.
                            </p>
                            
                            <!-- Simple radio buttons -->
                            <div class="space-y-3">
                                <!-- Default option - Let the firm decide -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="lawyer-select-default" 
                                            type="radio" 
                                            name="lawyer_selection" 
                                            value="__default__" 
                                            wire:model.live="selectedLawyerId"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="lawyer-select-default" class="font-medium text-gray-700 cursor-pointer font-raleway">Let the firm decide</label>
                                        <p class="text-gray-500 font-open-sans">The firm will assign the most suitable lawyer for your case</p>
                                    </div>
                                </div>
                                
                                <!-- Law Firm as Entity option -->
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="lawyer-select-firm" 
                                            type="radio" 
                                            name="lawyer_selection" 
                                            value="__firm__" 
                                            wire:model.live="selectedLawyerId"
                                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="lawyer-select-firm" class="font-medium text-gray-700 cursor-pointer font-raleway">{{ $lawyerName }}</label>
                                        <p class="text-gray-500 font-open-sans">Assign the firm on your consultation as an entity</p>
                                    </div>
                                </div>
                                
                                <!-- Lawyer Options -->
                                @if(count($firmLawyers) > 0)
                                    @foreach($firmLawyers as $lawyer)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="lawyer-select-{{ $lawyer['id'] }}" 
                                                type="radio" 
                                                name="lawyer_selection" 
                                                value="{{ $lawyer['id'] }}" 
                                                wire:model.live="selectedLawyerId"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                        </div>
                                        <div class="ml-3 text-sm flex items-center">
                                            @if(isset($lawyer['photo']) && $lawyer['photo'])
                                                <img class="h-6 w-6 object-cover rounded-full mr-2" 
                                                        src="{{ Storage::url($lawyer['photo']) }}" 
                                                        alt="{{ $lawyer['name'] }}">
                                            @else
                                                <div class="h-6 w-6 rounded-full bg-indigo-100 flex items-center justify-center mr-2">
                                                    <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            @endif
                                            <label for="lawyer-select-{{ $lawyer['id'] }}" class="font-medium text-gray-700 cursor-pointer font-raleway">{{ $lawyer['name'] }}</label>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                <p class="text-sm text-gray-700 p-4 bg-gray-100 rounded-md">
                                    No lawyers are currently associated with this firm. Your consultation will be handled by the firm directly.
                                </p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Consultation Date Selection -->
                        @if(!empty($availableDays))
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3 font-raleway">Choose Appointment Date & Time</h4>
                            
                            <div class="bg-blue-50 p-4 rounded-md mb-4">
                                <div class="flex items-center space-x-3">
                                    <input id="useAvailability" type="checkbox" wire:model.live="useAvailability" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <label for="useAvailability" class="text-sm font-medium text-blue-800 font-raleway">
                                        View lawyer's available time slots
                                    </label>
                                </div>
                                <p class="mt-1 text-sm text-blue-700 ml-7 font-open-sans">
                                    Select a date from the calendar and choose from available time slots for your consultation.
                                </p>
                            </div>
                            
                            @if($useAvailability)
                                <!-- Calendar Date Selection -->
                                <div class="mb-6">
                                    <h5 class="text-sm font-medium text-gray-800 mb-3 font-raleway">Select Date</h5>
                                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                                        <!-- Calendar Header -->
                                        <div class="flex items-center justify-between mb-4">
                                            <button type="button" wire:click="previousMonth" class="p-2 hover:bg-gray-100 rounded-full">
                                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                                </svg>
                                            </button>
                                            <h6 class="text-lg font-semibold text-gray-900 font-raleway">
                                                {{ \Carbon\Carbon::parse($currentCalendarMonth)->format('F Y') }}
                                            </h6>
                                            <button type="button" wire:click="nextMonth" class="p-2 hover:bg-gray-100 rounded-full">
                                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Days of Week Header -->
                                        <div class="grid grid-cols-7 gap-1 mb-2">
                                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                                <div class="p-2 text-center text-xs font-medium text-gray-500 font-raleway">{{ $day }}</div>
                                            @endforeach
                                        </div>
                                        
                                        <!-- Calendar Days -->
                                        <div class="grid grid-cols-7 gap-1">
                                            @foreach($calendarDays as $day)
                                                @php
                                                    $isToday = $day['date'] === \Carbon\Carbon::today()->format('Y-m-d');
                                                    $isSelected = $day['date'] === $selectedDate;
                                                    $isPast = $day['date'] < \Carbon\Carbon::today()->format('Y-m-d');
                                                    $isOtherMonth = !$day['isCurrentMonth'];
                                                    $hasAvailability = $day['hasAvailability'];
                                                    $canSelect = !$isPast && !$isOtherMonth && $hasAvailability;
                                                @endphp
                                                
                                                <button 
                                                    type="button"
                                                    wire:click="selectDate('{{ $day['date'] }}')"
                                                    @class([
                                                        'p-2 text-sm rounded-md relative transition-colors duration-200',
                                                        'hover:bg-blue-50' => $canSelect,
                                                        'cursor-pointer' => $canSelect,
                                                        'cursor-not-allowed opacity-50' => !$canSelect,
                                                        'text-gray-400' => $isOtherMonth || $isPast,
                                                        'text-gray-900' => !$isOtherMonth && !$isPast,
                                                        'bg-blue-500 text-white' => $isSelected,
                                                        'font-semibold' => $isToday,
                                                        'ring-2 ring-blue-300' => $isToday && !$isSelected,
                                                    ])
                                                    @if(!$canSelect) disabled @endif>
                                                    {{ $day['dayNumber'] }}
                                                    
                                                    @if($hasAvailability && !$isPast && !$isOtherMonth)
                                                        <div class="absolute bottom-1 left-1/2 transform -translate-x-1/2">
                                                            <div class="w-1 h-1 bg-green-400 rounded-full {{ $isSelected ? 'bg-white' : '' }}"></div>
                                                        </div>
                                                    @endif
                                                </button>
                                                    @endforeach
                                        </div>
                                        
                                        <!-- Calendar Legend -->
                                        <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600 font-open-sans">
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                                                <span>Available</span>
                                                </div>
                                            <div class="flex items-center">
                                                <div class="w-2 h-2 bg-gray-300 rounded-full mr-1"></div>
                                                <span>No availability</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($useAvailability && $selectedDate)
                                <!-- Available Time Slots for Selected Date -->
                                <div class="space-y-4 mb-6">
                                    <h5 class="text-sm font-medium text-gray-800 font-raleway">Available Time Slots</h5>

                                    <!-- Show selected date -->
                                    <div class="mb-4 p-3 bg-indigo-50 rounded-lg border border-indigo-200">
                                                    <div class="flex items-center">
                                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                            <span class="text-sm font-medium text-indigo-800 font-raleway">
                                                {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                                                        </span>
                                                    </div>
                                                </div>
                                    
                                    @if(!empty($availableTimeSlots))
                                            
                                            <!-- Time slots grid - cleaner design -->
                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                                @foreach($availableTimeSlots as $index => $slot)
                                                    <label wire:key="time_slot_{{ $index }}" class="relative bg-white border-2 rounded-lg p-3 cursor-pointer transition-all duration-200 hover:border-indigo-300 hover:shadow-md
                                                           {{ $selectedTimeSlot === $slot['datetime'] ? 'border-indigo-500 bg-indigo-50 shadow-md' : 'border-gray-200' }}">
                                                        <input 
                                                            type="radio" 
                                                            id="slot_{{ $index }}" 
                                                            name="timeSlot" 
                                                            value="{{ $slot['datetime'] }}" 
                                                            wire:model.live="selectedTimeSlot"
                                                            class="sr-only">
                                                        <div class="text-center">
                                                            <span class="text-sm font-medium {{ $selectedTimeSlot === $slot['datetime'] ? 'text-indigo-700' : 'text-gray-900' }} font-open-sans">
                                                                {{ $slot['display'] }}
                                                            </span>
                                                        </div>
                                                        @if($selectedTimeSlot === $slot['datetime'])
                                                            <div class="absolute -top-2 -right-2 h-6 w-6 bg-indigo-500 rounded-full flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('selectedTimeSlot') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                    @else
                                        <div class="p-4 bg-yellow-50 rounded-md">
                                            <p class="text-sm text-yellow-700 font-raleway">No available time slots for the selected date. Please select a different date.</p>
                                        </div>
                                    @endif
                                </div>
                            @elseif($useAvailability)
                                <div class="p-4 bg-blue-50 rounded-md">
                                    <p class="text-sm text-blue-700 font-raleway">Please select a date from the calendar above to view available time slots.</p>
                                </div>
                            @endif

                        </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800 font-raleway">
                                            Availability Not Set
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700 font-open-sans">
                                            <p>This lawyer hasn't set up their availability schedule yet. Please contact them directly to schedule your consultation.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    <!-- Description -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3 font-raleway">Describe Your Legal Issue</h4>
                            <p class="text-sm text-gray-500 mb-3 font-open-sans">
                                Please provide details about your legal matter. This will help the lawyer prepare for your consultation.
                            </p>
                            <textarea 
                                id="description" 
                                rows="6" 
                                wire:model="description" 
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md font-open-sans" 
                                placeholder="Please describe your legal issue in detail..."></textarea>
                            @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Document Upload -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-3 font-raleway">Relevant Documents (Complaint, Affidavit, Certificate to File Action, etc.)</h4>
                            <p class="text-sm text-gray-500 mb-3 font-open-sans">
                                Upload any documents that may be helpful for your consultation.
                            </p>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 font-open-sans">
                                        <span>Upload files</span>
                                        <input id="file-upload" type="file" class="sr-only" wire:model="documents" multiple>
                                    </label>
                                    <p class="pl-1 font-open-sans">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500 font-open-sans">
                                    PDF, DOC, DOCX, PNG, JPG up to 10MB
                                </p>
                            </div>
                        </div>
                            @error('documents.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        
                        <!-- File Preview -->
                            @if(count($documents) > 0)
                                <div class="mt-4">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2 font-raleway">Selected Files:</h5>
                                    <div class="space-y-2">
                                        @foreach($documents as $index => $document)
                                            <div class="flex items-center justify-between py-2 px-3 bg-white border rounded-lg shadow-sm">
                                                <div class="flex items-center">
                                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span class="text-sm text-gray-700 font-open-sans truncate max-w-xs">{{ $document->getClientOriginalName() }}</span>
                                                </div>
                                                <button type="button" wire:click="$set('documents.{{ $index }}', null)" class="inline-flex items-center p-1.5 border border-transparent rounded-full text-red-600 hover:bg-red-50 focus:outline-none">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                                    </div>
                            </div>
                        @endif
                    </div>

                    <!-- Submit Button -->
                        <div class="flex items-center justify-between mt-8">
                            <!-- Reservation Payment (₱500) -->
                            <div class="flex items-center space-x-3">
                                <input id="reservationPaid" type="checkbox" wire:model.live="reservationPaid" class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500" disabled {{ $reservationPaid ? 'checked' : '' }}>
                                <label for="reservationPaid" class="text-sm text-gray-700 font-raleway">
                                A deposit of ₱500 is required to reserve this consultation. This amount will be deducted from your total consultation fee. If your lawyer declines or does not proceed with the consultation, it will be refunded to you.
                                </label>
                                @if(!$reservationPaid)
                                    <button type="button" wire:click="openPaymentModal" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Pay Now
                                    </button>
                                @endif
                            </div>

                            <button 
                                type="submit"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway disabled:opacity-50"
                                @disabled(!$reservationPaid)
                            >
                                Submit Consultation Request
                                <svg class="ml-2 -mr-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal (Choose GCash or Card) -->
    <div x-data="{ open: @entangle('showPaymentModal').live }" x-show="open" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false; $wire.closePaymentModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <div class="bg-white px-6 py-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Pay Reservation Fee (₱500)</h3>
                    <p class="mt-1 text-sm text-gray-600">Choose your payment method:</p>
                    <div class="mt-5 grid grid-cols-1 gap-3">
                        <button type="button" wire:click="payWithGCash" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                            Pay with GCash
                        </button>
                        <button type="button" wire:click="payWithCard" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                            Pay with Card
                        </button>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="open = false; $wire.closePaymentModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>