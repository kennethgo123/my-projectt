<?php

namespace App\Livewire\Lawyer;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LegalCase;
use App\Models\User;
use App\Models\Consultation;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class InvoiceManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $status = '';
    public $searchClient = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
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
    public $selectedClient = null;
    public $selectedCase = null;
    public $clientCases = [];
    public $clients = []; // Property to store filtered clients for dropdown
    
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
        'invoiceDiscount' => 'numeric|min:0',
        'invoiceIssueDate' => 'required|date',
        'invoiceDueDate' => 'required|date|after_or_equal:invoiceIssueDate',
        'invoiceNotes' => 'nullable|string',
        'invoicePaymentPlan' => 'required|in:full,3_months,6_months,1_year',
        'selectedClient' => 'required',
        'selectedCase' => 'nullable',
        'invoiceItems' => 'required|array|min:1',
        'invoiceItems.*.description' => 'required|string',
        'invoiceItems.*.quantity' => 'nullable|numeric|min:0',
        'invoiceItems.*.unit_price' => 'required|numeric|min:0',
        'invoiceItems.*.type' => 'required|string|in:service,expense,billable_hours,other',
    ];

    public function mount()
    {
        $this->invoiceIssueDate = now()->format('Y-m-d');
        $this->invoiceDueDate = now()->addDays(30)->format('Y-m-d');
        $this->invoicePaymentPlan = Invoice::PAYMENT_PLAN_FULL;
        $this->addInvoiceItem();
    }
    
    public function render()
    {
        $lawyerId = Auth::id();
        
        $invoicesQuery = Invoice::where('lawyer_id', $lawyerId)
            ->with(['client.role', 'client.clientProfile', 'legalCase', 'items', 'payments']);
        
        if ($this->search) {
            $invoicesQuery->where(function($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        }
        
        if ($this->status) {
            $invoicesQuery->where('status', $this->status);
        }
        
        $invoices = $invoicesQuery
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
        
        // Only fetch clients if we don't already have them from a search
        if (empty($this->clients)) {
            // Get clients with completed consultations for creating new invoices
            $clientsQuery = User::whereHas('role', function($query) {
                    $query->where('name', 'client');
                })
                ->with(['role', 'clientProfile']) // Eager load role and client profiles
                ->whereHas('clientConsultations', function($query) use ($lawyerId) {
                    $query->where('lawyer_id', $lawyerId)
                        ->where('status', 'completed');
                });
                
            if ($this->searchClient) {
                $clientsQuery->where(function($query) {
                    $query->where('name', 'like', '%' . $this->searchClient . '%')
                        ->orWhere('email', 'like', '%' . $this->searchClient . '%')
                        ->orWhereHas('clientProfile', function($profileQuery) use ($query) {
                            $profileQuery->where('first_name', 'like', '%' . $this->searchClient . '%')
                                         ->orWhere('last_name', 'like', '%' . $this->searchClient . '%');
                        });
                });
            }
            
            $this->clients = $clientsQuery->get();
            
            // If no clients found, fallback to those with cases
            if ($this->clients->isEmpty()) {
                $this->clients = User::whereHas('role', function($query) {
                        $query->where('name', 'client');
                    })
                    ->with(['role', 'clientProfile']) // Eager load role and client profiles
                    ->whereHas('cases', function($query) use ($lawyerId) {
                        $query->where('lawyer_id', $lawyerId);
                    })
                    ->get();
            }
        }
            
        return view('livewire.lawyer.invoice-management', [
            'invoices' => $invoices,
            'clients' => $this->clients,
            'statuses' => [
                'all' => 'All',
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
    
    public function updatedSelectedClient($clientId)
    {
        if ($clientId) {
            // Get cases for the selected client where lawyer is current user
            $this->clientCases = LegalCase::where('client_id', $clientId)
                ->where('lawyer_id', Auth::id())
                ->get()
                ->map(function($case) {
                    return [
                        'id' => $case->id,
                        'title' => $case->title . ' (#' . $case->case_number . ')'
                    ];
                })
                ->toArray();
            
            $this->selectedCase = null;
        } else {
            $this->clientCases = [];
            $this->selectedCase = null;
        }
    }
    
    public function updatedSearchClient($query)
    {
        if (strlen($query) >= 2) {
            // Update the clients available in the dropdown based on the search query
            $lawyerId = Auth::id();
            
            $clientsQuery = User::whereHas('role', function($q) {
                    $q->where('name', 'client');
                })
                ->with(['role', 'clientProfile']) // Eager load role and client profiles
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                      ->orWhere('email', 'like', '%' . $query . '%')
                      ->orWhereHas('clientProfile', function($profileQuery) use ($query) {
                          $profileQuery->where('first_name', 'like', '%' . $query . '%')
                                       ->orWhere('last_name', 'like', '%' . $query . '%');
                      });
                });
                
            // First try to find clients with completed consultations
            $consultationClients = (clone $clientsQuery)
                ->whereHas('clientConsultations', function($q) use ($lawyerId) {
                    $q->where('lawyer_id', $lawyerId)
                      ->where('status', 'completed');
                })
                ->take(10)
                ->get();
                
            // If no results, try to find clients with cases
            if ($consultationClients->isEmpty()) {
                $caseClients = (clone $clientsQuery)
                    ->whereHas('cases', function($q) use ($lawyerId) {
                        $q->where('lawyer_id', $lawyerId);
                    })
                    ->take(10)
                    ->get();
                    
                $clients = $caseClients;
            } else {
                $clients = $consultationClients;
            }
            
            // If still no results and query is specific enough, get any matching clients
            if ($clients->isEmpty() && strlen($query) >= 3) {
                $clients = $clientsQuery->take(10)->get();
            }
            
            // Update the clients property with the filtered results
            $this->clients = $clients;
        }
    }
    
    // Invoice Modal Methods
    
    public function openInvoiceModal()
    {
        $this->resetInvoiceForm();
        $this->showInvoiceModal = true;
        $this->editMode = false;
        
        // Pre-fetch some clients to show in the dropdown
        $lawyerId = Auth::id();
        $clients = User::whereHas('role', function($query) {
                $query->where('name', 'client');
            })
            ->whereHas('clientConsultations', function($query) use ($lawyerId) {
                $query->where('lawyer_id', $lawyerId)
                    ->where('status', 'completed');
            })
            ->take(10)
            ->get();
            
        // Make sure we have at least some clients to show
        if ($clients->isEmpty()) {
            // As a fallback, get any clients that have cases with this lawyer
            $clients = User::whereHas('role', function($query) {
                    $query->where('name', 'client');
                })
                ->whereHas('cases', function($query) use ($lawyerId) {
                    $query->where('lawyer_id', $lawyerId);
                })
                ->take(10)
                ->get();
        }
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
            'selectedClient',
            'selectedCase',
            'clientCases',
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
        return $this->calculateSubtotal() - $this->invoiceDiscount;
    }
    
    public function generateInvoiceNumber()
    {
        $prefix = 'INV-';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -4));
        
        return $prefix . $year . $month . '-' . $random;
    }
    
    public function saveInvoice()
    {
        try {
            $this->validate();
            
            if ($this->editMode) {
                $invoice = Invoice::findOrFail($this->invoiceId);
            } else {
                $invoice = new Invoice();
                $invoice->legal_case_id = $this->selectedCase;
                $invoice->client_id = $this->selectedClient;
                $invoice->lawyer_id = Auth::id();
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
                $invoiceItem->amount = $item['quantity'] * $item['unit_price'];
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
                        'link' => route('client.invoices'),
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
        $this->selectedClient = $invoice->client_id;
        $this->selectedCase = $invoice->legal_case_id;
        $this->invoicePaymentPlan = $invoice->payment_plan;
        
        $this->updatedSelectedClient($invoice->client_id);
        
        // Load invoice items
        $this->invoiceItems = [];
        $this->nextItemKey = 0;
        
        foreach ($invoice->items as $item) {
            $this->invoiceItems[$this->nextItemKey] = [
                'id' => $item->id,
                'description' => $item->description,
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
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Don't send if already sent
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            session()->flash('error', 'Invoice has already been sent.');
            return;
        }
        
        // Change status from draft to pending
        $invoice->status = Invoice::STATUS_PENDING;
        $invoice->save();
        
        // Send notification to client
        NotificationService::invoiceNotification(
            'invoice_sent',
            $invoice->client_id,
            'Invoice Ready for Payment',
            'Invoice Ready for Payment: ' . $invoice->invoice_number,
            [
                'link' => route('client.invoices'),
                'invoice_id' => $invoice->id
            ]
        );
        
        session()->flash('message', 'Invoice sent to client successfully.');
    }
    
    public function cancelInvoice($invoiceId)
    {
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
                'link' => route('client.invoices'),
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