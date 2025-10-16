<?php

namespace App\Livewire\Client;

use App\Models\Invoice;
use App\Services\PayMongoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // View invoice modal
    public $showViewInvoiceModal = false;
    public $selectedInvoice = null;
    
    // Payment status
    public $paymentStatus = null;
    public $paymentMessage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function render()
    {
        $clientId = Auth::id();
        
        $invoicesQuery = Invoice::where('client_id', $clientId)
            ->whereIn('status', ['pending', 'partial', 'paid', 'overdue'])
            ->with([
                'legalCase',
                'lawyer',
                'lawyer.lawyerProfile',
                'lawyer.lawFirmProfile',
                'lawyer.lawFirmLawyer',
                'items',
                'payments'
            ]);
        
        if ($this->search) {
            $invoicesQuery->where(function($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%')
                    ->orWhereHas('lawyer', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }
        
        if ($this->status && $this->status !== 'all') {
            $invoicesQuery->where('status', $this->status);
        }
        
        $invoices = $invoicesQuery
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.client.invoice-management', [
            'invoices' => $invoices,
            'statuses' => [
                'all' => 'All',
                'pending' => 'Pending',
                'paid' => 'Paid',
                'partial' => 'Partially Paid',
                'overdue' => 'Overdue',
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
    
    public function viewInvoice($invoiceId)
    {
        $this->selectedInvoice = Invoice::with([
                'items', 
                'payments', 
                'lawyer', 
                'lawyer.lawyerProfile',
                'lawyer.lawFirmProfile',
                'lawyer.lawFirmLawyer',
                'legalCase'
            ])
            ->where('client_id', Auth::id())
            ->findOrFail($invoiceId);
        $this->showViewInvoiceModal = true;
    }
    
    public function closeViewInvoiceModal()
    {
        $this->showViewInvoiceModal = false;
    }
    
    public function payWithGCash($invoiceId)
    {
        $invoice = Invoice::where('client_id', Auth::id())->findOrFail($invoiceId);
        
        // Verify invoice can be paid
        if ($invoice->status === Invoice::STATUS_PAID) {
            $this->paymentStatus = 'error';
            $this->paymentMessage = 'This invoice has already been paid.';
            return;
        }
        
        $payMongoService = new PayMongoService();
        $result = $payMongoService->createSource($invoice, 'gcash');
        
        if ($result['success'] && isset($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        } else {
            $this->paymentStatus = 'error';
            $this->paymentMessage = $result['message'] ?? 'Failed to create payment source';
        }
    }
    
    public function payWithCreditCard($invoiceId)
    {
        $invoice = Invoice::where('client_id', Auth::id())->findOrFail($invoiceId);
        
        // Verify invoice can be paid
        if ($invoice->status === Invoice::STATUS_PAID) {
            $this->paymentStatus = 'error';
            $this->paymentMessage = 'This invoice has already been paid.';
            return;
        }
        
        $payMongoService = new PayMongoService();
        $result = $payMongoService->createPaymentIntent($invoice);
        
        if ($result['success']) {
            // Store payment intent data in session
            session([
                'payment_intent_id' => $result['intent_id'],
                'client_key' => $result['client_key'],
                'invoice_id' => $invoice->id
            ]);
            
            // Redirect to a custom card payment page
            return redirect()->route('client.payment.card', ['invoice' => $invoice->id]);
        } else {
            $this->paymentStatus = 'error';
            $this->paymentMessage = $result['message'] ?? 'Failed to create payment intent';
        }
    }
    
    public function downloadInvoice($invoiceId)
    {
        // This would normally generate a PDF download
        // For now, just display a message
        session()->flash('message', 'Invoice download functionality will be implemented soon.');
    }
} 