<?php

namespace App\Livewire\Lawyer;

use App\Models\LegalCase;
use App\Models\ContractAction;
use App\Notifications\ContractActionNotification;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\DocumentConversionService;

class CaseDetails extends Component
{
    use WithFileUploads;

    public LegalCase $case;
    public $contract;
    public $counterOfferContract;
    public $rejectionReason;
    public $counterOfferTerms;
    public $declineReason;
    public $showAcceptModal = false;
    public $showRejectModal = false;
    public $showAcceptNegotiationForm = false;
    public $showCounterOfferForm = false;
    public $showDeclineForm = false;
    public $showPhaseSetupModal = false;
    public $casePhases = [];
    public $currentPhase = null;
    public $phaseStartDate = null;
    public $phaseEndDate = null;
    public $phaseDescription = null;
    public $newTask = '';
    public $newTaskDueDate = null;
    public $clientTasks = [];
    public $activeTab = 'overview';
    public $hasLawyer;
    public $dbTasks;
    public $casePriority;

    protected $rules = [
        'contract' => 'required|file|mimes:pdf|max:10240',
        'counterOfferContract' => 'required|file|mimes:pdf|max:10240',
        'rejectionReason' => 'required|min:10|max:1000',
        'counterOfferTerms' => 'required|min:10|max:1000',
        'declineReason' => 'required|min:10|max:1000',
    ];

    protected $messages = [
        'contract.required' => 'Please select a contract file to upload.',
        'contract.mimes' => 'The contract must be a PDF file.',
        'contract.max' => 'The contract file size must not exceed 10MB.',
        'counterOfferContract.required' => 'Please upload a revised contract with your counter offer.',
        'counterOfferContract.mimes' => 'The contract must be a PDF file.',
        'counterOfferContract.max' => 'The contract file size must not exceed 10MB.',
        'rejectionReason.required' => 'Please provide a reason for rejecting the case.',
        'counterOfferTerms.required' => 'Please provide your counter offer terms.',
        'declineReason.required' => 'Please provide a reason for declining the changes.',
    ];

    public function mount(LegalCase $case)
    {
        $userId = Auth::id();
        // Check if user is either the primary lawyer or a team member
        $isAuthorized = $case->lawyer_id === $userId || 
                         $case->teamLawyers()->where('user_id', $userId)->exists();
                         
        if (!$isAuthorized) {
            abort(403, 'You are not authorized to view this case.');
        }
        
        $this->case = $case;
        $this->casePriority = $case->priority;
        
        // Check if case is already owned by a lawyer
        $this->hasLawyer = $case->lawyer_id !== null;
        
        // Default phases - can be customized in the future
        $this->casePhases = [
            ['name' => 'Filing', 'description' => 'Initial case filing and documentation', 'start_date' => null, 'end_date' => null, 'complete' => false],
            ['name' => 'Discovery', 'description' => 'Gathering evidence and information', 'start_date' => null, 'end_date' => null, 'complete' => false],
            ['name' => 'Pre-Trial', 'description' => 'Preparation for trial', 'start_date' => null, 'end_date' => null, 'complete' => false],
            ['name' => 'Trial', 'description' => 'Court proceedings', 'start_date' => null, 'end_date' => null, 'complete' => false],
            ['name' => 'Resolution', 'description' => 'Case resolution and wrap-up', 'start_date' => null, 'end_date' => null, 'complete' => false]
        ];
        
        // Load phases from case if they exist
        if ($case->phases) {
            $this->casePhases = $case->phases;
        }
        
        // Load client tasks
        if ($case->client_tasks) {
            $this->clientTasks = json_decode($case->client_tasks, true) ?? [];
        }
        
        // Also load tasks from the case_tasks table
        $this->dbTasks = \App\Models\CaseTask::where('legal_case_id', $case->id)
            ->orderBy('due_date')
            ->get();
    }
    
    /**
     * Helper method to check if current user is authorized to manage this case
     * Includes both primary lawyer and team members
     */
    private function isAuthorized()
    {
        $userId = Auth::id();
        return $this->case->lawyer_id === $userId || 
               $this->case->teamLawyers()->where('user_id', $userId)->exists();
    }

    public function showAcceptCase()
    {
        $this->showAcceptModal = true;
    }

    public function showRejectCase()
    {
        $this->showRejectModal = true;
    }

    public function acceptCase()
    {
        if (!$this->isAuthorized()) {
            abort(403);
        }

        // Create contract action record
        ContractAction::create([
            'legal_case_id' => $this->case->id,
            'action_type' => 'accepted',
            'actor_type' => 'lawyer',
            'actor_id' => Auth::id(),
            'details' => 'Case accepted by lawyer'
        ]);

        $this->case->update([
            'status' => 'accepted',
            'lawyer_response_required' => false
        ]);

        // Notify client
        $this->case->client->notify(new ContractActionNotification(
            $this->case,
            'Case Accepted',
            'Your case has been accepted by the lawyer.'
        ));

        session()->flash('message', 'Case accepted successfully.');
    }

