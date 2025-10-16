@php
    use App\Models\LegalCase;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Auth;
@endphp
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $showArchived ? 'Case Archive' : 'Manage Cases' }}
                </h1>
                <p class="text-gray-600 mt-1">
                    {{ $showArchived ? 'View your archived and completed cases' : 'Manage your active legal cases and client requests' }}
                </p>
            </div>
            
            <!-- Search and Filters -->
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search cases..." 
                        class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>
                <div>
                    <select wire:model.live="status" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button 
                        wire:click="toggleArchivedView" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md {{ $showArchived ? 'bg-gray-600 text-white' : 'bg-white text-gray-700 border-gray-300' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $showArchived ? 'Show Active Cases' : 'Show Case Archive' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Case Requests Section -->
    @if(!$showArchived)
        @include('livewire.lawyer.partials.pending-case-requests')
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
                                
                                <!-- Client Info -->
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $case->client->clientProfile->first_name ?? 'N/A' }} {{ $case->client->clientProfile->last_name ?? '' }}
                                </div>
                            </div>
                            
                            <!-- Priority Label -->
                            <div x-data="{ editing: false, label: @js($case->case_label ?? '') }">
                                <div x-show="!editing" class="flex items-center">
                                    @if($case->case_label === 'high_priority')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            High Priority
                                        </span>
                                    @elseif($case->case_label === 'medium_priority')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Medium Priority
                                        </span>
                                    @elseif($case->case_label === 'low_priority')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Low Priority
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            No Label
                                        </span>
                                    @endif
                                    
                                    @if(!$showArchived)
                                        <button @click="editing = true" class="ml-2 text-gray-400 hover:text-gray-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                <div x-show="editing" class="flex items-center">
                                    <select x-model="label" class="block w-full py-1.5 text-xs border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                                        <option value="">No Label</option>
                                        <option value="high_priority">High Priority</option>
                                        <option value="medium_priority">Medium Priority</option>
                                        <option value="low_priority">Low Priority</option>
                                    </select>
                                    <button @click="editing = false; $wire.updateCaseLabel({{ $case->id }}, label)" class="ml-2 text-green-600 hover:text-green-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    <button @click="editing = false" class="ml-1 text-red-600 hover:text-red-800">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4">
                        <!-- Status Badge -->
                        <div class="mb-3">
                            @if($case->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($case->status === 'case_request_sent_by_client')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    Case Request Sent By Client
                                </span>
                            @elseif($case->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @elseif($case->status === 'closed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Closed
                                </span>
                            @elseif($case->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @elseif($case->status === 'contract_sent')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Contract Sent
                                </span>
                            @elseif($case->status === 'contract_signed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    Contract Signed
                                </span>
                            @elseif($case->status === 'contract_rejected_by_client')
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-800">
                                        Contract Rejected by Client
                                    </span>
                                    @if($case->rejection_reason)
                                        <p class="text-xs text-red-700 mt-1">Reason: {{ Str::limit($case->rejection_reason, 50) }}</p>
                                    @endif
                                </div>
                            @elseif($case->status === 'changes_requested_by_client')
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-200 text-yellow-800">
                                        Changes Requested by Client
                                    </span>
                                    @if($case->requested_changes_details)
                                        <p class="text-xs text-yellow-700 mt-1">Requests: {{ Str::limit($case->requested_changes_details, 50) }}</p>
                                    @endif
                                </div>
                            @elseif($case->status === 'contract_revised_sent')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Contract Revised by Lawyer
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                </span>
                            @endif
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
                                <a href="{{ route('lawyer.case.setup', $case->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded-md hover:bg-indigo-700 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Details
                                </a>
                            @else
                                @if($case->status === LegalCase::STATUS_PENDING && ($case->lawyer_id === Auth::id() || ($case->lawFirm && $case->lawFirm->owner_id === Auth::id())))
                                    <button wire:click="showStartCaseForm({{ $case->id }})" 
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Start Case
                                    </button>
                                @endif

                                @if($case->status === LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT)
                                    <button wire:click="openUploadRevisedContractModal({{ $case->id }})" 
                                           class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-md hover:bg-yellow-600 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Upload Revised
                                    </button>
                                @endif
                                
                                @if($case->status === LegalCase::STATUS_CONTRACT_SENT && $case->lawyer_response_required)
                                    <a href="{{ route('lawyer.contract.review', $case->id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-orange-500 text-white text-xs font-medium rounded-md hover:bg-orange-600 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Review Changes
                                    </a>
                                @endif

                                @if(in_array($case->status, [LegalCase::STATUS_ACTIVE, LegalCase::STATUS_CONTRACT_SIGNED, LegalCase::STATUS_CONTRACT_SENT, LegalCase::STATUS_CONTRACT_REVISED_SENT]) && !($case->status === LegalCase::STATUS_CONTRACT_SENT && $case->lawyer_response_required))
                                    <a href="{{ route('lawyer.case.setup', $case->id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Manage Case
                                    </a>
                                @endif
                                
                                @if($case->signature_path || in_array($case->status, [LegalCase::STATUS_CONTRACT_SIGNED, LegalCase::STATUS_ACTIVE]))
                                    <button wire:click="viewSignature({{ $case->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-emerald-600 text-white text-xs font-medium rounded-md hover:bg-emerald-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        View Signature
                                    </button>
                                @endif
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
                    You don't have any active cases yet. They will appear here when clients request your services.
                @endif
            </p>
        </div>
    @endif

    <!-- Include existing modals -->
    @include('livewire.lawyers.partials.case-signature-modal')
    @include('livewire.lawyers.partials.start-case-modal')
    @include('livewire.lawyers.partials.upload-revised-contract-modal')
</div> 