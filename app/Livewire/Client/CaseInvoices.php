<?php

namespace App\Livewire\Client;

use App\Models\Invoice;
use App\Models\LegalCase;
use App\Services\PayMongoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CaseInvoices extends Component
{
    public LegalCase $case;
    public $invoices = [];
    
    // View invoice modal
    public $showViewInvoiceModal = false;
    public $selectedInvoice = null;
    
    // Payment status
    public $paymentStatus = null;
    public $paymentMessage = null;

    public function mount(LegalCase $case)
    {
        $this->case = $case;
        $this->loadInvoices();
    }
    
    public function loadInvoices()
    {
        $this->invoices = $this->case->invoices()
            ->where('client_id', Auth::id())
            ->whereIn('status', ['pending', 'partial', 'paid', 'overdue'])
            ->with(['items', 'payments'])
            ->latest()
            ->get();
    }
    
    public function render()
    {
        return view('livewire.client.case-invoices');
    }
    
    public function viewInvoice($invoiceId)
    {
        $this->selectedInvoice = Invoice::with(['items', 'payments', 'lawyer'])
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
        
        $payMongoService = new PayMongoService();
        $result = $payMongoService->createSource($invoice, 'gcash');
        
        if ($result['success'] && isset($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        } else {
            $this->paymentStatus = 'error';
            $this->paymentMessage = $result['message'] ?? 'Failed to create payment source';
        }
    }
    
    public function payWithCard($invoiceId)
    {
        $invoice = Invoice::where('client_id', Auth::id())->findOrFail($invoiceId);
        
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
}
