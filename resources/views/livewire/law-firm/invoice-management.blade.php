<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Header and Status Messages -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Invoice Management</h2>
                        <p class="mt-1 text-sm text-gray-600">Create and manage invoices for your clients</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <button wire:click="openInvoiceModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-plus mr-2"></i> Create New Invoice
                        </button>
                    </div>
                </div>
                
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

                <!-- Search and Filters -->
                <div class="flex flex-col md:flex-row mb-6 space-y-4 md:space-y-0 md:space-x-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" wire:model.debounce.300ms="search" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Search invoices...">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select wire:model="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partially Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <!-- Invoices Table -->
                <div class="overflow-x-auto bg-white rounded-lg shadow overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('invoice_number')">
                                    Invoice #
                                    @if ($sortField === 'invoice_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('title')">
                                    Title
                                    @if ($sortField === 'title')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Client
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('issue_date')">
                                    Issue Date
                                    @if ($sortField === 'issue_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('due_date')">
                                    Due Date
                                    @if ($sortField === 'due_date')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total')">
                                    Amount
                                    @if ($sortField === 'total')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                    Status
                                    @if ($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        <a href="#" wire:click.prevent="viewInvoice({{ $invoice->id }})">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $invoice->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $invoice->client->clientProfile->first_name ?? 'N/A' }} {{ $invoice->client->clientProfile->last_name ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $invoice->issue_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        PHP {{ number_format($invoice->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                                            @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
                                            @elseif($invoice->status === 'partial') bg-blue-100 text-blue-800
                                            @elseif($invoice->status === 'draft') bg-gray-100 text-gray-800
                                            @elseif($invoice->status === 'cancelled') bg-gray-100 text-gray-800 line-through
                                            @endif">
                                            {{ ucfirst($invoice->status) }}
                                            @if($invoice->status === \App\Models\Invoice::STATUS_PARTIAL)
                                                ({{ $invoice->installments_paid }}/{{ $invoice->getTotalInstallments() }})
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <!-- View button - always visible -->
                                            <button wire:click="viewInvoice({{ $invoice->id }})" class="text-indigo-600 hover:text-indigo-900" title="View">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            
                                            <!-- Edit button - only for draft -->
                                            @if($invoice->status === 'draft')
                                                <button wire:click="editInvoice({{ $invoice->id }})" class="text-yellow-600 hover:text-yellow-900" title="Edit Invoice">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                            
                                            <!-- Send button - only for draft -->
                                            @if($invoice->status === 'draft')
                                                <button wire:click="sendInvoice({{ $invoice->id }})" class="text-green-600 hover:text-green-900" title="Send Invoice">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                            
                                            <!-- Cancel button - for all except paid and cancelled -->
                                            @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                                <button wire:click="cancelInvoice({{ $invoice->id }})" class="text-red-600 hover:text-red-900" title="Cancel Invoice">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                            
                                            <!-- Download button - always visible -->
                                            <button wire:click="downloadInvoice({{ $invoice->id }})" class="text-gray-600 hover:text-gray-900" title="Download Invoice">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No invoices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Invoice Modal -->
    <div>
        <x-modal wire:model="showInvoiceModal" maxWidth="4xl">
            <div class="px-6 py-4">
                <div class="text-lg font-medium text-gray-900">
                    {{ $editMode ? 'Edit Invoice' : 'Create New Invoice' }}
                </div>

                <div class="mt-4">
                    <form wire:submit.prevent="saveInvoice">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Basic Invoice Info -->
                            <div>
                                <x-label for="invoiceTitle" value="Invoice Title" />
                                <x-input id="invoiceTitle" type="text" class="mt-1 block w-full" wire:model="invoiceTitle" />
                                @error('invoiceTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Client Selection -->
                            <div>
                                <x-label for="selectedClient" value="Client" class="flex items-center">
                                    Client <span class="ml-1 text-red-500">*</span>
                                </x-label>
                                <div class="relative">
                                    @error('selectedClient') 
                                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-2 mb-2">
                                            <p>{{ $message }}</p>
                                        </div>
                                    @enderror
                                    <select id="selectedClient" class="mt-1 block w-full @error('selectedClient') border-red-500 @else border-gray-300 @enderror focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" wire:model="selectedClient">
                                        <option value="">Select Client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">
                                                @if($client->clientProfile)
                                                    {{ $client->clientProfile->first_name }} {{ $client->clientProfile->last_name }}
                                                @else
                                                    {{ $client->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <x-label for="invoiceDescription" value="Description" />
                                <textarea id="invoiceDescription" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" wire:model="invoiceDescription" rows="3"></textarea>
                                @error('invoiceDescription') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <!-- Dates -->
                            <div>
                                <x-label for="invoiceIssueDate" value="Issue Date" />
                                <x-input id="invoiceIssueDate" type="date" class="mt-1 block w-full" wire:model="invoiceIssueDate" />
                                @error('invoiceIssueDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <x-label for="invoiceDueDate" value="Due Date" />
                                <x-input id="invoiceDueDate" type="date" class="mt-1 block w-full" wire:model="invoiceDueDate" />
                                @error('invoiceDueDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mt-6">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-medium text-gray-700">Invoice Items</h3>
                                <button type="button" wire:click="addInvoiceItem" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                                    <i class="fas fa-plus mr-2"></i> Add Item
                                </button>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                @foreach($invoiceItems as $key => $item)
                                    <div class="grid grid-cols-12 gap-2 mb-3 items-center">
                                        <div class="col-span-4">
                                            <x-label for="invoiceItems.{{ $key }}.description" value="Description" class="sr-only" />
                                            <x-input id="invoiceItems.{{ $key }}.description" type="text" class="block w-full" wire:model="invoiceItems.{{ $key }}.description" placeholder="Description" />
                                            @error("invoiceItems.{$key}.description") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-2">
                                            <x-label for="invoiceItems.{{ $key }}.type" value="Type" class="sr-only" />
                                            <select id="invoiceItems.{{ $key }}.type" class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" wire:model="invoiceItems.{{ $key }}.type">
                                                <option value="service">Service</option>
                                                <option value="expense">Expense</option>
                                                <option value="billable_hours">Billable Hours</option>
                                                <option value="other">Other</option>
                                            </select>
                                            @error("invoiceItems.{$key}.type") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-3">
                                            <x-label for="invoiceItems.{{ $key }}.unit_price" value="Price" class="sr-only" />
                                            <x-input id="invoiceItems.{{ $key }}.unit_price" type="number" step="0.01" min="0" class="block w-full" wire:model="invoiceItems.{{ $key }}.unit_price" placeholder="Price (PHP)" />
                                            @error("invoiceItems.{$key}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-span-2">
                                            <div class="text-right">
                                                PHP {{ number_format($item['unit_price'] ?? 0, 2) }}
                                            </div>
                                        </div>
                                        <div class="col-span-1">
                                            <button type="button" wire:click="removeInvoiceItem({{ $key }})" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Invoice Totals -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-label for="invoiceNotes" value="Notes" />
                                <textarea id="invoiceNotes" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" wire:model="invoiceNotes" rows="3"></textarea>
                                @error('invoiceNotes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-700">Subtotal:</span>
                                        <span class="font-medium">PHP {{ number_format($this->calculateSubtotal(), 2) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-700">Discount:</span>
                                        <div class="w-24">
                                            <x-input type="number" step="0.01" min="0" class="block w-full" wire:model="invoiceDiscount" />
                                        </div>
                                    </div>
                                    <div class="flex justify-between font-bold text-lg mt-4 pt-2 border-t border-gray-200">
                                        <span>Total:</span>
                                        <span>PHP {{ number_format($this->calculateTotal(), 2) }}</span>
                                    </div>
                                    
                                    <div class="mt-4 pt-2 border-t border-gray-200">
                                        <x-label for="invoicePaymentPlan" value="Payment Plan" />
                                        <select id="invoicePaymentPlan" 
                                                wire:model="invoicePaymentPlan"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                            <option value="full">Full Payment</option>
                                            <option value="3_months">3 Monthly Installments</option>
                                            <option value="6_months">6 Monthly Installments</option>
                                            <option value="1_year">12 Monthly Installments</option>
                                        </select>
                                        @error('invoicePaymentPlan') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <x-button wire:click="closeInvoiceModal" class="mr-3">
                    Cancel
                </x-button>
                <x-button type="button" wire:click="saveInvoice" class="bg-indigo-600 hover:bg-indigo-700">
                    {{ $editMode ? 'Update Invoice' : 'Create Invoice' }}
                </x-button>
            </div>
        </x-modal>
    </div>

    <!-- View Invoice Modal -->
    <div>
        <x-modal wire:model="showViewInvoiceModal" maxWidth="4xl">
            <div class="px-6 py-4">
                @if($selectedInvoice)
                    <div class="flex justify-between items-center">
                        <div class="text-lg font-medium text-gray-900">
                            Invoice: {{ $selectedInvoice->invoice_number }}
                        </div>
                        <div>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                @if($selectedInvoice->status === 'paid') bg-green-100 text-green-800
                                @elseif($selectedInvoice->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($selectedInvoice->status === 'overdue') bg-red-100 text-red-800
                                @elseif($selectedInvoice->status === 'partial') bg-blue-100 text-blue-800
                                @elseif($selectedInvoice->status === 'draft') bg-gray-100 text-gray-800
                                @elseif($selectedInvoice->status === 'cancelled') bg-gray-100 text-gray-800 line-through
                                @endif">
                                {{ ucfirst($selectedInvoice->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Invoice Details -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Invoice Information</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-sm text-gray-500">Title:</div>
                                <div class="text-sm font-medium">{{ $selectedInvoice->title }}</div>
                                <div class="text-sm text-gray-500">Description:</div>
                                <div class="text-sm font-medium">{{ $selectedInvoice->description }}</div>
                                <div class="text-sm text-gray-500">Issue Date:</div>
                                <div class="text-sm font-medium">{{ $selectedInvoice->issue_date->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">Due Date:</div>
                                <div class="text-sm font-medium">{{ $selectedInvoice->due_date->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">Case:</div>
                                <div class="text-sm font-medium">
                                    {{ $selectedInvoice->legalCase ? $selectedInvoice->legalCase->title . ' (#' . $selectedInvoice->legalCase->case_number . ')' : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Client Information -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Client Information</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-sm text-gray-500">Name:</div>
                                <div class="text-sm font-medium">{{ $selectedInvoice->client->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">Phone:</div>
                                <div class="text-sm font-medium">{{ $selectedInvoice->client->phone ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Invoice Items</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedInvoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($item->type) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PHP {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">PHP {{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">PHP {{ number_format($selectedInvoice->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Discount:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">PHP {{ number_format($selectedInvoice->discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total:</td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">PHP {{ number_format($selectedInvoice->total, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Payment Plan:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                        @if($selectedInvoice->payment_plan === 'full')
                                            Full Payment
                                        @elseif($selectedInvoice->payment_plan === '3_months')
                                            3 Monthly Installments
                                        @elseif($selectedInvoice->payment_plan === '6_months')
                                            6 Monthly Installments
                                        @elseif($selectedInvoice->payment_plan === '1_year')
                                            12 Monthly Installments
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $selectedInvoice->payment_plan)) }}
                                        @endif
                                    </td>
                                </tr>
                                @if($selectedInvoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                    <tr>
                                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Installment Amount:</td>
                                        <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">PHP {{ number_format($selectedInvoice->getInstallmentAmount(), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Installments Paid:</td>
                                        <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ $selectedInvoice->installments_paid }} / {{ $selectedInvoice->getTotalInstallments() }}</td>
                                    </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>

                    <!-- Notes -->
                    @if($selectedInvoice->notes)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Notes</h3>
                            <div class="bg-gray-50 p-4 rounded-lg text-sm text-gray-700">
                                {{ $selectedInvoice->notes }}
                            </div>
                        </div>
                    @endif

                    <!-- Payment History -->
                    @if(count($selectedInvoice->payments) > 0)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Payment History</h3>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($selectedInvoice->payments as $payment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($payment->payment_method) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->transaction_id ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($payment->status === 'success') bg-green-100 text-green-800
                                                @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">PHP {{ number_format($payment->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-between">
                <div class="flex space-x-2">
                    <!-- Send to client - only for draft invoices -->
                    @if($selectedInvoice && $selectedInvoice->status === 'draft')
                        <x-button wire:click="sendInvoice({{ $selectedInvoice->id }})" class="bg-green-600 hover:bg-green-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Send to Client
                        </x-button>
                    @endif
                    
                    <!-- Edit invoice - only for draft invoices -->
                    @if($selectedInvoice && $selectedInvoice->status === 'draft')
                        <x-button wire:click="editInvoice({{ $selectedInvoice->id }})" class="bg-yellow-600 hover:bg-yellow-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </x-button>
                    @endif
                    
                    <!-- Cancel invoice - for all except paid and cancelled -->
                    @if($selectedInvoice && $selectedInvoice->status !== 'paid' && $selectedInvoice->status !== 'cancelled')
                        <x-button wire:click="cancelInvoice({{ $selectedInvoice->id }})" class="bg-red-600 hover:bg-red-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </x-button>
                    @endif
                </div>
                
                <div class="flex space-x-2">
                    <x-button wire:click="downloadInvoice({{ $selectedInvoice ? $selectedInvoice->id : 0 }})" class="bg-gray-500 hover:bg-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                    </x-button>
                    
                    <x-button wire:click="closeViewInvoiceModal" class="bg-gray-300 text-gray-800 hover:bg-gray-400">
                        Close
                    </x-button>
                </div>
            </div>
        </x-modal>
    </div>
</div> 