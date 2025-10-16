<div>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
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

        <!-- Case Header -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-indigo-50">
                <h3 class="text-lg leading-6 font-medium text-indigo-900">
                    Contract Review
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-indigo-600">
                    Case #: {{ $case->case_number }}
                </p>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Contract Preview -->
                <div class="p-6 border-b md:border-b-0 md:border-r border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-medium text-gray-900">Review Contract</h4>
                        @if($case->contract_path)
                            <a href="{{ Storage::url($case->contract_path) }}" 
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Contract
                            </a>
                        @endif
                    </div>
                    
                    @if($case->contract_path)
                        <div class="mb-4">
                            <div class="relative" style="padding-top: 100%;">
                                <iframe 
                                    src="{{ Storage::url($case->contract_path) }}#toolbar=0&embedded=true&navpanes=0&view=fitH" 
                                    class="absolute top-0 left-0 w-full h-full border border-gray-200 rounded"
                                    type="application/pdf"
                                    sandbox="allow-scripts">
                                </iframe>
                            </div>
                            <div class="mt-4 p-4 bg-gray-50 rounded-md">
                                <p class="text-sm text-gray-600">
                                    If the preview is not loading, you can:
                                    <a href="{{ Storage::url($case->contract_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">
                                        open the contract in a new tab
                                    </a>
                                    or use the download button above.
                                </p>
                            </div>
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
                                        Contract document not available. Please contact your lawyer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Signature Area -->
                <div class="p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Contract Actions</h4>
                    
                    @if(in_array($case->status, [\App\Models\LegalCase::STATUS_CONTRACT_SENT, \App\Models\LegalCase::STATUS_CONTRACT_REVISED_SENT]))
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-yellow-800">
                            By signing this contract, you acknowledge that you have read and agree to all terms and conditions outlined in the document. Your e-signature is secured through our encrypted platform and will be used exclusively for this specific contract. For your protection, all signature data is stored in compliance with industry-standard security protocols. Please note that your lawyer or law firm may require an in-person physical signature upon your initial meeting to complete the documentation process. This digital acknowledgment serves as your preliminary agreement to the terms contained herein.
                            </p>
                        </div>
                        
                        <!-- Agreement Checkbox -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="agreement" wire:model.defer="agreementChecked" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="agreement" class="font-medium text-gray-700">I agree to the terms and conditions</label>
                                    <p class="text-gray-500">I have read and understood all terms outlined in this contract.</p>
                                    @error('agreementChecked') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">
                                Your Signature (Draw Below)
                            </label>
                            
                            <div 
                                x-data="{ 
                                    signaturePad: null,
                                    init() {
                                        const canvas = document.getElementById('signature-pad');
                                        this.signaturePad = new SignaturePad(canvas, {
                                            backgroundColor: 'rgb(255, 255, 255)'
                                        });
                                        
                                        window.addEventListener('resize', this.resizeCanvas);
                                        this.resizeCanvas();
                                    },
                                    clear() {
                                        this.signaturePad.clear();
                                    },
                                    resizeCanvas() {
                                        const canvas = document.getElementById('signature-pad');
                                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                                        canvas.width = canvas.offsetWidth * ratio;
                                        canvas.height = canvas.offsetHeight * ratio;
                                        canvas.getContext('2d').scale(ratio, ratio);
                                        this.signaturePad.clear();
                                    },
                                    saveSignature() {
                                        if (this.signaturePad.isEmpty()) {
                                            alert('Please provide your signature first.');
                                            return false;
                                        }
                                        
                                        const dataURL = this.signaturePad.toDataURL();
                                        @this.set('signature', dataURL);
                                        @this.submitSignature();
                                    }
                                }"
                                class="mb-4"
                            >
                                <div class="border rounded-md bg-white">
                                    <canvas id="signature-pad" class="w-full h-40 border-gray-300 rounded-md cursor-crosshair"></canvas>
                                </div>
                                
                                <div class="flex justify-between mt-2">
                                    <button type="button" x-on:click="clear()" class="px-3 py-2 text-sm text-gray-700 hover:text-gray-900">
                                        Clear Signature
                                    </button>
                                    
                                    <button type="button" x-on:click="saveSignature()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <span wire:loading.remove wire:target="submitSignature">
                                            Sign & Accept Contract
                                        </span>
                                        <span wire:loading wire:target="submitSignature" class="inline-flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Processing...
                                        </span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Simple alternative for signature -->
                            <div class="mt-6 border-t border-gray-200 pt-4">
                                <p class="text-sm text-gray-600 mb-4">Please type your full legal name below:</p>
                                
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        wire:model.defer="signatureText" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                        placeholder="Type your full legal name"
                                    >
                                    @error('signatureText') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Other Actions</h4>
                                <div class="flex space-x-3">
                                    <button type="button" wire:click="openRequestChangesModal" class="inline-flex items-center px-4 py-2 border border-orange-500 rounded-md shadow-sm text-sm font-medium text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Request Changes
                                    </button>
                                    <button type="button" wire:click="openRejectModal" class="inline-flex items-center px-4 py-2 border border-red-500 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        Reject Contract
                                    </button>
                                </div>
                            </div>
                        </div>
                    @elseif($case->contract_status === 'signed' && $case->status !== 'changes_requested_by_client')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <p class="text-green-800 font-medium">Contract has been signed</p>
                            </div>
                            <p class="mt-2 text-sm text-green-700">
                                You signed this contract on {{ $case->contract_signed_at ? $case->contract_signed_at->format('M d, Y h:i A') : 'N/A' }}.
                            </p>
                            
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="h-6 w-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-blue-800 font-medium">What happens next?</p>
                                </div>
                                <p class="mt-2 text-sm text-blue-700">
                                    Kindly wait for your lawyer to set up your case. Your lawyer will organize the case timeline, schedule important events, and add necessary tasks. You'll be able to track your case progress once setup is complete.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-gray-700">
                                This contract is not currently available for signing.
                                Current status: <span class="font-medium">{{ ucfirst($case->contract_status) }}</span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Case Details Summary -->
            <div class="border-t border-gray-200 px-6 py-4">
                <h4 class="text-lg font-medium text-gray-900 mb-2">Case Summary</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <h5 class="text-sm font-medium text-gray-500">Case Information</h5>
                        <dl class="mt-2 text-sm text-gray-700">
                            <div class="mt-1">
                                <dt class="inline font-medium">Title:</dt>
                                <dd class="inline ml-1">{{ $case->title }}</dd>
                            </div>
                            <div class="mt-1">
                                <dt class="inline font-medium">Created on:</dt>
                                <dd class="inline ml-1">{{ $case->created_at->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                    
                    <div>
                        <h5 class="text-sm font-medium text-gray-500">Lawyer Information</h5>
                        <dl class="mt-2 text-sm text-gray-700">
                            <div class="mt-1">
                                <dt class="inline font-medium">Name:</dt>
                                <dd class="inline ml-1">{{ $case->lawyer->name }}</dd>
                            </div>
                            @if($case->lawyer->lawyerProfile)
                                <div class="mt-1">
                                    <dt class="inline font-medium">Specialization:</dt>
                                    <dd class="inline ml-1">{{ $case->lawyer->lawyerProfile->specialization ?? 'Not specified' }}</dd>
                                </div>
                                <div class="mt-1">
                                    <dt class="inline font-medium">Contact:</dt>
                                    <dd class="inline ml-1">{{ $case->lawyer->email }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('client.cases') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                        &larr; Back to Cases
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    @endpush

    <!-- Reject Contract Modal -->
    @if($showRejectModal)
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="submitRejection">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Reject Contract
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Please provide your reason for rejecting this contract. This will be shared with the legal professional.
                                    </p>
                                    <div class="mt-4">
                                        <textarea wire:model.defer="rejectionReason" rows="4" class="shadow-sm focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Reason for rejection..."></textarea>
                                        @error('rejectionReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="submitRejection">Reject Contract</span>
                            <span wire:loading wire:target="submitRejection">Rejecting...</span>
                        </button>
                        <button type="button" wire:click="$set('showRejectModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Request Changes Modal -->
    @if($showRequestChangesModal)
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="submitRequestedChanges">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Request Changes to Contract
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Please detail the changes you are requesting for this contract. This will be shared with the legal professional.
                                    </p>
                                    <div class="mt-4">
                                        <textarea wire:model.defer="requestedChanges" rows="5" class="shadow-sm focus:ring-orange-500 focus:border-orange-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Specify requested changes..."></textarea>
                                        @error('requestedChanges') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span wire:loading.remove wire:target="submitRequestedChanges">Submit Request</span>
                            <span wire:loading wire:target="submitRequestedChanges">Submitting...</span>
                        </button>
                        <button type="button" wire:click="$set('showRequestChangesModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div> 