    public function rejectCase()
    {
        $this->validate();

        if (!$this->isAuthorized()) {
            abort(403);
        }

        // Create contract action record
        ContractAction::create([
            'legal_case_id' => $this->case->id,
            'action_type' => 'rejected',
            'actor_type' => 'lawyer',
            'actor_id' => Auth::id(),
            'details' => $this->rejectionReason
        ]);

        $this->case->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'lawyer_response_required' => false
        ]);

        // Notify client
        $this->case->client->notify(new ContractActionNotification(
            $this->case,
            'Case Rejected',
            'Your case has been rejected by the lawyer.'
        ));

        $this->showRejectModal = false;
        $this->rejectionReason = '';
        
        session()->flash('message', 'Case rejected successfully.');
    }

    public function uploadContract()
    {
        $this->validate([
            'contract' => 'required|file|mimes:pdf|max:10240'
        ]);

        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action.');
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
            
            $this->case->update([
                'status' => LegalCase::STATUS_CONTRACT_SENT,
                'contract_status' => LegalCase::CONTRACT_STATUS_SENT,
                'contract_path' => $pdfPath
            ]);
            
            DB::commit();
            
            session()->flash('message', 'Contract uploaded successfully. The client will be notified to review it.');
            $this->reset('contract');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract upload failed', [
                'error' => $e->getMessage(),
                'case_id' => $this->case->id
            ]);
            session()->flash('error', 'Failed to upload the contract. Please try again.');
        }
    }

    public function uploadRevisedContract()
    {
        $this->validate([
            'contract' => 'required|file|mimes:pdf|max:10240'
        ]);

        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }

        try {
            // If there's an existing contract, store it in revision history or delete it
            if ($this->case->contract_path) {
                Storage::disk('public')->delete($this->case->contract_path);
            }

            // Store the new contract
            $path = $this->contract->store('contracts', 'public');
            
            $this->case->update([
                'status' => LegalCase::STATUS_CONTRACT_SENT,
                'contract_status' => LegalCase::CONTRACT_STATUS_SENT,
                'contract_path' => $path,
                'negotiation_terms' => null // Clear negotiation terms as they've been addressed
            ]);
            
            session()->flash('message', 'Revised contract uploaded successfully. The client will be notified to review it.');
            $this->reset('contract');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload the revised contract. Please try again.');
        }
    }

    // Clear Response
    public function clearResponse()
    {
        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }

        try {
            $this->case->update([
                'lawyer_response' => null,
                'lawyer_response_message' => null
            ]);

            $this->showAcceptNegotiationForm = false;
            $this->showCounterOfferForm = false;
            $this->showDeclineForm = false;
            
            session()->flash('message', 'Response cleared. You can now provide a new response.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to clear response. Please try again.');
        }
    }

    // Show/Hide Form Methods
    public function showAcceptNegotiation()
    {
        $this->reset(['counterOfferTerms', 'declineReason', 'contract', 'counterOfferContract']);
        $this->showAcceptNegotiationForm = true;
        $this->showCounterOfferForm = false;
        $this->showDeclineForm = false;
    }

    public function showCounterOffer()
    {
        $this->reset(['declineReason', 'contract', 'counterOfferContract']);
        $this->showAcceptNegotiationForm = false;
        $this->showCounterOfferForm = true;
        $this->showDeclineForm = false;
    }

    public function showDeclineNegotiation()
    {
        $this->reset(['counterOfferTerms', 'contract', 'counterOfferContract']);
        $this->showAcceptNegotiationForm = false;
        $this->showCounterOfferForm = false;
        $this->showDeclineForm = true;
    }

    // Accept Negotiation
    public function acceptNegotiation()
    {
        $this->validate([
            'contract' => 'required|file|mimes:pdf|max:10240'
        ]);

        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }

        try {
            if ($this->case->contract_path) {
                Storage::disk('public')->delete($this->case->contract_path);
            }

            $path = $this->contract->store('contracts', 'public');
            
            $this->case->update([
                'status' => LegalCase::STATUS_CONTRACT_SENT,
                'contract_status' => LegalCase::CONTRACT_STATUS_SENT,
                'contract_path' => $path,
                'negotiation_terms' => null,
                'lawyer_response' => 'accepted',
                'lawyer_response_message' => 'Changes accepted and new contract uploaded.'
            ]);
            
            session()->flash('message', 'Changes accepted and new contract uploaded successfully.');
            $this->reset(['contract', 'showAcceptNegotiationForm']);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload the revised contract. Please try again.');
        }
    }

    // Submit Counter Offer
    public function submitCounterOffer()
    {
        $this->validate([
            'counterOfferTerms' => 'required|min:10|max:1000',
            'counterOfferContract' => 'required|file|mimes:pdf|max:10240'
        ]);

        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }

        try {
            // Store the new contract
            if ($this->case->contract_path) {
                Storage::disk('public')->delete($this->case->contract_path);
            }

            $path = $this->counterOfferContract->store('contracts', 'public');
            
            $this->case->update([
                'contract_status' => 'counter_offered',
                'lawyer_response' => 'counter_offered',
                'lawyer_response_message' => $this->counterOfferTerms,
                'contract_path' => $path
            ]);

            session()->flash('message', 'Counter offer and revised contract submitted successfully.');
            $this->reset(['counterOfferTerms', 'counterOfferContract', 'showCounterOfferForm']);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to submit counter offer. Please try again.');
        }
    }

    // Decline Negotiation
    public function declineNegotiation()
    {
        $this->validate([
            'declineReason' => 'required|min:10|max:1000'
        ]);

        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }

        try {
            $this->case->update([
                'contract_status' => 'sent', // Maintain original contract
                'lawyer_response' => 'declined',
                'lawyer_response_message' => $this->declineReason,
                'negotiation_terms' => null
            ]);

            session()->flash('message', 'Changes declined successfully.');
            $this->reset(['declineReason', 'showDeclineForm']);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to decline changes. Please try again.');
        }
    }

    public function showSetupPhases()
    {
        $this->showPhaseSetupModal = true;
    }

    public function setCurrentPhase($index)
    {
        $this->currentPhase = $index;
        $this->phaseDescription = $this->casePhases[$index]['description'] ?? '';
        $this->phaseStartDate = $this->casePhases[$index]['start_date'] ?? null;
        $this->phaseEndDate = $this->casePhases[$index]['end_date'] ?? null;
    }

    public function updatePhase()
    {
        if ($this->currentPhase === null) {
            return;
        }

        $this->casePhases[$this->currentPhase]['description'] = $this->phaseDescription;
        $this->casePhases[$this->currentPhase]['start_date'] = $this->phaseStartDate;
        $this->casePhases[$this->currentPhase]['end_date'] = $this->phaseEndDate;
        
        $this->currentPhase = null;
    }

    public function markPhaseComplete($index, $complete = true)
    {
        $this->casePhases[$index]['complete'] = $complete;
        
        // If marking as complete, set the next phase as active
        if ($complete && isset($this->casePhases[$index + 1])) {
            // Set the next phase start date to today if not set
            if (empty($this->casePhases[$index + 1]['start_date'])) {
                $this->casePhases[$index + 1]['start_date'] = now()->format('Y-m-d');
            }
        }
    }

    public function addTask()
    {
        if (empty($this->newTask) || empty($this->newTaskDueDate)) {
            session()->flash('error', 'Task description and due date are required');
            return;
        }

        // Create task in the case_tasks table instead of the JSON field
        \App\Models\CaseTask::create([
            'legal_case_id' => $this->case->id,
            'title' => $this->newTask,
            'description' => $this->newTask, // Using the same field for title and description
            'due_date' => $this->newTaskDueDate,
            'assigned_to_type' => 'App\Models\User',
            'assigned_to_id' => $this->case->client_id, // Assign to client by default
            'assigned_by' => auth()->id(), // Lawyer is assigning
            'is_completed' => false,
            'status' => 'pending'
        ]);

        $this->newTask = '';
        $this->newTaskDueDate = null;
        
        session()->flash('message', 'Task added successfully');
    }

    public function deleteTask($index)
    {
        if (isset($this->clientTasks[$index])) {
            unset($this->clientTasks[$index]);
            $this->clientTasks = array_values($this->clientTasks); // Re-index array
        }
    }

    public function toggleTaskComplete($index)
    {
        if (isset($this->clientTasks[$index])) {
            $this->clientTasks[$index]['completed'] = !$this->clientTasks[$index]['completed'];
        }
    }

    public function saveSetup()
    {
        // Check if case phases are set up properly
        $hasStartDate = false;
        foreach ($this->casePhases as $phase) {
            if (!empty($phase['start_date'])) {
                $hasStartDate = true;
                break;
            }
        }

        if (!$hasStartDate) {
            session()->flash('error', 'At least one phase must have a start date');
            return;
        }

        try {
            // Consolidate tasks from dbTasks and clientTasks (legacy) before saving
            $allTasksToSave = collect($this->clientTasks); // Start with legacy tasks

            if ($this->dbTasks) {
                foreach ($this->dbTasks as $dbTask) {
                    // Avoid duplicates if a legacy task was already migrated or similar
                    $exists = $allTasksToSave->first(function ($item) use ($dbTask) {
                        // Check by ID if available, otherwise by title/description
                        return (isset($item['id']) && $item['id'] === $dbTask->id) || 
                               (!isset($item['id']) && ($item['description'] ?? '') === $dbTask->title);
                    });
                    if (!$exists) {
                        // Add tasks from dbTasks that are not in clientTasks
                        $allTasksToSave->push([
                            'id' => $dbTask->id, // Keep track of existing DB task ID
                            'title' => $dbTask->title,
                            'description' => $dbTask->description,
                            'due_date' => $dbTask->due_date ? $dbTask->due_date->format('Y-m-d') : null,
                            'is_completed' => $dbTask->is_completed,
                            'assigned_to_id' => $dbTask->assigned_to_id,
                            'assigned_to_type' => $dbTask->assigned_to_type,
                            'assigned_by' => $dbTask->assigned_by,
                            'status' => $dbTask->status
                        ]);
                    }
                }
            }

            $this->case->update([
                'phases' => json_encode($this->casePhases),
                // 'client_tasks' => json_encode($allTasksToSave->toArray()), // Phasing out client_tasks JSON
                'status' => 'active', // Change from setup_pending to active
                'current_phase' => $this->getCurrentPhaseName()
            ]);
            
            // Save tasks to the case_tasks table (update existing or create new)
            foreach ($allTasksToSave as $taskData) {
                if (isset($taskData['id']) && $taskData['id']) {
                    $task = \App\Models\CaseTask::find($taskData['id']);
                    if ($task) {
                        $task->update([
                            'title' => $taskData['title'] ?? $taskData['description'], // Fallback for legacy
                            'description' => $taskData['description'] ?? $taskData['title'],
                            'due_date' => $taskData['due_date'],
                            'is_completed' => $taskData['is_completed'] ?? ($taskData['status'] === 'completed'),
                            'status' => ($taskData['is_completed'] ?? ($taskData['status'] === 'completed')) ? 'completed' : 'pending',
                            // Retain existing assigned_to if not explicitly changed
                            'assigned_to_id' => $taskData['assigned_to_id'] ?? $this->case->client_id, 
                            'assigned_to_type' => $taskData['assigned_to_type'] ?? 'App\Models\User',
                            'assigned_by' => $taskData['assigned_by'] ?? auth()->id(),
                        ]);
                    }
                } else {
                    // Create new task if it doesn't have an ID (likely a new legacy task)
                    if (!empty($taskData['description'])) { // Ensure there's something to save
                        \App\Models\CaseTask::create([
                            'legal_case_id' => $this->case->id,
                            'title' => $taskData['title'] ?? $taskData['description'],
                            'description' => $taskData['description'] ?? $taskData['title'],
                            'due_date' => $taskData['due_date'],
                            'assigned_to_type' => 'App\Models\User',
                            'assigned_to_id' => $taskData['assigned_to_id'] ?? $this->case->client_id, 
                            'assigned_by' => $taskData['assigned_by'] ?? auth()->id(), 
                            'is_completed' => $taskData['is_completed'] ?? false,
                            'status' => ($taskData['is_completed'] ?? false) ? 'completed' : 'pending',
                        ]);
                    }
                }
            }
            
            $this->showPhaseSetupModal = false;
            session()->flash('message', 'Case phases and tasks saved successfully');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save case setup: ' . $e->getMessage());
            Log::error('Case Setup Save Error: ' . $e->getMessage(), ['case_id' => $this->case->id, 'exception' => $e]);
        }
    }

    private function getCurrentPhaseName()
    {
        // Find the current active phase
        foreach ($this->casePhases as $index => $phase) {
            // If this phase is not complete but has a start date, or it's the first incomplete phase
            if ((!$phase['complete'] && !empty($phase['start_date'])) || 
                (!$phase['complete'] && $index === 0)) {
                return $phase['name'];
            }
        }
        
        // Default to the first phase if no active phase found
        return $this->casePhases[0]['name'] ?? 'Filing';
    }

    public function updatedCasePriority()
    {
        try {
            if (!$this->isAuthorized()) {
                session()->flash('error', 'You are not authorized to perform this action.');
                return;
            }
            
            $this->case->update([
                'priority' => $this->casePriority
            ]);
            
            session()->flash('message', 'Case priority updated to ' . ucfirst($this->casePriority));
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update case priority: ' . $e->getMessage());
            Log::error('Case Priority Update Error', ['case_id' => $this->case->id, 'error' => $e->getMessage()]);
            // Revert to original value
            $this->casePriority = $this->case->priority;
        }
    }

    public function render()
    {
        return view('livewire.lawyer.case-details');
    }
} 