<div x-data="{ open: @entangle('showActionModal').defer }" 
     x-show="open" 
     x-cloak
     class="fixed z-10 inset-0 overflow-y-auto" 
     aria-labelledby="action-modal-title" 
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
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        @if($selectedCase)
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="action-modal-title">
                                @switch($actionType)
                                    @case('accept')
                                        Accept Case
                                        @break
                                    @case('reject')
                                        Reject Case
                                        @break
                                    @case('upload_contract')
                                        Upload Contract
                                        @break
                                    @case('add_update')
                                        Add Case Update
                                        @break
                                    @case('mark_active')
                                        Mark Case as Active
                                        @break
                                    @default
                                        Case Action
                                @endswitch
                            </h3>
                            
                            <div class="mt-4">
                                <div class="text-sm text-gray-500 mb-4">
                                    <p><strong>Case:</strong> {{ $selectedCase->case_number }} - {{ $selectedCase->title }}</p>
                                    <p><strong>Client:</strong> {{ $selectedCase->client->first_name }} {{ $selectedCase->client->last_name }}</p>
                                </div>
                                
                                @switch($actionType)
                                    @case('accept')
                                        <p class="text-sm text-gray-600 mb-4">
                                            You are about to accept this case. This will notify the client and allow you to proceed with creating a contract.
                                        </p>
                                        
                                        @if($selectedCase->client_document_path)
                                            <div class="mt-3 bg-blue-50 rounded-md p-3">
                                                <p class="text-sm font-medium text-blue-800">The client has uploaded a supporting document.</p>
                                                <div class="mt-2">
                                                    <a href="{{ Storage::url($selectedCase->client_document_path) }}" 
                                                       target="_blank" 
                                                       class="inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                                        </svg>
                                                        View Document
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($selectedCase->consultation)
                                            <div class="mt-3 bg-indigo-50 rounded-md p-3">
                                                <p class="text-sm font-medium text-indigo-800">This case was created from a previous consultation.</p>
                                                @if($selectedCase->consultation->consultation_results)
                                                    <div class="mt-2">
                                                        <p class="text-xs font-medium text-indigo-700">Consultation Results:</p>
                                                        <p class="text-xs text-indigo-700 mt-1">{{ Str::limit($selectedCase->consultation->consultation_results, 200) }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        @break
                                        
                                    @case('reject')
                                        <div class="mb-4">
                                            <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-1">Reason for rejection</label>
                                            <textarea wire:model="rejectionReason" id="rejectionReason" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                            @error('rejectionReason')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        @break
                                        
                                    @case('upload_contract')
                                        <div class="mb-4">
                                            <label for="contract" class="block text-sm font-medium text-gray-700 mb-1">Contract Document (PDF, DOC, DOCX)</label>
                                            <input type="file" wire:model="contract" id="contract" class="w-full border border-gray-300 rounded-md py-2 px-3" accept=".pdf,.doc,.docx">
                                            <p class="mt-1 text-xs text-gray-500">Maximum size: 10MB. Files will be automatically converted to PDF format.</p>
                                            @error('contract')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        @break
                                        
                                    @case('add_update')
                                        <div class="mb-4">
                                            <label for="updateTitle" class="block text-sm font-medium text-gray-700 mb-1">Update Title</label>
                                            <input type="text" wire:model="updateTitle" id="updateTitle" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            @error('updateTitle')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="updateContent" class="block text-sm font-medium text-gray-700 mb-1">Update Content</label>
                                            <textarea wire:model="updateContent" id="updateContent" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                            @error('updateContent')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="updateVisibility" class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                                            <select wire:model="updateVisibility" id="updateVisibility" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <option value="both">Visible to Both (Client & Lawyer)</option>
                                                <option value="client">Client Only</option>
                                                <option value="lawyer">Lawyer Only (Private Note)</option>
                                            </select>
                                        </div>
                                        @break
                                        
                                    @case('mark_active')
                                        <p class="text-sm text-gray-600 mb-4">
                                            You are about to mark this case as active. This indicates that the contract has been signed and work on the case has officially begun.
                                        </p>
                                        @break
                                @endswitch
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="submitAction" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    @switch($actionType)
                        @case('accept')
                            Accept Case
                            @break
                        @case('reject')
                            Reject Case
                            @break
                        @case('upload_contract')
                            Upload Contract
                            @break
                        @case('add_update')
                            Add Update
                            @break
                        @case('mark_active')
                            Mark Active
                            @break
                        @default
                            Submit
                    @endswitch
                </button>
                <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div> 