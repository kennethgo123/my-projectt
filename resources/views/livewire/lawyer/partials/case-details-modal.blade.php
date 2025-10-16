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

                        <!-- Client Information Section -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Client Information</h4>
                            <div class="space-y-2">
                                <p><span class="font-medium">Name:</span> {{ $selectedCase->client->first_name }} {{ $selectedCase->client->last_name }}</p>
                                <p><span class="font-medium">Email:</span> {{ $selectedCase->client->email }}</p>
                                <p><span class="font-medium">Phone:</span> {{ $selectedCase->client->phone ?? 'Not provided' }}</p>
                                
                                @if($selectedCase->client->clientProfile)
                                    <p><span class="font-medium">Address:</span> {{ $selectedCase->client->clientProfile->address ?? 'Not provided' }}</p>
                                    <p><span class="font-medium">City:</span> {{ $selectedCase->client->clientProfile->city ?? 'Not provided' }}</p>
                                @endif
                                
                                @if($selectedCase->consultation)
                                    <p class="mt-2"><span class="font-medium">From Consultation:</span> Yes</p>
                                    <p><span class="font-medium">Consultation Date:</span> {{ $selectedCase->consultation->created_at->format('M d, Y') }}</p>
                                    
                                    @if($selectedCase->consultation->consultation_results)
                                        <div class="mt-3 bg-blue-50 p-3 rounded-md">
                                            <p class="text-sm font-medium text-blue-800">Consultation Results:</p>
                                            <p class="text-sm text-blue-700 mt-1">{{ $selectedCase->consultation->consultation_results }}</p>
                                        </div>
                                    @endif
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
                                <button wire:click="viewContract({{ $selectedCase->id }})" class="mt-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Contract
                                </button>
                            @else
                                <p><span class="font-medium">Contract:</span> Not yet uploaded</p>
                                @if($selectedCase->status == 'accepted')
                                    <button wire:click="showAction({{ $selectedCase->id }}, 'upload_contract')" class="mt-2 inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Upload Contract
                                    </button>
                                @endif
                            @endif

                            @if($selectedCase->contract_signed_at)
                                <p class="mt-2"><span class="font-medium">Signed at:</span> {{ $selectedCase->contract_signed_at->format('M d, Y h:i A') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Client Document Section -->
                    @if($selectedCase->client_document_path)
                        <div class="mt-6 bg-gray-50 rounded-lg p-4">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Client Documents</h4>
                            <div class="space-y-2">
                                <p><span class="font-medium">Supporting Document:</span> Available</p>
                                <div class="mt-2 flex">
                                    <a href="{{ Storage::url($selectedCase->client_document_path) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                        View Document
                                    </a>
                                    <a href="{{ Storage::url($selectedCase->client_document_path) }}" 
                                       download 
                                       class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Download Document
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Case Updates Section -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center">
                            <h4 class="text-md font-medium text-gray-900">Case Updates</h4>
                            <button wire:click="showAction({{ $selectedCase->id }}, 'add_update')" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Update
                            </button>
                        </div>
                        
                        @if($selectedCase->caseUpdates && $selectedCase->caseUpdates->count() > 0)
                            <div class="mt-3 flow-root">
                                <ul role="list" class="-mb-8">
                                    @foreach($selectedCase->caseUpdates->sortByDesc('created_at') as $update)
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
                                                            <div class="mt-1">
                                                                @switch($update->visibility)
                                                                    @case('both')
                                                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Visible to Client</span>
                                                                        @break
                                                                    @case('lawyer')
                                                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Private</span>
                                                                        @break
                                                                    @case('client')
                                                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Client Only</span>
                                                                        @break
                                                                @endswitch
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="mt-4 bg-yellow-50 border border-yellow-100 rounded-md p-4">
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