<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Header and Status Messages -->
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">My Invoices</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage and pay your invoices</p>
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

                @if($paymentStatus)
                    <div class="mb-6 p-4 rounded-md 
                        @if($paymentStatus === 'processing') bg-blue-100 text-blue-700 border border-blue-200
                        @elseif($paymentStatus === 'error') bg-red-100 text-red-700 border border-red-200
                        @else bg-gray-100 text-gray-700 border border-gray-200 @endif">
                        {{ $paymentMessage }}
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
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partially Paid</option>
                            <option value="overdue">Overdue</option>
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
                                    Lawyer
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
                                        @if($invoice->lawyer->isLawFirm() && $invoice->lawyer->lawFirmProfile)
                                            {{ $invoice->lawyer->lawFirmProfile->firm_name }}
                                        @elseif($invoice->lawyer->isLawyer() && $invoice->lawyer->lawyerProfile)
                                            {{ $invoice->lawyer->lawyerProfile->first_name }} {{ $invoice->lawyer->lawyerProfile->last_name }}
                                        @elseif($invoice->lawyer->lawFirmLawyer)
                                            {{ $invoice->lawyer->lawFirmLawyer->first_name }} {{ $invoice->lawyer->lawFirmLawyer->last_name }}
                                        @else
                                            {{ $invoice->lawyer->name ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $invoice->issue_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        PHP {{ number_format($invoice->total, 2) }}
                                        @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                            <br>
                                            <span class="text-xs text-gray-500">({{ $invoice->getTotalInstallments() }} installments of PHP {{ number_format($invoice->getInstallmentAmount(), 2) }})</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($invoice->status === 'paid') bg-green-100 text-green-800
                                            @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
                                            @elseif($invoice->status === 'partial') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($invoice->status) }}
                                            @if($invoice->status === \App\Models\Invoice::STATUS_PARTIAL)
                                                ({{ $invoice->installments_paid }}/{{ $invoice->getTotalInstallments() }})
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="#" wire:click.prevent="viewInvoice({{ $invoice->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                View
                                            </a>
                                            
                                            @if(in_array($invoice->status, ['pending', 'overdue', 'partial']))
                                                <a href="#" wire:click.prevent="payWithGCash({{ $invoice->id }})" class="text-green-600 hover:text-green-900 mr-3">
                                                    Pay with GCash
                                                </a>
                                                
                                                <a href="#" wire:click.prevent="payWithCreditCard({{ $invoice->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                                    Pay with Card
                                                </a>
                                            @endif
                                            
                                            <a href="#" wire:click.prevent="downloadInvoice({{ $invoice->id }})" class="text-gray-600 hover:text-gray-900">
                                                Download
                                            </a>
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

    <!-- View Invoice Modal -->
    <div x-data="{ show: @entangle('showViewInvoiceModal') }">
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
                        
                        <!-- Lawyer Information -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Lawyer Information</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-sm text-gray-500">Name:</div>
                                <div class="text-sm font-medium">
                                    @if($selectedInvoice->lawyer->isLawFirm() && $selectedInvoice->lawyer->lawFirmProfile)
                                        {{ $selectedInvoice->lawyer->lawFirmProfile->firm_name }}
                                    @elseif($selectedInvoice->lawyer->isLawyer() && $selectedInvoice->lawyer->lawyerProfile)
                                        {{ $selectedInvoice->lawyer->lawyerProfile->first_name }} {{ $selectedInvoice->lawyer->lawyerProfile->last_name }}
                                    @elseif($selectedInvoice->lawyer->lawFirmLawyer)
                                        {{ $selectedInvoice->lawyer->lawFirmLawyer->first_name }} {{ $selectedInvoice->lawyer->lawFirmLawyer->last_name }}
                                    @else
                                        {{ $selectedInvoice->lawyer->name ?? 'N/A' }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">Email:</div>
                                <div class="text-sm font-medium">{{ $selectedInvoice->lawyer->email ?? 'N/A' }}</div>
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (PHP)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedInvoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($item->type) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
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
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Tax:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">PHP {{ number_format($selectedInvoice->tax, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Discount:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">PHP {{ number_format($selectedInvoice->discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total:</td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">PHP {{ number_format($selectedInvoice->total, 2) }}</td>
                                </tr>
                                @if($selectedInvoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Payment Plan:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                                        {{ $selectedInvoice->getTotalInstallments() }} installments
                                    </td>
                                </tr>
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
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
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
                    
                    @if(in_array($selectedInvoice->status, [\App\Models\Invoice::STATUS_PENDING, \App\Models\Invoice::STATUS_OVERDUE, \App\Models\Invoice::STATUS_PARTIAL]))
                        <div class="mt-6 border-t pt-4">
                            <h3 class="text-md font-medium text-gray-700 mb-2">Pay Invoice</h3>
                            @if($selectedInvoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                <p class="text-sm text-gray-600 mb-3">
                                    Next Installment Amount: PHP {{ number_format($selectedInvoice->getInstallmentAmount(), 2) }}
                                </p>
                            @endif
                            <div class="flex space-x-2">
                                <button wire:click="payWithGCash({{ $selectedInvoice->id }})" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Pay with GCash
                                </button>
                                <button wire:click="payWithCreditCard({{ $selectedInvoice->id }})" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Pay with Card
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end">
                <div class="flex">
                    <x-button wire:click="downloadInvoice({{ $selectedInvoice ? $selectedInvoice->id : 0 }})" class="mr-3">
                        <i class="fas fa-download mr-2"></i> Download
                    </x-button>
                    <x-button wire:click="closeViewInvoiceModal">
                        Close
                    </x-button>
                </div>
            </div>
        </x-modal>
    </div>
</div> 