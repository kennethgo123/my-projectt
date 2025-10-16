<div>
    <!-- Rating Modal -->
    <div x-data="{ showModal: @entangle('showRatingModal') }" 
         x-show="showModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="rate-lawyer-modal" 
         role="dialog" 
         aria-modal="true">
        
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 transition-opacity" 
                 aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal Panel -->
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button wire:click="closeModal" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="sr-only">Close</span>
                        <!-- Heroicon name: outline/x -->
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                @if($legalCase)
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="rate-lawyer-modal">
                            Rate Your Lawyer
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Please rate your experience with the lawyer for this case.
                            </p>
                            <div class="mt-4">
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Lawyer: 
                                        <span class="font-semibold">
                                            {{ $legalCase->lawyer->lawyerProfile ? $legalCase->lawyer->lawyerProfile->first_name . ' ' . $legalCase->lawyer->lawyerProfile->last_name : $legalCase->lawyer->name }}
                                        </span>
                                    </p>
                                    <p class="text-sm font-medium text-gray-700 mb-2">Case: 
                                        <span class="font-semibold">
                                            {{ $legalCase->title }} ({{ $legalCase->case_number }})
                                        </span>
                                    </p>
                                </div>
                                
                                <!-- Star Rating -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating:</label>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" wire:click="setRating({{ $i }})" class="p-1 focus:outline-none">
                                                <svg class="w-8 h-8 {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400" 
                                                     fill="currentColor" 
                                                     viewBox="0 0 20 20" 
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            </button>
                                        @endfor
                                    </div>
                                    @error('rating') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                
                                <!-- Feedback -->
                                <div class="mb-4">
                                    <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">Feedback:</label>
                                    <textarea id="feedback" 
                                              wire:model="feedback" 
                                              rows="4" 
                                              class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('feedback') border-red-500 @enderror" 
                                              placeholder="Please share your experience with this lawyer..."></textarea>
                                    @error('feedback') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button wire:click="submitRating" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Submit Rating
                    </button>
                    <button wire:click="closeModal" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
