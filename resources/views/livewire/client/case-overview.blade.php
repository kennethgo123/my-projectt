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
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ $case->title }}
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Case #: {{ $case->case_number }}
                    </p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $case->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ ucfirst($case->status) }}
                </span>
            </div>
        </div>

        @if (!$case->setup_completed && $case->status !== \App\Models\LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT)
            <!-- Case setup pending message -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Case Setup In Progress:</strong> Kindly wait for your lawyer to set up your case. Your lawyer will organize the case timeline, schedule important events, and add necessary tasks. You'll be able to track your case progress once setup is complete.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'overview')" class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'documents')" class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'documents' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Documents
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            @if ($activeTab === 'overview')
                <div class="px-4 py-5 sm:p-6">
                    <!-- Case Progression Monitor -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Case Progression Monitor</h3>
                        
                        <!-- Phase Timeline -->
                        <div class="relative mb-8">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-between">
                                @foreach ($phases as $index => $phase)
                                    <div class="flex flex-col items-center">
                                        <span class="relative flex h-10 w-10 items-center justify-center rounded-full {{ $phase['is_completed'] ? 'bg-green-500' : ($phase['is_current'] ? 'bg-indigo-600' : 'bg-gray-300') }} {{ $phase['is_completed'] ? 'text-white' : ($phase['is_current'] ? 'text-white' : 'text-gray-700') }}">
                                            @if ($phase['is_completed'])
                                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </span>
                                        <span class="mt-2 text-xs text-center text-gray-900">{{ $phase['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Current Phase Details -->
                        @if ($currentPhase)
                            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                                <h4 class="text-md font-medium text-indigo-900 mb-2">Current Phase: {{ $currentPhase['name'] }}</h4>
                                <p class="text-sm text-indigo-800 mb-3">{{ $currentPhase['description'] }}</p>
                                <div class="flex justify-between text-xs text-indigo-700">
                                    <span>Started: {{ \Carbon\Carbon::parse($currentPhase['start_date'])->format('M d, Y') }}</span>
                                    <span>Expected completion: {{ \Carbon\Carbon::parse($currentPhase['end_date'])->format('M d, Y') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Upcoming Events -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Events</h3>
                        
                        @if (count($upcomingEvents) === 0)
                            <p class="text-sm text-gray-500">No upcoming events scheduled.</p>
                        @else
                            <div class="overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Event
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date & Time
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Location
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($upcomingEvents as $event)
                                            <tr>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $event['title'] }}</div>
                                                    <div class="text-sm text-gray-500">{{ Str::limit($event['description'], 50) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">
                                                        {{ \Carbon\Carbon::parse($event['event_date'])->format('M d, Y') }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::parse($event['event_time'])->format('h:i A') }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $event['location'] ?: 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    
                    <!-- My Tasks -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">My Tasks</h3>
                        
                        @if (count($myTasks) === 0)
                            <p class="text-sm text-gray-500">No tasks assigned to you.</p>
                        @else
                            <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                                @foreach ($myTasks as $task)
                                    <li class="px-4 py-4 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                id="task-{{ $task['id'] }}" 
                                                wire:click="toggleTaskCompletion({{ $task['id'] }})"
                                                {{ $task['is_completed'] ? 'checked' : '' }}
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            >
                                            <label for="task-{{ $task['id'] }}" class="ml-3 block">
                                                <span class="text-sm font-medium {{ $task['is_completed'] ? 'text-gray-400 line-through' : 'text-gray-700' }}">
                                                    {{ $task['title'] }}
                                                </span>
                                                <span class="text-sm text-gray-500 block">Due: {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}</span>
                                            </label>
                                        </div>
                                        
                                        <div class="ml-4 flex-shrink-0">
                                            <button 
                                                wire:click="viewTaskDetails({{ $task['id'] }})"
                                                class="font-medium text-indigo-600 hover:text-indigo-500 text-sm"
                                            >
                                                Details
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    
                    <!-- Invoices Requiring Payment -->
                    @if(isset($invoices) && $invoices->count() > 0)
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoices Requiring Payment</h3>
                            <div class="bg-white overflow-hidden border border-gray-200 rounded-lg shadow-sm">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Plan</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($invoices as $invoice)
                                            <tr>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $invoice->title }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                                        <div>
                                                            <div>PHP {{ number_format($invoice->getInstallmentAmount(), 2) }} / installment</div>
                                                            <div class="text-xs text-gray-500">
                                                                (Total: PHP {{ number_format($invoice->total, 2) }})
                                                            </div>
                                                        </div>
                                                    @else
                                                        PHP {{ number_format($invoice->total, 2) }}
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($invoice->status === 'paid') bg-green-100 text-green-800
                                                        @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                                        @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
                                                        @elseif($invoice->status === 'partial') bg-blue-100 text-blue-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($invoice->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $invoice->due_date->format('M d, Y') }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    @if($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                                        Full Payment
                                                    @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_3_MONTHS)
                                                        3 Installments
                                                        <div class="text-xs text-gray-400">
                                                            {{ $invoice->installments_paid }}/3 paid
                                                        </div>
                                                    @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_6_MONTHS)
                                                        6 Installments
                                                        <div class="text-xs text-gray-400">
                                                            {{ $invoice->installments_paid }}/6 paid
                                                        </div>
                                                    @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_1_YEAR)
                                                        12 Installments
                                                        <div class="text-xs text-gray-400">
                                                            {{ $invoice->installments_paid }}/12 paid
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex justify-end space-x-2">
                                                        <button 
                                                            wire:click="viewInvoice({{ $invoice->id }})" 
                                                            class="text-blue-600 hover:text-blue-900 focus:outline-none" 
                                                            title="View Invoice Details"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                            </svg>
                                                        </button>
                                                        <button 
                                                            wire:click="payWithGCash({{ $invoice->id }})" 
                                                            class="text-green-600 hover:text-green-900 focus:outline-none" 
                                                            title="Pay with GCash"
                                                        >
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            
            @if ($activeTab === 'documents')
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Case Documents</h3>
                    </div>
                    
                    @livewire('shared.case-documents', ['case' => $case])
                </div>
            @endif
        </div>

        <div class="mt-6">
            <a href="{{ route('client.cases') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                &larr; Back to Cases
            </a>
        </div>
    </div>
    
    <!-- Task Details Modal -->
    <div
        x-data="{ shown: false }"
        x-on:open-modal.window="$event.detail == 'task-details-modal' ? shown = true : null"
        x-on:close-modal.window="$event.detail == 'task-details-modal' ? shown = false : null"
        x-show="shown"
        x-cloak
        class="fixed z-10 inset-0 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div
                x-show="shown"
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
                x-show="shown"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                @if ($selectedTask)
                    <div>
                        <div class="mt-3">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ $selectedTask['title'] }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ $selectedTask['description'] }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-8">
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($selectedTask['due_date'])->format('M d, Y') }}</dd>
                                </div>
                                <div class="col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if ($selectedTask['is_completed'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                @endif
                
                <div class="mt-5 sm:mt-6">
                    <button 
                        type="button" 
                        x-on:click="$dispatch('close-modal', 'task-details-modal')"
                        class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Invoice Modal -->
    <div x-data="{ showViewInvoiceModal: false, selectedInvoice: null }" class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
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
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ $case->title }}
                            </h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                Case #: {{ $case->case_number }}
                            </p>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $case->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($case->status) }}
                        </span>
                    </div>
                </div>

                @if (!$case->setup_completed)
                    <!-- Case setup pending message -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Case Setup In Progress:</strong> Kindly wait for your lawyer to set up your case. Your lawyer will organize the case timeline, schedule important events, and add necessary tasks. You'll be able to track your case progress once setup is complete.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Tabs -->
                    <div class="mb-6 border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button wire:click="$set('activeTab', 'overview')" class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Overview
                            </button>
                            <button wire:click="$set('activeTab', 'documents')" class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'documents' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Documents
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        @if ($activeTab === 'overview')
                            <div class="px-4 py-5 sm:p-6">
                                <!-- Case Progression Monitor -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Case Progression Monitor</h3>
                                    
                                    <!-- Phase Timeline -->
                                    <div class="relative mb-8">
                                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                            <div class="w-full border-t border-gray-300"></div>
                                        </div>
                                        <div class="relative flex justify-between">
                                            @foreach ($phases as $index => $phase)
                                                <div class="flex flex-col items-center">
                                                    <span class="relative flex h-10 w-10 items-center justify-center rounded-full {{ $phase['is_completed'] ? 'bg-green-500' : ($phase['is_current'] ? 'bg-indigo-600' : 'bg-gray-300') }} {{ $phase['is_completed'] ? 'text-white' : ($phase['is_current'] ? 'text-white' : 'text-gray-700') }}">
                                                        @if ($phase['is_completed'])
                                                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        @else
                                                            {{ $index + 1 }}
                                                        @endif
                                                    </span>
                                                    <span class="mt-2 text-xs text-center text-gray-900">{{ $phase['name'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <!-- Current Phase Details -->
                                    @if ($currentPhase)
                                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                                            <h4 class="text-md font-medium text-indigo-900 mb-2">Current Phase: {{ $currentPhase['name'] }}</h4>
                                            <p class="text-sm text-indigo-800 mb-3">{{ $currentPhase['description'] }}</p>
                                            <div class="flex justify-between text-xs text-indigo-700">
                                                <span>Started: {{ \Carbon\Carbon::parse($currentPhase['start_date'])->format('M d, Y') }}</span>
                                                <span>Expected completion: {{ \Carbon\Carbon::parse($currentPhase['end_date'])->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Upcoming Events -->
                                <div class="mb-8">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Events</h3>
                                    
                                    @if (count($upcomingEvents) === 0)
                                        <p class="text-sm text-gray-500">No upcoming events scheduled.</p>
                                    @else
                                        <div class="overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Event
                                                        </th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Date & Time
                                                        </th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                            Location
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach ($upcomingEvents as $event)
                                                        <tr>
                                                            <td class="px-6 py-4">
                                                                <div class="text-sm font-medium text-gray-900">{{ $event['title'] }}</div>
                                                                <div class="text-sm text-gray-500">{{ Str::limit($event['description'], 50) }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <div class="text-sm text-gray-900">
                                                                    {{ \Carbon\Carbon::parse($event['event_date'])->format('M d, Y') }}
                                                                </div>
                                                                <div class="text-sm text-gray-500">
                                                                    {{ \Carbon\Carbon::parse($event['event_time'])->format('h:i A') }}
                                                                </div>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {{ $event['location'] ?: 'N/A' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- My Tasks -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">My Tasks</h3>
                                    
                                    @if (count($myTasks) === 0)
                                        <p class="text-sm text-gray-500">No tasks assigned to you.</p>
                                    @else
                                        <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                                            @foreach ($myTasks as $task)
                                                <li class="px-4 py-4 flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <input 
                                                            type="checkbox" 
                                                            id="task-{{ $task['id'] }}" 
                                                            wire:click="toggleTaskCompletion({{ $task['id'] }})"
                                                            {{ $task['is_completed'] ? 'checked' : '' }}
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        >
                                                        <label for="task-{{ $task['id'] }}" class="ml-3 block">
                                                            <span class="text-sm font-medium {{ $task['is_completed'] ? 'text-gray-400 line-through' : 'text-gray-700' }}">
                                                                {{ $task['title'] }}
                                                            </span>
                                                            <span class="text-sm text-gray-500 block">Due: {{ \Carbon\Carbon::parse($task['due_date'])->format('M d, Y') }}</span>
                                                        </label>
                                                    </div>
                                                    
                                                    <div class="ml-4 flex-shrink-0">
                                                        <button 
                                                            wire:click="viewTaskDetails({{ $task['id'] }})"
                                                            class="font-medium text-indigo-600 hover:text-indigo-500 text-sm"
                                                        >
                                                            Details
                                                        </button>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        @if ($activeTab === 'documents')
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Case Documents</h3>
                                </div>
                                
                                @livewire('shared.case-documents', ['case' => $case])
                            </div>
                        @endif
                    </div>
                @endif
                
                <div class="mt-6">
                    <a href="{{ route('client.cases') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        &larr; Back to Cases
                    </a>
                </div>
            </div>
        </div>
    </div>
</div> 