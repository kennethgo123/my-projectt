<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
        <div class="flex items-center space-x-4">
            <select wire:model.debounce.300ms="status" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="deactivated">Deactivated</option>
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

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name/Firm
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Joined
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst($user->role->name) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($user->role->name === 'lawyer' && $user->lawyerProfile)
                                {{ $user->lawyerProfile->first_name }} {{ $user->lawyerProfile->last_name }}
                            @elseif($user->role->name === 'client' && $user->clientProfile)
                                {{ $user->clientProfile->first_name }} {{ $user->clientProfile->last_name }}
                            @elseif($user->role->name === 'law_firm' && $user->lawFirmProfile)
                                {{ $user->lawFirmProfile->firm_name }}
                            @else
                                <span class="text-gray-400 italic">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($user->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @elseif($user->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($user->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @elseif($user->status === 'deactivated')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Deactivated
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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
                                @elseif($selectedUser->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif($selectedUser->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                @elseif($selectedUser->status === 'deactivated')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
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

                        @if($selectedUser->status === 'rejected' && $selectedUser->rejection_reason)
                            <div class="text-sm col-span-2">
                                <span class="font-semibold text-gray-900">Rejection Reason:</span>
                                <span class="ml-2">{{ $selectedUser->rejection_reason }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="font-semibold text-gray-900">Rejected At:</span>
                                <span class="ml-2">{{ $selectedUser->rejected_at ? $selectedUser->rejected_at->format('M d, Y') : 'N/A' }}</span>
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
                    <button wire:click="closeViewModal" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div> 