<div>
    {{-- Success is as dangerous as failure. --}}
</div>

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
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 md:mb-0">Manage Legal Cases</h2>
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
                            <select wire:model.live="priorityFilter" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All Priorities</option>
                                <option value="low">Low Priority</option>
                                <option value="medium">Medium Priority</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">High Priority/Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Pending Case Requests Section -->
                @include('livewire.lawyer.partials.pending-case-requests')

                <!-- Consultation to Case Conversion Section -->
                @if($consultations->isNotEmpty())
                    <div class="bg-blue-50 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-blue-800 mb-2">Recent Completed Consultations</h3>
                        <p class="text-sm text-blue-600 mb-4">These consultations can be converted into cases. Click on "Start Case" to begin the process.</p>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($consultations as $consultation)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $consultation->client->first_name }} {{ $consultation->client->last_name }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $consultation->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button wire:click="startCaseFromConsultation({{ $consultation->id }})" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    Start Case
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

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
                                    You don't have any cases yet. They will appear here when clients request your services or when you create them from consultations.
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
                                        Case Label
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Client
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('priority')">
                                        Priority
                                        @if($sortField === 'priority')
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
                                    {{-- Temporary Blade Debug --}}
                                    @php
                                        \Illuminate\Support\Facades\Log::info('BLADE DEBUG - Lawyer Case Item', [
                                            'case_id' => $case->id,
                                            'case_title' => $case->title,
                                            'case_label_in_blade' => $case->case_label,
                                            'original_label_in_blade' => $case->label,
                                            'current_user_id' => auth()->id(),
                                            'current_user_firm_id' => auth()->user()->firm_id
                                        ]);
                                    @endphp
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
                                            <div x-data="{ editing: false, label: '{{ $case->case_label ?? '' }}' }">
                                                <div x-show="!editing" @click="editing = true" class="cursor-pointer hover:bg-gray-50 py-1 px-2 rounded flex items-center">
                                                    @if($case->case_label == 'high_priority')
                                                        <span class="text-sm font-medium text-red-700">High Priority</span>
                                                    @elseif($case->case_label == 'medium_priority')
                                                        <span class="text-sm font-medium text-yellow-700">Medium Priority</span>
                                                    @elseif($case->case_label == 'low_priority')
                                                        <span class="text-sm font-medium text-green-700">Low Priority</span>
                                                    @else
                                                        <span class="text-sm text-gray-400 italic">Add label</span>
                                                    @endif
                                                    <svg class="h-3 w-3 ml-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </div>
                                                <div x-show="editing" @click.away="editing = false" class="flex items-center">
                                                    <select x-model="label" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-sm">
                                                        <option value="">No Label</option>
                                                        <option value="high_priority">High Priority</option>
                                                        <option value="medium_priority">Medium Priority</option>
                                                        <option value="low_priority">Low Priority</option>
                                                    </select>
                                                    <button type="button" 
                                                        @click="
                                                            console.log('Save button clicked for case {{ $case->id }}');
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
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $case->client->first_name }} {{ $case->client->last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $case->client->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $case->created_at->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
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
                                                @case('closed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Closed
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst($case->status) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($case->priority)
                                                @case('low')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Low
                                                    </span>
                                                    @break
                                                @case('medium')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Medium
                                                    </span>
                                                    @break
                                                @case('high')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        High
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst($case->priority) }}
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <div class="relative" x-data="{ isOpen: false }">
                                                    <button @click="isOpen = !isOpen" class="text-gray-600 hover:text-gray-900" type="button">
                                                        <span class="inline-block w-2 h-2 rounded-full mr-1
                                                            @if($case->priority === 'urgent') bg-red-500
                                                            @elseif($case->priority === 'high') bg-orange-500
                                                            @elseif($case->priority === 'medium') bg-blue-500
                                                            @elseif($case->priority === 'low') bg-green-500
                                                            @else bg-gray-500 @endif">
                                                        </span>
                                                        Priority
                                                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        </svg>
                                                    </button>
                                                    <div x-show="isOpen" 
                                                         @click.away="isOpen = false"
                                                         class="absolute right-0 z-10 mt-2 w-36 bg-white shadow-lg rounded-md py-1 text-sm">
                                                        <button wire:click="updatePriority({{ $case->id }}, 'low')" class="w-full text-left px-4 py-2 text-green-800 hover:bg-green-100">
                                                            Low Priority
                                                        </button>
                                                        <button wire:click="updatePriority({{ $case->id }}, 'medium')" class="w-full text-left px-4 py-2 text-blue-800 hover:bg-blue-100">
                                                            Medium Priority
                                                        </button>
                                                        <button wire:click="updatePriority({{ $case->id }}, 'high')" class="w-full text-left px-4 py-2 text-orange-800 hover:bg-orange-100">
                                                            High Priority
                                                        </button>
                                                        <button wire:click="updatePriority({{ $case->id }}, 'urgent')" class="w-full text-left px-4 py-2 text-red-800 hover:bg-red-100">
                                                            High Priority/Urgent
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                @if($case->status == 'pending')
                                                    <button wire:click="showAction({{ $case->id }}, 'accept')" class="text-green-600 hover:text-green-900">
                                                        Accept
                                                    </button>
                                                    <button wire:click="showAction({{ $case->id }}, 'reject')" class="text-red-600 hover:text-red-900">
                                                        Reject
                                                    </button>
                                                @endif
                                                
                                                @if($case->status == 'accepted')
                                                    <button wire:click="showAction({{ $case->id }}, 'upload_contract')" class="text-blue-600 hover:text-blue-900">
                                                        Upload Contract
                                                    </button>
                                                @endif
                                                
                                                @if($case->contract_path)
                                                    <button wire:click="viewContract({{ $case->id }})" class="text-blue-600 hover:text-blue-900">
                                                        View Contract
                                                    </button>
                                                @endif
                                                
                                                @if($case->signature_path)
                                                    <button wire:click="viewSignature({{ $case->id }})" class="text-blue-600 hover:text-blue-900">
                                                        View Signature
                                                    </button>
                                                @endif
                                                
                                                @if($case->status == 'contract_signed')
                                                    <button wire:click="finishSetup({{ $case->id }})" class="text-green-600 hover:text-green-900">
                                                        Finish Setup
                                                    </button>
                                                @endif
                                                
                                                <button wire:click="showAction({{ $case->id }}, 'add_update')" class="text-purple-600 hover:text-purple-900">
                                                    Add Update
                                                </button>
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

    <!-- Action Modal -->
    @include('livewire.lawyer.partials.case-action-modal')
    
    <!-- Details Modal -->
    @include('livewire.lawyer.partials.case-details-modal')
    
    <!-- Contract View Modal -->
    @include('livewire.lawyer.partials.case-contract-modal')
    
    <!-- Signature View Modal -->
    @include('livewire.lawyer.partials.case-signature-modal')
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('open-signature', ({ url }) => {
            window.open(url, '_blank');
        });
    });
</script>
