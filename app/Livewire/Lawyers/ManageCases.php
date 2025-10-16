<?php

namespace App\Livewire\Lawyers;

use App\Models\LegalCase;
use App\Models\User;
use App\Models\ContractAction;
use App\Services\NotificationService;
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
    public $showArchived = false; // Flag to control archived cases view
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Selected case property
    public $selectedCase = null;
    
    // Modal states
    public $showSignatureModal = false;
    
    // Signature acknowledgment
    public $signatureAcknowledged = false;

    // New properties for revised contract upload
    public $showUploadRevisedContractModal = false;
    public $selectedCaseForRevision;
    public $revisedContractDocument;
    public $declineReason = '';

    // Add the case label constants
    const CASE_LABEL_HIGH_PRIORITY = 'high_priority';
    const CASE_LABEL_MEDIUM_PRIORITY = 'medium_priority';
    const CASE_LABEL_LOW_PRIORITY = 'low_priority';

    public $showActionModal = false;
    public $actionType = '';
    public $contract = null;
    public $currentAction = '';

    public $showStartCaseModal = false;
    public $caseTitle = '';
    public $caseDescription = '';
    public $contractDocument = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    protected $rules = [
        'contract' => 'required|file|mimes:pdf|max:10240', // 10MB Max
        'revisedContractDocument' => 'nullable|file|mimes:pdf|max:10240', // 10MB Max
        'declineReason' => 'nullable|string|min:10|max:2000',
        'caseTitle' => 'required|min:5|max:255',
        'caseDescription' => 'required|min:10',
        'contractDocument' => 'required|file|max:10240|mimes:pdf'
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
     * Toggle between archived and active cases view
     */
    public function toggleArchivedView()
    {
        $this->showArchived = !$this->showArchived;
        $this->resetPage(); // Reset pagination when switching views
    }

    public function render()
    {
        // Get cases where lawyer is either the primary lawyer or part of the team
        $casesQuery = LegalCase::where(function($query) {
                $query->where('lawyer_id', Auth::id())
                      ->orWhereHas('teamLawyers', function($q) {
                          $q->where('user_id', Auth::id());
                      });
            })
            ->with(['client.clientProfile'])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('case_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('client', function($clientQuery) {
                          $clientQuery->where(\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->showArchived, function ($query) {
                // Show only archived cases
                $query->where(function ($q) {
                    $q->where('archived', true)
                      ->orWhere('closed_at', '!=', null);
                });
            }, function ($query) {
                // Show only non-archived cases by default
                $query->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('archived', false)
                           ->orWhereNull('archived');
                    })
                    ->whereNull('closed_at');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        // Get pending cases that require lawyer response - this should always show for non-archived view
        $pendingCases = collect(); // Default to empty collection
        if (!$this->showArchived) {
            $pendingCases = LegalCase::with([
                'client.clientProfile', 
                'consultation'
            ])
                ->where(function($query) {
                    $query->where('lawyer_id', Auth::id())
                          ->orWhereHas('teamLawyers', function($q) {
                              $q->where('user_id', Auth::id());
                          });
                })
                ->where('status', LegalCase::STATUS_PENDING)
                ->latest()
                ->get();
        }
            
        $statuses = [
            LegalCase::STATUS_PENDING => 'Pending',
            LegalCase::STATUS_CONTRACT_SENT => 'Contract Sent',
            LegalCase::STATUS_CONTRACT_REJECTED_BY_CLIENT => 'Contract Rejected by Client',
            LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT => 'Changes Requested by Client',
            LegalCase::STATUS_CONTRACT_REVISED_SENT => 'Contract Revised by Lawyer',
            LegalCase::STATUS_ACTIVE => 'Active',
            LegalCase::STATUS_CLOSED => 'Closed'
        ];
            
        return view('livewire.lawyers.manage-cases', [
            'cases' => $casesQuery->paginate(10),
            'statuses' => $statuses,
            'pendingCases' => $pendingCases,
            'showArchived' => $this->showArchived
        ]);
    }

    /**
     * Update the case label with new system
     *
     * @param int $caseId
     * @param string $label
     * @return void
     */
    public function updateCaseLabel($caseId, $label)
    {
        \Illuminate\Support\Facades\Log::info('CASE LABEL UPDATE - Called from CORRECT component', [
            'caseId' => $caseId,
            'label' => $label,
            'user_id' => auth()->id()
        ]);
        
        try {
            $case = LegalCase::findOrFail($caseId);
            
            // Ensure only lawyer who owns this case can update the label
            if ($case->lawyer_id !== Auth::id()) {
                session()->flash('error', 'You are not authorized to update this case.');
                return;
            }
            
            // Validate that label is one of the allowed values
            $validLabels = [
                self::CASE_LABEL_HIGH_PRIORITY,
                self::CASE_LABEL_MEDIUM_PRIORITY, 
                self::CASE_LABEL_LOW_PRIORITY
            ];
            
            if (!in_array($label, $validLabels) && $label !== '') {
                session()->flash('error', 'Invalid label value.');
                return;
            }
            
            $case->update(['case_label' => $label]);
            
            \Illuminate\Support\Facades\Log::info('CASE LABEL UPDATE - After Update', [
                'updated' => true,
                'current_label' => $case->case_label,
            ]);
            
            session()->flash('message', 'Case label updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('CASE LABEL UPDATE - Error', [
                'caseId' => $caseId,
                'label' => $label,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error updating case label: ' . $e->getMessage());
        }
    }

    /**
     * View the client's signature for a case
     *
     * @param int $caseId
     * @return void
     */
    public function viewSignature($caseId)
    {
        Log::info('viewSignature method called', [
            'case_id' => $caseId,
            'user_id' => Auth::id(),
            'component_id' => $this->getId()
        ]);
        
        // Get the case directly from the database to ensure fresh data
        $case = DB::table('legal_cases')->where('id', $caseId)->first();
        
        if (!$case) {
            Log::error('Case not found for signature viewing', ['case_id' => $caseId]);
            session()->flash('error', 'Case not found.');
            return;
        }
        
        // Log the case data for debugging
        Log::info('Case data fetched for signature viewing', [
            'case_id' => $caseId,
            'signature_path' => $case->signature_path ?? 'null',
            'contract_path' => $case->contract_path ?? 'null',
            'status' => $case->status
        ]);
        
        // Load the full case model with relationships
        $this->selectedCase = LegalCase::with(['contractActions'])->findOrFail($caseId);
        
        try {
            // First, directly check for signature_path on the case
            if ($case->signature_path) {
                Log::info('Using signature directly from case record', [
                    'signature_path' => $case->signature_path
                ]);
                
                $this->selectedCase->signature_path = $case->signature_path;
                $this->signatureAcknowledged = false;
                $this->showSignatureModal = true;
                
                // Debug the state after setting
                Log::info('Modal state after setting', [
                    'showSignatureModal' => $this->showSignatureModal,
                    'selectedCase' => [
                        'id' => $this->selectedCase->id,
                        'signature_path' => $this->selectedCase->signature_path
                    ]
                ]);
                
                $this->dispatch('signature-modal-opened', ['case_id' => $caseId]);
                
                return;
            }
            
            // If we still don't have a signature, check contract actions
            $contractAction = $this->selectedCase->contractActions()
                ->whereNotNull('signature_path')
                ->latest()
                ->first();
                
            if ($contractAction && $contractAction->signature_path) {
                Log::info('Using signature from contract action', [
                    'signature_path' => $contractAction->signature_path,
                    'action_type' => $contractAction->action_type
                ]);
                
                $this->selectedCase->signature_path = $contractAction->signature_path;
                $this->signatureAcknowledged = false;
                $this->showSignatureModal = true;
                
                // Update the case record with this signature path for future reference
                DB::table('legal_cases')
                    ->where('id', $caseId)
                    ->update(['signature_path' => $contractAction->signature_path]);
                
                $this->dispatch('signature-modal-opened', ['case_id' => $caseId]);
                
                return;
            }
            
            // No signature found
            Log::warning('No signature found for case', [
                'case_id' => $caseId
            ]);
            session()->flash('error', 'No signature found for this case.');
            
        } catch (\Exception $e) {
            Log::error('Error displaying signature', [
                'case_id' => $caseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error displaying signature: ' . $e->getMessage());
        }
    }
    
    /**
     * Acknowledge client's electronic signature for a specific contract
     */
    public function acknowledgeSignature()
    {
        if (!$this->selectedCase) {
            session()->flash('error', 'No case selected.');
            return;
        }
        
        if (!$this->signatureAcknowledged) {
            session()->flash('error', 'You must acknowledge the statement to proceed.');
            return;
        }
        
        try {
            // Find the 'accepted' contract action for this case
            $contractAction = $this->selectedCase->contractActions()
                ->where('action_type', 'accepted')
                ->orderBy('created_at', 'desc')
                ->first();
                
            if (!$contractAction) {
                session()->flash('error', 'No signed contract found for this case.');
                return;
            }
            
            // Mark the contract action as acknowledged by lawyer
            $contractAction->update([
                'lawyer_acknowledged' => true,
                'lawyer_acknowledged_at' => now(),
                'acknowledged_by' => Auth::id()
            ]);
            
            // Record a case update
            $this->selectedCase->caseUpdates()->create([
                'title' => 'Signature Acknowledged',
                'content' => 'Lawyer has acknowledged receipt of client\'s electronic signature.',
                'user_id' => Auth::id(),
                'visibility' => 'both'
            ]);
            
            session()->flash('message', 'Client signature has been acknowledged.');
            $this->showSignatureModal = false;
            
        } catch (\Exception $e) {
            Log::error('Signature acknowledgment failed', [
                'error' => $e->getMessage(),
                'case_id' => $this->selectedCase->id ?? null
            ]);
            session()->flash('error', 'Failed to acknowledge signature: ' . $e->getMessage());
        }
    }

    // Method to open the revised contract upload modal
    public function openUploadRevisedContractModal($caseId)
    {
        $case = LegalCase::findOrFail($caseId);
        // Authorization check: ensure the lawyer owns the case or is part of the team
        if ($case->lawyer_id !== Auth::id() && !$case->teamLawyers()->where('user_id', Auth::id())->exists()) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }
        if ($case->status !== LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT) {
            session()->flash('error', 'A revised contract can only be uploaded if the client has requested changes.');
            return;
        }
        $this->selectedCaseForRevision = $case;
        $this->revisedContractDocument = null; // Reset previous selection
        $this->resetValidation(); // Reset validation errors
        $this->showUploadRevisedContractModal = true;
    }

    // Method to submit the revised contract
    public function submitRevisedContract()
    {
        $this->validateOnly('revisedContractDocument', [
            'revisedContractDocument' => 'required|file|mimes:pdf|max:10240',
        ], [
            'revisedContractDocument.required' => 'Please select a revised contract document to upload.'
        ]);

        if (!$this->selectedCaseForRevision) {
            session()->flash('error', 'No case selected for revision.');
            return;
        }

        // Authorization check (already in openUploadRevisedContractModal, but good for belt-and-suspenders)
        if ($this->selectedCaseForRevision->lawyer_id !== Auth::id() && !$this->selectedCaseForRevision->teamLawyers()->where('user_id', Auth::id())->exists()) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }

        try {
            DB::beginTransaction();

            // Store the new contract document
            $newContractPath = $this->revisedContractDocument->store('contracts', 'public');

            // Update case details
            $this->selectedCaseForRevision->contract_path = $newContractPath;
            $this->selectedCaseForRevision->status = LegalCase::STATUS_CONTRACT_REVISED_SENT;
            $this->selectedCaseForRevision->contract_status = LegalCase::CONTRACT_STATUS_REVISED_SENT;
            $this->selectedCaseForRevision->lawyer_response_required = false; // Client's turn
            $this->selectedCaseForRevision->lawyer_response_message = null; // Clear any previous decline message
            // Clear previous rejection/change request details if any
            // $this->selectedCaseForRevision->rejection_reason = null; // Keep client's rejection reason if any
            // $this->selectedCaseForRevision->requested_changes_details = null; // Keep client's requested changes
            $this->selectedCaseForRevision->save();

            // Create a contract action
            ContractAction::create([
                'legal_case_id' => $this->selectedCaseForRevision->id,
                'action_type' => 'revised_contract_uploaded',
                'actor_type' => User::class, 
                'actor_id' => Auth::id(),
                'details' => 'Lawyer uploaded a revised contract.',
                'document_path' => $newContractPath, 
            ]);

            // Notify Client
            if ($this->selectedCaseForRevision->client) {
                // Assuming NotificationService has a method for this
                NotificationService::revisedContractUploaded($this->selectedCaseForRevision, Auth::user());
            }

            DB::commit();
            $this->showUploadRevisedContractModal = false;
            $this->selectedCaseForRevision = null;
            $this->revisedContractDocument = null;
            session()->flash('message', 'Revised contract has been uploaded and the client has been notified.');
            
            // The lawyer should stay on their page - no redirect needed
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit revised contract', [
                'case_id' => $this->selectedCaseForRevision->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to upload revised contract: ' . $e->getMessage());
        }
    }

    public function submitDeclineChanges()
    {
        $this->validateOnly('declineReason', [
            'declineReason' => 'required|string|min:10|max:2000',
        ], [
            'declineReason.required' => 'Please provide a reason for declining the client\'s changes.',
            'declineReason.min' => 'The decline reason must be at least 10 characters.',
        ]);

        if (!$this->selectedCaseForRevision) {
            session()->flash('error', 'No case selected for action.');
            return;
        }

        // Authorization check
        if ($this->selectedCaseForRevision->lawyer_id !== Auth::id() && !$this->selectedCaseForRevision->teamLawyers()->where('user_id', Auth::id())->exists()) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }

        try {
            DB::beginTransaction();

            $this->selectedCaseForRevision->status = LegalCase::STATUS_CONTRACT_REVISED_SENT; // Or a new status like 'changes_declined_by_lawyer'
            $this->selectedCaseForRevision->contract_status = 'lawyer_declined_changes'; // This needs to be a valid enum or string field
            $this->selectedCaseForRevision->lawyer_response_message = $this->declineReason;
            $this->selectedCaseForRevision->lawyer_response_required = false; // Client's turn to review the declined changes
            $this->selectedCaseForRevision->save();

            ContractAction::create([
                'legal_case_id' => $this->selectedCaseForRevision->id,
                'action_type' => 'lawyer_declined_changes',
                'actor_type' => User::class,
                'actor_id' => Auth::id(),
                'details' => 'Lawyer declined client\'s requested changes. Reason: ' . $this->declineReason,
            ]);

            // Notify Client
            if ($this->selectedCaseForRevision->client) {
                // You'll need a specific notification for this, e.g., NotificationService::contractChangesDeclinedByLawyer()
                NotificationService::contractChangesDeclinedByLawyer($this->selectedCaseForRevision, Auth::user(), $this->declineReason);
            }

            DB::commit();
            $this->showUploadRevisedContractModal = false;
            $this->selectedCaseForRevision = null;
            $this->revisedContractDocument = null;
            $this->declineReason = '';
            session()->flash('message', 'Client\'s contract changes have been declined and the client has been notified.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to decline contract changes', [
                'case_id' => $this->selectedCaseForRevision->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to decline contract changes: ' . $e->getMessage());
        }
    }

    /**
     * Alias method for declining client changes (called from modal)
     */
    public function declineClientChanges()
    {
        return $this->submitDeclineChanges();
    }

    public function archiveCase($caseId)
    {
        // Implementation of archiveCase method
    }

    /**
     * View case details
     * 
     * @param int $caseId
     * @return void
     */
    public function viewDetails($caseId)
    {
        $case = LegalCase::with(['client.clientProfile', 'consultation', 'contractActions', 'caseUpdates'])
            ->findOrFail($caseId);
        
        // Authorization check
        if ($case->lawyer_id !== Auth::id() && !$case->teamLawyers()->where('user_id', Auth::id())->exists()) {
            session()->flash('error', 'You are not authorized to view this case.');
            return;
        }

        // For now, just redirect to the case setup page or show a modal
        // You can implement a details modal similar to the law firm version
        return redirect()->route('lawyer.case.setup', $case->id);
    }

    /**
     * Show action modal for various case actions
     * 
     * @param int $caseId
     * @param string $action
     * @return void
     */
    public function showAction($caseId, $action)
    {
        $case = LegalCase::findOrFail($caseId);
        
        // Authorization check
        if ($case->lawyer_id !== Auth::id() && !$case->teamLawyers()->where('user_id', Auth::id())->exists()) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }

        $this->selectedCase = $case;
        $this->currentAction = $action;

        switch ($action) {
            case 'upload_contract':
                if ($case->status !== LegalCase::STATUS_CASE_REQUEST_SENT_BY_CLIENT && $case->status !== LegalCase::STATUS_PENDING) {
                    session()->flash('error', 'This case is not in a state where you can send a contract.');
                    return;
                }
                $this->showActionModal = true;
                break;
            // ... other cases ...
        }
    }

    /**
     * Submit the action for the selected case
     */
    public function submitAction()
    {
        if (!$this->selectedCase) {
            session()->flash('error', 'No case selected.');
            return;
        }

        try {
            switch ($this->currentAction) {
                case 'upload_contract':
                    $this->validate([
                        'contract' => 'required|file|mimes:pdf|max:10240'
                    ]);

                    // Store the contract
                    $contractPath = $this->contract->store('contracts', 'public');

                    // Update case status
                    $this->selectedCase->update([
                        'contract_path' => $contractPath,
                        'status' => LegalCase::STATUS_CONTRACT_SENT,
                        'contract_status' => LegalCase::CONTRACT_STATUS_SENT
                    ]);

                    // Create contract action record
                    ContractAction::create([
                        'legal_case_id' => $this->selectedCase->id,
                        'action_type' => 'contract_sent',
                        'actor_type' => User::class,
                        'actor_id' => Auth::id(),
                        'document_path' => $contractPath
                    ]);

                    session()->flash('message', 'Contract has been sent to the client.');
                    break;

                default:
                    session()->flash('error', 'Invalid action type.');
                    return;
            }

            $this->showActionModal = false;
            $this->reset(['contract', 'currentAction']);

        } catch (\Exception $e) {
            Log::error('Error submitting action', [
                'action' => $this->currentAction,
                'case_id' => $this->selectedCase->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error processing action: ' . $e->getMessage());
        }
    }

    public function showStartCaseForm($caseId)
    {
        $case = LegalCase::findOrFail($caseId);
        
        // Authorization check
        if ($case->lawyer_id !== Auth::id() && !$case->teamLawyers()->where('user_id', Auth::id())->exists()) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }

        $this->selectedCase = $case;
        $this->caseTitle = '';
        $this->caseDescription = '';
        $this->contractDocument = null;
        $this->showStartCaseModal = true;
    }

    public function startCase()
    {
        $this->validate([
            'caseTitle' => 'required|min:5|max:255',
            'caseDescription' => 'required|min:10',
            'contractDocument' => 'required|file|max:10240|mimes:pdf'
        ]);

        if (!$this->selectedCase) {
            session()->flash('error', 'No case selected.');
            return;
        }

        try {
            // Store the contract document
            $contractPath = $this->contractDocument->store('contracts', 'public');

            // Update the case
            $this->selectedCase->update([
                'title' => $this->caseTitle,
                'description' => $this->caseDescription,
                'status' => 'contract_sent',
                'contract_path' => $contractPath,
                'contract_status' => 'sent'
            ]);

            // Create contract action record
            ContractAction::create([
                'legal_case_id' => $this->selectedCase->id,
                'action_type' => 'contract_sent',
                'actor_type' => User::class,
                'actor_id' => Auth::id(),
                'document_path' => $contractPath
            ]);

            // Send notification to the client
            NotificationService::caseStarted($this->selectedCase);

            $this->showStartCaseModal = false;
            $this->reset(['caseTitle', 'caseDescription', 'contractDocument', 'selectedCase']);
            
            session()->flash('message', 'Case started successfully. The contract has been sent to the client.');
        } catch (\Exception $e) {
            Log::error('Error starting case', [
                'case_id' => $this->selectedCase->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to start case: ' . $e->getMessage());
        }
    }
} 