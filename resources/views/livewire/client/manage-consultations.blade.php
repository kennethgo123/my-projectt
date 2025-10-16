<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 font-raleway">Your Consultations</h1>
            <p class="mt-1 text-sm text-gray-600 font-open-sans">Track and manage your legal consultations with lawyers.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="mb-6 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 font-raleway">
                    <svg class="mr-1.5 h-2 w-2 text-blue-400" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    {{ $consultations->count() }} Consultation{{ $consultations->count() != 1 ? 's' : '' }}
                </span>
            </div>
            <a href="{{ route('client.nearby-lawyers') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 font-raleway">
                <svg class="mr-2 -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Find New Lawyer
            </a>
        </div>
        
        <!-- Content Area -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            @if($consultations->isEmpty())
                <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="rounded-full bg-indigo-50 p-3 mb-4">
                            <svg class="h-8 w-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 font-raleway">No consultations yet</h3>
                        <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto font-open-sans">Get started by booking a consultation with a lawyer to discuss your legal needs and explore solutions.</p>
                        <div class="mt-6">
                            <a href="{{ route('client.nearby-lawyers') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway">
                                <svg class="mr-2 -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                                Browse Lawyers
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 rounded-t-lg">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <h3 class="text-lg font-medium text-gray-900 font-raleway">Consultation History</h3>
                            <div class="mt-2 md:mt-0 flex items-center">
                                <div class="flex items-center space-x-3 text-sm font-medium text-gray-500">
                                    <div class="flex items-center">
                                        <span class="w-3 h-3 bg-yellow-400 rounded-full mr-1.5"></span>
                                        <span>Pending</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-3 h-3 bg-green-400 rounded-full mr-1.5"></span>
                                        <span>Accepted</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-3 h-3 bg-blue-400 rounded-full mr-1.5"></span>
                                        <span>Completed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-raleway">Lawyer</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-raleway">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-raleway">Requested</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-raleway">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-raleway">Appointment</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-raleway">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($consultations as $consultation)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @php
                                                        $photoPath = null;
                                                        $lawyerName = 'Unknown Lawyer';
                                                        $profilePhotoUrl = $consultation->lawyer->profile_photo_url ?? null;
                                                        $cityName = null;

                                                        if ($consultation->lawyer) {
                                                            if ($consultation->lawyer->lawFirmLawyer) {
                                                                $photoPath = $consultation->lawyer->lawFirmLawyer->photo_path;
                                                                $lawyerName = $consultation->lawyer->lawFirmLawyer->first_name . ' ' . $consultation->lawyer->lawFirmLawyer->last_name;
                                                                $cityName = $consultation->lawyer->lawFirmLawyer->city;
                                                            } elseif ($consultation->lawyer->lawyerProfile) {
                                                                $photoPath = $consultation->lawyer->lawyerProfile->photo_path;
                                                                $lawyerName = $consultation->lawyer->lawyerProfile->first_name . ' ' . $consultation->lawyer->lawyerProfile->last_name;
                                                                $cityName = $consultation->lawyer->lawyerProfile->city;
                                                            } elseif ($consultation->lawyer->lawFirmProfile) {
                                                                $photoPath = $consultation->lawyer->lawFirmProfile->photo_path;
                                                                $lawyerName = $consultation->lawyer->lawFirmProfile->firm_name;
                                                                $cityName = $consultation->lawyer->lawFirmProfile->city;
                                                            } else {
                                                                $lawyerName = $consultation->lawyer->name;
                                                            }
                                                        }
                                                    @endphp

                                                    @if($photoPath)
                                                        <img class="h-10 w-10 rounded-full object-cover shadow-sm border border-gray-200" src="{{ Storage::url($photoPath) }}" alt="{{ $lawyerName }}">
                                                    @elseif($profilePhotoUrl)
                                                        <img class="h-10 w-10 rounded-full object-cover shadow-sm border border-gray-200" src="{{ $profilePhotoUrl }}" alt="{{ $lawyerName }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center shadow-sm border border-gray-200">
                                                            <svg class="h-6 w-6 text-indigo-500" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0 2a10 10 0 00-10 10h20a10 10 0 00-10-10z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 font-raleway">
                                                        {{ $lawyerName }}
                                                    </div>
                                                    @if($cityName)
                                                        <div class="text-xs text-gray-500 font-open-sans flex items-center">
                                                            <svg class="mr-1 h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            {{ $cityName }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-open-sans flex items-center">
                                                @if($consultation->consultation_type == 'online' || $consultation->consultation_type == 'Online Consultation')
                                                    <svg class="mr-1.5 h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                @else
                                                    <svg class="mr-1.5 h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                @endif
                                                {{ ucfirst($consultation->consultation_type) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 font-open-sans">{{ $consultation->created_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-500 font-open-sans flex items-center">
                                                <svg class="mr-1 h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                {{ $consultation->created_at->format('h:i A') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClasses = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                    'accepted' => 'bg-green-100 text-green-800 border-green-200',
                                                    'declined' => 'bg-red-100 text-red-800 border-red-200',
                                                    'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                    'done' => 'bg-gray-100 text-gray-800 border-gray-200'
                                                ];
                                                $statusClass = $statusClasses[$consultation->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                            @endphp
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $statusClass }} font-raleway">
                                                {{ ucfirst($consultation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($consultation->selected_date && ($consultation->status == 'accepted' || $consultation->status == 'completed' || $consultation->status == 'done'))
                                                <div class="text-sm text-gray-900 font-open-sans">{{ \Carbon\Carbon::parse($consultation->selected_date)->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500 font-open-sans flex items-center">
                                                    <svg class="mr-1 h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ \Carbon\Carbon::parse($consultation->selected_date)->format('h:i A') }}
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-500 italic font-open-sans flex items-center">
                                                    <svg class="mr-1 h-3 w-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                    Not scheduled
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-wrap gap-2">
                                                @if($consultation->status === 'accepted')
                                                    {{-- Join Meeting Link --}}
                                                    @if($consultation->meeting_link)
                                                        <a href="{{ $consultation->meeting_link }}" target="_blank" 
                                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition duration-150">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                            </svg>
                                                            Join Meeting
                                                        </a>
                                                    @endif

                                                    {{-- Office Address Button --}}
                                                    @if($consultation->consultation_type === 'in-house' || $consultation->consultation_type === 'inhouse' || $consultation->consultation_type === 'In-House Consultation')
                                                        @php
                                                            $lat = null;
                                                            $lng = null;
                                                            
                                                            if ($consultation->lawyer->lawFirmProfile) {
                                                                $lat = $consultation->lawyer->lawFirmProfile->lat;
                                                                $lng = $consultation->lawyer->lawFirmProfile->lng;
                                                            } elseif ($consultation->lawyer->lawyerProfile) {
                                                                $lat = $consultation->lawyer->lawyerProfile->lat;
                                                                $lng = $consultation->lawyer->lawyerProfile->lng;
                                                            } elseif ($consultation->lawyer->lawFirmLawyer) {
                                                                $lat = $consultation->lawyer->lawFirmLawyer->lat;
                                                                $lng = $consultation->lawyer->lawFirmLawyer->lng;
                                                            }
                                                        @endphp
                                                        
                                                        @if($lat && $lng)
                                                            <a href="https://www.openstreetmap.org/?mlat={{ $lat }}&mlon={{ $lng }}#map=16/{{ $lat }}/{{ $lng }}" target="_blank" 
                                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-raleway transition duration-150">
                                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                </svg>
                                                                Office Address
                                                            </a>
                                                        @endif
                                                    @endif

                                                    {{-- Message Button --}}
                                                    <a href="{{ route('messages', ['receiver_id' => $consultation->lawyer_id]) }}" 
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition duration-150">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                        </svg>
                                                        Message
                                                    </a>
                                                @elseif($consultation->status === 'declined')
                                                    <span class="text-gray-500 italic font-open-sans text-xs">{{ Str::limit($consultation->decline_reason ?: 'No reason provided', 50) }}</span>
                                                @elseif($consultation->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs bg-yellow-50 text-yellow-800 border border-yellow-200 font-open-sans">
                                                        <svg class="animate-pulse mr-1.5 h-2 w-2 text-yellow-600" fill="currentColor" viewBox="0 0 8 8">
                                                            <circle cx="4" cy="4" r="3" />
                                                        </svg>
                                                        Awaiting response
                                                    </span>
                                                @elseif($consultation->status === 'completed')
                                                    <div class="flex flex-wrap gap-2">
                                                        <button wire:click="viewConsultationResults({{ $consultation->id }})" 
                                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 font-raleway transition duration-150">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            View Results
                                                        </button>
                                                        <a href="{{ route('messages', ['receiver_id' => $consultation->lawyer_id]) }}" 
                                                        class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition duration-150">
                                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                            </svg>
                                                            Message
                                                        </a>
                                                        @if($consultation->case)
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                                <svg class="mr-1 h-3 w-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                Case Request Sent
                                                            </span>
                                                        @elseif($consultation->can_start_case)
                                                            <button wire:click="showStartCaseForm({{ $consultation->id }})" 
                                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition duration-150">
                                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                                </svg>
                                                                Request to Start Case
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if(method_exists($consultations, 'hasPages') && $consultations->hasPages())
                        <div class="px-4 py-4 bg-white border-t border-gray-200 sm:px-6">
                            {{ $consultations->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Start Case Modal -->
    <div x-data="{ open: @entangle('showStartCaseModal') }" x-show="open" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

            <div x-show="open" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2"></div>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 font-raleway flex items-center">
                                <svg class="h-5 w-5 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Start a New Case
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="caseTitle" class="block text-sm font-medium text-gray-700 font-raleway">Case Title</label>
                                    <input type="text" wire:model="caseTitle" id="caseTitle" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 font-open-sans">
                                    @error('caseTitle') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="caseDescription" class="block text-sm font-medium text-gray-700 font-raleway">Case Description</label>
                                    <textarea wire:model="caseDescription" id="caseDescription" rows="4"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 font-open-sans"
                                              placeholder="Please describe your case and what you would like to achieve..."></textarea>
                                    @error('caseDescription') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="caseDocument" class="block text-sm font-medium text-gray-700 font-raleway">Supporting Document (Optional)</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition duration-150">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                                    <span>Upload a file</span>
                                                    <input id="file-upload" wire:model="caseDocument" type="file" class="sr-only" accept=".pdf,.doc,.docx">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                PDF, DOC, DOCX up to 10MB
                                            </p>
                                        </div>
                                    </div>
                                    @error('caseDocument') <span class="text-red-600 text-sm mt-1">{{ $message }}</span> @enderror
                                    
                                    <div wire:loading wire:target="caseDocument" class="mt-2 text-sm text-indigo-600 flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Uploading document...
                                    </div>
                                    
                                    @if($caseDocument)
                                    <div class="mt-2 text-sm text-gray-700 bg-gray-50 p-2 rounded border border-gray-200 flex items-center">
                                        <svg class="h-4 w-4 text-indigo-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="font-medium">{{ $caseDocument->getClientOriginalName() }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="startCase" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm font-raleway transition duration-150">
                        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Start Case
                    </button>
                    <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-raleway transition duration-150">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Consultation Results Modal -->
    <div x-data="{ open: @entangle('showResultsModal') }" x-show="open" class="fixed z-10 inset-0 overflow-y-auto" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>

            <div x-show="open" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <!-- Modal Header with decoration -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 h-2"></div>
                
                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-xl leading-6 font-semibold text-gray-900 font-raleway flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Consultation Results
                            </h3>
                            
                            @if($selectedConsultation)
                                <p class="mt-2 text-sm text-gray-600 font-open-sans">
                                    Your lawyer has provided the following results and findings from your consultation.
                                </p>
                                
                                <!-- Office Address Section (show only for in-house consultations) -->
                                @if(($selectedConsultation->consultation_type === 'in_house' || $selectedConsultation->consultation_type === 'inhouse' || $selectedConsultation->consultation_type === 'In-House Consultation' || $selectedConsultation->type === 'inhouse' || $selectedConsultation->type === 'in-house') &&
                                    $selectedConsultation->status === 'accepted' &&
                                    $selectedConsultation->lawyer &&
                                    $selectedConsultation->lawyer->lawyerProfile &&
                                    $selectedConsultation->lawyer->lawyerProfile->office_address)
                                    <div class="mt-4 mb-4 overflow-hidden bg-emerald-50 rounded-lg border border-emerald-200 shadow-sm">
                                        <div class="px-4 py-3 sm:px-6 bg-emerald-100 flex items-center">
                                            <svg class="h-5 w-5 text-emerald-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <h4 class="text-md font-medium text-emerald-900 font-raleway">Office Location</h4>
                                        </div>
                                        <div class="px-4 py-3 sm:p-4 bg-white">
                                            <p class="text-sm text-gray-700 font-open-sans leading-relaxed">
                                                {{ $selectedConsultation->lawyer->lawyerProfile->office_address }}
                                            </p>
                                            @if($selectedConsultation->lawyer->lawyerProfile->google_maps_link)
                                                <div class="mt-2">
                                                    <a href="{{ $selectedConsultation->lawyer->lawyerProfile->google_maps_link }}" 
                                                       target="_blank"
                                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-raleway">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                        </svg>
                                                        Open in Google Maps
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Results and Findings Card -->
                                <div class="overflow-hidden bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-4 py-3 sm:px-6 bg-gray-50 flex items-center">
                                        <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <h4 class="text-md font-medium text-gray-900 font-raleway">Consultation Results</h4>
                                    </div>
                                    <div class="px-4 py-3 sm:p-4 bg-white">
                                        <p class="text-sm text-gray-700 whitespace-pre-line font-open-sans leading-relaxed">{{ $selectedConsultation->consultation_results }}</p>
                                    </div>
                                </div>

                                <!-- Meeting Notes Card (if available) -->
                                @if($selectedConsultation->meeting_notes)
                                <div class="mt-4 overflow-hidden bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-4 py-3 sm:px-6 bg-gray-50 flex items-center">
                                        <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <h4 class="text-md font-medium text-gray-900 font-raleway">Meeting Notes</h4>
                                    </div>
                                    <div class="px-4 py-3 sm:p-4 bg-white">
                                        <p class="text-sm text-gray-700 whitespace-pre-line font-open-sans leading-relaxed">{{ $selectedConsultation->meeting_notes }}</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Consultation Document Card (if available) -->
                                @if($selectedConsultation->consultation_document_path)
                                <div class="mt-4 overflow-hidden bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <div class="px-4 py-3 sm:px-6 bg-gray-50 flex items-center">
                                        <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <h4 class="text-md font-medium text-gray-900 font-raleway">Consultation Document</h4>
                                    </div>
                                    <div class="px-4 py-3 sm:p-4 bg-white">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                            <div>
                                                <p class="text-sm text-gray-700 font-open-sans">Your lawyer has provided a detailed document with additional information about your consultation.</p>
                                                <p class="text-xs text-gray-500 mt-1 font-open-sans">You can download this document for your records or to review the details offline.</p>
                                            </div>
                                            <a href="{{ Storage::url($selectedConsultation->consultation_document_path) }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition duration-150">
                                                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Download Document
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Request to Start Case Section -->
                                @if(!$selectedConsultation->case && $selectedConsultation->can_start_case)
                                <div class="mt-4 bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-md font-medium text-indigo-900 font-raleway">Ready to move forward?</h3>
                                            <div class="mt-2 text-sm text-indigo-700 font-open-sans">
                                                <p>Based on this consultation, you can now request to start a legal case with your lawyer.</p>
                                            </div>
                                            <div class="mt-4">
                                                <button wire:click="showStartCaseForm({{ $selectedConsultation->id }})" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition duration-150">
                                                    <svg class="mr-2 -ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    Request to Start Case
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Message Lawyer Button -->
                                <div class="mt-4 flex justify-between items-center">
                                    <a href="{{ route('messages', ['receiver_id' => $selectedConsultation->lawyer_id]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway transition duration-150">
                                        <svg class="mr-2 -ml-1 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                        Message Your Lawyer
                                    </a>
                                    <span class="text-xs text-gray-500 font-open-sans">Updated: {{ $selectedConsultation->updated_at->format('M d, Y - h:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="open = false" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm font-raleway">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
