<!-- Upload Revised Contract Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: {{ $showUploadRevisedContractModal ? 'block' : 'none' }}">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Upload Revised Contract</h3>
                <button wire:click="$set('showUploadRevisedContractModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4">
                @if($selectedCaseForRevision)
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                        <h4 class="text-sm font-medium text-yellow-800">Client's Requested Changes:</h4>
                        <p class="mt-1 text-sm text-yellow-700">
                            {{ $selectedCaseForRevision->requested_changes_details ?? 'No specific details provided.' }}
                        </p>
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Upload Revised Contract Section -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Upload Revised Contract</h4>
                        
                        <div>
                            <label for="revisedContractDocument" class="block text-sm font-medium text-gray-700">Revised Contract Document</label>
                            <div class="mt-1 flex items-center">
                                <label class="block w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                                    <span>{{ $revisedContractDocument ? 'Change Document' : 'Upload Revised Contract' }}</span>
                                    <input id="revisedContractDocument" type="file" wire:model="revisedContractDocument" class="sr-only" accept=".pdf,.doc,.docx">
                                </label>
                            </div>
                            <div class="mt-2">
                                @if($revisedContractDocument)
                                    <p class="text-sm text-gray-600">{{ $revisedContractDocument->getClientOriginalName() }}</p>
                                @else
                                    <p class="text-sm text-gray-500">PDF, DOC, or DOCX up to 10MB</p>
                                @endif
                            </div>
                            @error('revisedContractDocument') 
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button 
                                wire:click="submitRevisedContract"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                wire:loading.attr="disabled"
                                {{ !$revisedContractDocument ? 'disabled' : '' }}
                            >
                                <span wire:loading.remove wire:target="submitRevisedContract">Upload Revised Contract</span>
                                <span wire:loading wire:target="submitRevisedContract">Uploading...</span>
                            </button>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">OR</span>
                        </div>
                    </div>

                    <!-- Decline Changes Section -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-3">Decline Client's Changes</h4>
                        
                        <div>
                            <label for="declineReason" class="block text-sm font-medium text-gray-700">Reason for Declining</label>
                            <textarea 
                                id="declineReason" 
                                wire:model.live="declineReason" 
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Explain why you cannot accommodate the requested changes..."
                            ></textarea>
                            <p class="mt-1 text-sm text-gray-500">
                                Minimum 10 characters required
                            </p>
                            @error('declineReason') 
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button 
                                wire:click="declineClientChanges"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                wire:loading.attr="disabled"
                                {{ strlen($declineReason) < 10 ? 'disabled' : '' }}
                            >
                                <span wire:loading.remove wire:target="declineClientChanges">Decline Client's Changes</span>
                                <span wire:loading wire:target="declineClientChanges">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end mt-6 pt-4 border-t">
                    <button 
                        type="button" 
                        wire:click="$set('showUploadRevisedContractModal', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 