<div x-data="{ open: @entangle('showDetailsModal').defer }" 
     x-show="open" 
     x-cloak
     class="fixed z-10 inset-0 overflow-y-auto" 
     aria-labelledby="details-modal-title" 
     role="dialog" 
     aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             aria-hidden="true">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                @if($selectedCase)
                    <div class="flex justify-between items-center border-b pb-3 mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="details-modal-title">
                            Case Details: {{ $selectedCase->case_number }}
                        </h3>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Case Overview Section -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Case Overview</h4>
                            <div class="space-y-2">
                                <p><span class="font-medium">Title:</span> {{ $selectedCase->title }}</p>
                                <p><span class="font-medium">Status:</span> 
                                    @switch($selectedCase->status)
                                        @case('pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            @break
                                        @case('accepted')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Accepted</span>
                                            @break
                                        @case('rejected')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                            @break
                                        @case('contract_sent')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">Contract Sent</span>
                                            @break
                                        @case('contract_signed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Contract Signed</span>
                                            @break
                                        @case('active')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @break
                                        @case('closed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Closed</span>
                                            @break
                                        @default
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($selectedCase->status) }}</span>
                                    @endswitch
                                </p>
                                <p><span class="font-medium">Created:</span> {{ $selectedCase->created_at->format('M d, Y h:i A') }}</p>
                                <p class="mt-2"><span class="font-medium">Description:</span></p>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $selectedCase->description }}</p>
                            </div>
                        </div>

                        <!-- Lawyer Information Section -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Lawyer Information</h4>
                            <div class="space-y-2">
                                <p><span class="font-medium">Name:</span> 
                                    @if($selectedCase->lawyer->isLawFirm() && $selectedCase->lawyer->lawFirmProfile)
                                        {{ $selectedCase->lawyer->lawFirmProfile->firm_name }}
                                    @elseif($selectedCase->lawyer->isLawyer() && $selectedCase->lawyer->lawyerProfile)
                                        {{ $selectedCase->lawyer->lawyerProfile->first_name }} {{ $selectedCase->lawyer->lawyerProfile->last_name }}
                                    @elseif($selectedCase->lawyer->lawFirmLawyer)
                                        {{ $selectedCase->lawyer->lawFirmLawyer->first_name }} {{ $selectedCase->lawyer->lawFirmLawyer->last_name }}
                                    @else
                                        {{ $selectedCase->lawyer->first_name }} {{ $selectedCase->lawyer->last_name }}
                                    @endif
                                </p>
                                @if($selectedCase->lawyer->lawyerProfile)
                                    <p><span class="font-medium">Specialization:</span> {{ $selectedCase->lawyer->lawyerProfile->specialization ?? 'Not specified' }}</p>
                                    <p><span class="font-medium">Email:</span> {{ $selectedCase->lawyer->email }}</p>
                                    <p><span class="font-medium">Phone:</span> {{ $selectedCase->lawyer->lawyerProfile->contact_number ?? 'Not provided' }}</p>

                                    @if($selectedCase->consultation && ($selectedCase->consultation->type == 'inhouse' || $selectedCase->consultation->type == 'in_house' || $selectedCase->consultation->type == 'in-house') && $selectedCase->lawyer->lawyerProfile->google_maps_link)
                                    <p class="mt-2">
                                        <span class="font-medium">Office Address:</span> 
                                        <a href="{{ $selectedCase->lawyer->lawyerProfile->google_maps_link }}" target="_blank" class="inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $selectedCase->lawyer->lawyerProfile->office_address ?: 'View on Google Maps' }}
                                        </a>
                                    </p>
                                    @endif
                                @elseif($selectedCase->lawyer->lawFirmProfile)
                                    <p><span class="font-medium">Specialization:</span> {{ $selectedCase->lawyer->lawFirmProfile->specializations ?? 'Not specified' }}</p>
                                    <p><span class="font-medium">Email:</span> {{ $selectedCase->lawyer->email }}</p>
                                    <p><span class="font-medium">Phone:</span> {{ $selectedCase->lawyer->lawFirmProfile->contact_number ?? 'Not provided' }}</p>

                                    @if($selectedCase->lawyer->lawFirmProfile->google_maps_link)
                                    <p class="mt-2">
                                        <span class="font-medium">Office Address:</span> 
                                        <a href="{{ $selectedCase->lawyer->lawFirmProfile->google_maps_link }}" target="_blank" class="inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $selectedCase->lawyer->lawFirmProfile->office_address ?: 'View on Google Maps' }}
                                        </a>
                                    </p>
                                    @endif
                                @elseif($selectedCase->lawyer->lawFirmLawyer)
                                    <p><span class="font-medium">Specialization:</span> {{ $selectedCase->lawyer->lawFirmLawyer->specializations ?? 'Not specified' }}</p>
                                    <p><span class="font-medium">Email:</span> {{ $selectedCase->lawyer->email }}</p>
                                    <p><span class="font-medium">Phone:</span> {{ $selectedCase->lawyer->lawFirmLawyer->contact_number ?? 'Not provided' }}</p>
                                @endif
                                
                                @if($selectedCase->consultation)
                                    <p class="mt-2"><span class="font-medium">From Consultation:</span> Yes</p>
                                    <p><span class="font-medium">Consultation Date:</span> {{ $selectedCase->consultation->created_at->format('M d, Y') }}</p>
                                @else
                                    <p class="mt-2"><span class="font-medium">From Consultation:</span> No</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Contract Information Section -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Contract Information</h4>
                        <div class="space-y-2">
                            <p><span class="font-medium">Contract Status:</span> 
                                @switch($selectedCase->contract_status)
                                    @case('pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @break
                                    @case('sent')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Sent</span>
                                        @break
                                    @case('signed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Signed</span>
                                        @break
                                    @case('rejected')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                        @break
                                    @case('negotiating')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Negotiating</span>
                                        @break
                                    @default
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($selectedCase->contract_status ?? 'Not Set') }}</span>
                                @endswitch
                            </p>
                            
                            @if($selectedCase->contract_path)
                                <p><span class="font-medium">Contract:</span> Available</p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <button wire:click="viewContract({{ $selectedCase->id }})" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        View Contract
                                    </button>
                                    
                                    @if($selectedCase->contract_status === 'sent')
                                        <button wire:click="showSignContract({{ $selectedCase->id }})" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Sign Contract
                                        </button>
                                        
                                        <button wire:click="showNegotiateContract({{ $selectedCase->id }})" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            Negotiate Terms
                                        </button>
                                    @endif
                                </div>
                            @else
                                <p><span class="font-medium">Contract:</span> Not yet available</p>
                            @endif

                            @if($selectedCase->contract_signed_at)
                                <p class="mt-2"><span class="font-medium">Signed at:</span> {{ $selectedCase->contract_signed_at->format('M d, Y h:i A') }}</p>
                            @endif
                            
                            @if($selectedCase->status === 'rejected')
                                <div class="mt-3 p-3 bg-red-50 rounded-md">
                                    <p class="text-sm font-medium text-red-800">Rejection Reason:</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $selectedCase->rejection_reason }}</p>
                                </div>
                            @endif
                            
                            @if($selectedCase->contract_status === 'negotiating')
                                <div class="mt-3 p-3 bg-purple-50 rounded-md">
                                    <p class="text-sm font-medium text-purple-800">Negotiation Terms:</p>
                                    <p class="text-sm text-purple-700 mt-1">{{ $selectedCase->negotiation_terms }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Case Updates Section -->
                    <div class="mt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Case Updates</h4>
                        
                        @if($selectedCase->caseUpdates && $selectedCase->caseUpdates->where(function($update) { return $update->visibility === 'both' || $update->visibility === 'client'; })->count() > 0)
                            <div class="mt-3 flow-root">
                                <ul role="list" class="-mb-8">
                                    @foreach($selectedCase->caseUpdates->where(function($update) { return $update->visibility === 'both' || $update->visibility === 'client'; })->sortByDesc('created_at') as $update)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex space-x-3">
                                                    <div>
                                                        <span class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center ring-8 ring-white">
                                                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                        <div>
                                                            <p class="text-sm text-gray-900 font-medium">{{ $update->title }}</p>
                                                            <p class="mt-1 text-sm text-gray-500 whitespace-pre-line">{{ $update->content }}</p>
                                                        </div>
                                                        <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                            <div>{{ $update->created_at->format('M d, Y') }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-100 rounded-md p-4">
                                <p class="text-sm text-yellow-700">No updates have been added to this case yet.</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div> 