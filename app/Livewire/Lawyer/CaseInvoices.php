<?php

namespace App\Livewire\Lawyer;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LegalCase;
use App\Models\Payment;
use App\Services\NotificationService;
use App\Services\PayMongoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CaseInvoices extends Component
{
    use WithPagination, WithFileUploads;

    public LegalCase $case;
    public $invoices = [];
    public $isPrimaryLawyer = false; // Flag to track if current user is primary lawyer
    
    // Invoice form properties
    public $showInvoiceModal = false;
    public $editMode = false;
    public $invoiceId = null;
    public $invoiceTitle = '';
    public $invoiceDescription = '';
    public $invoiceTax = 0;
    public $invoiceDiscount = 0;
    public $invoiceIssueDate;
    public $invoiceDueDate;
    public $invoiceNotes = '';
    public $invoicePaymentPlan = 'full';
    
    // Invoice items
    public $invoiceItems = [];
    public $nextItemKey = 0;
    
    // Payment modal properties
    public $showPaymentModal = false;
    public $selectedInvoice = null;
    public $paymentMethod = '';
    public $paymentAmount = 0;
    public $paymentDate;
    public $paymentNotes = '';
    public $receipt = null;
    
    // View invoice modal
    public $showViewInvoiceModal = false;
    
    protected $listeners = [
        'refresh' => '$refresh'
    ];
    
    protected $rules = [
        'invoiceTitle' => 'required|string|max:255',
        'invoiceDescription' => 'nullable|string',
        'invoiceTax' => 'numeric|min:0',
        'invoiceDiscount' => 'numeric|min:0',
        'invoiceIssueDate' => 'required|date',
        'invoiceDueDate' => 'required|date|after_or_equal:invoiceIssueDate',
        'invoiceNotes' => 'nullable|string',
        'invoicePaymentPlan' => 'required|in:full,3_months,6_months,1_year',
        'invoiceItems' => 'required|array|min:1',
        'invoiceItems.*.description' => 'required|string',
        'invoiceItems.*.quantity' => 'nullable|numeric|min:0',
        'invoiceItems.*.unit_price' => 'required|numeric|min:0',
        'invoiceItems.*.type' => 'required|string|in:service,expense,billable_hours,other',
    ];
    
    // Separate validation rules for payments
    protected $paymentRules = [
        'paymentMethod' => 'required',
        'paymentAmount' => 'required|numeric|min:0.01',
        'paymentDate' => 'required|date',
        'paymentNotes' => 'nullable|string',
        'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ];

    public function mount(LegalCase $case)
    {
        $this->case = $case;
        $this->loadInvoices();
        $this->paymentDate = now()->format('Y-m-d');
        $this->addInvoiceItem();
        $this->invoicePaymentPlan = Invoice::PAYMENT_PLAN_FULL;
        
        // Check if the current user is the primary lawyer for this case
        $this->isPrimaryLawyer = $this->checkIfPrimaryLawyer();
    }
    
    /**
     * Check if the current user is the primary lawyer for this case
     * Only primary lawyers are allowed to create invoices
     */
    private function checkIfPrimaryLawyer()
    {
        $userId = Auth::id();
        
        // Case 1: User is the primary lawyer in the lawyer_id field
        if ($this->case->lawyer_id === $userId) {
            return true;
        }
        
        // Case 2: User is marked as primary in the case_lawyer pivot table
        return $this->case->teamLawyers()
            ->where('user_id', $userId)
            ->where('is_primary', true)
            ->exists();
    }
    
    public function loadInvoices()
    {
        $this->invoices = $this->case->invoices()
            ->with(['items', 'payments', 'client.clientProfile'])
            ->latest()
            ->get();
    }
    
    public function render()
    {
        return view('livewire.lawyer.case-invoices');
    }
    
    // Invoice Modal Methods
    
    public function openInvoiceModal()
    {
        // Only allow primary lawyers to create invoices
        if (!$this->isPrimaryLawyer) {
            $this->dispatch('show-message', message: "Only the primary lawyer can create invoices for this case.", type: 'error');
            return;
        }
        
        $this->resetInvoiceForm();
        $this->showInvoiceModal = true;
        $this->editMode = false;
        
        // Dispatch browser events for Alpine.js
        $this->dispatch('invoice-modal-toggle');
        
        // Dispatch another global event to ensure all Alpine instances receive it
        $this->dispatch('open-invoice-modal');
    }
    
    public function editInvoice($invoiceId)
    {
        // Only allow primary lawyers to edit invoices
        if (!$this->isPrimaryLawyer) {
            $this->dispatch('show-message', message: "Only the primary lawyer can edit invoices for this case.", type: 'error');
            return;
        }
        
        $this->editMode = true;
        $this->invoiceId = $invoiceId;
        $invoice = Invoice::with('items')->findOrFail($invoiceId);
        
        $this->invoiceTitle = $invoice->title;
        $this->invoiceDescription = $invoice->description;
        $this->invoiceTax = $invoice->tax;
        $this->invoiceDiscount = $invoice->discount;
        $this->invoiceIssueDate = $invoice->issue_date->format('Y-m-d');
        $this->invoiceDueDate = $invoice->due_date->format('Y-m-d');
        $this->invoiceNotes = $invoice->notes;
        $this->invoicePaymentPlan = $invoice->payment_plan;
        
        // Load invoice items
        $this->invoiceItems = [];
        $this->nextItemKey = 0;
        
        foreach ($invoice->items as $item) {
            $this->invoiceItems[$this->nextItemKey] = [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'type' => $item->type,
            ];
            $this->nextItemKey++;
        }
        
        $this->showInvoiceModal = true;
    }
    
    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
    }
    
    public function resetInvoiceForm()
    {
        $this->reset([
            'invoiceId',
            'invoiceTitle',
            'invoiceDescription',
            'invoiceTax',
            'invoiceDiscount',
            'invoiceNotes',
            'invoiceIssueDate',
            'invoiceDueDate',
            'invoicePaymentPlan',
        ]);
        
        // Set default dates
        $this->invoiceIssueDate = now()->format('Y-m-d');
        $this->invoiceDueDate = now()->addDays(30)->format('Y-m-d');
        $this->invoicePaymentPlan = Invoice::PAYMENT_PLAN_FULL;
        
        // Reset invoice items
        $this->invoiceItems = [];
        $this->nextItemKey = 0;
        $this->addInvoiceItem();
    }
    
    public function addInvoiceItem()
    {
        $this->invoiceItems[$this->nextItemKey] = [
            'description' => '',
            'quantity' => null,
            'unit_price' => 0,
            'type' => 'service',
        ];
        
        $this->nextItemKey++;
    }
    
    public function removeInvoiceItem($key)
    {
        unset($this->invoiceItems[$key]);
        // Re-index to avoid gaps
        $this->invoiceItems = array_values($this->invoiceItems);
    }
    
    public function calculateSubtotal()
    {
        $subtotal = 0;
        foreach ($this->invoiceItems as $item) {
            if (isset($item['quantity']) && $item['quantity'] !== null) {
                $subtotal += $item['quantity'] * ($item['unit_price'] ?? 0);
            } else {
                $subtotal += $item['unit_price'] ?? 0;
            }
        }
        return $subtotal;
    }
    
    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        // Setting tax to 0 as per requirement
        $this->invoiceTax = 0;
        return $subtotal - $this->invoiceDiscount;
    }
    
    public function saveInvoice()
    {
        try {
            // Only allow primary lawyers to save invoices
            if (!$this->isPrimaryLawyer) {
                $this->dispatch('show-message', message: "Only the primary lawyer can create or edit invoices for this case.", type: 'error');
                return;
            }
            
            $this->validate();
            
            if ($this->editMode) {
                $invoice = Invoice::findOrFail($this->invoiceId);
            } else {
                $invoice = new Invoice();
                $invoice->legal_case_id = $this->case->id;
                $invoice->client_id = $this->case->client_id;
                $invoice->lawyer_id = $this->case->lawyer_id;
                $invoice->invoice_number = $this->generateInvoiceNumber();
                $invoice->status = Invoice::STATUS_DRAFT;
            }
            
            $invoice->title = $this->invoiceTitle;
            $invoice->description = $this->invoiceDescription;
            $invoice->subtotal = $this->calculateSubtotal();
            $invoice->tax = 0; // Set tax to 0 as per requirement
            $invoice->discount = $this->invoiceDiscount;
            $invoice->total = $this->calculateTotal();
            $invoice->issue_date = $this->invoiceIssueDate;
            $invoice->due_date = $this->invoiceDueDate;
            $invoice->notes = $this->invoiceNotes;
            $invoice->payment_plan = $this->invoicePaymentPlan;
            
            \Log::info('Saving invoice', [
                'data' => $invoice->toArray(),
                'lawyer_id' => auth()->id(),
                'case_id' => $this->case->id
            ]);
            
            $invoice->save();
            
            // Save invoice items
            if ($this->editMode) {
                // Delete removed items
                $existingItemIds = collect($this->invoiceItems)
                    ->pluck('id')
                    ->filter()
                    ->toArray();
                
                InvoiceItem::where('invoice_id', $invoice->id)
                    ->whereNotIn('id', $existingItemIds)
                    ->delete();
            }
            
            foreach ($this->invoiceItems as $itemData) {
                if (isset($itemData['id'])) {
                    // Update existing item
                    $item = InvoiceItem::findOrFail($itemData['id']);
                } else {
                    // Create new item
                    $item = new InvoiceItem();
                    $item->invoice_id = $invoice->id;
                }
                
                $item->description = $itemData['description'];
                $item->quantity = $itemData['quantity'];
                $item->unit_price = $itemData['unit_price'];
                $item->type = $itemData['type'];
                $item->amount = $itemData['quantity'] * $itemData['unit_price'];
                
                \Log::info('Saving invoice item', [
                    'data' => $item->toArray(),
                    'invoice_id' => $invoice->id
                ]);
                
                $item->save();
            }
            
            $this->loadInvoices();
            $this->closeInvoiceModal();
            
            $action = $this->editMode ? 'updated' : 'created';
            $this->dispatch('show-message', message: "Invoice {$action} successfully!", type: 'success');
            
            // Notify client if not a draft
            if (!$this->editMode || $invoice->status !== Invoice::STATUS_DRAFT) {
                NotificationService::invoiceNotification(
                    'invoice_created',
                    $this->case->client_id,
                    'New Invoice Available',
                    'New Invoice Available: ' . $invoice->title,
                    [
                        'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                        'case_id' => $this->case->id,
                        'invoice_id' => $invoice->id
                    ]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Invoice creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'title' => $this->invoiceTitle,
                    'case_id' => $this->case->id,
                    'total' => $this->calculateTotal()
                ]
            ]);
            
            $this->dispatch('invoiceValidationError', error: $e->getMessage());
            $this->dispatch('show-message', message: "Error creating invoice: " . $e->getMessage(), type: 'error');
        }
    }
    
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        // Get the current user
        $user = Auth::user();
        
        // First check: Is this a lawyer under a law firm?
        if ($user->firm_id) {
            // If yes, we need to include all invoices from the same firm
            $lastInvoice = Invoice::where(function($query) use ($user) {
                    // Include invoices from current lawyer
                    $query->where('lawyer_id', $user->id)
                          // OR invoices from other lawyers in the same firm
                          ->orWhereIn('lawyer_id', function($subquery) use ($user) {
                              $subquery->select('id')
                                  ->from('users')
                                  ->where('firm_id', $user->firm_id);
                          })
                          // OR invoices directly from the law firm
                          ->orWhere('lawyer_id', $user->firm_id);
                })
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->orderBy('id', 'desc')
                ->first();
        } else {
            // Regular check for non-firm lawyers
            $lastInvoice = Invoice::where('lawyer_id', $user->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->orderBy('id', 'desc')
                ->first();
        }
        
        // Backup check: look for ANY invoice with this pattern to avoid duplicates
        $pattern = sprintf("%s-%s%s-", $prefix, $year, $month);
        $maxNumberInvoice = Invoice::where('invoice_number', 'LIKE', $pattern . '%')
            ->orderByRaw('CAST(SUBSTRING(invoice_number, -4) AS UNSIGNED) DESC')
            ->first();
        
        // Use the highest invoice number from either query
        $lastSeq = 0;
        
        if ($lastInvoice) {
            $lastSeq = (int)substr($lastInvoice->invoice_number, -4);
        }
        
        if ($maxNumberInvoice) {
            $maxSeq = (int)substr($maxNumberInvoice->invoice_number, -4);
            $lastSeq = max($lastSeq, $maxSeq);
        }
        
        $sequence = $lastSeq + 1;
        return sprintf("%s-%s%s-%04d", $prefix, $year, $month, $sequence);
    }
    
    // Invoice Actions
    
    public function sendInvoice($invoiceId)
    {
        // Only allow primary lawyers to send invoices
        if (!$this->isPrimaryLawyer) {
            $this->dispatch('show-message', message: "Only the primary lawyer can send invoices for this case.", type: 'error');
            return;
        }
        
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Check if this is a pro bono case
        if ($this->case->is_pro_bono) {
            // For pro bono cases: set invoice total to 0, mark as paid automatically
            $invoice->total = 0;
            $invoice->subtotal = 0;
            $invoice->status = Invoice::STATUS_PAID;
            $invoice->save();
            
            // Create a payment record for the pro bono invoice
            $payment = new \App\Models\Payment();
            $payment->invoice_id = $invoice->id;
            $payment->client_id = $invoice->client_id;
            $payment->amount = 0;
            $payment->payment_method = \App\Models\Payment::METHOD_PRO_BONO;
            $payment->status = \App\Models\Payment::STATUS_SUCCESS;
            $payment->payment_date = now();
            $payment->payment_notes = 'Pro bono case - automatically marked as paid';
            $payment->save();
            
            $this->loadInvoices();
            $this->dispatch('show-message', message: 'Pro bono invoice sent and automatically marked as paid!', type: 'success');
            
            // Notify client about pro bono invoice
            NotificationService::invoiceNotification(
                'invoice_pro_bono',
                $this->case->client_id,
                'Pro Bono Invoice - No Payment Required',
                'Pro Bono Invoice (No Payment Required): ' . $invoice->invoice_number,
                [
                    'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                    'case_id' => $this->case->id,
                    'invoice_id' => $invoice->id
                ]
            );
        } else {
            // Regular invoice
            $invoice->status = Invoice::STATUS_PENDING;
            $invoice->save();
            
            $this->loadInvoices();
            $this->dispatch('show-message', message: 'Invoice sent to client successfully!', type: 'success');
            
            // Notify client - Fix: Use static method instead of instance method
            NotificationService::invoiceNotification(
                'invoice_available',
                $this->case->client_id,
                'New Invoice Available',
                'New Invoice Available: ' . $invoice->title,
                [
                    'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                    'case_id' => $this->case->id,
                    'invoice_id' => $invoice->id
                ]
            );
        }
    }
    
    public function cancelInvoice($invoiceId)
    {
        // Only allow primary lawyers to cancel invoices
        if (!$this->isPrimaryLawyer) {
            $this->dispatch('show-message', message: "Only the primary lawyer can cancel invoices for this case.", type: 'error');
            return;
        }
        
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->status = Invoice::STATUS_CANCELLED;
        $invoice->save();
        
        $this->loadInvoices();
        $this->dispatch('show-message', message: 'Invoice cancelled successfully!', type: 'success');
    }
    
    public function generatePaymentLink($invoiceId)
    {
        // Only allow primary lawyers to generate payment links
        if (!$this->isPrimaryLawyer) {
            $this->dispatch('show-message', message: "Only the primary lawyer can generate payment links for this case.", type: 'error');
            return;
        }
        
        $invoice = Invoice::findOrFail($invoiceId);
        $payMongoService = new PayMongoService();
        
        // Generate GCash payment link
        $result = $payMongoService->createSource($invoice, 'gcash');
        
        if ($result['success']) {
            $this->loadInvoices();
            $this->dispatch('show-message', message: 'Payment link generated successfully!', type: 'success');
        } else {
            $this->dispatch('show-message', message: 'Failed to generate payment link: ' . ($result['message'] ?? 'Unknown error'), type: 'error');
        }
    }
    
    // Payment Modal Methods
    
    public function openPaymentModal($invoiceId)
    {
        $this->resetPaymentForm();
        $this->selectedInvoice = Invoice::findOrFail($invoiceId);
        $this->paymentAmount = $this->selectedInvoice->total;
        $this->showPaymentModal = true;
    }
    
    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
    }
    
    public function resetPaymentForm()
    {
        $this->reset([
            'selectedInvoice',
            'paymentMethod',
            'paymentAmount',
            'paymentNotes',
            'receipt',
        ]);
        
        $this->paymentDate = now()->format('Y-m-d');
    }
    
    public function recordPayment()
    {
        $this->validate($this->paymentRules);
        
        $receiptPath = null;
        if ($this->receipt) {
            $receiptPath = $this->receipt->store('receipts', 'public');
        }
        
        $payment = new Payment();
        $payment->invoice_id = $this->selectedInvoice->id;
        $payment->client_id = $this->case->client_id;
        $payment->amount = $this->paymentAmount;
        $payment->payment_method = $this->paymentMethod;
        $payment->status = Payment::STATUS_SUCCESS;
        $payment->payment_date = Carbon::parse($this->paymentDate);
        $payment->receipt_path = $receiptPath;
        $payment->save();
        
        // Invoice status will be updated via observer
        
        $this->loadInvoices();
        $this->closePaymentModal();
        $this->dispatch('show-message', message: 'Payment recorded successfully!', type: 'success');
        
        // Notify client
        NotificationService::invoiceNotification(
            'payment_recorded',
            $this->case->client_id,
            'Payment Recorded',
            'Payment Recorded: ' . $this->selectedInvoice->invoice_number,
            [
                'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                'case_id' => $this->case->id,
                'invoice_id' => $this->selectedInvoice->id
            ]
        );
    }
    
    // View Invoice Modal
    
    public function viewInvoice($invoiceId)
    {
        $this->selectedInvoice = Invoice::with(['items', 'payments', 'client', 'lawyer'])->findOrFail($invoiceId);
        $this->showViewInvoiceModal = true;
    }
    
    public function closeViewInvoiceModal()
    {
        $this->showViewInvoiceModal = false;
    }
}
