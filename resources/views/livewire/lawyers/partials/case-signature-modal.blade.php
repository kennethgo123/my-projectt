<div 
     x-data="{ 
         open: @entangle('showSignatureModal').defer,
         debug() {
             console.log('Signature modal state:', { 
                 open: this.open,
                 hasCase: {{ isset($selectedCase) ? 'true' : 'false' }},
                 hasSignature: {{ isset($selectedCase) && $selectedCase->signature_path ? 'true' : 'false' }},
                 signaturePath: '{{ isset($selectedCase) && $selectedCase->signature_path ? $selectedCase->signature_path : 'none' }}'
             });
         }
     }" 
     x-init="debug(); $watch('open', value => debug())"
     x-show="open" 
     x-cloak
     @signature-modal-opened.window="open = true; debug()"
     class="fixed z-10 inset-0 overflow-y-auto" 
     aria-labelledby="signature-modal-title" 
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
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="signature-modal-title">
                        Client E-Signature
                    </h3>
                    <button @click="open = false; console.log('Modal closed with X button')" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                @if($selectedCase && $selectedCase->signature_path)
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
                                        @default
                                            bg-gray-100 text-gray-800
                                    @endswitch
                                ">
                                    {{ ucfirst($selectedCase->contract_status ?? 'pending') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Signature Viewer -->
                    <div class="bg-gray-100 rounded-lg mb-4">
                        <div class="h-48">
                            <img 
                                src="{{ Storage::url($selectedCase->signature_path) }}" 
                                alt="Client Signature" 
                                class="object-contain w-full h-full rounded-lg"
                                onerror="console.error('Failed to load signature image:', '{{ $selectedCase->signature_path }}'); this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48dGV4dCB4PSIxMCIgeT0iNTAiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9InJlZCI+RXJyb3IgbG9hZGluZyBzaWduYXR1cmUgaW1hZ2U8L3RleHQ+PC9zdmc+'"
                            >
                        </div>
                    </div>
                    
                    <!-- Acknowledgment Message -->
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md mb-4">
                        <h5 class="text-sm font-medium text-yellow-800 mb-2">Legal Acknowledgment</h5>
                        <p class="text-sm text-yellow-700">
                            "I acknowledge receipt of the client's electronic signature for this specific contract only. I understand and affirm that this e-signature is exclusively authorized for the present document and cannot be used, reproduced, or applied to any other contracts or documents. Any misuse of this electronic signature is strictly prohibited, constitutes a violation of professional ethics, and is subject to legal penalties under applicable electronic signature laws."
                        </p>
                        
                        <div class="relative flex items-start mt-3">
                            <div class="flex items-center h-5">
                                <input wire:model="signatureAcknowledged" id="signature-acknowledgment" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="signature-acknowledgment" class="font-medium text-gray-700">I acknowledge and agree to the statement above</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0">
                        <!-- Open in New Tab -->
                        <a href="{{ Storage::url($selectedCase->signature_path) }}" 
                           target="_blank" 
                           class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                            </svg>
                            Open in New Tab
                        </a>
                        
                        <button wire:click="acknowledgeSignature" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Acknowledge Signature
                        </button>
                    </div>
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
                                    No signature is available for this case.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="open = false; console.log('Modal closed with Close button')" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div> 