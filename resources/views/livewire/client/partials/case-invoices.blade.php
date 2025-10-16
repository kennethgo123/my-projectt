<div class="bg-white shadow-md rounded-lg p-6">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-2">My Invoices</h3>
        <p class="text-sm text-gray-600">View and pay your invoices for this case</p>
    </div>

    @if(session('payment_status'))
        <div class="mb-6 p-4 rounded-md 
            @if(session('payment_status') === 'success') bg-green-100 text-green-700 border border-green-200
            @elseif(session('payment_status') === 'error') bg-red-100 text-red-700 border border-red-200
            @else bg-blue-100 text-blue-700 border border-blue-200 @endif">
            {{ session('payment_message') }}
        </div>
    @endif

    <!-- Invoices List -->
    <div class="overflow-x-auto">
        @if(count($invoices) > 0)
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($invoices as $invoice)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->issue_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->due_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($invoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                    <div>PHP {{ number_format($invoice->getInstallmentAmount(), 2) }} / installment</div>
                                    <div class="text-xs text-gray-500">
                                        (Total: PHP {{ number_format($invoice->total, 2) }})
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        @if($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_3_MONTHS)
                                            3 Installments ({{ $invoice->installments_paid }}/3 paid)
                                        @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_6_MONTHS)
                                            6 Installments ({{ $invoice->installments_paid }}/6 paid)
                                        @elseif($invoice->payment_plan === \App\Models\Invoice::PAYMENT_PLAN_1_YEAR)
                                            12 Installments ({{ $invoice->installments_paid }}/12 paid)
                                        @endif
                                    </div>
                                @else
                                    PHP {{ number_format($invoice->total, 2) }}
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
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button wire:click="viewInvoice({{ $invoice->id }})" class="text-blue-600 hover:text-blue-900" title="View Invoice">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    
                                    @if(in_array($invoice->status, ['pending', 'overdue', 'partial']))
                                        <button wire:click="payWithGCash({{ $invoice->id }})" class="text-green-600 hover:text-green-900" title="Pay with GCash">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                        </button>
                                        
                                        <button wire:click="payWithCard({{ $invoice->id }})" class="text-blue-600 hover:text-blue-900" title="Pay with Card">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="bg-white p-6 text-center">
                <p class="text-gray-500">No invoices available for this case.</p>
            </div>
        @endif
    </div>
</div>

<!-- View Invoice Modal -->
<div x-data="{ showModal: @entangle('showViewInvoiceModal') }" 
     x-show="showModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="view-invoice-modal-title" 
     role="dialog" 
     aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showModal" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                @if($selectedInvoice)
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="view-invoice-modal-title">
                                Invoice Details
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $selectedInvoice->invoice_number }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-700">Status:</div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($selectedInvoice->status === 'paid') bg-green-100 text-green-800
                                @elseif($selectedInvoice->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($selectedInvoice->status === 'overdue') bg-red-100 text-red-800
                                @elseif($selectedInvoice->status === 'draft') bg-gray-100 text-gray-800
                                @elseif($selectedInvoice->status === 'partial') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($selectedInvoice->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">From:</h4>
                            <p class="text-sm">{{ $selectedInvoice->lawyer->name }}</p>
                            <p class="text-sm text-gray-500">{{ $selectedInvoice->lawyer->email }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">To:</h4>
                            <p class="text-sm">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Invoice Date:</h4>
                            <p class="text-sm">{{ $selectedInvoice->issue_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Due Date:</h4>
                            <p class="text-sm">{{ $selectedInvoice->due_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Case:</h4>
                            <p class="text-sm">{{ $selectedInvoice->legalCase->title }}</p>
                        </div>
                    </div>
                    
                    <!-- Invoice Items Table -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Invoice Items:</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price<(PHP)/th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedInvoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($item->type) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PHP {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PHP {{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Invoice Totals -->
                    <div class="flex justify-end mb-6">
                        <div class="w-64">
                            <div class="flex justify-between py-1">
                                <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-sm font-medium text-gray-700">Tax:</span>
                                <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->tax, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="text-sm font-medium text-gray-700">Discount:</span>
                                <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->discount, 2) }}</span>
                            </div>
                            <div class="flex justify-between py-1 font-bold border-t">
                                <span class="text-sm font-medium text-gray-700">Total:</span>
                                <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->total, 2) }}</span>
                            </div>

                            <!-- Payment Plan Details -->
                            @if($selectedInvoice->payment_plan !== null)
                                <div class="flex justify-between py-1 mt-2">
                                    <span class="text-sm font-medium text-gray-700">Payment Plan:</span>
                                    <span class="text-sm text-gray-900">
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
                                    </span>
                                </div>
                                @if($selectedInvoice->payment_plan !== \App\Models\Invoice::PAYMENT_PLAN_FULL)
                                    <div class="flex justify-between py-1">
                                        <span class="text-sm font-medium text-gray-700">Installment Amount:</span>
                                        <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->getInstallmentAmount(), 2) }}</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="text-sm font-medium text-gray-700">Installments Paid:</span>
                                        <span class="text-sm text-gray-900">{{ $selectedInvoice->installments_paid }} / {{ $selectedInvoice->getTotalInstallments() }}</span>
                                    </div>
                                    <div class="flex justify-between py-1">
                                        <span class="text-sm font-medium text-gray-700">Remaining Balance:</span>
                                        <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->total - ($selectedInvoice->getInstallmentAmount() * $selectedInvoice->installments_paid), 2) }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Payment History -->
                    @if($selectedInvoice->payments->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Payment History:</h4>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($selectedInvoice->payments as $payment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PHP {{ number_format($payment->amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($payment->status === 'success') bg-green-100 text-green-800
                                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    
                    <!-- Payment Options -->
                    @if(in_array($selectedInvoice->status, ['pending', 'overdue', 'partial']))
                        <div class="mt-8 mb-4 border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Payment Options:</h4>
                            <div class="flex space-x-4">
                                <button wire:click="payWithGCash({{ $selectedInvoice->id }})" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Pay with GCash
                                </button>
                                <button wire:click="payWithCard({{ $selectedInvoice->id }})" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Pay with Card
                                </button>
                            </div>
                        </div>
                    @endif
                    
                @endif
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" wire:click="closeViewInvoiceModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div> 