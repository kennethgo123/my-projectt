<div>
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-2xl font-bold text-gray-900">Manage Consultations</h1>
                <p class="text-gray-600 mt-1">Review consultation requests and manage your client meetings</p>
            </div>
            
            <!-- Tabs -->
            <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
                <button wire:click="$set('activeTab', 'consultations')" 
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $activeTab === 'consultations' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Consultations
                        </button>
                        
                        @if($canSetAvailability)
                    <button wire:click="$set('activeTab', 'availability')" 
                        class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $activeTab === 'availability' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Availability
                            </button>
                        @else
                            <div class="relative group">
                        <button disabled class="px-4 py-2 text-sm font-medium rounded-md text-gray-400 cursor-not-allowed">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Availability
                                </button>
                                <div class="absolute z-10 hidden group-hover:block bg-black text-white text-xs rounded py-1 px-2 right-0 bottom-full mb-1 w-48">
                                    Kindly refer to your firm for details
                                </div>
                            </div>
                        @endif
            </div>
        </div>
            </div>
            
                    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('message') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
                        </div>
                    @endif

    @if($activeTab === 'consultations')
        <!-- Quick Stats -->
        @php
            $pendingCount = $consultations->where('status', 'pending')->count();
            $upcomingCount = $consultations->where('status', 'accepted')->where('is_completed', false)->count();
            $completedCount = $consultations->where('status', 'completed')->count();
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">Pending Requests</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $pendingCount }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">Upcoming</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $upcomingCount }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                                </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">Completed</p>
                        <p class="text-2xl font-bold text-green-900">{{ $completedCount }}</p>
                                                    </div>
                                                    </div>
                                                </div>
                                            </div>

        <!-- Consultations Grid -->
        @if($consultations->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
                @foreach($consultations as $consultation)
                    <div wire:key="consultation-card-{{ $consultation->id }}" class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                        <!-- Card Header -->
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                            @if($consultation->client->clientProfile)
                                                                {{ $consultation->client->clientProfile->first_name }} {{ $consultation->client->clientProfile->last_name }}
                                                            @else
                                                                {{ $consultation->client->name ?? 'Client #' . $consultation->client->id }}
                                                            @endif
                                                @if($consultation->client->status === 'approved')
                                            <svg class="inline w-4 h-4 text-green-500 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                            @endif
                                        </h3>
                                    <p class="text-sm text-gray-600">{{ ucfirst($consultation->consultation_type) }}</p>
                                    </div>
                                        <span @class([
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            'bg-yellow-100 text-yellow-800' => $consultation->status === 'pending',
                                            'bg-green-100 text-green-800' => $consultation->status === 'accepted',
                                            'bg-red-100 text-red-800' => $consultation->status === 'declined',
                                            'bg-blue-100 text-blue-800' => $consultation->status === 'completed'
                                        ])>
                                            {{ ucfirst($consultation->status) }}
                                        </span>
                                    </div>
                                </div>

                        <!-- Card Body -->
                        <div class="p-4">
                            <!-- Client Info -->
                            <div class="mb-3">
                                <div class="text-sm text-gray-600">
                                                <p><span class="font-medium">Email:</span> {{ $consultation->client->email }}</p>
                                    @if($consultation->client->clientProfile && $consultation->client->clientProfile->contact_number)
                                        <p><span class="font-medium">Phone:</span> {{ $consultation->client->clientProfile->contact_number }}</p>
                                                @endif
                                    @if($consultation->client->clientProfile && $consultation->client->clientProfile->city)
                                                    <p><span class="font-medium">Location:</span> {{ $consultation->client->clientProfile->city }}</p>
                                                @endif
                                            </div>
                                    </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <p class="text-sm text-gray-700 line-clamp-3">{{ $consultation->description }}</p>
                                    </div>

                            <!-- Preferred Dates -->
                            @if($consultation->status === 'pending')
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-900 mb-2">Preferred Dates:</p>
                                    <div class="space-y-1">
                                            @foreach(json_decode($consultation->preferred_dates) as $date)
                                                <div class="flex items-center space-x-2">
                                                        <input type="radio" 
                                                            name="selected_date_{{ $consultation->id }}" 
                                                            value="{{ $date }}"
                                                            wire:model="selectedDates.{{ $consultation->id }}"
                                                    class="text-indigo-600 focus:ring-indigo-500 h-3 w-3 border-gray-300">
                                                <span class="text-xs text-gray-600">
                                                        {{ \Carbon\Carbon::parse($date)->format('M d, Y g:i A') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                            @elseif($consultation->selected_date)
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-900">Scheduled:</p>
                                    <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($consultation->selected_date)->format('l, F j, Y g:i A') }}</p>
                                </div>
                            @endif

                            <!-- Documents -->
                                @if($consultation->documents)
                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-900 mb-1">Documents:</p>
                                    <div class="space-y-1">
                                            @foreach(json_decode($consultation->documents) as $document)
                                            <a href="{{ Storage::url($document) }}" target="_blank" 
                                                class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.415a6 6 0 108.486 8.486L20.5 13"></path>
                                                </svg>
                                                    {{ basename($document) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            <!-- Meeting Link -->
                            @if($consultation->meeting_link && $consultation->status !== 'pending')
                                <div class="mb-3">
                                    <a href="{{ $consultation->meeting_link }}" target="_blank" 
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"></path>
                                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5z"></path>
                                        </svg>
                                        Join Meeting
                                    </a>
                                                </div>
                                    @endif

                            <!-- Consultation Results (for completed) -->
                            @if($consultation->status === 'completed' && $consultation->consultation_results)
                                <div class="mb-3 p-3 bg-green-50 rounded-lg">
                                    <p class="text-sm font-medium text-green-800 mb-1">Results:</p>
                                    <p class="text-sm text-green-700 line-clamp-3">{{ $consultation->consultation_results }}</p>
                                    @if($consultation->consultation_document_path)
                                        <a href="{{ Storage::url($consultation->consultation_document_path) }}" target="_blank" 
                                            class="inline-flex items-center mt-2 text-xs text-green-700 hover:text-green-900">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            View Document
                                        </a>
                                    @endif
                                </div>
                            @endif

                            <!-- Request Date -->
                            <p class="text-xs text-gray-500">Requested {{ $consultation->created_at->format('M d, Y') }}</p>
                                </div>

                        <!-- Card Footer - Action Buttons -->
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                            <div class="flex flex-wrap gap-2">
                                @if($consultation->status === 'pending')
                                    <!-- Meeting Link Input for Online Consultations -->
                                    @if($consultation->consultation_type === 'Online Consultation')
                                        <div class="w-full mb-2">
                                            <input type="url" wire:model="customMeetingLink" 
                                                placeholder="Meeting link (optional)" 
                                                class="w-full text-xs border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    @endif
                                    
                                    <button wire:click.prevent="acceptConsultation({{ $consultation->id }})" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Accept
                                    </button>
                                    
                                    <button wire:click.prevent="showDeclineForm({{ $consultation->id }})" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                            Decline
                                        </button>

                                @elseif($consultation->status === 'accepted' && !$consultation->is_completed)
                                    <button type="button" wire:click.prevent="showCompleteForm({{ $consultation->id }})" 
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Complete
                                        </button>
                                    
                                    @if($consultation->consultation_type === 'Online Consultation')
                                        <button wire:click.prevent="showGoogleMeetLinkInput({{ $consultation->id }})" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.414a4 4 0 00-5.656-5.656l-6.415 6.415a6 6 0 108.486 8.486L20.5 13"></path>
                                                            </svg>
                                            {{ $consultation->meeting_link ? 'Update' : 'Add' }} Link
                                                        </button>
                                    @endif

                                @elseif($consultation->status === 'completed')
                                    @if(!$consultation->case)
                                        <button wire:click.prevent="openStartCaseModal({{ $consultation->id }})" 
                                            class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Start Case
                                        </button>
                                    @else
                                        <button wire:click.prevent="showReviewContractModal({{ $consultation->id }})" 
                                            class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            View Contract
                                        </button>
                                    @endif
                                @endif

                                <!-- Message Client Button (always available for non-pending) -->
                                @if($consultation->status !== 'pending')
                                    <a href="{{ route('messages', ['receiver_id' => $consultation->client_id]) }}" 
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                </svg>
                                        Message
                                    </a>
                                                    @endif
                                                </div>

                            <!-- Meeting Link Input for Accepted Consultations -->
                            @if($consultation->status === 'accepted' && $consultation->consultation_type === 'Online Consultation' && !empty($showGoogleMeetInput[$consultation->id]))
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <div class="flex space-x-2">
                                        <input type="url" wire:model.live="googleMeetLink" 
                                            placeholder="Enter meeting link" 
                                            class="flex-1 text-xs border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                        <button wire:click="updateMeetingLink({{ $consultation->id }})" 
                                            class="px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                            Save
                                        </button>
                                        <button wire:click="$set('showGoogleMeetInput.{{ $consultation->id }}', false)" 
                                            class="px-2 py-1.5 text-gray-400 hover:text-gray-600">
                                            ×
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                    </div>
                @endforeach
                            </div>

            <!-- Pagination -->
                        @if($consultations->hasPages())
                            <div class="mt-6">
                                {{ $consultations->links() }}
                            </div>
                        @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No consultations</h3>
                <p class="mt-1 text-sm text-gray-500">You don't have any consultation requests yet.</p>
                </div>
        @endif
            @endif
            
            @if($activeTab === 'availability')
                <livewire:lawyer.manage-availability />
            @endif
    </div>

    <!-- Decline Modal -->
    @if($showDeclineModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" wire:ignore.self>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Decline Consultation</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Please provide a reason for declining this consultation request. This will be shared with the client.</p>
                                <div class="mt-4">
                                    <textarea wire:model="declineReason" rows="4" 
                                        class="shadow-sm block w-full focus:ring-red-500 focus:border-red-500 sm:text-sm border-gray-300 rounded-md" 
                                        placeholder="Enter your reason here..."></textarea>
                                    @error('declineReason')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="declineConsultation" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Decline
                    </button>
                    <button type="button" wire:click="$set('showDeclineModal', false)" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Complete Consultation Modal (Always in DOM; toggled via entangle) -->
    <div x-data="{ open: @entangle('showCompleteModal').live }" x-show="open" x-cloak wire:ignore.self
        @keydown.escape.window="open = false; $wire.$set('showCompleteModal', false)"
        class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false; $wire.$set('showCompleteModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Complete Consultation</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Mark this consultation as complete and provide the results and findings. This information will be shared with the client.</p>
                                <div class="mt-4">
                                    <label for="consultationResults" class="block text-sm font-medium text-gray-700">Consultation Results and Findings (Required)</label>
                                    <textarea id="consultationResults" wire:model="consultationResults" rows="4" 
                                        class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                        placeholder="Enter the consultation results and findings here..."></textarea>
                                    @error('consultationResults')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="mt-4">
                                    <label for="meetingNotes" class="block text-sm font-medium text-gray-700">Meeting Notes (Optional)</label>
                                    <textarea id="meetingNotes" wire:model="meetingNotes" rows="3" 
                                        class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                        placeholder="Additional notes from the consultation..."></textarea>
                                </div>
                                <div class="mt-4">
                                    <label for="consultationDocument" class="block text-sm font-medium text-gray-700">Supporting Document (Optional)</label>
                                    <input type="file" id="consultationDocument" wire:model="consultationDocument" 
                                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                                        @error('consultationDocument')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="markConsultationComplete" 
                        wire:loading.attr="disabled"
                        wire:target="markConsultationComplete"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="markConsultationComplete">Complete Consultation</span>
                        <span wire:loading wire:target="markConsultationComplete">Processing...</span>
                    </button>
                    <button type="button" @click="open = false; $wire.$set('showCompleteModal', false)" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Case Modal -->
    @if($showStartCaseModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" wire:ignore.self>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Start Case</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Create a new case from this completed consultation and upload the initial contract.</p>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="caseTitle" class="block text-sm font-medium text-gray-700">Case Title</label>
                                        <input type="text" id="caseTitle" wire:model="caseTitle" 
                                            class="mt-1 shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                            placeholder="Enter case title">
                                        @error('caseTitle')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="caseDescription" class="block text-sm font-medium text-gray-700">Case Description</label>
                                        <textarea id="caseDescription" wire:model="caseDescription" rows="3" 
                                            class="mt-1 shadow-sm focus:ring-purple-500 focus:border-purple-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                            placeholder="Describe the case details"></textarea>
                                        @error('caseDescription')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="contractDocument" class="block text-sm font-medium text-gray-700">Contract Document</label>
                                        <input type="file" id="contractDocument" wire:model="contractDocument" 
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                                        @error('contractDocument')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="createNewCase" 
                        wire:loading.attr="disabled"
                        wire:target="createNewCase"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="createNewCase">Start Case</span>
                        <span wire:loading wire:target="createNewCase">Creating...</span>
                    </button>
                    <button type="button" wire:click="$set('showStartCaseModal', false)" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Review Contract Modal (Always in DOM; toggled via entangle) -->
    <div x-data="{ open: @entangle('showReviewContractModal').live }" x-show="open" x-cloak wire:ignore.self
        @keydown.escape.window="open = false; $wire.$set('showReviewContractModal', false)"
        class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false; $wire.$set('showReviewContractModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Case Contract</h3>
                            <div class="mt-2">
                                @if($selectedConsultationForReview)
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Case Title</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $reviewCaseTitle }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Case Description</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $reviewCaseDescription }}</p>
                                        </div>
                                        @if($reviewContractPath)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Contract Document</label>
                                            <a href="{{ Storage::url($reviewContractPath) }}" target="_blank" 
                                                class="mt-1 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                View Contract
                                            </a>
    </div>
    @endif
    </div>
    @endif
    </div>
                        </div>
</div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="open = false; $wire.$set('showReviewContractModal', false)" 
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>