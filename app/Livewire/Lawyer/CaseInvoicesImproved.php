<?php

namespace App\Livewire\Lawyer;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LegalCase;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class CaseInvoicesImproved extends Component
{
    use WithPagination, WithFileUploads;

    public LegalCase $case;
    public $search = '';
    public $status = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $isPrimaryLawyer = false;
    
    // Invoice form properties
    public $showInvoiceModal = false;
    public $editMode = false;
    public $invoiceId = null;
    public $invoiceTitle = '';
    public $invoiceDescription = '';
    public $invoiceTax = 0;
    public $invoiceIssueDate;
    public $invoiceDueDate;
    public $invoiceNotes = '';
    public $invoicePaymentPlan = 'full';
    
    // Invoice items
    public $invoiceItems = [];
    public $nextItemKey = 0;
    
    // View invoice modal
    public $showViewInvoiceModal = false;
    public $selectedInvoice = null;
    
    protected $listeners = [
        'refresh' => '$refresh'
    ];
    
    protected $rules = [
        'invoiceTitle' => 'required|string|max:255',
        'invoiceDescription' => 'nullable|string',
        'invoiceTax' => 'numeric|min:0',
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

    public function mount(LegalCase $case)
    {
        $this->case = $case->load('client.clientProfile');
        $this->invoiceIssueDate = now()->format('Y-m-d');
        $this->invoiceDueDate = now()->addDays(30)->format('Y-m-d');
        $this->invoicePaymentPlan = Invoice::PAYMENT_PLAN_FULL;
        $this->addInvoiceItem();
        
        // Check if the current user is the primary lawyer for this case
        $this->isPrimaryLawyer = $this->checkIfPrimaryLawyer();
    }
    
    /**
     * Check if the current user is the primary lawyer for this case
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
    
    public function render()
    {
        $invoicesQuery = Invoice::where('legal_case_id', $this->case->id)
            ->with(['client.role', 'client.clientProfile', 'legalCase', 'items', 'payments']);
        
        if ($this->search) {
            $invoicesQuery->where(function($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->status) {
            $invoicesQuery->where('status', $this->status);
        }
        
        $invoices = $invoicesQuery
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.lawyer.case-invoices-improved', [
            'invoices' => $invoices,
            'statuses' => [
                '' => 'All Statuses',
                'draft' => 'Draft',
                'pending' => 'Pending',
                'paid' => 'Paid',
                'partial' => 'Partially Paid',
                'overdue' => 'Overdue',
                'cancelled' => 'Cancelled',
            ]
        ]);
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    // Invoice Modal Methods
    
    public function openInvoiceModal()
    {
        // Only allow primary lawyers to create invoices
        if (!$this->isPrimaryLawyer) {
            session()->flash('error', 'Only the primary lawyer can create invoices for this case.');
            return;
        }
        
        $this->resetInvoiceForm();
        $this->showInvoiceModal = true;
        $this->editMode = false;
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
            'invoiceNotes',
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
        if (isset($this->invoiceItems[$key])) {
            unset($this->invoiceItems[$key]);
            $this->invoiceItems = array_values($this->invoiceItems);
        }
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
        // Setting tax to 0 as per requirement
        $this->invoiceTax = 0;
        return $this->calculateSubtotal();
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
    
    public function saveInvoice()
    {
        try {
            // Only allow primary lawyers to save invoices
            if (!$this->isPrimaryLawyer) {
                session()->flash('error', 'Only the primary lawyer can create or edit invoices for this case.');
                return;
            }
            
            $this->validate();
            
            if ($this->editMode) {
                $invoice = Invoice::findOrFail($this->invoiceId);
            } else {
                $invoice = new Invoice();
                $invoice->legal_case_id = $this->case->id; // Automatically set to current case
                $invoice->client_id = $this->case->client_id; // Automatically set to case client
                $invoice->lawyer_id = Auth::id();
                $invoice->invoice_number = $this->generateInvoiceNumber();
                $invoice->status = Invoice::STATUS_DRAFT;
            }
            
            $invoice->title = $this->invoiceTitle;
            $invoice->description = $this->invoiceDescription;
            $invoice->subtotal = $this->calculateSubtotal();
            $invoice->tax = 0; // Set tax to 0 as per requirement
            $invoice->discount = 0; // Remove discount functionality
            $invoice->total = $this->calculateTotal();
            $invoice->issue_date = $this->invoiceIssueDate;
            $invoice->due_date = $this->invoiceDueDate;
            $invoice->notes = $this->invoiceNotes;
            $invoice->payment_plan = $this->invoicePaymentPlan;
            
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
            
            foreach ($this->invoiceItems as $item) {
                if (isset($item['id'])) {
                    // Update existing item
                    $invoiceItem = InvoiceItem::find($item['id']);
                } else {
                    // Create new item
                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                }
                
                $invoiceItem->description = $item['description'];
                $invoiceItem->quantity = $item['quantity'];
                $invoiceItem->unit_price = $item['unit_price'];
                $invoiceItem->amount = ($item['quantity'] ?? 1) * $item['unit_price'];
                $invoiceItem->type = $item['type'];
                $invoiceItem->save();
            }
            
            $this->showInvoiceModal = false;
            
            // Send notification to client for new invoice
            if (!$this->editMode) {
                NotificationService::invoiceNotification(
                    'invoice_created',
                    $invoice->client_id,
                    'New Invoice',
                    'New Invoice: ' . $invoice->invoice_number,
                    [
                        'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                        'case_id' => $this->case->id,
                        'invoice_id' => $invoice->id
                    ]
                );
            }
            
            session()->flash('message', $this->editMode ? 'Invoice updated successfully.' : 'Invoice created successfully.');
            $this->resetInvoiceForm();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function editInvoice($invoiceId)
    {
        // Only allow primary lawyers to edit invoices
        if (!$this->isPrimaryLawyer) {
            session()->flash('error', 'Only the primary lawyer can edit invoices for this case.');
            return;
        }
        
        $this->editMode = true;
        $this->invoiceId = $invoiceId;
        $invoice = Invoice::with('items')->findOrFail($invoiceId);
        
        $this->invoiceTitle = $invoice->title;
        $this->invoiceDescription = $invoice->description;
        $this->invoiceTax = $invoice->tax;
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
    
    public function viewInvoice($invoiceId)
    {
        $this->selectedInvoice = Invoice::with(['items', 'payments', 'client', 'lawyer', 'legalCase'])
            ->findOrFail($invoiceId);
        $this->showViewInvoiceModal = true;
    }
    
    public function closeViewInvoiceModal()
    {
        $this->showViewInvoiceModal = false;
    }
    
    public function sendInvoice($invoiceId)
    {
        // Only allow primary lawyers to send invoices
        if (!$this->isPrimaryLawyer) {
            session()->flash('error', 'Only the primary lawyer can send invoices for this case.');
            return;
        }
        
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Don't send if already sent
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            session()->flash('error', 'Invoice has already been sent.');
            return;
        }
        
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
            
            // Send notification to client about pro bono invoice
            NotificationService::invoiceNotification(
                'invoice_pro_bono',
                $invoice->client_id,
                'Pro Bono Invoice - No Payment Required',
                'Pro Bono Invoice (No Payment Required): ' . $invoice->invoice_number,
                [
                    'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                    'case_id' => $this->case->id,
                    'invoice_id' => $invoice->id
                ]
            );
            
            session()->flash('message', 'Pro bono invoice sent and automatically marked as paid.');
        } else {
            // Regular invoice: change status from draft to pending
            $invoice->status = Invoice::STATUS_PENDING;
            $invoice->save();
            
            // Send notification to client
            NotificationService::invoiceNotification(
                'invoice_sent',
                $invoice->client_id,
                'Invoice Ready for Payment',
                'Invoice Ready for Payment: ' . $invoice->invoice_number,
                [
                    'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                    'case_id' => $this->case->id,
                    'invoice_id' => $invoice->id
                ]
            );
            
            session()->flash('message', 'Invoice sent to client successfully.');
        }
    }
    
    public function cancelInvoice($invoiceId)
    {
        // Only allow primary lawyers to cancel invoices
        if (!$this->isPrimaryLawyer) {
            session()->flash('error', 'Only the primary lawyer can cancel invoices for this case.');
            return;
        }
        
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Don't cancel if paid
        if ($invoice->status === Invoice::STATUS_PAID) {
            session()->flash('error', 'Cannot cancel a paid invoice.');
            return;
        }
        
        $invoice->status = Invoice::STATUS_CANCELLED;
        $invoice->save();
        
        // Send notification to client
        NotificationService::invoiceNotification(
            'invoice_cancelled',
            $invoice->client_id,
            'Invoice Cancelled',
            'Invoice Cancelled: ' . $invoice->invoice_number,
            [
                'link' => route('client.case.view', ['case' => $this->case->id, 'tab' => 'invoices']),
                'case_id' => $this->case->id,
                'invoice_id' => $invoice->id
            ]
        );
        
        session()->flash('message', 'Invoice cancelled successfully.');
    }
    
    public function downloadInvoice($invoiceId)
    {
        // This would normally generate a PDF download
        // For now, just display a message
        session()->flash('message', 'Invoice download functionality will be implemented soon.');
    }
}
