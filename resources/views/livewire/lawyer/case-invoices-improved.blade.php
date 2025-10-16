<div>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <!-- Header and Status Messages -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Invoice Management</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Invoices for: <span class="font-medium">{{ $case->client->clientProfile->first_name ?? $case->client->name }} {{ $case->client->clientProfile->last_name ?? '' }}</span>
                </p>
            </div>
            <div class="mt-4 md:mt-0">
                @if($isPrimaryLawyer)
                    <button wire:click="openInvoiceModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-plus mr-2"></i> Create New Invoice
                    </button>
                @else
                    <span class="text-sm text-gray-500">Only primary lawyers can create invoices</span>
                @endif
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
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('invoice_number')">
                            Invoice #
                            @if($sortField === 'invoice_number')
                                @if($sortDirection === 'asc')
                                    <i class="fas fa-sort-up ml-1"></i>
                                @else
                                    <i class="fas fa-sort-down ml-1"></i>
                                @endif
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('title')">
                            Title
                            @if($sortField === 'title')
                                @if($sortDirection === 'asc')
                                    <i class="fas fa-sort-up ml-1"></i>
                                @else
                                    <i class="fas fa-sort-down ml-1"></i>
                                @endif
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('total')">
                            Amount
                            @if($sortField === 'total')
                                @if($sortDirection === 'asc')
                                    <i class="fas fa-sort-up ml-1"></i>
                                @else
                                    <i class="fas fa-sort-down ml-1"></i>
                                @endif
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                            Status
                            @if($sortField === 'status')
                                @if($sortDirection === 'asc')
                                    <i class="fas fa-sort-up ml-1"></i>
                                @else
                                    <i class="fas fa-sort-down ml-1"></i>
                                @endif
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('due_date')">
                            Due Date
                            @if($sortField === 'due_date')
                                @if($sortDirection === 'asc')
                                    <i class="fas fa-sort-up ml-1"></i>
                                @else
                                    <i class="fas fa-sort-down ml-1"></i>
                                @endif
                            @else
                                <i class="fas fa-sort ml-1 text-gray-400"></i>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $invoice->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($invoice->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₱{{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-gray-100 text-gray-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                        'partial' => 'bg-blue-100 text-blue-800',
                                        'overdue' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-gray-100 text-gray-600'
                                    ];
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $invoice->due_date ? $invoice->due_date->format('M j, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button wire:click="viewInvoice({{ $invoice->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                
                                @if($isPrimaryLawyer)
                                    @if($invoice->status === 'draft')
                                        <button wire:click="editInvoice({{ $invoice->id }})" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button wire:click="sendInvoice({{ $invoice->id }})" class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-paper-plane"></i> Send
                                        </button>
                                    @endif
                                    
                                    @if(in_array($invoice->status, ['draft', 'pending']))
                                        <button wire:click="cancelInvoice({{ $invoice->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this invoice?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    @endif
                                @endif
                                
                                <button wire:click="downloadInvoice({{ $invoice->id }})" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No invoices found. 
                                @if($isPrimaryLawyer)
                                    <button wire:click="openInvoiceModal" class="text-indigo-600 hover:text-indigo-900 underline">Create your first invoice</button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $invoices->links() }}
        </div>
    </div>

    <!-- Create/Edit Invoice Modal -->
    @if($showInvoiceModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <form wire:submit.prevent="saveInvoice">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $editMode ? 'Edit Invoice' : 'Create New Invoice' }}
                                </h3>
                                <button type="button" wire:click="closeInvoiceModal" class="text-gray-400 hover:text-gray-600">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Client Information Display -->
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                                <h4 class="text-sm font-medium text-blue-800 mb-2">Invoice for:</h4>
                                <p class="text-sm text-blue-700">
                                    <strong>{{ $case->client->clientProfile->first_name ?? $case->client->name }} {{ $case->client->clientProfile->last_name ?? '' }}</strong>
                                </p>
                                <p class="text-xs text-blue-600">{{ $case->client->email }}</p>
                                <p class="text-xs text-blue-600 mt-1">Case: {{ $case->title }} (#{{ $case->case_number }})</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-4">
                                    <div>
                                        <label for="invoiceTitle" class="block text-sm font-medium text-gray-700">Invoice Title *</label>
                                        <input type="text" id="invoiceTitle" wire:model.defer="invoiceTitle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        @error('invoiceTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="invoiceDescription" class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea id="invoiceDescription" wire:model.defer="invoiceDescription" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                                        @error('invoiceDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="invoicePaymentPlan" class="block text-sm font-medium text-gray-700">Payment Plan *</label>
                                        <select id="invoicePaymentPlan" wire:model.defer="invoicePaymentPlan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                            <option value="full">Pay in Full</option>
                                            <option value="3_months">3 Months</option>
                                            <option value="6_months">6 Months</option>
                                            <option value="1_year">1 Year</option>
                                        </select>
                                        @error('invoicePaymentPlan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-4">
                                    <div>
                                        <label for="invoiceIssueDate" class="block text-sm font-medium text-gray-700">Issue Date *</label>
                                        <input type="date" id="invoiceIssueDate" wire:model.defer="invoiceIssueDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        @error('invoiceIssueDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="invoiceDueDate" class="block text-sm font-medium text-gray-700">Due Date *</label>
                                        <input type="date" id="invoiceDueDate" wire:model.defer="invoiceDueDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        @error('invoiceDueDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                </div>
                            </div>

                            <!-- Invoice Items -->
                            <div class="mt-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-lg font-medium text-gray-900">Invoice Items</h4>
                                    <button type="button" wire:click="addInvoiceItem" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-1 px-3 rounded">
                                        <i class="fas fa-plus mr-1"></i> Add Item
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    @foreach($invoiceItems as $index => $item)
                                        <div class="border border-gray-200 rounded-md p-4 bg-gray-50">
                                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700">Description *</label>
                                                    <input type="text" wire:model.defer="invoiceItems.{{ $index }}.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                    @error("invoiceItems.{$index}.description") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                                    <input type="number" wire:model.defer="invoiceItems.{{ $index }}.quantity" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    @error("invoiceItems.{$index}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Unit Price (₱) *</label>
                                                    <input type="number" wire:model.defer="invoiceItems.{{ $index }}.unit_price" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                    @error("invoiceItems.{$index}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700">Type *</label>
                                                    <select wire:model.defer="invoiceItems.{{ $index }}.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                                        <option value="service">Service</option>
                                                        <option value="expense">Expense</option>
                                                        <option value="billable_hours">Billable Hours</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    @error("invoiceItems.{$index}.type") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div class="flex items-end">
                                                    @if(count($invoiceItems) > 1)
                                                        <button type="button" wire:click="removeInvoiceItem({{ $index }})" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 px-3 rounded">
                                                            <i class="fas fa-trash"></i> Remove
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Invoice Notes -->
                            <div class="mt-6">
                                <label for="invoiceNotes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="invoiceNotes" wire:model.defer="invoiceNotes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Additional notes for the invoice... (Kindly still add notes for Pro Bono Cases)"></textarea>
                                @error('invoiceNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Total Summary -->
                            <div class="mt-6 bg-gray-50 rounded-md p-4">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total:</span>
                                    <span>₱{{ number_format($this->calculateTotal(), 2) }}</span>
                                </div>
                                @if($case->is_pro_bono)
                                    <div class="mt-2 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-700">
                                            <svg class="w-4 h-4 mr-1.5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M13 2C13 2 17 4 17 8C17 10 15.5 11.5 13.5 12.5L12 14L10.5 12.5C8.5 11.5 7 10 7 8C7 4 11 2 11 2H13Z"/>
                                                <path d="M6 12C6 12 2 14 2 18C2 20 3.5 21.5 5.5 22.5L7 24L8.5 22.5C10.5 21.5 12 20 12 18C12 14 8 12 8 12H6Z"/>
                                                <path d="M18 12C18 12 22 14 22 18C22 20 20.5 21.5 18.5 22.5L17 24L15.5 22.5C13.5 21.5 12 20 12 18C12 14 16 12 16 12H18Z"/>
                                            </svg>
                                            Pro Bono Case - Invoice will be set to ₱0 when sent
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $editMode ? 'Update Invoice' : 'Create Invoice' }}
                            </button>
                            <button type="button" wire:click="closeInvoiceModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- View Invoice Modal -->
    @if($showViewInvoiceModal && $selectedInvoice)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Invoice Details
                            </h3>
                            <button type="button" wire:click="closeViewInvoiceModal" class="text-gray-400 hover:text-gray-600">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Invoice Header -->
                        <div class="border border-gray-200 rounded-md p-6 mb-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">{{ $selectedInvoice->title }}</h2>
                                    <p class="text-sm text-gray-600">{{ $selectedInvoice->invoice_number }}</p>
                                </div>
                                <div class="text-right">
                                    @php
                                        $statusClasses = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            'partial' => 'bg-blue-100 text-blue-800',
                                            'overdue' => 'bg-red-100 text-red-800',
                                            'cancelled' => 'bg-gray-100 text-gray-600'
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusClasses[$selectedInvoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($selectedInvoice->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-2">Bill To:</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $selectedInvoice->client->clientProfile->first_name ?? $selectedInvoice->client->name }} {{ $selectedInvoice->client->clientProfile->last_name ?? '' }}<br>
                                        {{ $selectedInvoice->client->email }}
                                    </p>
                                </div>
                                <div class="text-md-right">
                                    <h4 class="font-medium text-gray-900 mb-2">Invoice Details:</h4>
                                    <p class="text-sm text-gray-600">
                                        Issue Date: {{ $selectedInvoice->issue_date ? $selectedInvoice->issue_date->format('M j, Y') : 'N/A' }}<br>
                                        Due Date: {{ $selectedInvoice->due_date ? $selectedInvoice->due_date->format('M j, Y') : 'N/A' }}<br>
                                        Payment Plan: {{ ucfirst(str_replace('_', ' ', $selectedInvoice->payment_plan)) }}
                                    </p>
                                </div>
                            </div>

                            @if($selectedInvoice->description)
                                <div class="mt-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Description:</h4>
                                    <p class="text-sm text-gray-600">{{ $selectedInvoice->description }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Invoice Items -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-900 mb-4">Items:</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($selectedInvoice->items as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->description }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst($item->type) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity ?? '1' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₱{{ number_format($item->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-gray-50 rounded-md p-4">
                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total:</span>
                                        <span>₱{{ number_format($selectedInvoice->total, 2) }}</span>
                                    </div>
                                    @if($selectedInvoice->total == 0 && $case->is_pro_bono)
                                        <div class="text-center mt-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                <svg class="w-3 h-3 mr-1 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M13 2C13 2 17 4 17 8C17 10 15.5 11.5 13.5 12.5L12 14L10.5 12.5C8.5 11.5 7 10 7 8C7 4 11 2 11 2H13Z"/>
                                                    <path d="M6 12C6 12 2 14 2 18C2 20 3.5 21.5 5.5 22.5L7 24L8.5 22.5C10.5 21.5 12 20 12 18C12 14 8 12 8 12H6Z"/>
                                                    <path d="M18 12C18 12 22 14 22 18C22 20 20.5 21.5 18.5 22.5L17 24L15.5 22.5C13.5 21.5 12 20 12 18C12 14 16 12 16 12H18Z"/>
                                                </svg>
                                                Pro Bono Case
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($selectedInvoice->notes)
                            <div class="mt-6">
                                <h4 class="font-medium text-gray-900 mb-2">Notes:</h4>
                                <p class="text-sm text-gray-600">{{ $selectedInvoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeViewInvoiceModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
