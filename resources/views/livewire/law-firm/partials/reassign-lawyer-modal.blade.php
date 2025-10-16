<!-- Reassign Lawyer Modal -->
<div x-data="{ show: @entangle('showReassignModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="show" class="fixed inset-0 transition-opacity" aria-hidden="true"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div x-show="show"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Close button (X) in top right corner -->
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button @click="show = false" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Reassign Case
                        </h3>
                        
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-4">
                                Select a lawyer to reassign this case. This will transfer all case responsibilities to the selected lawyer.
                            </p>
                            
                            @if (session()->has('error'))
                                <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            <div class="mb-4">
                                <label for="lawyer-select" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Lawyer
                                </label>
                                <select id="lawyer-select" wire:model="selectedLawyerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="">-- Select a lawyer --</option>
                                    @foreach($firmLawyers as $lawyer)
                                        <option value="{{ $lawyer['id'] }}">
                                            {{ $lawyer['name'] }} {{ $lawyer['is_firm'] ? '(Law Firm)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            @if($selectedCase)
                                <div class="border rounded-md p-3 bg-gray-50 mb-4">
                                    <h4 class="font-medium text-gray-700 mb-2">Case Information</h4>
                                    <p><span class="font-medium">Title:</span> {{ $selectedCase->title }}</p>
                                    <p><span class="font-medium">Case Number:</span> {{ $selectedCase->case_number }}</p>
                                    <p><span class="font-medium">Status:</span> {{ ucfirst($selectedCase->status) }}</p>
                                    
                                    <p class="mt-2"><span class="font-medium">Currently Assigned To:</span></p>
                                    <p class="text-indigo-600">
                                        @if($selectedCase->lawyer_id == auth()->id())
                                            Your Firm
                                        @elseif($selectedCase->lawyer && $selectedCase->lawyer->lawFirmLawyer)
                                            {{ $selectedCase->lawyer->lawFirmLawyer->first_name }} {{ $selectedCase->lawyer->lawFirmLawyer->last_name }}
                                        @elseif($selectedCase->lawyer && $selectedCase->lawyer->lawyerProfile)
                                            {{ $selectedCase->lawyer->lawyerProfile->first_name }} {{ $selectedCase->lawyer->lawyerProfile->last_name }}
                                        @elseif($selectedCase->lawyer && $selectedCase->lawyer->lawFirmProfile)
                                            {{ $selectedCase->lawyer->lawFirmProfile->firm_name }}
                                        @else
                                            Unknown Lawyer
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="reassignLawyer" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Changes
                </button>
                <button @click="show = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div> 