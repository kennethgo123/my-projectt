<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\CasePhase;
use App\Models\CaseEvent;
use App\Models\CaseTask;
use App\Models\CaseDocument;
use App\Models\Invoice;
use App\Services\PayMongoService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CaseOverview extends Component
{
    public LegalCase $case;
    public $activeTab = 'overview';
    public $phases = [];
    public $currentPhase = null;
    public $upcomingEvents = [];
    public $myTasks = [];
    public $documents = [];
    public $selectedTask = null;
    
    // Payment status
    public $paymentStatus = null;
    public $paymentMessage = null;

    protected $listeners = [
        'payWithGCash',
        'payWithCard'
    ];

    public function mount(LegalCase $case)
    {
        // Ensure the user has permission to view this case
        if ($case->client_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this case.');
        }
        
        $this->case = $case->load([
            'lawyer.lawyerProfile',
        ]);
        
        $this->loadCaseData();
    }
    
    private function loadCaseData()
    {
        // Load phases
        $this->phases = CasePhase::where('legal_case_id', $this->case->id)
            ->orderBy('start_date')
            ->get()
            ->toArray();
            
        // Set current phase only if there are phases
        if (count($this->phases) > 0) {
            $currentPhase = CasePhase::where('legal_case_id', $this->case->id)
                ->where('is_current', true)
                ->first();
                
            $this->currentPhase = $currentPhase ? $currentPhase->toArray() : null;
        } else {
            $this->currentPhase = null;
        }
        
        // Load upcoming events (next 30 days)
        try {
            $this->upcomingEvents = CaseEvent::where('legal_case_id', $this->case->id)
                ->whereDate('event_date', '>=', Carbon::today())
                ->whereDate('event_date', '<=', Carbon::today()->addDays(30))
                ->orderBy('event_date')
                ->orderBy('event_time')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->upcomingEvents = [];
        }
            
        // Load tasks assigned to client
        try {
            $this->myTasks = CaseTask::where('legal_case_id', $this->case->id)
                ->where(function($query) {
                    $query->where('assigned_to', Auth::id())
                          ->orWhere('assigned_to_id', Auth::id());
                })
                ->orderBy('due_date')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->myTasks = [];
        }
            
        // Load documents
        try {
            $this->documents = CaseDocument::where('legal_case_id', $this->case->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            $this->documents = [];
        }
    }
    
    public function toggleTaskCompletion($taskId)
    {
        try {
            $task = CaseTask::where('id', $taskId)
                ->where('legal_case_id', $this->case->id)
                ->where(function($query) {
                    $query->where('assigned_to', Auth::id())
                          ->orWhere('assigned_to_id', Auth::id());
                })
                ->first();
                
            if ($task) {
                $task->is_completed = !$task->is_completed;
                $task->completed_at = $task->is_completed ? now() : null;
                $task->save();
                
                // Reload tasks
                $this->myTasks = CaseTask::where('legal_case_id', $this->case->id)
                    ->where(function($query) {
                        $query->where('assigned_to', Auth::id())
                              ->orWhere('assigned_to_id', Auth::id());
                    })
                    ->orderBy('due_date')
                    ->get()
                    ->toArray();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating task: ' . $e->getMessage());
        }
    }
    
    public function viewTaskDetails($taskId)
    {
        try {
            $task = CaseTask::where('id', $taskId)
                ->where('legal_case_id', $this->case->id)
                ->where(function($query) {
                    $query->where('assigned_to', Auth::id())
                          ->orWhere('assigned_to_id', Auth::id());
                })
                ->first();
                
            if ($task) {
                $this->selectedTask = $task->toArray();
                $this->dispatchBrowserEvent('open-modal', 'task-details-modal');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error viewing task details: ' . $e->getMessage());
        }
    }
    
    /**
     * Open invoice details modal
     */
    public function viewInvoice($invoiceId)
    {
        $invoice = Invoice::with(['items', 'payments', 'lawyer', 'legalCase'])
            ->where('client_id', Auth::id())
            ->where('legal_case_id', $this->case->id)
            ->findOrFail($invoiceId);
            
        $this->dispatchBrowserEvent('show-invoice-modal', $invoice);
    }
    
    /**
     * Pay invoice with GCash
     */
    public function payWithGCash($invoiceId)
    {
        $invoice = Invoice::where('client_id', Auth::id())
            ->where('legal_case_id', $this->case->id)
            ->findOrFail($invoiceId);
        
        $payMongoService = new PayMongoService();
        $result = $payMongoService->createSource($invoice, 'gcash');
        
        if ($result['success'] && isset($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        } else {
            $this->paymentStatus = 'error';
            $this->paymentMessage = $result['message'] ?? 'Failed to create payment source';
            session()->flash('error', $this->paymentMessage);
        }
    }
    
    /**
     * Pay invoice with credit card
     */
    public function payWithCard($invoiceId)
    {
        $invoice = Invoice::where('client_id', Auth::id())
            ->where('legal_case_id', $this->case->id)
            ->findOrFail($invoiceId);
        
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
            session()->flash('error', $this->paymentMessage);
        }
    }

    public function render()
    {
        // Get the case's invoices for the current client
        $invoices = $this->case->invoices()
            ->where('client_id', Auth::id())
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->latest()
            ->get();
            
        return view('livewire.client.case-overview', [
            'invoices' => $invoices
        ]);
    }
} 