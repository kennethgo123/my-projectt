@php
    use Illuminate\Support\Facades\DB;
@endphp
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pending Users</h1>
        <div class="flex items-center">
            <input type="text" wire:model.debounce.300ms="search" placeholder="Search users..." 
                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">
                        <div class="ml-4">Email</div>
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                        <div class="ml-4">Role</div>
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                        <div class="ml-4">Date Applied</div>
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <div class="ml-10">{{ $user->email }}</div>
                        </td>
                        <td class="py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="ml-10">{{ ucfirst($user->role->name) }}</div>
                        </td>
                        <td class="py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="ml-10">{{ $user->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="pr-10 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="viewUser({{ $user->id }})" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md">
                                View Details
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <!-- View Modal -->
    @if($showViewModal && $selectedUser)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center overflow-y-auto">
            <div class="bg-white p-6 rounded-lg shadow-lg w-3/4 max-w-4xl my-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">User Information</h2>
                    <button x-data="{}" x-on:click="$wire.closeViewModal()" type="button" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-sm">
                            <span class="font-semibold text-gray-900">Email:</span>
                            <span class="ml-2">{{ $selectedUser->email }}</span>
                        </div>
                        <div class="text-sm">
                            <span class="font-semibold text-gray-900">Role:</span>
                            <span class="ml-2">{{ ucfirst($selectedUser->role->name) }}</span>
                        </div>

                        @if(!$selectedUser->profile_completed)
                            <div class="text-sm col-span-2 mt-4 mb-4">
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                This user has not completed their profile yet.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($selectedUser->role->name === 'lawyer')
                            @if($selectedUser->lawFirmLawyer)
                                <!-- Law Firm Lawyer Information -->
                                <div class="text-sm col-span-2 mt-4">
                                    <h3 class="font-bold text-gray-900 mb-2">Law Firm Lawyer Information</h3>
                                </div>
                                
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Name:</span>
                                    <span class="ml-2">{{ $selectedUser->name }}</span>
                                </div>

                                @if($selectedUser->lawFirmLawyer && $selectedUser->lawFirmLawyer->lawFirm)
                                    <div class="text-sm col-span-2 mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                        <span class="font-bold text-blue-900">Associated Law Firm:</span>
                                        <span class="ml-2 text-blue-800">{{ $selectedUser->lawFirmLawyer->lawFirm->firm_name }}</span>
                                        <div class="mt-2">
                                            <span class="font-semibold text-blue-900">Professional Fee Range:</span>
                                            <span class="ml-2 text-blue-800">₱{{ number_format($selectedUser->lawFirmLawyer->min_budget, 2) }} - ₱{{ number_format($selectedUser->lawFirmLawyer->max_budget, 2) }}</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Contact Number:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmLawyer->contact_number }}</span>
                                </div>
                                <div class="text-sm col-span-2">
                                    <span class="font-semibold text-gray-900">Address:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmLawyer->address }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">City:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmLawyer->city }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Valid ID Type:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmLawyer->valid_id_type }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Valid ID:</span>
                                    <a href="{{ asset('storage/' . $selectedUser->lawFirmLawyer->valid_id_file) }}" 
                                        target="_blank" 
                                        class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                        View Document
                                    </a>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Bar Admission Type:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmLawyer->bar_admission_type }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Bar Admission File:</span>
                                    <a href="{{ asset('storage/' . $selectedUser->lawFirmLawyer->bar_admission_file) }}" 
                                        target="_blank" 
                                        class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                        View Document
                                    </a>
                                </div>
                                <div class="text-sm col-span-2">
                                    <span class="font-semibold text-gray-900">Professional Fee Range:</span>
                                    <span class="ml-2">
                                        ₱{{ number_format($selectedUser->lawFirmLawyer->min_budget, 2) }} - 
                                        ₱{{ number_format($selectedUser->lawFirmLawyer->max_budget, 2) }}
                                    </span>
                                </div>
                                @if($selectedUser->lawFirmLawyer->services)
                                    <div class="text-sm col-span-2">
                                        <span class="font-semibold text-gray-900">Services:</span>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($selectedUser->lawFirmLawyer->services as $service)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @elseif($selectedUser->lawyerProfile)
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Name:</span>
                                    <span class="ml-2">
                                        {{ $selectedUser->lawyerProfile->first_name }}
                                        {{ $selectedUser->lawyerProfile->middle_name }}
                                        {{ $selectedUser->lawyerProfile->last_name }}
                                    </span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Contact Number:</span>
                                    <span class="ml-2">{{ $selectedUser->lawyerProfile->contact_number }}</span>
                                </div>
                                <div class="text-sm col-span-2">
                                    <span class="font-semibold text-gray-900">Address:</span>
                                    <span class="ml-2">{{ $selectedUser->lawyerProfile->address }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">City:</span>
                                    <span class="ml-2">{{ $selectedUser->lawyerProfile->city }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Valid ID Type:</span>
                                    <span class="ml-2">{{ $selectedUser->lawyerProfile->valid_id_type }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Valid ID:</span>
                                    <a href="{{ asset('storage/' . $selectedUser->lawyerProfile->valid_id_file) }}" 
                                        target="_blank" 
                                        class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                        View Document
                                    </a>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Bar Admission Type:</span>
                                    <span class="ml-2">{{ $selectedUser->lawyerProfile->bar_admission_type }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Bar Admission File:</span>
                                    <a href="{{ asset('storage/' . $selectedUser->lawyerProfile->bar_admission_file) }}" 
                                        target="_blank" 
                                        class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                        View Document
                                    </a>
                                </div>
                                <div class="text-sm col-span-2">
                                    <span class="font-semibold text-gray-900">Professional Fee Range:</span>
                                    <span class="ml-2">
                                        ₱{{ number_format($selectedUser->lawyerProfile->min_budget, 2) }} - 
                                        ₱{{ number_format($selectedUser->lawyerProfile->max_budget, 2) }}
                                    </span>
                                </div>
                                @if($selectedUser->lawyerProfile->services)
                                    <div class="text-sm col-span-2">
                                        <span class="font-semibold text-gray-900">Services:</span>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($selectedUser->lawyerProfile->services as $service)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-sm col-span-2">
                                    <span class="text-gray-500 italic">No lawyer profile information available yet.</span>
                                </div>
                            @endif
                        @endif

                        @if($selectedUser->role->name === 'client')
                            @if($selectedUser->clientProfile)
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Name:</span>
                                    <span class="ml-2">
                                        {{ $selectedUser->clientProfile->first_name }}
                                        {{ $selectedUser->clientProfile->middle_name }}
                                        {{ $selectedUser->clientProfile->last_name }}
                                    </span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Contact Number:</span>
                                    <span class="ml-2">{{ $selectedUser->clientProfile->contact_number }}</span>
                                </div>
                                <div class="text-sm col-span-2">
                                    <span class="font-semibold text-gray-900">Address:</span>
                                    <span class="ml-2">{{ $selectedUser->clientProfile->address }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">City:</span>
                                    <span class="ml-2">{{ $selectedUser->clientProfile->city }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Valid ID Type:</span>
                                    <span class="ml-2">{{ $selectedUser->clientProfile->valid_id_type }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Valid ID:</span>
                                    <a href="{{ asset('storage/' . $selectedUser->clientProfile->valid_id_file) }}" 
                                        target="_blank" 
                                        class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                        View Document
                                    </a>
                                </div>
                            @else
                                <div class="text-sm col-span-2">
                                    <span class="text-gray-500 italic">No client profile information available yet.</span>
                                </div>
                            @endif
                        @endif

                        @if($selectedUser->role->name === 'law_firm')
                            @if($selectedUser->lawFirmProfile)
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Firm Name:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmProfile->firm_name }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Contact Number:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmProfile->contact_number }}</span>
                                </div>
                                <div class="text-sm col-span-2">
                                    <span class="font-semibold text-gray-900">Address:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmProfile->address }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">City:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmProfile->city }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Registration Type:</span>
                                    <span class="ml-2">{{ $selectedUser->lawFirmProfile->registration_type }}</span>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">Registration Certificate:</span>
                                    <a href="{{ asset('storage/' . $selectedUser->lawFirmProfile->registration_certificate_file) }}" 
                                        target="_blank" 
                                        class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                        View Document
                                    </a>
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-900">BIR Certificate:</span>
                                    <a href="{{ asset('storage/' . $selectedUser->lawFirmProfile->bir_certificate_file) }}" 
                                        target="_blank" 
                                        class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                        View Document
                                    </a>
                                </div>
                                <div class="text-sm col-span-2">
                                    <span class="font-semibold text-gray-900">Professional Fee Range:</span>
                                    <span class="ml-2">
                                        ₱{{ number_format($selectedUser->lawFirmProfile->min_budget, 2) }} - 
                                        ₱{{ number_format($selectedUser->lawFirmProfile->max_budget, 2) }}
                                    </span>
                                </div>
                                @if($selectedUser->lawFirmProfile->services)
                                    <div class="text-sm col-span-2">
                                        <span class="font-semibold text-gray-900">Services:</span>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($selectedUser->lawFirmProfile->services as $service)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $service->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-sm col-span-2">
                                    <span class="text-gray-500 italic">No law firm profile information available yet.</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button x-data="{}" x-on:click="$wire.closeViewModal()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-black bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Close
                    </button>
                    <button x-data="{}" x-on:click="$wire.approveUser({{ $selectedUser->id }})" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-black text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Approve
                    </button>
                    <button 
                        x-data="{}" 
                        x-on:click="$dispatch('open-reject-modal', { userId: {{ $selectedUser->id }} })"
                        type="button" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Reject
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Reject Modal -->
    @if($showRejectModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center overflow-y-auto z-50 reject-modal">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/2 max-w-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Reject User Application</h3>
                    <button wire:click="closeRejectModal" type="button" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @if($selectedUser)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                You are about to reject <strong>{{ $selectedUser->email }}</strong>'s application. This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Rejection <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">This reason will be included in the email sent to the applicant.</p>
                    <textarea
                        id="rejectionReason"
                        wire:model="rejectionReason"
                        rows="4"
                        class="shadow-sm block w-full focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md"
                        placeholder="Please provide a detailed explanation for rejecting this application..."></textarea>
                    @error('rejectionReason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <button x-data="{}" x-on:click="$wire.closeRejectModal()" type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button x-data="{}" x-on:click="$wire.rejectUser()" type="button"
                        class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Confirm Rejection
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="fixed bottom-4 right-4">
            <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('message') }}
            </div>
        </div>
    @endif

    <!-- Debug Scripts -->
    <script>
        document.addEventListener('livewire:init', () => {
            // All custom JavaScript click handlers have been removed from here.
            // We will rely on x-on:click="$wire.method()" for interactions.
            console.log('Livewire initialized. Custom click handlers removed.');
        });
    </script>
</div> 