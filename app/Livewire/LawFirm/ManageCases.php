<?php

namespace App\Livewire\LawFirm;

use App\Models\LegalCase;
use App\Models\User;
use App\Models\Consultation;
use App\Models\ContractAction;
use App\Models\CaseUpdate;
use App\Models\AppNotification;
use App\Events\NotificationReceived;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Carbon\Carbon;
use App\Services\NotificationService;
use App\Services\DocumentConversionService;

// Debug: Log constants to verify they're defined correctly
Log::info('LawFirm ManageCases constants check', [
    'STATUS_CHANGES_REQUESTED_BY_CLIENT' => defined('App\Models\LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT') 
        ? LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT : 'Not defined',
    'STATUS_CONTRACT_REVISED_SENT' => defined('App\Models\LegalCase::STATUS_CONTRACT_REVISED_SENT') 
        ? LegalCase::STATUS_CONTRACT_REVISED_SENT : 'Not defined',
    'CONTRACT_STATUS_REVISED_SENT' => defined('App\Models\LegalCase::CONTRACT_STATUS_REVISED_SENT') 
        ? LegalCase::CONTRACT_STATUS_REVISED_SENT : 'Not defined',
]);

class ManageCases extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $status = '';
    public $priorityFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showArchived = false; // Added for archived cases view
    
    // Selected case properties
    public $selectedCase = null;
    
    // Modal states
    public $showContractModal = false;
    public $showSignatureModal = false;
    public $showActionModal = false;
    public $showDetailsModal = false;
    public $showReassignModal = false;
    public $showTeamManagementModal = false;
    public $showUploadRevisedContractModal = false;
    public $showStartCaseModal = false; // Added for the new modal
    
    // Lawyer reassignment properties
    public $firmLawyers = [];
    public $selectedLawyerId = null;
    public $reassignCaseId = null;
    
    // Action properties
    public $actionType = null; // 'accept', 'reject', 'upload_contract', 'add_update', 'mark_active'
    public $rejectionReason = '';
    public $contract = null;
    public $updateTitle = '';
    public $updateContent = '';
    public $updateVisibility = 'both';
    
    // Signature acknowledgment
    public $signatureAcknowledged = false;

    // Properties for revised contract upload by Law Firm
    public $selectedCaseForRevision;
    public $revisedContractDocument;
    public $declineReason = '';

    // Properties for Start Case Modal (Send Contract)
    public $caseTitle = '';
    public $caseDescription = '';
    public $contractDocument = null;

    protected $rules = [
        'rejectionReason' => 'required_if:actionType,reject|string|min:10',
        'contract' => 'required_if:actionType,upload_contract|file|mimes:pdf|max:10240',
        'updateTitle' => 'required_if:actionType,add_update|string|min:5',
        'updateContent' => 'required_if:actionType,add_update|string|min:10',
        'updateVisibility' => 'required_if:actionType,add_update|string|in:both,client,lawyer',
        'revisedContractDocument' => 'nullable|file|mimes:pdf|max:10240', // For revised contract
        'declineReason' => 'nullable|string|min:10|max:2000', // For revised contract decline
        'caseTitle' => 'required|string|min:5|max:255',
        'caseDescription' => 'required|string|min:10',
        'contractDocument' => 'required|file|mimes:pdf|max:10240', // 10MB Max
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
    
    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    // Added method to toggle archived view
    public function toggleArchivedView()
    {
        $this->showArchived = !$this->showArchived;
        $this->status = ''; // Reset status filter when toggling archived view
        $this->resetPage();
    }
    
    public function viewContract($caseId)
    {
        $this->selectedCase = LegalCase::with(['client', 'contractActions'])->findOrFail($caseId);
        $this->showContractModal = true;
    }
    
    public function viewSignature($caseId)
    {
        Log::info('viewSignature method called', [
            'case_id' => $caseId,
            'user_id' => Auth::id(),
            'component_id' => $this->getId()
        ]);
        
        // Get the case directly from the database to ensure fresh data
        $case = \DB::table('legal_cases')->where('id', $caseId)->first();
        
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
        $this->selectedCase = \App\Models\LegalCase::with(['contractActions'])->findOrFail($caseId);
        
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
                
                // Only dispatch the event to show the modal
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
                \DB::table('legal_cases')
                    ->where('id', $caseId)
                    ->update(['signature_path' => $contractAction->signature_path]);
                
                // Only dispatch the event to show the modal
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
    
    public function showAction($caseId, $action)
    {
        $this->selectedCase = LegalCase::with(['client', 'consultation'])->findOrFail($caseId);
        $this->actionType = $action;
        
        // Handle special action types
        if ($action === 'reassign_lawyer') {
            $this->showReassignLawyer($caseId);
            return;
        }
        
        if ($action === 'manage_team') {
            $this->showTeamManagement($caseId);
            return;
        }
        
        // Reset form fields
        $this->rejectionReason = '';
        $this->contract = null;
        $this->updateTitle = '';
        $this->updateContent = '';
        $this->updateVisibility = 'both';
        
        $this->showActionModal = true;
    }
    
    public function viewDetails($caseId)
    {
        $this->selectedCase = LegalCase::with(['client', 'consultation', 'contractActions', 'caseUpdates'])
            ->findOrFail($caseId);
        $this->showDetailsModal = true;
    }
    
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
            
            // Mark the contract action as acknowledged by law firm
            $contractAction->update([
                'lawyer_acknowledged' => true,
                'lawyer_acknowledged_at' => now(),
                'acknowledged_by' => Auth::id()
            ]);
            
            // Record a case update
            $this->selectedCase->caseUpdates()->create([
                'title' => 'Signature Acknowledged',
                'content' => 'Law firm has acknowledged receipt of client\'s electronic signature.',
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

    /**
     * Submit the selected action on a case
     */
    public function submitAction()
    {
        if (!$this->selectedCase) {
            session()->flash('error', 'No case selected.');
            return;
        }
        
        try {
            switch ($this->actionType) {
                case 'accept':
                    $this->acceptCase();
                    break;
                    
                case 'reject':
                    $this->validate([
                        'rejectionReason' => 'required|min:10',
                    ]);
                    $this->rejectCase();
                    break;
                    
                case 'upload_contract':
                    $this->validate([
                        'contract' => 'required|file|mimes:pdf|max:10240',
                    ]);
                    $this->uploadContract();
                    break;
                    
                case 'add_update':
                    $this->validate([
                        'updateTitle' => 'required|min:5|max:255',
                        'updateContent' => 'required|min:10',
                    ]);
                    $this->addCaseUpdate();
                    break;
                    
                case 'mark_active':
                    $this->markCaseActive();
                    break;
                    
                case 'mark_complete':
                    $this->markCaseComplete();
                    break;
                    
                default:
                    session()->flash('error', 'Invalid action type.');
                    return;
            }
            
            // Close the modal
            $this->resetAction();
            
        } catch (\Exception $e) {
            Log::error('Case action failed', [
                'error' => $e->getMessage(),
                'action' => $this->actionType,
                'case_id' => $this->selectedCase->id ?? null
            ]);
            session()->flash('error', 'Action failed: ' . $e->getMessage());
        }
    }
    
    protected function acceptCase()
    {
        $this->selectedCase->update([
            'status' => LegalCase::STATUS_ACCEPTED,
        ]);
        
        // Create case update
        $this->selectedCase->caseUpdates()->create([
            'title' => 'Case Accepted',
            'content' => 'Your case has been accepted by the law firm. They will prepare a contract for you to review.',
            'user_id' => Auth::id(),
            'visibility' => 'both'
        ]);
        
        session()->flash('message', 'Case has been accepted successfully.');
    }
    
    protected function rejectCase()
    {
        $this->selectedCase->update([
            'status' => LegalCase::STATUS_REJECTED,
            'rejection_reason' => $this->rejectionReason
        ]);
        
        // Create case update
        $this->selectedCase->caseUpdates()->create([
            'title' => 'Case Rejected',
            'content' => 'This case has been rejected. Reason: ' . $this->rejectionReason,
            'user_id' => Auth::id(),
            'visibility' => 'both'
        ]);
        
        session()->flash('message', 'Case has been rejected.');
    }
    
    protected function uploadContract()
    {
        $this->validate([
            'contract' => 'required|file|mimes:pdf|max:10240'
        ]);

        if (!$this->selectedCase) {
            session()->flash('error', 'No case selected.');
            return;
        }

        try {
            DB::beginTransaction();

            // Store the original file first
            $originalPath = $this->contract->store('contracts', 'public');
            
            // Convert to PDF if needed
            $conversionService = app(DocumentConversionService::class);
            $pdfPath = $conversionService->convertContractToPdf($originalPath);
            
            if (!$pdfPath) {
                DB::rollBack();
                session()->flash('error', 'Failed to convert the contract to PDF. Please try again.');
                return;
            }
            
            $this->selectedCase->update([
                'status' => LegalCase::STATUS_CONTRACT_SENT,
                'contract_status' => LegalCase::CONTRACT_STATUS_SENT,
                'contract_path' => $pdfPath
            ]);

            // Record contract action
            $this->selectedCase->contractActions()->create([
                'action_type' => 'sent',
                'user_id' => Auth::id(),
                'notes' => 'Contract sent to client for review and signature.',
                'document_path' => $pdfPath
            ]);
            
            // Create case update
            $this->selectedCase->caseUpdates()->create([
                'title' => 'Contract Sent',
                'content' => 'A contract has been sent for your review and signature.',
                'user_id' => Auth::id(),
                'visibility' => 'both'
            ]);
            
            DB::commit();
            
            session()->flash('message', 'Contract uploaded successfully. The client will be notified to review it.');
            $this->reset('contract');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract upload failed', [
                'error' => $e->getMessage(),
                'case_id' => $this->selectedCase->id
            ]);
            session()->flash('error', 'Failed to upload the contract. Please try again.');
        }
    }
    
    protected function addCaseUpdate()
    {
        // Create case update
        $this->selectedCase->caseUpdates()->create([
            'title' => $this->updateTitle,
            'content' => $this->updateContent,
            'user_id' => Auth::id(),
            'visibility' => $this->updateVisibility
        ]);
        
        session()->flash('message', 'Case update added successfully.');
    }
    
    /**
     * Marks a case as finished setup and changes status to active
     *
     * @param int $caseId
     * @return void
     */
    public function finishSetup($caseId)
    {
        try {
            $case = LegalCase::findOrFail($caseId);
            
            // Only allow finishing setup for contract_signed cases
            if ($case->status !== LegalCase::STATUS_CONTRACT_SIGNED) {
                session()->flash('error', 'Only cases with signed contracts can be marked as active.');
                return;
            }
            
            // Update the case status to active
            $case->update([
                'status' => LegalCase::STATUS_ACTIVE,
            ]);
            
            // Create case update
            $case->caseUpdates()->create([
                'title' => 'Case Activated',
                'content' => 'Case setup has been completed and case is now active.',
                'user_id' => Auth::id(),
                'visibility' => 'both'
            ]);
            
            session()->flash('message', 'Case has been marked as active successfully.');
        } catch (\Exception $e) {
            Log::error('Case Activation Error', ['case_id' => $caseId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Failed to activate case: ' . $e->getMessage());
        }
    }
    
    protected function markCaseActive()
    {
        $this->selectedCase->update([
            'status' => LegalCase::STATUS_ACTIVE,
        ]);
        
        // Create case update
        $this->selectedCase->caseUpdates()->create([
            'title' => 'Case Active',
            'content' => 'This case is now active and work has officially begun.',
            'user_id' => Auth::id(),
            'visibility' => 'both'
        ]);
        
        session()->flash('message', 'Case has been marked as active.');
    }
    
    protected function resetAction()
    {
        $this->showActionModal = false;
        $this->showDetailsModal = false;
        $this->showContractModal = false;
        $this->showReassignModal = false;
        $this->actionType = null;
        $this->rejectionReason = '';
        $this->contract = null;
        $this->updateTitle = '';
        $this->updateContent = '';
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
        \Illuminate\Support\Facades\Log::info('CASE LABEL UPDATE - Called', [
            'caseId' => $caseId,
            'label' => $label,
            'user_id' => auth()->id()
        ]);
        
        try {
            $case = LegalCase::findOrFail($caseId);
            
            // Validate that label is one of the allowed values
            $validLabels = [
                LegalCase::CASE_LABEL_HIGH_PRIORITY,
                LegalCase::CASE_LABEL_MEDIUM_PRIORITY, 
                LegalCase::CASE_LABEL_LOW_PRIORITY
            ];
            
            if (!in_array($label, $validLabels) && $label !== '') {
                \Illuminate\Support\Facades\Log::warning('CASE LABEL UPDATE - Invalid Label', [
                    'provided_label' => $label,
                    'allowed_labels' => $validLabels
                ]);
                session()->flash('error', 'Invalid case label value.');
                return;
            }
            
            \Illuminate\Support\Facades\Log::info('CASE LABEL UPDATE - Before Update', [
                'old_label' => $case->case_label,
                'new_label' => $label
            ]);
            
            $updated = $case->update(['case_label' => $label]);
            
            \Illuminate\Support\Facades\Log::info('CASE LABEL UPDATE - After Update', [
                'updated' => $updated,
                'current_label' => $case->fresh()->case_label
            ]);
            
            session()->flash('message', 'Case label updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('CASE LABEL UPDATE - Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error updating case label: ' . $e->getMessage());
        }
    }

    /**
     * Update the case priority
     *
     * @param int $caseId
     * @param string $priority
     * @return void
     */
    public function updatePriority($caseId, $priority)
    {
        try {
            $case = LegalCase::findOrFail($caseId);
            
            // Update the case priority
            $case->update([
                'priority' => $priority
            ]);
            
            session()->flash('message', 'Case priority updated to ' . ucfirst($priority));
        } catch (\Exception $e) {
            Log::error('Case Priority Update Error', ['case_id' => $caseId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Failed to update case priority: ' . $e->getMessage());
        }
    }
    
    /**
     * Start a case from a completed consultation
     *
     * @param int $consultationId
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function startCaseFromConsultation($consultationId)
    {
        $consultation = Consultation::findOrFail($consultationId);
        
        // Check if consultation is completed
        if ($consultation->status !== 'completed' || !$consultation->can_start_case) {
            session()->flash('error', 'The consultation must be completed before starting a case.');
            return;
        }
        
        // Check if a case already exists for this consultation
        if ($consultation->case) {
            session()->flash('error', 'A case already exists for this consultation.');
            return;
        }
        
        // Redirect to start case form with consultation ID
        return redirect()->route('law-firm.start-case', ['consultation' => $consultationId]);
    }

    /**
     * Show the reassign lawyer modal for a case
     *
     * @param int $caseId
     * @return void
     */
    public function showReassignLawyer($caseId)
    {
        $this->reassignCaseId = $caseId;
        $this->selectedCase = LegalCase::findOrFail($caseId);
        $this->selectedLawyerId = $this->selectedCase->lawyer_id;
        
        // Get all lawyers under this law firm
        $lawFirmId = Auth::id();
        $this->loadFirmLawyers($lawFirmId);
        
        $this->showReassignModal = true;
    }
    
    /**
     * Load all lawyers that belong to this law firm
     *
     * @param int $lawFirmId
     * @return void
     */
    protected function loadFirmLawyers($lawFirmId)
    {
        // Find lawyers with firm_id equal to the law firm user ID
        $firmLawyers = User::where('firm_id', $lawFirmId)
            ->where('status', 'approved')
            ->whereHas('role', function($query) {
                $query->where('name', 'lawyer');
            })
            ->with(['lawyerProfile', 'lawFirmLawyer'])
            ->get();
            
        // Add the law firm itself as an option
        $lawFirm = User::with('lawFirmProfile')->find($lawFirmId);
        
        $this->firmLawyers = collect([
            [
                'id' => $lawFirmId,
                'name' => $lawFirm->lawFirmProfile ? $lawFirm->lawFirmProfile->firm_name : 'Your Firm',
                'is_firm' => true
            ]
        ])->concat(
            $firmLawyers->map(function($lawyer) {
                $name = '';
                if ($lawyer->lawFirmLawyer) {
                    $name = $lawyer->lawFirmLawyer->first_name . ' ' . $lawyer->lawFirmLawyer->last_name;
                } elseif ($lawyer->lawyerProfile) {
                    $name = $lawyer->lawyerProfile->first_name . ' ' . $lawyer->lawyerProfile->last_name;
                } else {
                    $name = $lawyer->name;
                }
                
                return [
                    'id' => $lawyer->id,
                    'name' => $name,
                    'is_firm' => false
                ];
            })
        )->toArray();
    }

    /**
     * Reassign a case to a different lawyer
     *
     * @return void
     */
    public function reassignLawyer()
    {
        if (!$this->reassignCaseId || !$this->selectedLawyerId) {
            session()->flash('error', 'Please select a lawyer to reassign the case.');
            return;
        }
        
        try {
            $case = LegalCase::findOrFail($this->reassignCaseId);
            $oldLawyerId = $case->lawyer_id;
            
            // Don't do anything if the lawyer hasn't changed
            if ($oldLawyerId == $this->selectedLawyerId) {
                $this->resetReassignment();
                session()->flash('message', 'No changes were made as the selected lawyer is already assigned to this case.');
                return;
            }
            
            // Get lawyer information for notifications
            $oldLawyer = User::find($oldLawyerId);
            $newLawyer = User::find($this->selectedLawyerId);
            
            // Update the case with the new lawyer
            $case->update([
                'lawyer_id' => $this->selectedLawyerId
            ]);
            
            // Create a case update record
            $case->caseUpdates()->create([
                'title' => 'Lawyer Reassigned',
                'content' => 'This case has been reassigned to a different lawyer by the law firm.',
                'user_id' => Auth::id(),
                'visibility' => 'law_firm' // Only visible to the law firm
            ]);
            
            // Notify the client about the lawyer change
            try {
                // Format lawyer names for notification
                $newLawyerName = $this->formatLawyerName($newLawyer);
                
                // Create notification for client
                AppNotification::create([
                    'user_id' => $case->client_id,
                    'type' => 'lawyer_reassigned',
                    'title' => 'Lawyer Changed on Your Case',
                    'content' => "Your case has been reassigned to {$newLawyerName}.",
                    'link' => route('client.cases'),
                    'is_read' => false
                ]);
                
                // Notify the newly assigned lawyer
                if ($this->selectedLawyerId != Auth::id()) {
                    AppNotification::create([
                        'user_id' => $this->selectedLawyerId,
                        'type' => 'case_assigned',
                        'title' => 'New Case Assigned',
                        'content' => "A case has been assigned to you by your law firm: {$case->title}",
                        'link' => route('lawyer.cases'),
                        'is_read' => false
                    ]);
                    
                    // Dispatch notification event for real-time updates
                    try {
                        event(new NotificationReceived($this->selectedLawyerId));
                    } catch (\Exception $e) {
                        Log::warning('Failed to dispatch lawyer notification event: ' . $e->getMessage());
                    }
                }
                
                // Notify the previously assigned lawyer if it's not the firm itself
                if ($oldLawyerId != Auth::id()) {
                    AppNotification::create([
                        'user_id' => $oldLawyerId,
                        'type' => 'case_unassigned',
                        'title' => 'Case Reassigned',
                        'content' => "A case previously assigned to you has been reassigned: {$case->title}",
                        'link' => route('lawyer.cases'),
                        'is_read' => false
                    ]);
                    
                    // Dispatch notification event for real-time updates
                    try {
                        event(new NotificationReceived($oldLawyerId));
                    } catch (\Exception $e) {
                        Log::warning('Failed to dispatch lawyer notification event: ' . $e->getMessage());
                    }
                }
                
                // Dispatch notification event for client
                try {
                    event(new NotificationReceived($case->client_id));
                } catch (\Exception $e) {
                    Log::warning('Failed to dispatch client notification event: ' . $e->getMessage());
                }
                
            } catch (\Exception $e) {
                Log::warning('Failed to create notification for lawyer reassignment: ' . $e->getMessage());
                // Continue execution even if notification fails
            }
            
            $this->resetReassignment();
            session()->flash('message', 'Case has been successfully reassigned.');
            
        } catch (\Exception $e) {
            Log::error('Case Reassignment Error', [
                'case_id' => $this->reassignCaseId, 
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to reassign case: ' . $e->getMessage());
        }
    }
    
    /**
     * Helper method to format lawyer name for display and notifications
     *
     * @param User $lawyer
     * @return string
     */
    protected function formatLawyerName($lawyer)
    {
        if (!$lawyer) {
            return 'Unknown Lawyer';
        }
        
        if ($lawyer->isLawFirm() && $lawyer->lawFirmProfile) {
            return $lawyer->lawFirmProfile->firm_name;
        } elseif ($lawyer->lawFirmLawyer) {
            return $lawyer->lawFirmLawyer->first_name . ' ' . $lawyer->lawFirmLawyer->last_name;
        } elseif ($lawyer->lawyerProfile) {
            return $lawyer->lawyerProfile->first_name . ' ' . $lawyer->lawyerProfile->last_name;
        } else {
            return $lawyer->name;
        }
    }
    
    /**
     * Reset reassignment related properties
     *
     * @return void
     */
    protected function resetReassignment()
    {
        $this->showReassignModal = false;
        $this->reassignCaseId = null;
        $this->selectedLawyerId = null;
        $this->firmLawyers = [];
    }

    /**
     * Properties and methods for multiple lawyer assignment
     */
    
    // Modal states for multiple lawyer assignment
    public $showTeamModal = false;
    
    // Team management properties
    public $teamCaseId = null;
    public $selectedCase2 = null;
    public $assignedLawyers = [];
    public $availableLawyers = [];
    public $newLawyerId = null;
    public $lawyerRole = 'team_member';
    public $lawyerNotes = '';
    public $editingLawyerId = null;
    public $editingRole = '';
    public $editingNotes = '';

    /**
     * Show the team management modal for a case
     *
     * @param int $caseId
     * @return void
     */
    public function showTeamManagement($caseId)
    {
        try {
            $this->teamCaseId = $caseId;
            $this->selectedCase2 = LegalCase::with([
                'assignedLawyers.lawyerProfile', 
                'assignedLawyers.lawFirmLawyer',
                'caseLawyers'
            ])->findOrFail($caseId);
            
            // Get the assigned lawyers
            $this->loadAssignedLawyers();
            
            // Get the available lawyers (not yet assigned to this case)
            $this->loadAvailableLawyers();
            
            // Reset form fields
            $this->newLawyerId = null;
            $this->lawyerRole = 'team_member';
            $this->lawyerNotes = '';
            $this->editingLawyerId = null;
            $this->editingRole = '';
            $this->editingNotes = '';
            
            // Show the modal
            $this->showTeamModal = true;
            
        } catch (\Exception $e) {
            Log::error('Error showing team management modal', [
                'error' => $e->getMessage(),
                'case_id' => $caseId
            ]);
            session()->flash('error', 'Error loading team management: ' . $e->getMessage());
        }
    }
    
    /**
     * Load lawyers currently assigned to the case
     *
     * @return void
     */
    protected function loadAssignedLawyers()
    {
        if (!$this->selectedCase2) {
            return;
        }
        
        // Get case lawyer relationships with all data
        $this->assignedLawyers = $this->selectedCase2->caseLawyers()
            ->with(['lawyer.lawyerProfile', 'lawyer.lawFirmLawyer', 'assignedBy'])
            ->get()
            ->map(function($caseLawyer) {
                // Format the lawyer name
                $lawyerName = $this->formatLawyerName($caseLawyer->lawyer);
                
                // Format the assigned by name
                $assignedByName = $caseLawyer->assignedBy ? 
                    $this->formatLawyerName($caseLawyer->assignedBy) : 
                    'Unknown';
                
                return [
                    'id' => $caseLawyer->id,
                    'lawyer_id' => $caseLawyer->user_id,
                    'lawyer_name' => $lawyerName,
                    'role' => $caseLawyer->role,
                    'notes' => $caseLawyer->notes,
                    'is_primary' => $caseLawyer->is_primary,
                    'assigned_by' => $assignedByName,
                    'assigned_at' => $caseLawyer->created_at->format('M d, Y'),
                ];
            })
            ->toArray();
    }
    
    /**
     * Load lawyers available to be assigned to the case
     *
     * @return void
     */
    protected function loadAvailableLawyers()
    {
        if (!$this->selectedCase2) {
            return;
        }
        
        $lawFirmId = Auth::id();
        
        // Get all lawyers under this law firm
        $allFirmLawyers = User::where('firm_id', $lawFirmId)
            ->where('status', 'approved')
            ->whereHas('role', function($query) {
                $query->where('name', 'lawyer');
            })
            ->with(['lawyerProfile', 'lawFirmLawyer'])
            ->get();
            
        // Get IDs of lawyers already assigned to this case
        $assignedLawyerIds = $this->selectedCase2->assignedLawyers()->pluck('users.id')->toArray();
        
        // Filter out already assigned lawyers
        $this->availableLawyers = $allFirmLawyers
            ->filter(function($lawyer) use ($assignedLawyerIds) {
                return !in_array($lawyer->id, $assignedLawyerIds);
            })
            ->map(function($lawyer) {
                return [
                    'id' => $lawyer->id,
                    'name' => $this->formatLawyerName($lawyer),
                ];
            })
            ->toArray();
            
        // Also add the law firm itself as an option if not already assigned
        if (!in_array($lawFirmId, $assignedLawyerIds)) {
            $lawFirm = User::with('lawFirmProfile')->find($lawFirmId);
            array_unshift($this->availableLawyers, [
                'id' => $lawFirmId,
                'name' => $lawFirm->lawFirmProfile ? $lawFirm->lawFirmProfile->firm_name : 'Your Firm',
            ]);
        }
    }
    
    /**
     * Add a lawyer to the case team
     *
     * @return void
     */
    public function addLawyerToTeam()
    {
        if (!$this->teamCaseId || !$this->newLawyerId) {
            session()->flash('error', 'Please select a lawyer to add to the team.');
            return;
        }
        
        try {
            $case = LegalCase::findOrFail($this->teamCaseId);
            
            // Check if this is the first lawyer being assigned (primary)
            $isPrimary = $case->caseLawyers()->count() === 0;
            
            // Create the case lawyer record
            $caseLawyer = \App\Models\CaseLawyer::create([
                'legal_case_id' => $this->teamCaseId,
                'user_id' => $this->newLawyerId,
                'assigned_by' => Auth::id(),
                'role' => $this->lawyerRole,
                'notes' => $this->lawyerNotes,
                'is_primary' => $isPrimary,
            ]);
            
            // If this is the first lawyer assigned, also update the main lawyer_id on the case
            if ($isPrimary) {
                $case->update([
                    'lawyer_id' => $this->newLawyerId
                ]);
            }
            
            // Get the added lawyer
            $lawyer = User::find($this->newLawyerId);
            
            // Create a case update record
            $case->caseUpdates()->create([
                'title' => 'Lawyer Added to Team',
                'content' => $this->formatLawyerName($lawyer) . ' has been added to the case team' . 
                             ($this->lawyerRole ? ' with the role of ' . $this->lawyerRole : '') . '.',
                'user_id' => Auth::id(),
                'visibility' => 'law_firm' // Only visible to the law firm
            ]);
            
            // Notify the newly assigned lawyer if it's not the current user
            if ($this->newLawyerId != Auth::id()) {
                try {
                    // Create notification
                    AppNotification::create([
                        'user_id' => $this->newLawyerId,
                        'type' => 'case_team_added',
                        'title' => 'Added to Case Team',
                        'content' => "You've been added to the team for case: {$case->title}",
                        'link' => route('lawyer.cases'),
                        'is_read' => false
                    ]);
                    
                    // Dispatch notification event for real-time updates
                    event(new NotificationReceived($this->newLawyerId));
                } catch (\Exception $e) {
                    Log::warning('Failed to create notification for lawyer team assignment: ' . $e->getMessage());
                }
            }
            
            // Refresh the lawyer lists
            $this->loadAssignedLawyers();
            $this->loadAvailableLawyers();
            
            // Reset form fields
            $this->newLawyerId = null;
            $this->lawyerRole = 'team_member';
            $this->lawyerNotes = '';
            
            session()->flash('message', 'Lawyer has been added to the case team.');
            
        } catch (\Exception $e) {
            Log::error('Error adding lawyer to team', [
                'error' => $e->getMessage(),
                'case_id' => $this->teamCaseId,
                'lawyer_id' => $this->newLawyerId
            ]);
            session()->flash('error', 'Failed to add lawyer to team: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove a lawyer from the case team
     *
     * @param int $caseLawyerId
     * @return void
     */
    public function removeLawyerFromTeam($caseLawyerId)
    {
        try {
            // Find the case lawyer relationship
            $caseLawyer = \App\Models\CaseLawyer::with(['lawyer', 'legalCase'])->findOrFail($caseLawyerId);
            $lawyerId = $caseLawyer->user_id;
            $lawyerName = $this->formatLawyerName($caseLawyer->lawyer);
            $case = $caseLawyer->legalCase;
            
            // Check if this is the primary lawyer
            $isPrimary = $caseLawyer->is_primary;
            
            // Delete the relationship
            $caseLawyer->delete();
            
            // If this was the primary lawyer, assign a new primary
            if ($isPrimary) {
                // Find another lawyer assigned to the case
                $newPrimary = \App\Models\CaseLawyer::where('legal_case_id', $case->id)
                    ->first();
                
                if ($newPrimary) {
                    // Mark this lawyer as primary
                    $newPrimary->update(['is_primary' => true]);
                    
                    // Update the case lawyer_id
                    $case->update(['lawyer_id' => $newPrimary->user_id]);
                    
                    // Add a note about the primary lawyer change
                    $case->caseUpdates()->create([
                        'title' => 'Primary Lawyer Changed',
                        'content' => $this->formatLawyerName(User::find($newPrimary->user_id)) . 
                                     ' is now the primary lawyer for this case.',
                        'user_id' => Auth::id(),
                        'visibility' => 'law_firm'
                    ]);
                }
            }
            
            // Create a case update record
            $case->caseUpdates()->create([
                'title' => 'Lawyer Removed from Team',
                'content' => $lawyerName . ' has been removed from the case team.',
                'user_id' => Auth::id(),
                'visibility' => 'law_firm' // Only visible to the law firm
            ]);
            
            // Notify the lawyer that they've been removed (if it's not the current user)
            if ($lawyerId != Auth::id()) {
                try {
                    // Create notification
                    AppNotification::create([
                        'user_id' => $lawyerId,
                        'type' => 'case_team_removed',
                        'title' => 'Removed from Case Team',
                        'content' => "You've been removed from the team for case: {$case->title}",
                        'link' => route('lawyer.cases'),
                        'is_read' => false
                    ]);
                    
                    // Dispatch notification event
                    event(new NotificationReceived($lawyerId));
                } catch (\Exception $e) {
                    Log::warning('Failed to create notification for lawyer team removal: ' . $e->getMessage());
                }
            }
            
            // Refresh the lawyer lists
            $this->loadAssignedLawyers();
            $this->loadAvailableLawyers();
            
            session()->flash('message', 'Lawyer has been removed from the case team.');
            
        } catch (\Exception $e) {
            Log::error('Error removing lawyer from team', [
                'error' => $e->getMessage(),
                'case_lawyer_id' => $caseLawyerId
            ]);
            session()->flash('error', 'Failed to remove lawyer from team: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the edit lawyer role modal
     *
     * @param int $caseLawyerId
     * @return void
     */
    public function showEditLawyerRole($caseLawyerId)
    {
        try {
            $caseLawyer = \App\Models\CaseLawyer::findOrFail($caseLawyerId);
            
            $this->editingLawyerId = $caseLawyerId;
            $this->editingRole = $caseLawyer->role ?? '';
            $this->editingNotes = $caseLawyer->notes ?? '';
            
        } catch (\Exception $e) {
            Log::error('Error showing edit lawyer role', [
                'error' => $e->getMessage(),
                'case_lawyer_id' => $caseLawyerId
            ]);
            session()->flash('error', 'Error loading lawyer details: ' . $e->getMessage());
        }
    }
    
    /**
     * Update a lawyer's role/notes on the case team
     *
     * @return void
     */
    public function updateLawyerRole()
    {
        if (!$this->editingLawyerId) {
            session()->flash('error', 'No lawyer selected for update.');
            return;
        }
        
        try {
            // Find the case lawyer relationship
            $caseLawyer = \App\Models\CaseLawyer::with(['lawyer', 'legalCase'])->findOrFail($this->editingLawyerId);
            $lawyerName = $this->formatLawyerName($caseLawyer->lawyer);
            
            // Update the role and notes
            $caseLawyer->update([
                'role' => $this->editingRole,
                'notes' => $this->editingNotes
            ]);
            
            // Create a case update record
            $caseLawyer->legalCase->caseUpdates()->create([
                'title' => 'Lawyer Role Updated',
                'content' => $lawyerName . '\'s role has been updated to "' . $this->editingRole . '".',
                'user_id' => Auth::id(),
                'visibility' => 'law_firm' // Only visible to the law firm
            ]);
            
            // Refresh the assigned lawyers list
            $this->loadAssignedLawyers();
            
            // Reset form fields
            $this->editingLawyerId = null;
            $this->editingRole = '';
            $this->editingNotes = '';
            
            session()->flash('message', 'Lawyer role has been updated.');
            
        } catch (\Exception $e) {
            Log::error('Error updating lawyer role', [
                'error' => $e->getMessage(),
                'case_lawyer_id' => $this->editingLawyerId
            ]);
            session()->flash('error', 'Failed to update lawyer role: ' . $e->getMessage());
        }
    }
    
    /**
     * Make a lawyer the primary lawyer for the case
     *
     * @param int $caseLawyerId
     * @return void
     */
    public function makePrimaryLawyer($caseLawyerId)
    {
        try {
            // Find the case lawyer relationship
            $caseLawyer = \App\Models\CaseLawyer::with(['lawyer', 'legalCase'])->findOrFail($caseLawyerId);
            $lawyerName = $this->formatLawyerName($caseLawyer->lawyer);
            $case = $caseLawyer->legalCase;
            
            // Reset primary status for all lawyers on this case
            \App\Models\CaseLawyer::where('legal_case_id', $case->id)
                ->update(['is_primary' => false]);
            
            // Set this lawyer as primary
            $caseLawyer->update(['is_primary' => true]);
            
            // Update the main lawyer_id on the case
            $case->update(['lawyer_id' => $caseLawyer->user_id]);
            
            // Create a case update record
            $case->caseUpdates()->create([
                'title' => 'Primary Lawyer Changed',
                'content' => $lawyerName . ' is now the primary lawyer for this case.',
                'user_id' => Auth::id(),
                'visibility' => 'law_firm' // Only visible to the law firm
            ]);
            
            // Refresh the assigned lawyers list
            $this->loadAssignedLawyers();
            
            session()->flash('message', $lawyerName . ' is now the primary lawyer for this case.');
            
        } catch (\Exception $e) {
            Log::error('Error setting primary lawyer', [
                'error' => $e->getMessage(),
                'case_lawyer_id' => $caseLawyerId
            ]);
            session()->flash('error', 'Failed to set primary lawyer: ' . $e->getMessage());
        }
    }
    
    /**
     * Reset the team management form
     */
    protected function resetTeamManagement()
    {
        $this->showTeamModal = false;
        $this->teamCaseId = null;
        $this->selectedCase2 = null;
        $this->assignedLawyers = [];
        $this->availableLawyers = [];
        $this->newLawyerId = null;
        $this->lawyerRole = 'team_member';
        $this->lawyerNotes = '';
        $this->editingLawyerId = null;
        $this->editingRole = '';
        $this->editingNotes = '';
    }

    // Debug method to test if button clicks are working
    public function debugSelectCase()
    {
        Log::info('Debug method called - button clicks are working');
        
        // Find the first case with changes_requested_by_client status for testing
        $case = LegalCase::where('status', LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT)->first();
        
        if ($case) {
            $this->selectedCaseForRevision = $case;
            $this->showUploadRevisedContractModal = true;
            Log::info('Debug: Selected first case with changes requested status', ['case_id' => $case->id]);
        } else {
            Log::info('Debug: No cases found with changes_requested_by_client status');
            session()->flash('error', 'No cases found with changes_requested_by_client status for testing.');
        }
    }

    // Method to open the revised contract upload modal for Law Firms
    public function openUploadRevisedContractModal($caseId)
    {
        Log::info('LawFirm openUploadRevisedContractModal called', ['case_id' => $caseId, 'law_firm_id' => Auth::id()]);
        
        try {
            $case = LegalCase::findOrFail($caseId);
            $lawFirmId = Auth::id();

            Log::info('Case found', ['case_id' => $caseId, 'case_status' => $case->status, 'lawyer_id' => $case->lawyer_id]);

            // Authorization check
            $caseLawyerId = $case->lawyer_id;
            $isAssignedToFirmItself = ($caseLawyerId == $lawFirmId);
            $assignedLawyer = User::find($caseLawyerId);
            $isAssignedToFirmsLawyer = ($assignedLawyer && $assignedLawyer->firm_id == $lawFirmId);
            $isTeamMemberFromFirm = $case->teamLawyers()->where('firm_id', $lawFirmId)->exists();

            Log::info('Authorization check', [
                'isAssignedToFirmItself' => $isAssignedToFirmItself,
                'isAssignedToFirmsLawyer' => $isAssignedToFirmsLawyer,
                'isTeamMemberFromFirm' => $isTeamMemberFromFirm
            ]);

            if (!($isAssignedToFirmItself || $isAssignedToFirmsLawyer || $isTeamMemberFromFirm)) {
                Log::warning('Authorization failed for openUploadRevisedContractModal');
                session()->flash('error', 'You are not authorized to manage this case.');
                return;
            }

            if ($case->status !== LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT) {
                Log::warning('Invalid case status for revised contract upload', ['status' => $case->status]);
                session()->flash('error', 'A revised contract can only be uploaded if the client has requested changes. Case status is: ' . $case->status);
                return;
            }
            
            $this->selectedCaseForRevision = $case;
            $this->revisedContractDocument = null; // Reset previous selection
            $this->declineReason = ''; // Reset
            $this->resetValidation(); // Reset validation errors
            $this->showUploadRevisedContractModal = true;

            Log::info('Modal opened successfully', ['showModal' => $this->showUploadRevisedContractModal]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Case not found during openUploadRevisedContractModal', ['case_id' => $caseId, 'error' => $e->getMessage()]);
            session()->flash('error', 'Case not found.');
        } catch (\Exception $e) {
            Log::error('Exception in openUploadRevisedContractModal', [
                'case_id' => $caseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'An unexpected error occurred while opening the upload modal.');
        }
    }

    // Method to submit the revised contract for Law Firms
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

        $case = $this->selectedCaseForRevision;
        $lawFirmId = Auth::id();

        // Authorization check
        $caseLawyerId = $case->lawyer_id;
        $isAssignedToFirmItself = ($caseLawyerId == $lawFirmId);
        
        $assignedLawyer = User::find($caseLawyerId);
        $isAssignedToFirmsLawyer = ($assignedLawyer && $assignedLawyer->firm_id == $lawFirmId);
        
        $isTeamMemberFromFirm = $case->teamLawyers()->where('firm_id', $lawFirmId)->exists();

        if (!($isAssignedToFirmItself || $isAssignedToFirmsLawyer || $isTeamMemberFromFirm)) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }

        try {
            DB::beginTransaction();

            $newContractPath = $this->revisedContractDocument->store('contracts', 'public');

            $case->contract_path = $newContractPath;
            $case->status = LegalCase::STATUS_CONTRACT_REVISED_SENT;
            $case->contract_status = LegalCase::CONTRACT_STATUS_REVISED_SENT;
            $case->lawyer_response_required = false; // Client's turn
            $case->lawyer_response_message = null; 
            $case->save();

            ContractAction::create([
                'legal_case_id' => $case->id,
                'action_type' => 'revised_contract_uploaded',
                'actor_type' => User::class, // Actor is the Law Firm User
                'actor_id' => Auth::id(),
                'details' => 'Law firm uploaded a revised contract.',
                'document_path' => $newContractPath,
            ]);

            if ($case->client) {
                NotificationService::revisedContractUploaded($case, Auth::user());
            }

            DB::commit();
            $this->showUploadRevisedContractModal = false;
            $this->selectedCaseForRevision = null;
            $this->revisedContractDocument = null;
            $this->declineReason = '';
            session()->flash('message', 'Revised contract has been uploaded and the client has been notified.');
            
            // Law firm should stay on their page - no redirect needed

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit revised contract by Law Firm', [
                'case_id' => $case->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to upload revised contract: ' . $e->getMessage());
        }
    }

    // Method to decline client's changes for Law Firms
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

        $case = $this->selectedCaseForRevision;
        $lawFirmId = Auth::id();

        // Authorization check
        $caseLawyerId = $case->lawyer_id;
        $isAssignedToFirmItself = ($caseLawyerId == $lawFirmId);
        
        $assignedLawyer = User::find($caseLawyerId);
        $isAssignedToFirmsLawyer = ($assignedLawyer && $assignedLawyer->firm_id == $lawFirmId);
        
        $isTeamMemberFromFirm = $case->teamLawyers()->where('firm_id', $lawFirmId)->exists();

        if (!($isAssignedToFirmItself || $isAssignedToFirmsLawyer || $isTeamMemberFromFirm)) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }

        try {
            DB::beginTransaction();

            $case->status = LegalCase::STATUS_CONTRACT_REVISED_SENT; 
            // Consider a more specific contract_status like 'firm_declined_changes' if your ENUM supports it
            // For now, using a generic status that implies lawyer/firm action and client needs to review.
            $case->contract_status = 'lawyer_declined_changes'; 
            $case->lawyer_response_message = $this->declineReason;
            $case->lawyer_response_required = false; 
            $case->save();

            ContractAction::create([
                'legal_case_id' => $case->id,
                'action_type' => 'lawyer_declined_changes', // Or 'firm_declined_changes'
                'actor_type' => User::class, // Actor is Law Firm User
                'actor_id' => Auth::id(),
                'details' => 'Law firm declined client\'s requested changes. Reason: ' . $this->declineReason,
            ]);

            if ($case->client) {
                NotificationService::contractChangesDeclinedByLawyer($case, Auth::user(), $this->declineReason);
            }

            DB::commit();
            $this->showUploadRevisedContractModal = false;
            $this->selectedCaseForRevision = null;
            $this->revisedContractDocument = null;
            $this->declineReason = '';
            session()->flash('message', 'Client\'s contract changes have been declined and the client has been notified.');
            
            // Consider redirecting client to review the declined changes if there's a specific view for that
            // For now, redirecting to the main case details for the client.
             return redirect()->route('client.case.details', ['id' => $case->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to decline contract changes by Law Firm', [
                'case_id' => $case->id ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to decline contract changes: ' . $e->getMessage());
        }
    }

    /**
     * Marks a case as completed
     *
     * @return void
     */
    protected function markCaseComplete()
    {
        try {
            if (!$this->selectedCase) {
                session()->flash('error', 'No case selected.');
                return;
            }
            
            // Only allow completing active or contract signed cases
            if (!in_array($this->selectedCase->status, [LegalCase::STATUS_ACTIVE, LegalCase::STATUS_CONTRACT_SIGNED])) {
                session()->flash('error', 'Only active cases can be marked as completed.');
                return;
            }
            
            // Update the case status to completed
            $this->selectedCase->update([
                'status' => LegalCase::STATUS_COMPLETED,
                'closed_at' => now(),
                'archived' => true
            ]);
            
            // Create case update
            $this->selectedCase->caseUpdates()->create([
                'title' => 'Case Completed',
                'content' => 'Case has been marked as completed.',
                'user_id' => Auth::id(),
                'visibility' => 'both'
            ]);
            
            // Notify client that case is complete
            try {
                // Get the client
                $client = $this->selectedCase->client;
                
                if ($client) {
                    AppNotification::create([
                        'user_id' => $client->id,
                        'type' => 'case_completed',
                        'title' => 'Case Completed',
                        'content' => "Your case {$this->selectedCase->case_number} has been marked as complete.",
                        'link' => route('client.cases'),
                        'is_read' => false
                    ]);
                    
                    // Dispatch notification event
                    event(new NotificationReceived($client->id));
                }
            } catch (\Exception $e) {
                Log::warning('Failed to create notification for case completion: ' . $e->getMessage());
            }
            
            session()->flash('message', 'Case has been marked as completed successfully.');
        } catch (\Exception $e) {
            Log::error('Case Completion Error', ['case_id' => $this->selectedCase->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function showStartCaseForm($caseId)
    {
        try {
            $this->selectedCase = LegalCase::with('client.clientProfile')->findOrFail($caseId);
            
            // Check if the law firm is authorized to manage this case
            $lawFirmId = Auth::id();
            
            // Case is directly assigned to the law firm
            $isDirectlyAssigned = $this->selectedCase->lawyer_id === $lawFirmId;
            
            // Case is assigned to a lawyer who belongs to this law firm
            $isAssignedToFirmLawyer = false;
            if ($this->selectedCase->lawyer) {
                $isAssignedToFirmLawyer = $this->selectedCase->lawyer->firm_id === $lawFirmId;
            }
            
            // Admin check
            $isAdmin = Auth::user()->isAdmin();
            
            // Debug information
            Log::info('Law Firm Case Authorization Check', [
                'case_id' => $caseId,
                'law_firm_id' => $lawFirmId,
                'case_lawyer_id' => $this->selectedCase->lawyer_id,
                'isDirectlyAssigned' => $isDirectlyAssigned,
                'isAssignedToFirmLawyer' => $isAssignedToFirmLawyer,
                'isAdmin' => $isAdmin
            ]);

            if (!($isDirectlyAssigned || $isAssignedToFirmLawyer || $isAdmin)) {
                session()->flash('error', 'You are not authorized to manage this case.');
                $this->selectedCase = null;
                return;
            }

            if ($this->selectedCase->status !== LegalCase::STATUS_PENDING) {
                session()->flash('error', 'This case is not in a Pending state.');
                $this->selectedCase = null;
                return;
            }

            $this->caseTitle = $this->selectedCase->title ?? '';
            $this->caseDescription = $this->selectedCase->description ?? '';
            $this->contractDocument = null;
            $this->resetValidation();
            $this->showStartCaseModal = true;
        } catch (\Exception $e) {
            Log::error('Error showing start case form for law firm: ' . $e->getMessage(), ['case_id' => $caseId, 'exception' => $e]);
            session()->flash('error', 'Could not load case details. ' . $e->getMessage());
        }
    }

    public function startCase()
    {
        if (!$this->selectedCase) {
            session()->flash('error', 'No case selected. Please try again.');
            return;
        }

        $this->validate([
            'caseTitle' => 'required|string|min:5|max:255',
            'caseDescription' => 'required|string|min:10',
            'contractDocument' => 'required|file|mimes:pdf|max:10240',
        ]);

        // Check if the law firm is authorized to manage this case
        $lawFirmId = Auth::id();
        
        // Case is directly assigned to the law firm
        $isDirectlyAssigned = $this->selectedCase->lawyer_id === $lawFirmId;
        
        // Case is assigned to a lawyer who belongs to this law firm
        $isAssignedToFirmLawyer = false;
        if ($this->selectedCase->lawyer) {
            $isAssignedToFirmLawyer = $this->selectedCase->lawyer->firm_id === $lawFirmId;
        }
        
        // Admin check
        $isAdmin = Auth::user()->isAdmin();

        if (!($isDirectlyAssigned || $isAssignedToFirmLawyer || $isAdmin)) {
            session()->flash('error', 'You are not authorized to manage this case.');
            return;
        }

        try {
            DB::beginTransaction();

            $contractPath = $this->contractDocument->store('contracts', 'public');

            $this->selectedCase->title = $this->caseTitle;
            $this->selectedCase->description = $this->caseDescription;
            $this->selectedCase->contract_path = $contractPath;
            $this->selectedCase->status = LegalCase::STATUS_CONTRACT_SENT;
            $this->selectedCase->contract_status = LegalCase::CONTRACT_STATUS_SENT;
            
            if ($this->selectedCase->law_firm_id && !$this->selectedCase->lawyer_id) {
                $this->selectedCase->lawyer_id = Auth::id(); 
            }
            $this->selectedCase->save();

            ContractAction::create([
                'legal_case_id' => $this->selectedCase->id,
                'action_type' => 'contract_sent',
                'actor_type' => User::class,
                'actor_id' => Auth::id(),
                'document_path' => $contractPath,
                'details' => 'Contract sent by law firm.'
            ]);

            if ($this->selectedCase->client) {
                NotificationService::contractSent($this->selectedCase);
            }

            DB::commit();
            $this->showStartCaseModal = false;
            $this->reset(['caseTitle', 'caseDescription', 'contractDocument', 'selectedCase']);
            session()->flash('message', 'Contract has been sent to the client and the case has been started.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting case for law firm: ' . $e->getMessage(), [
                'case_id' => $this->selectedCase->id ?? 'N/A',
                'error' => $e->getMessage(),
                'exception' => $e
            ]);
            session()->flash('error', 'Failed to send contract and start case: ' . $e->getMessage());
        }
    }

    public function openDetailsModal($caseId)
    {
        $this->selectedCase = LegalCase::with(['client', 'consultation', 'contractActions', 'caseUpdates'])
            ->findOrFail($caseId);
        $this->showDetailsModal = true;
    }

    public function render()
    {
        // Get all lawyers under this law firm
        $lawFirmId = Auth::id();
        $lawyerIds = \App\Models\User::whereHas('role', function($query) {
                $query->where('name', 'lawyer');
            })
            ->where('firm_id', $lawFirmId)
            ->pluck('id')
            ->push($lawFirmId) // Include the law firm's own ID
            ->toArray();

        $query = LegalCase::with([
            'client.clientProfile', 
            'lawyer.lawyerProfile', 
            'lawyer.lawFirmProfile',
            'lawyer.lawFirmLawyer'
        ])
            ->whereIn('lawyer_id', $lawyerIds);

        // Filter for archived or active cases
        if ($this->showArchived) {
            $query->whereIn('status', [LegalCase::STATUS_COMPLETED, LegalCase::STATUS_CLOSED]);
        } else {
            $query->whereNotIn('status', [LegalCase::STATUS_COMPLETED, LegalCase::STATUS_CLOSED]);
            if ($this->status) { // Apply status filter only for active cases view
                $query->where('status', $this->status);
            }
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('case_number', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client.clientProfile', function($q) {
                      $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        $cases = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        // Get pending cases that require lawyer response - this should always show for non-archived view
        $pendingCases = collect(); // Default to empty collection
        if (!$this->showArchived) {
            $pendingCases = LegalCase::with([
                'client.clientProfile', 
                'consultation',
                'lawyer.lawyerProfile',
                'lawyer.lawFirmProfile',
                'lawyer.lawFirmLawyer'
            ])
                ->whereIn('lawyer_id', $lawyerIds)
                ->where('status', 'pending')
                ->where('lawyer_response_required', true)
                ->latest()
                ->get();
        }

        $statuses = [
            'pending' => 'Pending',
            'active' => 'Active',
            // Removed completed, closed, archived as they are handled by the toggle
            LegalCase::STATUS_CONTRACT_SENT => 'Contract Sent',
            LegalCase::STATUS_CONTRACT_SIGNED => 'Contract Signed',
            LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT => 'Changes Requested',
            LegalCase::STATUS_CONTRACT_REVISED_SENT => 'Revised Contract Sent',
            LegalCase::STATUS_ACCEPTED => 'Accepted',
            LegalCase::STATUS_REJECTED => 'Rejected',
            LegalCase::STATUS_IN_PROGRESS => 'In Progress',
        ];
        
        // If showing archived, only allow filtering by completed or closed
        if ($this->showArchived) {
            $statuses = [
                LegalCase::STATUS_COMPLETED => 'Completed',
                LegalCase::STATUS_CLOSED => 'Closed',
            ];
        }

        return view('livewire.law-firm.manage-cases', [
            'cases' => $cases,
            'statuses' => $statuses,
            'pendingCases' => $pendingCases,
            'showArchived' => $this->showArchived // Pass showArchived to the view
        ]);
    }
} 