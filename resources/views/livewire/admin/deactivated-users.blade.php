<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Account Management</h1>
        <div class="flex items-center space-x-4">
            <select wire:model.debounce.300ms="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Users</option>
                <option value="active">Active Users</option>
                <option value="deactivated">Deactivated Users</option>
            </select>
            <select wire:model.debounce.300ms="role" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Roles</option>
                <option value="client">Clients</option>
                <option value="lawyer">Lawyers</option>
                <option value="law_firm">Law Firms</option>
            </select>
            <input type="text" wire:model.debounce.300ms="search" placeholder="Search users..." 
                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif

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
                        <div class="ml-4">Status</div>
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
                            <div class="ml-10">
                                @if($user->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @elseif($user->status === 'deactivated')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Deactivated
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="pr-10 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button wire:click="viewUser({{ $user->id }})" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-black text-sm font-medium rounded-md">
                                View Details
                            </button>
                            @if($user->status === 'approved')
                                <button wire:click="showDeactivateForm({{ $user->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-black text-sm font-medium rounded-md">
                                    Deactivate
                                </button>
                            @else
                                <button wire:click="showReactivateForm({{ $user->id }})" 
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-black text-sm font-medium rounded-md">
                                    Reactivate
                                </button>
                            @endif
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
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-500">
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
                        <div class="text-sm">
                            <span class="font-semibold text-gray-900">Status:</span>
                            <span class="ml-2">
                                @if($selectedUser->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @elseif($selectedUser->status === 'deactivated')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Deactivated
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="text-sm">
                            <span class="font-semibold text-gray-900">Created At:</span>
                            <span class="ml-2">{{ $selectedUser->created_at->format('M d, Y') }}</span>
                        </div>

                        @if($selectedUser->status === 'deactivated' && $selectedUser->deactivation_reason)
                            <div class="text-sm col-span-2">
                                <span class="font-semibold text-gray-900">Deactivation Reason:</span>
                                <span class="ml-2">{{ $selectedUser->deactivation_reason }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-semibold text-gray-900">Deactivated At:</span>
                                <span class="ml-2">{{ $selectedUser->deactivated_at ? $selectedUser->deactivated_at->format('M d, Y') : 'N/A' }}</span>
                            </div>
                        @endif

                        @if($selectedUser->role->name === 'lawyer')
                            @if($selectedUser->lawyerProfile)
                                <div class="text-sm col-span-2 mt-2 border-t border-gray-200 pt-2">
                                    <span class="font-semibold text-gray-900">Lawyer Profile</span>
                                </div>
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
                                    <span class="font-semibold text-gray-900">Budget Range:</span>
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
                                    <span class="text-gray-500 italic">No lawyer profile information available.</span>
                                </div>
                            @endif
                        @endif

                        @if($selectedUser->role->name === 'client')
                            @if($selectedUser->clientProfile)
                                <div class="text-sm col-span-2 mt-2 border-t border-gray-200 pt-2">
                                    <span class="font-semibold text-gray-900">Client Profile</span>
                                </div>
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
                                    <span class="text-gray-500 italic">No client profile information available.</span>
                                </div>
                            @endif
                        @endif

                        @if($selectedUser->role->name === 'law_firm')
                            @if($selectedUser->lawFirmProfile)
                                <div class="text-sm col-span-2 mt-2 border-t border-gray-200 pt-2">
                                    <span class="font-semibold text-gray-900">Law Firm Profile</span>
                                </div>
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
                                    <span class="font-semibold text-gray-900">Budget Range:</span>
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
                                    <span class="text-gray-500 italic">No law firm profile information available.</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="flex justify-end">
                    @if($selectedUser->status === 'approved')
                        <button wire:click="showDeactivateForm({{ $selectedUser->id }})" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-black text-sm font-medium rounded-md">
                            Deactivate Account
                        </button>
                    @else
                        <button wire:click="showReactivateForm({{ $selectedUser->id }})" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-black text-sm font-medium rounded-md">
                            Reactivate Account
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Deactivate Modal -->
    @if($showDeactivateModal && $selectedUser)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center overflow-y-auto">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/2 max-w-2xl my-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Deactivate Account</h2>
                    <button wire:click="closeDeactivateModal" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mb-4 text-gray-700">
                    You are about to deactivate the account for <span class="font-bold">{{ $selectedUser->email }}</span>. 
                    Please provide a reason for deactivation.
                </p>

                <form wire:submit.prevent="deactivateUser">
                    <div class="mb-6">
                        <label for="deactivationReason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Deactivation</label>
                        <textarea wire:model.defer="deactivationReason" id="deactivationReason" rows="4" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Provide a reason for deactivating this account"></textarea>
                        @error('deactivationReason') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" wire:click="closeDeactivateModal" 
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-black text-sm font-medium rounded-md">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-black text-sm font-medium rounded-md">
                            Confirm Deactivation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Reactivate Modal -->
    @if($showReactivateModal && $selectedUser)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center overflow-y-auto">
            <div class="bg-white p-6 rounded-lg shadow-lg w-1/2 max-w-2xl my-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Reactivate Account</h2>
                    <button wire:click="closeReactivateModal" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="mb-6 text-gray-700">
                    You are about to reactivate the account for <span class="font-bold">{{ $selectedUser->email }}</span>. 
                    This will restore full access to the account. Are you sure?
                </p>

                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="closeReactivateModal" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-black text-sm font-medium rounded-md">
                        Cancel
                    </button>
                    <button type="button" wire:click="reactivateUser" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-black text-sm font-medium rounded-md">
                        Confirm Reactivation
                    </button>
                </div>
            </div>
        </div>
    @endif
</div> 