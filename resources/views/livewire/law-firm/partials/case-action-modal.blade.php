<!-- Case Action Modal -->
<div x-data="{ open: @entangle('showActionModal') }" x-show="open" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="action-modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        @if($selectedCase)
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="action-modal-title">
                                @switch($actionType)
                                    @case('upload_contract')
                                        Upload Contract
                                        @break
                                    @case('add_update')
                                        Add Case Update
                                        @break
                                    @case('mark_complete')
                                        Mark Case as Complete
                                        @break
                                    @default
                                        Case Action
                                @endswitch
                            </h3>
                            
                            <div class="mt-4">
                                <div class="text-sm text-gray-500 mb-4">
                                    <p><strong>Case:</strong> {{ $selectedCase->case_number }} - {{ $selectedCase->title }}</p>
                                    <p><strong>Client:</strong> 
                                        @if($selectedCase->client && $selectedCase->client->clientProfile)
                                            {{ $selectedCase->client->clientProfile->first_name }} {{ $selectedCase->client->clientProfile->last_name }}
                                        @elseif($selectedCase->client)
                                            {{ $selectedCase->client->email }}
                                        @else
                                            Unknown Client
                                        @endif
                                    </p>
                                </div>
                                
                                @switch($actionType)
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
                                            <label for="update_title" class="block text-sm font-medium text-gray-700 mb-1">Update Title</label>
                                            <input type="text" wire:model="update_title" id="update_title" class="w-full border border-gray-300 rounded-md py-2 px-3">
                                            @error('update_title')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-4">
                                            <label for="update_content" class="block text-sm font-medium text-gray-700 mb-1">Update Content</label>
                                            <textarea wire:model="update_content" id="update_content" rows="4" class="w-full border border-gray-300 rounded-md py-2 px-3"></textarea>
                                            @error('update_content')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="mb-4">
                                            <label for="update_visibility" class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                                            <select wire:model="update_visibility" id="update_visibility" class="w-full border border-gray-300 rounded-md py-2 px-3">
                                                <option value="both">Both Client and Team</option>
                                                <option value="team">Team Only</option>
                                            </select>
                                            @error('update_visibility')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        @break
                                        
                                    @case('mark_complete')
                                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-yellow-700">
                                                        <strong>Warning:</strong> Marking this case as complete will archive it and make it read-only. This action cannot be undone.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            Are you sure you want to mark this case as complete? This will notify the client that the case has been completed.
                                        </p>
                                        @break
                                        
                                    @default
                                        <p class="text-sm text-gray-600">
                                            Please select an action to perform on this case.
                                        </p>
                                @endswitch
                            </div>
                        @else
                            <p class="text-red-500">No case selected.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="submitAction" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    @switch($actionType)
                        @case('upload_contract')
                            Upload Contract
                            @break
                        @case('add_update')
                            Add Update
                            @break
                        @case('mark_complete')
                            Mark Complete
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