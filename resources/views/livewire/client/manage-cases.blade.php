<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if (session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-1">
                            @if($showArchived)
                                My Archived Cases
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Archived</span>
                            @else
                                My Legal Cases
                            @endif
                        </h2>
                        @if($showArchived)
                            <p class="text-sm text-gray-600">These cases have been closed but are kept for your reference.</p>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-4 md:mt-0">
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
                                {{ $showArchived ? 'Show Active Cases' : 'Show Archived Cases' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cases Table -->
                @if($cases->isEmpty())
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    You don't have any legal cases yet. To get started, schedule a consultation with a lawyer or convert a completed consultation into a case.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('case_number')">
                                        Case # 
                                        @if($sortField === 'case_number')
                                            @if($sortDirection === 'asc')
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            @else
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            @endif
                                        @endif
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('title')">
                                        Title
                                        @if($sortField === 'title')
                                            @if($sortDirection === 'asc')
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            @else
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            @endif
                                        @endif
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lawyer
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                                        Date
                                        @if($sortField === 'created_at')
                                            @if($sortDirection === 'asc')
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            @else
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            @endif
                                        @endif
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                        Status
                                        @if($sortField === 'status')
                                            @if($sortDirection === 'asc')
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                            @else
                                                <svg class="inline-block w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            @endif
                                        @endif
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($cases as $case)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $case->case_number }}</div>
                                            @if($case->consultation_id)
                                                <div class="text-xs text-blue-600">From consultation</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $case->title }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($case->description, 50) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if($case->lawyer->isLawFirm() && $case->lawyer->lawFirmProfile)
                                                    {{ $case->lawyer->lawFirmProfile->firm_name }}
                                                @elseif($case->lawyer->isLawyer() && $case->lawyer->lawyerProfile)
                                                    {{ $case->lawyer->lawyerProfile->first_name }} {{ $case->lawyer->lawyerProfile->last_name }}
                                                @elseif($case->lawyer->lawFirmLawyer)
                                                    {{ $case->lawyer->lawFirmLawyer->first_name }} {{ $case->lawyer->lawFirmLawyer->last_name }}
                                                @else
                                                    {{ $case->lawyer->first_name }} {{ $case->lawyer->last_name }}
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                @if($case->lawyer->lawyerProfile)
                                                    {{ $case->lawyer->lawyerProfile->specialization ?? 'Lawyer' }}
                                                @else
                                                    Lawyer
                                                @endif
                                            </div>
                                            @if($case->consultation && ($case->consultation->type == 'inhouse' || $case->consultation->type == 'in_house' || $case->consultation->type == 'in-house') && $case->lawyer->lawyerProfile && $case->lawyer->lawyerProfile->google_maps_link)
                                                <div class="mt-1">
                                                    <a href="{{ $case->lawyer->lawyerProfile->google_maps_link }}" target="_blank" class="inline-flex items-center text-xs text-indigo-600 hover:text-indigo-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                                        </svg>
                                                        Office Address
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $case->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                @if($case->status === \App\Models\LegalCase::STATUS_CONTRACT_SENT)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Contract Review Required
                                                    </span>
                                                @elseif($case->status === \App\Models\LegalCase::STATUS_CONTRACT_REJECTED_BY_CLIENT)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Contract Rejected
                                                    </span>
                                                    @if($case->rejection_reason)
                                                        <p class="text-xs text-red-700 mt-1 truncate" title="{{ $case->rejection_reason }}">
                                                            Reason: {{ Str::limit($case->rejection_reason, 50) }}
                                                        </p>
                                                    @endif
                                                @elseif($case->status === \App\Models\LegalCase::STATUS_CONTRACT_SIGNED)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Contract Signed
                                                    </span>
                                                @elseif($case->status === \App\Models\LegalCase::STATUS_ACTIVE)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @elseif($case->status === \App\Models\LegalCase::STATUS_COMPLETED)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Completed
                                                    </span>
                                                @elseif($case->status === \App\Models\LegalCase::STATUS_CLOSED)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Closed
                                                    </span>
                                                @elseif($case->status === \App\Models\LegalCase::STATUS_PENDING && $case->status !== \App\Models\LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex flex-col items-end space-y-2">
                                                <!-- Main action buttons -->
                                                <div>
                                                    @if($case->status === \App\Models\LegalCase::STATUS_CONTRACT_SENT)
                                                        <a href="{{ route('client.contract.review', $case->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition-all duration-300">
                                                            Review Contract
                                                        </a>
                                                    @elseif($case->status === \App\Models\LegalCase::STATUS_CONTRACT_REVISED_SENT)
                                                        <a href="{{ route('client.contract.review', $case->id) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 transition-all duration-300" title="The lawyer has sent a revised contract for your review.">
                                                            Review Revised Contract
                                                        </a>
                                                    @elseif($case->contract_status === 'signed')
                                                        <a href="{{ route('client.case.overview', $case->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition-all duration-300">
                                                            View Case
                                                        </a>
                                                    @else
                                                        <a href="{{ route('client.case.details', $case->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition-all duration-300">
                                                            Details
                                                        </a>
                                                    @endif
                                                </div>

                                                <!-- Rating buttons for closed/completed cases -->
                                                @if($case->status === 'closed' || $case->status === 'completed' || $case->closed_at)
                                                    <div class="flex items-center space-x-2">
                                                        @if($this->caseHasLawFirm($case->id))
                                                            <!-- Law firm case -->
                                                            <button 
                                                                wire:click="openRateLawFirmModal({{ $case->id }})" 
                                                                class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green active:bg-green-700 transition duration-150">
                                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                Rate Law Firm
                                                            </button>
                                                        @endif

                                                        @if($this->caseHasMultipleLawyers($case->id))
                                                            <!-- Multiple lawyers case -->
                                                            <button 
                                                                wire:click="openRateTeamLawyerModal({{ $case->id }})" 
                                                                class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-purple-600 hover:bg-purple-500 focus:outline-none focus:border-purple-700 focus:shadow-outline-purple active:bg-purple-700 transition duration-150">
                                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                                                                </svg>
                                                                Rate Team
                                                            </button>
                                                        @else
                                                            <!-- Single lawyer case -->
                                                            <button 
                                                                wire:click="openRateLawyerModal({{ $case->id }})" 
                                                                class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150">
                                                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                                </svg>
                                                                Rate Lawyer
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $cases->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    @include('livewire.client.partials.case-details-modal')
    
    <!-- Contract View Modal -->
    @include('livewire.client.partials.case-contract-modal')
    
    <!-- Sign Contract Modal -->
    @include('livewire.client.partials.case-sign-modal')
    
    <!-- Negotiate Contract Modal -->
    @include('livewire.client.partials.case-negotiate-modal')
    
    <!-- Start Case Modal -->
    @include('livewire.client.partials.case-start-modal')

    <!-- Include the Lawyer Rating Component -->
    <livewire:client.rate-lawyer />

    <!-- Include the Team Lawyer Rating Component -->
    <livewire:client.rate-team-lawyer />
    
    <!-- Include the Law Firm Rating Component -->
    <livewire:client.rate-law-firm />
</div> 