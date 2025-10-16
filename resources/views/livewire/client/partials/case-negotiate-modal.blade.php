<div x-data="{ open: @entangle('showNegotiateModal').defer }" 
     x-show="open" 
     x-cloak
     class="fixed z-10 inset-0 overflow-y-auto" 
     aria-labelledby="negotiate-modal-title" 
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
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="negotiate-modal-title">
                        Negotiate Contract Terms
                    </h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                @if($selectedCase)
                    <div class="mb-4">
                        <div class="text-sm text-gray-500 mb-4">
                            <p><strong>Case:</strong> {{ $selectedCase->case_number }} - {{ $selectedCase->title }}</p>
                            <p><strong>Lawyer:</strong> {{ $selectedCase->lawyer->first_name }} {{ $selectedCase->lawyer->last_name }}</p>
                        </div>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Please describe in detail what terms of the contract you would like to negotiate. Your lawyer will review your request and respond accordingly.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="negotiationTerms" class="block text-sm font-medium text-gray-700 mb-1">Your Proposed Changes</label>
                            <textarea 
                                wire:model="negotiationTerms" 
                                id="negotiationTerms" 
                                rows="6" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                placeholder="Please be specific about which clauses you'd like to change and what changes you're proposing..."
                            ></textarea>
                            
                            @error('negotiationTerms')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="text-sm text-gray-500">
                            <p>Examples of negotiation points:</p>
                            <ul class="list-disc pl-5 mt-2 space-y-1">
                                <li>Changes to payment terms or fee structure</li>
                                <li>Modifications to timelines or deadlines</li>
                                <li>Clarification of specific responsibilities</li>
                                <li>Changes to confidentiality or intellectual property clauses</li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="submitNegotiation" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Submit Negotiation
                </button>
                <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div> 