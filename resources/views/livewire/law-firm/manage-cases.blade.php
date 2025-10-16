<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $showArchived ? 'Case Archive' : 'Manage Cases' }}
                </h1>
                <p class="text-gray-600 mt-1">
                    {{ $showArchived ? 'View your archived and completed cases' : 'Manage your law firm\'s legal cases and client requests' }}
                </p>
                    </div>
            
            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <div class="w-full sm:w-auto">
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search cases..." 
                                class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div class="w-full sm:w-auto">
                            <select wire:model.live="status" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full sm:w-auto">
                            <select wire:model.live="priorityFilter" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All Priorities</option>
                                <option value="low">Low Priority</option>
                                <option value="medium">Medium Priority</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">High Priority/Urgent</option>
                            </select>
                        </div>
                <div class="w-full sm:w-auto">
                    <button 
                        wire:click="toggleArchivedView" 
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md {{ $showArchived ? 'bg-gray-600 text-white' : 'bg-white text-gray-700 border-gray-300' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $showArchived ? 'Show Active Cases' : 'Show Case Archive' }}
                    </button>
                </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Case Requests Section -->
                @if(!$showArchived)
                    @include('livewire.law-firm.partials.pending-case-requests')
                @endif

    <!-- Cases Grid -->
    @if($cases->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-6">
            @foreach($cases as $case)
                <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                    <!-- Card Header -->
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $case->title }}</h3>
                                <p class="text-sm text-gray-600 mb-2">Case #{{ $case->case_number }}</p>
                                @if($case->consultation_id)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                                        From consultation
                                    </span>
                                @endif
                                
                                <!-- Client Info -->
                                <div class="flex items-center text-sm text-gray-500 mb-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                    @if ($case->client && $case->client->clientProfile)
                                        {{ $case->client->clientProfile->first_name }} {{ $case->client->clientProfile->last_name }}
                                    @elseif ($case->client)
                                        {{ $case->client->email }}
                                    @else
                                        Unknown Client
                                    @endif
                            </div>
                                
                                <!-- Lawyer Info -->
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-8 0V6a2 2 0 00-2 2v6"></path>
                                    </svg>
                                    @if($case->lawyer_id == auth()->id())
                                        <span class="text-indigo-600 font-medium">Your Firm</span>
                                    @elseif($case->lawyer && $case->lawyer->lawFirmLawyer)
                                        {{ $case->lawyer->lawFirmLawyer->first_name }} {{ $case->lawyer->lawFirmLawyer->last_name }}
                                    @elseif($case->lawyer && $case->lawyer->lawyerProfile)
                                        {{ $case->lawyer->lawyerProfile->first_name }} {{ $case->lawyer->lawyerProfile->last_name }}
                                    @elseif($case->lawyer && $case->lawyer->lawFirmProfile)
                                        {{ $case->lawyer->lawFirmProfile->firm_name }}
                @else
                                        Unknown Lawyer
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Priority Label -->
                                            <div x-data="{ editing: false, label: '{{ $case->case_label ?? '' }}' }">
                                                <div x-show="!editing" @click="editing = true" class="cursor-pointer hover:bg-gray-50 py-1 px-2 rounded flex items-center">
                                                    @if($case->case_label == 'high_priority')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            High Priority
                                        </span>
                                                    @elseif($case->case_label == 'medium_priority')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Medium Priority
                                        </span>
                                                    @elseif($case->case_label == 'low_priority')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Low Priority
                                        </span>
                                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Add label
                                        </span>
                                                    @endif
                                                    <svg class="h-3 w-3 ml-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </div>
                                                <div x-show="editing" @click.away="editing = false" class="flex items-center">
                                    <select x-model="label" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-xs">
                                                        <option value="">No Label</option>
                                                        <option value="high_priority">High Priority</option>
                                                        <option value="medium_priority">Medium Priority</option>
                                                        <option value="low_priority">Low Priority</option>
                                                    </select>
                                                    <button type="button" 
                                                        @click="
                                                            $el.classList.add('bg-yellow-500');
                                                            $el.innerText = 'Saving...';
                                                            $wire.updateCaseLabel({{ $case->id }}, label).then(() => {
                                                                $el.classList.remove('bg-yellow-500');
                                                                $el.innerText = 'Save';
                                                                editing = false;
                                                            });" 
                                                        class="inline-flex items-center ml-1 px-2 py-1 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Save
                                                    </button>
                                                </div>
                                            </div>
                        </div>
                                        </div>

                    <!-- Card Body -->
                    <div class="p-4">
                        <!-- Case Description -->
                        @if($case->description)
                            <p class="text-sm text-gray-600 mb-3">{{ Str::limit($case->description, 100) }}</p>
                                            @endif
                        
                        <!-- Status Badge -->
                        <div class="mb-3">
                                            @switch($case->status)
                                                @case('pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                    @break
                                                @case('accepted')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Accepted
                                                    </span>
                                                    @break
                                                @case('rejected')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Rejected
                                                    </span>
                                                    @break
                                                @case('contract_sent')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                        Contract Sent
                                                    </span>
                                                    @break
                                @case('changes_requested_by_client')
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-200 text-yellow-800">
                                            Changes Requested by Client
                                        </span>
                                        @if($case->requested_changes_details)
                                            <p class="text-xs text-yellow-700 mt-1">Requests: {{ Str::limit($case->requested_changes_details, 50) }}</p>
                                        @endif
                                    </div>
                                    @break
                                @case('contract_revised_sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Contract Revised by Law Firm
                                                    </span>
                                                    @break
                                                @case('contract_signed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                        Contract Signed
                                                    </span>
                                                    @break
                                                @case('active')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                    @break
                                @case('completed')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Completed
                                                    </span>
                                                    @break
                                                @case('closed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Closed
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                                    </span>
                                            @endswitch
                        </div>

                        <!-- Case Details -->
                        <div class="text-sm text-gray-600 mb-4">
                            <div class="flex items-center mb-1">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Created: {{ $case->created_at->format('M d, Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer - Actions -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <div class="flex flex-wrap gap-2">
                            @if($showArchived)
                                <a href="{{ route('law-firm.case-details', $case->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </a>
                            @else
                                @if($case->status == 'pending')
                                    <button wire:click="showAction({{ $case->id }}, 'accept')" 
                                           class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Accept
                                    </button>
                                    <button wire:click="showAction({{ $case->id }}, 'reject')" 
                                           class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                        Reject
                                                    </button>
                                                @endif

                                @if($case->status == 'accepted')
                                    <button wire:click="showAction({{ $case->id }}, 'upload_contract')" 
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                            </svg>
                                        Upload Contract
                                                        </button>
                                @endif
                                
                                @if($case->contract_path)
                                    <button wire:click="viewContract({{ $case->id }})" 
                                           class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                        View Contract
                                    </button>
                                                @endif

                                                @if($case->signature_path)
                                    <button wire:click="viewSignature({{ $case->id }})" 
                                           class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                                        View Signature
                                                    </button>
                                                @endif
                                                
                                @if($case->status === 'changes_requested_by_client')
                                    <button wire:click="openUploadRevisedContractModal({{ $case->id }})" 
                                           class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-md hover:bg-yellow-600 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                                </svg>
                                        Upload Revised
                                                            </button>
                                                @endif

                                @if(in_array($case->status, ['active', 'contract_signed', 'contract_sent']))
                                    <a href="{{ route('law-firm.case-details', $case->id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                        Manage Case
                                                </a>
                                            @endif
                                
                                <!-- Reassign Lawyer Button -->
                                <button wire:click="showReassignModal({{ $case->id }})" 
                                       class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded-md hover:bg-purple-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Reassign
                                </button>
                                
                                <!-- Team Management Button -->
                                <button wire:click="showTeamManagement({{ $case->id }})" 
                                       class="inline-flex items-center px-3 py-1.5 bg-teal-600 text-white text-xs font-medium rounded-md hover:bg-teal-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Team
                                </button>
                                
                                <!-- Case Details Button -->
                                <button wire:click="viewDetails({{ $case->id }})" 
                                       class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-md hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                    Details
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $cases->links() }}
                </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="flex justify-center mb-4">
                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Cases Found</h3>
            <p class="text-gray-600">
                @if($showArchived)
                    No archived cases found matching your criteria.
                @else
                    You don't have any active cases yet. They will appear here when clients request your services or when you create them from consultations.
                @endif
            </p>
        </div>
    @endif

    <!-- Include existing modals -->
    @include('livewire.law-firm.partials.case-contract-modal')
    @include('livewire.law-firm.partials.case-signature-modal')
    @include('livewire.law-firm.partials.case-action-modal')
    @include('livewire.law-firm.partials.case-details-modal')
    @include('livewire.law-firm.partials.reassign-lawyer-modal')
    @include('livewire.law-firm.partials.team-management-modal')
    @include('livewire.law-firm.partials.upload-revised-contract-modal')
    @include('livewire.law-firm.partials.start-case-modal')
    </div>
