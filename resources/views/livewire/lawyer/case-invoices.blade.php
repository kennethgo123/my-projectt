<div class="bg-white shadow-md rounded-lg p-6">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('livewire:load', function() {
                Livewire.hook('message.failed', (message, component) => {
                    console.error('Livewire Error:', message);
                });
                
                Livewire.on('invoiceValidationError', (error) => {
                    console.error('Invoice Validation Error:', error);
                });
                
                Livewire.on('show-message', (event) => {
                    console.log('Show Message:', event);
                    alert(event.message); // Simple alert for now
                });
            });
        });
    </script>

    <!-- Flash Messages -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-on:show-message.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => { show = false }, 5000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="fixed top-4 right-4 z-50 max-w-sm"
         :class="{'bg-green-100 border-green-400 text-green-700': type === 'success', 
                  'bg-red-100 border-red-400 text-red-700': type === 'error'}"
         style="display: none;">
        <div class="p-4 rounded-lg border shadow-md">
            <div class="flex items-start">
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-medium" x-text="message"></p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button @click="show = false" class="inline-flex text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Case Invoices</h3>
        @if($isPrimaryLawyer)
        <button wire:click="openInvoiceModal" class="mt-3 md:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Invoice
        </button>
        @else
        <div class="mt-3 md:mt-0 text-sm text-gray-600 bg-gray-100 p-2 rounded">
            Only the primary lawyer can create invoices
        </div>
        @endif
    </div>

    <!-- Invoices List -->
    <div class="overflow-x-auto">
        @if(count($invoices) > 0)
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead>
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $invoice->client->clientProfile->first_name ?? 'N/A' }} {{ $invoice->client->clientProfile->last_name ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->issue_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->due_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PHP {{ number_format($invoice->total, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($invoice->status === 'paid') bg-green-100 text-green-800
                                    @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($invoice->status === 'overdue') bg-red-100 text-red-800
                                    @elseif($invoice->status === 'draft') bg-gray-100 text-gray-800
                                    @elseif($invoice->status === 'partial') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($invoice->status) }}
                                    @if($invoice->status === \App\Models\Invoice::STATUS_PARTIAL)
                                        ({{ $invoice->installments_paid }}/{{ $invoice->getTotalInstallments() }})
                                    @endif
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
                                    
                                    @if($invoice->status === 'draft')
                                        @if($isPrimaryLawyer)
                                        <button wire:click="editInvoice({{ $invoice->id }})" class="text-yellow-600 hover:text-yellow-900" title="Edit Invoice">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        
                                        <button wire:click="sendInvoice({{ $invoice->id }})" class="text-green-600 hover:text-green-900" title="Send Invoice">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </button>
                                        
                                        <button wire:click="cancelInvoice({{ $invoice->id }})" class="text-red-600 hover:text-red-900" title="Cancel Invoice">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                        @endif
                                    @endif
                                    
                                    @if(in_array($invoice->status, ['pending', 'overdue']))
                                        <button wire:click="openPaymentModal({{ $invoice->id }})" class="text-green-600 hover:text-green-900" title="Record Payment">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </button>
                                        
                                        <button wire:click="generatePaymentLink({{ $invoice->id }})" class="text-purple-600 hover:text-purple-900" title="Generate Payment Link">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
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
                <p class="text-gray-500">No invoices created yet for this case.</p>
                @if($isPrimaryLawyer)
                <button wire:click="openInvoiceModal" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Create First Invoice
                </button>
                @else
                <div class="mt-4 text-sm text-gray-600 bg-gray-100 p-2 rounded inline-block">
                    Only the primary lawyer can create invoices
                </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Create/Edit Invoice Modal -->
    <div x-data="{ 
            showModal: @entangle('showInvoiceModal'),
            init() {
                this.$watch('showModal', value => {
                    if (value) {
                        document.body.classList.add('overflow-hidden');
                    } else {
                        document.body.classList.remove('overflow-hidden');
                    }
                });
                
                // Additional global event listener
                window.addEventListener('open-invoice-modal', () => {
                    this.showModal = true;
                });
            }
        }" 
         x-show="showModal" 
         x-cloak
         @open-invoice-modal.window="showModal = true"
         class="fixed inset-0 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         style="z-index: 9999; position: fixed; top: 0; right: 0; bottom: 0; left: 0;"
    >
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
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ $editMode ? 'Edit Invoice' : 'Create New Invoice' }}
                            </h3>
                            <div class="mt-4 space-y-4 w-full">
                                <!-- Invoice Form -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="invoiceTitle" class="block text-sm font-medium text-gray-700">Invoice Title</label>
                                        <input type="text" 
                                               wire:model="invoiceTitle" 
                                               id="invoiceTitle" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('invoiceTitle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="invoiceDescription" class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea wire:model="invoiceDescription" 
                                                  id="invoiceDescription" 
                                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                                  rows="1"></textarea>
                                        @error('invoiceDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="invoiceIssueDate" class="block text-sm font-medium text-gray-700">Issue Date</label>
                                        <input type="date" 
                                               wire:model="invoiceIssueDate" 
                                               id="invoiceIssueDate" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('invoiceIssueDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="invoiceDueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                                        <input type="date" 
                                               wire:model="invoiceDueDate" 
                                               id="invoiceDueDate" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('invoiceDueDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="invoiceDiscount" class="block text-sm font-medium text-gray-700">Discount (PHP)</label>
                                        <input type="number" 
                                               wire:model="invoiceDiscount" 
                                               id="invoiceDiscount" 
                                               step="0.01" 
                                               min="0" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('invoiceDiscount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="invoicePaymentPlan" class="block text-sm font-medium text-gray-700">Payment Plan</label>
                                        <select wire:model="invoicePaymentPlan" 
                                               id="invoicePaymentPlan" 
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <option value="full">Full Payment</option>
                                            <option value="3_months">3 Monthly Installments</option>
                                            <option value="6_months">6 Monthly Installments</option>
                                            <option value="1_year">12 Monthly Installments</option>
                                        </select>
                                        @error('invoicePaymentPlan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <!-- Invoice Items -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="text-md font-medium text-gray-700">Invoice Items</h4>
                                        <button type="button" 
                                                wire:click="addInvoiceItem" 
                                                class="inline-flex items-center px-3 py-1 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Add Item
                                        </button>
                                    </div>
                                    
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        @foreach($invoiceItems as $key => $item)
                                            <div class="grid grid-cols-12 gap-2 mb-3 items-end">
                                                <div class="col-span-5">
                                                    <label class="block text-xs font-medium text-gray-700">Description</label>
                                                    <input type="text" 
                                                           wire:model="invoiceItems.{{ $key }}.description" 
                                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                    @error("invoiceItems.{$key}.description") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-gray-700">Type</label>
                                                    <select wire:model="invoiceItems.{{ $key }}.type" 
                                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                        <option value="service">Service</option>
                                                        <option value="expense">Expense</option>
                                                        <option value="billable_hours">Billable Hours</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                    @error("invoiceItems.{$key}.type") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div class="col-span-3">
                                                    <label class="block text-xs font-medium text-gray-700">Price (PHP)</label>
                                                    <input type="number" 
                                                           wire:model="invoiceItems.{{ $key }}.unit_price" 
                                                           step="0.01" 
                                                           min="0" 
                                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                    @error("invoiceItems.{$key}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                                </div>
                                                
                                                <div class="col-span-1">
                                                    <label class="block text-xs font-medium text-gray-700">Amount</label>
                                                    <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm text-sm">
                                                        PHP {{ number_format($item['unit_price'] ?? 0, 2) }}
                                                    </div>
                                                </div>
                                                
                                                <div class="col-span-1">
                                                    <button type="button" 
                                                            wire:click="removeInvoiceItem({{ $key }})" 
                                                            class="inline-flex items-center p-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <!-- Totals -->
                                        <div class="mt-4 border-t pt-4">
                                            <div class="flex justify-end">
                                                <div class="w-64">
                                                    <div class="flex justify-between py-1">
                                                        <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                                        <span class="text-sm text-gray-900">PHP {{ number_format($this->calculateSubtotal(), 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between py-1">
                                                        <span class="text-sm font-medium text-gray-700">Discount:</span>
                                                        <span class="text-sm text-gray-900">PHP {{ number_format($invoiceDiscount, 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between py-1 font-bold border-t">
                                                        <span class="text-sm font-medium text-gray-700">Total:</span>
                                                        <span class="text-sm text-gray-900">PHP {{ number_format($this->calculateTotal(), 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="invoiceNotes" class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea wire:model="invoiceNotes" 
                                              id="invoiceNotes" 
                                              class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                              rows="2"></textarea>
                                    @error('invoiceNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="saveInvoice" 
                            wire:loading.attr="disabled"
                            wire:target="saveInvoice"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <span wire:loading wire:target="saveInvoice" class="mr-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        {{ $editMode ? 'Update Invoice' : 'Create Invoice' }}
                    </button>
                    <button type="button" 
                            wire:click="closeInvoiceModal" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-data="{ showModal: @entangle('showPaymentModal') }" 
         x-show="showModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="payment-modal-title" 
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
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="payment-modal-title">
                                Record Payment
                            </h3>
                            <div class="mt-4 space-y-4 w-full">
                                @if($selectedInvoice)
                                    <div class="bg-gray-50 p-4 rounded mb-4">
                                        <p class="text-sm font-medium">Invoice: <span class="font-normal">{{ $selectedInvoice->invoice_number }}</span></p>
                                        <p class="text-sm font-medium">Total Amount: <span class="font-normal">PHP {{ number_format($selectedInvoice->total, 2) }}</span></p>
                                    </div>
                                @endif
                                
                                <div>
                                    <label for="paymentMethod" class="block text-sm font-medium text-gray-700">Payment Method</label>
                                    <select wire:model="paymentMethod" 
                                            id="paymentMethod" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        <option value="">Select a payment method</option>
                                        <option value="gcash">GCash</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="debit_card">Debit Card</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('paymentMethod') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="paymentAmount" class="block text-sm font-medium text-gray-700">Amount</label>
                                    <input type="number" 
                                           wire:model="paymentAmount" 
                                           id="paymentAmount" 
                                           step="0.01" 
                                           min="0" 
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('paymentAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="paymentDate" class="block text-sm font-medium text-gray-700">Payment Date</label>
                                    <input type="date" 
                                           wire:model="paymentDate" 
                                           id="paymentDate" 
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('paymentDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="receipt" class="block text-sm font-medium text-gray-700">Receipt (optional)</label>
                                    <input type="file" 
                                           wire:model="receipt" 
                                           id="receipt" 
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('receipt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">Accepted file types: PDF, JPG, JPEG, PNG (max 5MB)</p>
                                </div>
                                
                                <div>
                                    <label for="paymentNotes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                                    <textarea wire:model="paymentNotes" 
                                              id="paymentNotes" 
                                              class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" 
                                              rows="2"></textarea>
                                    @error('paymentNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="recordPayment" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Record Payment
                    </button>
                    <button type="button" 
                            wire:click="closePaymentModal" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
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
                                <p class="text-sm">{{ $selectedInvoice->client->clientProfile->first_name }} {{ $selectedInvoice->client->clientProfile->last_name }}</p>
                                <p class="text-sm text-gray-500">{{ $selectedInvoice->client->email }}</p>
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
                        
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Invoice Items:</h4>
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price(PHP)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($selectedInvoice->items as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($item->type) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PHP {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">PHP {{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="flex justify-end mb-6">
                            <div class="w-64">
                                <div class="flex justify-between py-1">
                                    <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                                    <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-sm font-medium text-gray-700">Discount:</span>
                                    <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->discount, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-1 font-bold border-t">
                                    <span class="text-sm font-medium text-gray-700">Total:</span>
                                    <span class="text-sm text-gray-900">PHP {{ number_format($selectedInvoice->total, 2) }}</span>
                                </div>
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
                                @endif
                            </div>
                        </div>
                        
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
                        
                        @if($selectedInvoice->notes)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-1">Notes:</h4>
                                <p class="text-sm text-gray-600">{{ $selectedInvoice->notes }}</p>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="closeViewInvoiceModal" 
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
