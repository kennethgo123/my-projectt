<div x-data="{ open: @entangle('showContractModal').defer }" 
     x-show="open" 
     x-cloak
     class="fixed z-10 inset-0 overflow-y-auto" 
     aria-labelledby="contract-modal-title" 
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
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="contract-modal-title">
                        Contract Viewer
                    </h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                @if($selectedCase && $selectedCase->contract_path)
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-md font-medium text-gray-900">{{ $selectedCase->case_number }}</h4>
                                <p class="text-sm text-gray-500">{{ $selectedCase->title }}</p>
                            </div>
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @switch($selectedCase->contract_status)
                                        @case('signed')
                                            bg-green-100 text-green-800
                                            @break
                                        @case('sent')
                                            bg-blue-100 text-blue-800
                                            @break
                                        @case('pending')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('rejected')
                                            bg-red-100 text-red-800
                                            @break
                                        @case('negotiating')
                                            bg-purple-100 text-purple-800
                                            @break
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ ucfirst($selectedCase->contract_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contract PDF Viewer -->
                    <div class="bg-gray-100 rounded-lg">
                        <div class="h-96">
                            <iframe src="{{ Storage::url($selectedCase->contract_path) }}" class="w-full h-full rounded-lg"></iframe>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0">
                        <!-- Download Link -->
                        <a href="{{ Storage::url($selectedCase->contract_path) }}" 
                           download 
                           class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                            Download Contract
                        </a>
                        
                        <!-- Open in New Tab -->
                        <a href="{{ Storage::url($selectedCase->contract_path) }}" 
                           target="_blank" 
                           class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                            </svg>
                            Open in New Tab
                        </a>
                    </div>
                    
                    @if($selectedCase->contract_status == 'signed' && $selectedCase->status != 'active')
                        <div class="mt-4 border-t pt-4">
                            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-700">
                                            The contract has been signed by the client. You can now mark the case as active to begin work.
                                        </p>
                                        
                                        @php
                                            $contractAction = $selectedCase->contractActions->first();
                                            $isAcknowledged = $contractAction && $contractAction->lawyer_acknowledged;
                                        @endphp
                                        
                                        @if(!$isAcknowledged)
                                            <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                                                <h5 class="text-sm font-medium text-yellow-800 mb-2">Signature Acknowledgment Required</h5>
                                                <p class="text-sm text-yellow-700 mb-3">
                                                    Before you can proceed, you must acknowledge receipt of the client's electronic signature.
                                                </p>
                                                
                                                <div class="relative flex items-start mb-3">
                                                    <div class="flex items-center h-5">
                                                        <input wire:model="signatureAcknowledged" id="signature-acknowledgment" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="signature-acknowledgment" class="font-medium text-gray-700">I acknowledge and agree to the following statement:</label>
                                                    </div>
                                                </div>
                                                
                                                <div class="bg-white p-3 rounded-md text-sm text-gray-700 border border-gray-200">
                                                    "I acknowledge receipt of the client's electronic signature for this specific contract only. I understand and affirm that this e-signature is exclusively authorized for the present document and cannot be used, reproduced, or applied to any other contracts or documents. Any misuse of this electronic signature is strictly prohibited, constitutes a violation of professional ethics, and is subject to legal penalties under applicable electronic signature laws."
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <button wire:click="acknowledgeSignature" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Acknowledge Signature
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                                <p class="text-sm text-green-700">
                                                    <span class="font-medium">Signature Acknowledged:</span> 
                                                    You acknowledged receipt of the client's signature on 
                                                    {{ $contractAction->lawyer_acknowledged_at->format('M d, Y h:i A') }}.
                                                </p>
                                            </div>
                                        @endif
                                        
                                        <div class="mt-2">
                                            <button wire:click="showAction({{ $selectedCase->id }}, 'mark_active')" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                @if(!$isAcknowledged) disabled @endif>
                                                Mark Case as Active
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    No contract is available for this case. Please upload a contract first.
                                </p>
                            </div>
                        </div>
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