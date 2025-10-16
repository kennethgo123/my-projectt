@if($showModal && $selectedUser)
<div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            User Details
                        </h3>
                        <div class="mt-4 space-y-4">
                            <!-- Basic Information -->
                            <div>
                                <h4 class="font-semibold">Basic Information</h4>
                                <p><span class="text-gray-600">Email:</span> {{ $selectedUser->email }}</p>
                                <p><span class="text-gray-600">Role:</span> {{ ucfirst($selectedUser->role->name) }}</p>
                            </div>

                            <!-- Profile Information -->
                            @if($userType === 'client' && $selectedUser->clientProfile)
                                <div>
                                    <h4 class="font-semibold">Profile Information</h4>
                                    <p><span class="text-gray-600">Name:</span> {{ $selectedUser->clientProfile->first_name }} {{ $selectedUser->clientProfile->middle_name }} {{ $selectedUser->clientProfile->last_name }}</p>
                                    <p><span class="text-gray-600">Contact Number:</span> {{ $selectedUser->clientProfile->contact_number }}</p>
                                    <p><span class="text-gray-600">Address:</span> {{ $selectedUser->clientProfile->address }}</p>
                                    <p><span class="text-gray-600">City:</span> {{ $selectedUser->clientProfile->city }}</p>
                                    <p><span class="text-gray-600">Valid ID Type:</span> {{ $selectedUser->clientProfile->valid_id_type }}</p>
                                    @if($selectedUser->clientProfile->valid_id_file)
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($selectedUser->clientProfile->valid_id_file) }}" 
                                               target="_blank"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View Valid ID
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($userType === 'lawyer' && $selectedUser->lawyerProfile)
                                <div>
                                    <h4 class="font-semibold">Profile Information</h4>
                                    <p><span class="text-gray-600">Name:</span> {{ $selectedUser->lawyerProfile->first_name }} {{ $selectedUser->lawyerProfile->middle_name }} {{ $selectedUser->lawyerProfile->last_name }}</p>
                                    <p><span class="text-gray-600">Contact Number:</span> {{ $selectedUser->lawyerProfile->contact_number }}</p>
                                    <p><span class="text-gray-600">Address:</span> {{ $selectedUser->lawyerProfile->address }}</p>
                                    <p><span class="text-gray-600">City:</span> {{ $selectedUser->lawyerProfile->city }}</p>
                                    <p><span class="text-gray-600">Bar Admission Type:</span> {{ $selectedUser->lawyerProfile->bar_admission_type }}</p>
                                    <p><span class="text-gray-600">Professional Fee Range:</span> ₱{{ number_format($selectedUser->lawyerProfile->min_budget, 2) }} - ₱{{ number_format($selectedUser->lawyerProfile->max_budget, 2) }}</p>
                                    
                                    <div class="mt-2">
                                        <h5 class="font-medium text-gray-700">Services Offered:</h5>
                                        <div class="mt-1 flex flex-wrap gap-2">
                                            @foreach($selectedUser->lawyerProfile->services as $service)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-2">
                                        <div>
                                            <a href="{{ Storage::url($selectedUser->lawyerProfile->valid_id_file) }}" 
                                               target="_blank"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View Valid ID
                                            </a>
                                        </div>
                                        <div>
                                            <a href="{{ Storage::url($selectedUser->lawyerProfile->bar_admission_file) }}" 
                                               target="_blank"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View Bar Admission Document
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($userType === 'law_firm' && $selectedUser->lawFirmProfile)
                                <div>
                                    <h4 class="font-semibold">Profile Information</h4>
                                    <p><span class="text-gray-600">Firm Name:</span> {{ $selectedUser->lawFirmProfile->firm_name }}</p>
                                    <p><span class="text-gray-600">Contact Number:</span> {{ $selectedUser->lawFirmProfile->contact_number }}</p>
                                    <p><span class="text-gray-600">Address:</span> {{ $selectedUser->lawFirmProfile->address }}</p>
                                    <p><span class="text-gray-600">City:</span> {{ $selectedUser->lawFirmProfile->city }}</p>
                                    <p><span class="text-gray-600">Registration Type:</span> {{ $selectedUser->lawFirmProfile->registration_type }}</p>
                                    <p><span class="text-gray-600">Professional Fee Range:</span> ₱{{ number_format($selectedUser->lawFirmProfile->min_budget, 2) }} - ₱{{ number_format($selectedUser->lawFirmProfile->max_budget, 2) }}</p>

                                    <div class="mt-2">
                                        <h5 class="font-medium text-gray-700">Services Offered:</h5>
                                        <div class="mt-1 flex flex-wrap gap-2">
                                            @foreach($selectedUser->lawFirmProfile->services as $service)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="mt-4 space-y-2">
                                        <div>
                                            <a href="{{ Storage::url($selectedUser->lawFirmProfile->registration_certificate_file) }}" 
                                               target="_blank"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View Registration Certificate
                                            </a>
                                        </div>
                                        <div>
                                            <a href="{{ Storage::url($selectedUser->lawFirmProfile->bir_certificate_file) }}" 
                                               target="_blank"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View BIR Certificate
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="approveUser" type="button" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-500 text-base font-medium text-white hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Approve
                </button>
                <button wire:click="showRejectModal({{ $selectedUser->id }})" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Reject
                </button>
                <button wire:click="closeModal" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif 