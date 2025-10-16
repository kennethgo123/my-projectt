<!-- Start Case Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: {{ $showStartCaseModal ? 'block' : 'none' }}">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Start Case</h3>
                <button wire:click="$set('showStartCaseModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4">
                <form wire:submit.prevent="startCase">
                    <div class="space-y-4">
                        <!-- Case Title -->
                        <div>
                            <label for="caseTitle" class="block text-sm font-medium text-gray-700">Case Title</label>
                            <input 
                                type="text" 
                                id="caseTitle" 
                                wire:model="caseTitle" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter a title for this case"
                            >
                            @error('caseTitle')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Case Description -->
                        <div>
                            <label for="caseDescription" class="block text-sm font-medium text-gray-700">Case Description</label>
                            <textarea 
                                id="caseDescription" 
                                wire:model="caseDescription" 
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Describe the case details..."
                            ></textarea>
                            @error('caseDescription')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contract Upload -->
                        <div>
                            <label for="contractDocument" class="block text-sm font-medium text-gray-700">Contract Document</label>
                            <div class="mt-1 flex items-center">
                                <label class="block w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                                    <span>{{ $contractDocument ? 'Change Contract' : 'Upload Contract' }}</span>
                                    <input id="contractDocument" type="file" wire:model="contractDocument" class="sr-only" accept=".pdf,.doc,.docx">
                                </label>
                            </div>
                            <div class="mt-2">
                                @if($contractDocument)
                                    <p class="text-sm text-gray-600">{{ $contractDocument->getClientOriginalName() }}</p>
                                @else
                                    <p class="text-sm text-gray-500">PDF, DOC, or DOCX up to 10MB</p>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Upload a contract that will be sent to the client for review and signature.
                            </p>
                            @error('contractDocument') 
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                        <button 
                            type="button" 
                            wire:click="$set('showStartCaseModal', false)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Start Case</span>
                            <span wire:loading>Starting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 