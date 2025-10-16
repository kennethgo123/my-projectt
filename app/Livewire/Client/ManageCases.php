<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\Consultation;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ManageCases extends Component
{
    use WithPagination, WithFileUploads;

    // Search and filter properties
    public $search = '';
    public $status = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showArchived = false; // Flag to control archived cases view
    
    // Selected case properties
    public $selectedCase = null;
    
    // Modal states
    public $showContractModal = false;
    public $showDetailsModal = false;
    public $showSignContractModal = false;
    public $showNegotiateModal = false;
    public $showStartCaseModal = false;
    
    // Contract related properties
    public $negotiationTerms = '';
    public $signature = null;
    public $caseTitle = '';
    public $caseDescription = '';
    public $caseDocument = null;
    public $selectedConsultation = null;

    // Case action properties
    public $actionType = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected $rules = [
        'negotiationTerms' => 'required_if:actionType,negotiate|min:10',
        'signature' => 'required_if:actionType,sign|image|max:2048',
        'caseTitle' => 'required_if:actionType,start_case|min:5|max:255',
        'caseDescription' => 'required_if:actionType,start_case|min:10',
        'caseDocument' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
    ];

    protected $messages = [
        'negotiationTerms.required_if' => 'Please provide your terms for negotiation.',
        'signature.required_if' => 'Please provide your signature.',
        'signature.image' => 'The signature must be an image.',
        'signature.max' => 'The signature should not be larger than 2MB.',
        'caseTitle.required_if' => 'Please provide a title for your case.',
        'caseDescription.required_if' => 'Please describe your case in detail.',
        'caseDocument.max' => 'The document should not be larger than 10MB.',
        'caseDocument.mimes' => 'The document must be a PDF, DOC, or DOCX file.',
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    /**
     * View case details
     */
    public function viewDetails($caseId)
    {
        $this->selectedCase = LegalCase::with(['lawyer.lawyerProfile', 'lawyer.lawFirmProfile', 'lawyer.lawFirmLawyer', 'caseUpdates', 'contractActions', 'consultation'])
            ->findOrFail($caseId);
            
        // Only allow viewing if this is the client's case
        if ($this->selectedCase->client_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to view this case.');
            return;
        }
        
        $this->showDetailsModal = true;
    }

    /**
     * View contract for a case
     */
    public function viewContract($caseId)
    {
        $this->selectedCase = LegalCase::findOrFail($caseId);
        
        // Only allow viewing if this is the client's case
        if ($this->selectedCase->client_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to view this contract.');
            return;
        }
        
        // Check if contract exists
        if (!$this->selectedCase->contract_path) {
            session()->flash('error', 'No contract is available for this case.');
            return;
        }
        
        $this->showContractModal = true;
    }

    /**
     * Show sign contract modal
     */
    public function showSignContract($caseId)
    {
        $this->selectedCase = LegalCase::findOrFail($caseId);
        
        // Only allow signing if this is the client's case
        if ($this->selectedCase->client_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to sign this contract.');
            return;
        }
        
        // Check if contract exists and is in the right state
        if (!$this->selectedCase->contract_path || $this->selectedCase->contract_status !== LegalCase::CONTRACT_STATUS_SENT) {
            session()->flash('error', 'This contract is not available for signing.');
            return;
        }
        
        $this->signature = null;
        $this->actionType = 'sign';
        $this->showSignContractModal = true;
    }

    /**
     * Show negotiate contract modal
     */
    public function showNegotiateContract($caseId)
    {
        $this->selectedCase = LegalCase::findOrFail($caseId);
        
        // Only allow negotiating if this is the client's case
        if ($this->selectedCase->client_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to negotiate this contract.');
            return;
        }
        
        // Check if contract exists and is in the right state
        if (!$this->selectedCase->contract_path || 
            !in_array($this->selectedCase->contract_status, [LegalCase::CONTRACT_STATUS_SENT, LegalCase::CONTRACT_STATUS_NEGOTIATING])) {
            session()->flash('error', 'This contract is not available for negotiation.');
            return;
        }
        
        $this->negotiationTerms = $this->selectedCase->negotiation_terms ?? '';
        $this->actionType = 'negotiate';
        $this->showNegotiateModal = true;
    }

    /**
     * Show start case modal from consultation
     */
    public function showStartCaseForm($consultationId)
    {
        try {
            $this->selectedConsultation = Consultation::with('lawyer')
                ->where('client_id', Auth::id())
                ->findOrFail($consultationId);
            
            // Check if consultation is completed and can start a case
            if ($this->selectedConsultation->status !== 'completed' || !$this->selectedConsultation->can_start_case) {
                session()->flash('error', 'This consultation is not ready to start a case yet.');
                return;
            }
            
            // Check if a case already exists for this consultation
            if ($this->selectedConsultation->case) {
                session()->flash('error', 'A case already exists for this consultation.');
                return;
            }
            
            $this->caseTitle = '';
            $this->caseDescription = '';
            $this->caseDocument = null;
            $this->actionType = 'start_case';
            $this->showStartCaseModal = true;
        } catch (\Exception $e) {
            Log::error('Error showing start case form', [
                'error' => $e->getMessage(),
                'consultation_id' => $consultationId
            ]);
            session()->flash('error', 'Could not load consultation details. Please try again.');
        }
    }

    /**
     * Submit contract negotiation terms
     */
    public function submitNegotiation()
    {
        try {
            $this->validate([
                'negotiationTerms' => 'required|min:10'
            ]);
            
            if (!$this->selectedCase) {
                session()->flash('error', 'No case selected.');
                return;
            }
            
            $this->selectedCase->update([
                'negotiation_terms' => $this->negotiationTerms,
                'contract_status' => LegalCase::CONTRACT_STATUS_NEGOTIATING,
                'lawyer_response_required' => true
            ]);
            
            // Record contract action
            $this->selectedCase->contractActions()->create([
                'action_type' => 'negotiation_submitted',
                'actor_type' => 'App\Models\User',
                'actor_id' => Auth::id(),
                'details' => 'Client submitted negotiation terms'
            ]);
            
            session()->flash('message', 'Your negotiation terms have been submitted to the lawyer.');
            $this->showNegotiateModal = false;
        } catch (\Exception $e) {
            Log::error('Contract negotiation failed', [
                'error' => $e->getMessage(),
                'case_id' => $this->selectedCase->id ?? null
            ]);
            session()->flash('error', 'Failed to submit negotiation terms: ' . $e->getMessage());
        }
    }

    /**
     * Sign contract and update case status
     */
    public function signContract()
    {
        try {
            $this->validate([
                'signature' => 'required|image|max:2048'
            ]);
            
            if (!$this->selectedCase) {
                session()->flash('error', 'No case selected.');
                return;
            }
            
            // Store signature image
            $signaturePath = $this->signature->store('signatures', 'public');
            
            // Update case with signed contract status
            $this->selectedCase->update([
                'contract_status' => LegalCase::CONTRACT_STATUS_SIGNED,
                'status' => LegalCase::STATUS_CONTRACT_SIGNED,
                'contract_signed_at' => now(),
                'signature_path' => $signaturePath
            ]);
            
            // Record contract action
            $this->selectedCase->contractActions()->create([
                'action_type' => 'contract_signed',
                'actor_type' => 'App\Models\User',
                'actor_id' => Auth::id(),
                'details' => 'Client signed the contract',
                'signature_path' => $signaturePath
            ]);
            
            session()->flash('message', 'Contract signed successfully! The case will be activated soon.');
            $this->showSignContractModal = false;
        } catch (\Exception $e) {
            Log::error('Contract signing failed', [
                'error' => $e->getMessage(),
                'case_id' => $this->selectedCase->id ?? null
            ]);
            session()->flash('error', 'Failed to sign contract: ' . $e->getMessage());
        }
    }

    /**
     * Start a new case from consultation
     */
    public function startCase()
    {
        $this->validate();

        if (!$this->selectedConsultation) {
            session()->flash('error', 'No consultation selected.');
            return;
        }

        try {
            // Create case data array
            $caseData = [
                'title' => $this->caseTitle,
                'description' => $this->caseDescription,
                'status' => LegalCase::STATUS_PENDING,
                'lawyer_id' => $this->selectedConsultation->lawyer_id,
                'client_id' => Auth::id(),
                'case_number' => LegalCase::generateCaseNumber(),
                'contract_status' => LegalCase::CONTRACT_STATUS_PENDING,
                'consultation_id' => $this->selectedConsultation->id
            ];
            
            // If there's a service_id field on the consultation, include it
            if (isset($this->selectedConsultation->service_id)) {
                $caseData['service_id'] = $this->selectedConsultation->service_id;
            }

            // Handle document upload if provided
            if ($this->caseDocument) {
                $documentPath = $this->caseDocument->store('case-documents', 'public');
                $caseData['client_document_path'] = $documentPath;
            }

            // Create the case
            $case = LegalCase::create($caseData);

            session()->flash('message', 'Case request sent successfully! The lawyer will be notified and will review your request.');
            $this->showStartCaseModal = false;
            $this->reset(['selectedConsultation', 'caseTitle', 'caseDescription', 'caseDocument']);
        } catch (\Exception $e) {
            Log::error("Error creating case: " . $e->getMessage());
            session()->flash('error', 'Could not create the case. Please try again.');
        }
    }
    
    /**
     * Reset all modals and form fields
     */
    public function resetModals()
    {
        $this->showContractModal = false;
        $this->showDetailsModal = false;
        $this->showSignContractModal = false;
        $this->showNegotiateModal = false;
        $this->showStartCaseModal = false;
        
        $this->selectedCase = null;
        $this->selectedConsultation = null;
        $this->negotiationTerms = '';
        $this->signature = null;
        $this->caseTitle = '';
        $this->caseDescription = '';
        $this->caseDocument = null;
        $this->actionType = null;
    }

    /**
     * Toggle between archived and active cases view
     */
    public function toggleArchivedView()
    {
        $this->showArchived = !$this->showArchived;
        $this->resetPage(); // Reset pagination when switching views
    }

    public function openRateLawyerModal($caseId)
    {
        $this->dispatch('openRatingModal', $caseId);
    }

    public function openRateTeamLawyerModal($caseId)
    {
        $this->dispatch('openTeamRatingModal', $caseId);
    }

    public function openRateLawFirmModal($caseId)
    {
        $this->dispatch('openLawFirmRatingModal', $caseId);
    }

    /**
     * Check if case has multiple lawyers assigned
     */
    public function caseHasMultipleLawyers($caseId)
    {
        $case = LegalCase::find($caseId);
        if (!$case) {
            return false;
        }
        
        return $case->teamLawyers()->count() > 1;
    }

    /**
     * Check if case is handled by a law firm or lawyer under a firm
     */
    public function caseHasLawFirm($caseId)
    {
        $case = LegalCase::find($caseId);
        if (!$case) {
            return false;
        }
        
        // Check if primary lawyer is directly a law firm
        if ($case->lawyer && $case->lawyer->isLawFirm()) {
            return true;
        }
        
        // Check if primary lawyer is under a law firm
        if ($case->lawyer && $case->lawyer->firm_id) {
            return true;
        }
        
        // Check if any team lawyer is under a law firm
        foreach ($case->teamLawyers as $lawyer) {
            if ($lawyer->firm_id) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Render the component
     */
    public function render()
    {
        // Get all cases for the current client
        $casesQuery = LegalCase::where('client_id', Auth::id())
            ->with([
                'lawyer.lawyerProfile', 
                'lawyer.lawFirmProfile',
                'lawyer.lawFirmLawyer',
                'service',
                'consultation'
            ])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('case_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('lawyer', function($lawyerQuery) {
                          $lawyerQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->showArchived, function ($query) {
                // Show archived cases and also cases with Completed status
                $query->where(function($q) {
                    $q->where('archived', true)
                      ->orWhere('status', LegalCase::STATUS_COMPLETED);
                });
            }, function ($query) {
                // Show only non-archived cases and cases not in Completed status
                $query->where(function ($q) {
                    $q->where(function($subQ) {
                        $subQ->where('archived', false)
                             ->orWhereNull('archived');
                    })
                    ->where(function($subQ) {
                        $subQ->where('status', '!=', LegalCase::STATUS_COMPLETED)
                             ->orWhereNull('status');
                    });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
            
        $cases = $casesQuery->paginate(10);

        $statuses = [
            'pending' => 'Pending',
            LegalCase::STATUS_CONTRACT_SENT => 'Contract Sent',
            LegalCase::STATUS_ACTIVE => 'Active',
            LegalCase::STATUS_CLOSED => 'Closed'
        ];
            
        return view('livewire.client.manage-cases', [
            'cases' => $cases,
            'statuses' => $statuses,
            'showArchived' => $this->showArchived
        ]);
    }
} 