<div>
    <!-- Debug JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🔍 Case details page loaded');
            
            // Add a global helper function to manually trigger acceptance
            window.manuallyAcceptContract = function() {
                console.log('🔄 Manually triggering contract acceptance');
                try {
                    // Find the Livewire component
                    const componentId = document.querySelector('[wire\\:id]').getAttribute('wire:id');
                    console.log('📦 Component ID:', componentId);
                    
                    if (window.Livewire) {
                        console.log('✅ Livewire object found');
                        const component = window.Livewire.find(componentId);
                        
                        if (component) {
                            console.log('✅ Component found, calling forceAcceptContract');
                            component.call('forceAcceptContract');
                            return true;
                        } else {
                            console.error('❌ Component not found with ID:', componentId);
                        }
                    } else {
                        console.error('❌ Livewire object not available');
                    }
                } catch (error) {
                    console.error('❌ Error triggering contract acceptance:', error);
                }
                return false;
            };
            
            // Add listener to all form submits
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    console.log('📝 Form submitted:', this.id || 'unnamed form');
                });
            });
        });
    </script>
    
    <!-- Manual Debug Button (always visible) -->
    <div class="fixed bottom-4 right-4 z-50">
        <button 
            type="button"
            onclick="manuallyAcceptContract()"
            class="px-4 py-2 bg-red-600 text-white rounded-lg shadow-lg hover:bg-red-700"
        >
            💥 Emergency Accept
        </button>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Page header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Case Details
                </h2>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Case Overview -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Case Overview
                        </h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Case Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $case->case_number }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Title</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $case->title }}</dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $case->description }}</dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @switch($case->status)
                                @case('pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @break
                                @case('accepted')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Accepted</span>
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
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($case->status) }}</span>
                            @endswitch
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Priority</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    @switch($case->priority)
                                        @case('low')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Low Priority</span>
                                            @break
                                        @case('medium')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Medium Priority</span>
                                            @break
                                        @case('high')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">High Priority</span>
                                            @break
                                        @case('urgent')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">High Priority/Urgent</span>
                                            @break
                                        @default
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($case->priority) }}</span>
                                    @endswitch
                                </dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $case->created_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

        @if ($case->status === App\Models\LegalCase::STATUS_CONTRACT_SIGNED || $case->status === App\Models\LegalCase::STATUS_ACTIVE)
            @if($case->status !== \App\Models\LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT)
                <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-4 mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <svg class="fill-current h-6 w-6 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M10 20a10 10 0 110-20 10 10 0 010 20zm0-2a8 8 0 100-16 8 8 0 000 16zm-1-6a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1zm0-4a1 1 0 112 0v2a1 1 0 11-2 0V8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold">Case Setup In Progress</p>
                            <p class="text-sm">Kindly wait for your lawyer to set up your case. Your lawyer will organize the case timeline, schedule important events, and add necessary tasks. You'll be able to track your case progress once setup is complete.</p>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Lawyer Information -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Lawyer Information
                            </h3>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $case->lawyer->first_name }} {{ $case->lawyer->last_name }}
                        </dd>
                    </div>
                    @if($case->lawyer->lawyerProfile)
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Specialization</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $case->lawyer->lawyerProfile->specialization ?? 'Not specified' }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Contact</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <div>Email: {{ $case->lawyer->email }}</div>
                                <div>Phone: {{ $case->lawyer->lawyerProfile->phone ?? 'Not provided' }}</div>
                            </dd>
                                                </div>
                                            @endif
                </dl>
                                        </div>
                            </div>

        <!-- Contract Section -->
        @if($case->contract_path)
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                        <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Contract</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Status: {{ ucfirst($case->contract_status) }}
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <div class="px-4 py-5 sm:p-6">
                        <!-- Contract Preview -->
                        <div class="mb-4">
                            <iframe src="{{ Storage::url($case->contract_path) }}" class="w-full h-96 border-0"></iframe>
                        </div>

                        <!-- Contract Actions -->
                        @if($case->status === 'contract_sent' && $case->contract_status === 'sent')
                            <div class="mt-4 space-y-4">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <h3 class="text-lg font-medium text-yellow-900 mb-2">Contract Review</h3>
                                    <p class="text-sm text-yellow-700 mb-4">
                                        Please review the contract carefully before accepting. By accepting, you agree to all terms and conditions outlined in the document above.
                                    </p>
                                    
                                    <div class="flex flex-col space-y-3">
                                        <button 
                                            wire:click="directAcceptContract"
                                            wire:loading.attr="disabled"
                                            wire:target="directAcceptContract"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                                        >
                                            <span wire:loading.remove wire:target="directAcceptContract">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                                Accept Contract
                                            </span>
                                            <span wire:loading wire:target="directAcceptContract" class="inline-flex items-center">
                                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Processing...
                                            </span>
                                        </button>

                                        <button 
                                            wire:click="openNegotiateModal"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                        </svg>
                                            Request Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
        </div>
    </div>
        @endif

        <!-- Case Updates -->
        @if($case->caseUpdates->isNotEmpty())
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Case Updates</h3>
                </div>
                <div class="border-t border-gray-200">
                    <ul role="list" class="divide-y divide-gray-200">
                        @foreach($case->caseUpdates->sortByDesc('created_at') as $update)
                            <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $update->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $update->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                                <p class="mt-2 text-sm text-gray-500">{{ $update->content }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <!-- Negotiate Contract Modal -->
    <div x-data="{ show: @entangle('showNegotiateModal') }">
        <div
            x-show="show"
            x-cloak
            class="fixed z-10 inset-0 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div
                    x-show="show"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                ></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    x-show="show"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                >
                    <div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Request Contract Changes
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Please describe the changes you would like to request. Be as specific as possible.
                                </p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <textarea
                                wire:model.defer="negotiationTerms"
                                rows="4"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                placeholder="Enter your requested changes here..."
                            ></textarea>
                            @error('negotiationTerms')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button
                            wire:click="submitNegotiation"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                        >
                            Submit Changes
                        </button>
                        <button
                            type="button"
                            wire:click="$set('showNegotiateModal', false)"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
                </div>
            </div>
</div> 