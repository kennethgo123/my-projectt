<?php

namespace App\Livewire\Components;

use App\Models\LegalCase;
use App\Models\CasePhase;
use App\Models\CaseEvent;
use App\Models\CaseUpdate;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CasePhaseTracker extends Component
{
    public $case;
    public $phases = [];
    public $currentPhase = null;
    public $currentPhaseUpdates = [];
    public $canManagePhases = false;
    public $readOnly = false;
    public $upcomingEvents = [];
    public $isLastPhase = false;
    public $isPrimaryLawyer = false; // Flag to track if current user is primary lawyer
    
    // For case closing
    public $caseCloseNote = '';
    
    // Form properties for adding a new phase
    public $newPhaseName = '';
    public $newPhaseDescription = '';
    public $newPhaseStartDate = '';
    public $newPhaseEndDate = '';
    public $newPhaseUpdate = '';
    
    // For existing phase updates
    public $selectedPhaseId = null;
    public $phaseUpdateText = '';
    
    // For editing phases
    public $editPhaseId = null;
    public $editPhaseName = '';
    public $editPhaseDescription = '';
    public $editPhaseStartDate = '';
    public $editPhaseEndDate = '';
    
    // Navigation controls
    public $hasNextPhase = false;
    public $hasPreviousPhase = false;
    
    protected $rules = [
        'newPhaseName' => 'required|string|max:100',
        'newPhaseDescription' => 'required|string',
        'newPhaseStartDate' => 'required|date',
        'newPhaseEndDate' => 'required|date|after_or_equal:newPhaseStartDate',
        'phaseUpdateText' => 'required|string|min:10',
        'editPhaseName' => 'required|string|max:100',
        'editPhaseDescription' => 'required|string',
        'editPhaseStartDate' => 'required|date',
        'editPhaseEndDate' => 'required|date|after_or_equal:editPhaseStartDate',
        'caseCloseNote' => 'required|string|min:10',
    ];
    
    protected function getListeners()
    {
        return [
            'closeCase' => 'closeCase'
        ];
    }

    public function mount($caseId, $readOnly = false)
    {
        $this->case = LegalCase::findOrFail($caseId);
        $this->readOnly = $readOnly;
        
        $user = Auth::user();
        $userId = Auth::id();
        
        // Updated to allow team members to manage phases
        $this->canManagePhases = !$this->readOnly && Auth::check() && (
            $user->id === $this->case->lawyer_id || // Direct lawyer
            $this->case->teamLawyers()->where('user_id', $userId)->exists() || // Team member
            ($user->isLawFirm() && DB::table('users')
                ->where('id', $this->case->lawyer_id)
                ->where('firm_id', $user->id)
                ->exists()) // Law firm of the lawyer
        );
        
        // Check if the current user is the primary lawyer for this case
        $this->isPrimaryLawyer = $this->checkIfPrimaryLawyer();
        
        $this->loadPhases();
        $this->loadUpcomingEvents();
        $this->checkNavigationAvailability();
        $this->checkIfLastPhase();
    }
    
    public function loadPhases()
    {
        $this->phases = CasePhase::where('legal_case_id', $this->case->id)
            ->orderBy('order')
            ->get();
            
        $this->currentPhase = $this->phases->firstWhere('is_current', true);
        
        if (!$this->currentPhase && $this->phases->count() > 0) {
            $this->currentPhase = $this->phases->firstWhere('is_completed', false) ?? $this->phases->first();
        }

        $this->currentPhaseUpdates = [];
        if ($this->currentPhase) {
            $this->currentPhaseUpdates = CaseUpdate::where('legal_case_id', $this->case->id)
                ->where('title', 'Phase Update: ' . $this->currentPhase->name)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        $this->selectedPhaseId = null;
    }
    
    public function loadUpcomingEvents()
    {
        // Load events regardless of role, view will control display
        $this->upcomingEvents = CaseEvent::where('legal_case_id', $this->case->id)
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->take(3) // Limit for display purposes
            ->get();
    }
    
    public function checkNavigationAvailability()
    {
        if (!$this->currentPhase || $this->phases->count() <= 1) {
            $this->hasNextPhase = false;
            $this->hasPreviousPhase = false;
            return;
        }
        
        $currentPhaseIndex = $this->phases->search(fn($phase) => $phase->id === $this->currentPhase->id);
        
        $this->hasPreviousPhase = $currentPhaseIndex > 0;
        $this->hasNextPhase = $currentPhaseIndex < ($this->phases->count() - 1);
    }
    
    public function navigateToNextPhase()
    {
        if (!$this->canManagePhases || !$this->hasNextPhase) return;
        $currentPhaseIndex = $this->phases->search(fn($phase) => $phase->id === $this->currentPhase->id);
        $nextPhase = $this->phases[$currentPhaseIndex + 1];
        $this->setCurrentPhase($nextPhase->id);
        $this->checkIfLastPhase();
    }
    
    public function navigateToPreviousPhase()
    {
        if (!$this->canManagePhases || !$this->hasPreviousPhase) return;
        $currentPhaseIndex = $this->phases->search(fn($phase) => $phase->id === $this->currentPhase->id);
        $previousPhase = $this->phases[$currentPhaseIndex - 1];
        $this->setCurrentPhase($previousPhase->id);
        $this->checkIfLastPhase();
    }
    
    public function prepareEditPhase($phaseId)
    {
        if (!$this->canManagePhases) return; 
        
        $phase = CasePhase::where('id', $phaseId)->where('legal_case_id', $this->case->id)->first();
        if (!$phase) {
             session()->flash('error', 'Invalid phase selected.');
             return;
        }
        
        $this->editPhaseId = $phase->id;
        $this->editPhaseName = $phase->name;
        $this->editPhaseDescription = $phase->description;
        $this->editPhaseStartDate = $phase->start_date ? Carbon::parse($phase->start_date)->format('Y-m-d') : null;
        $this->editPhaseEndDate = $phase->end_date ? Carbon::parse($phase->end_date)->format('Y-m-d') : null;
    }
    
    public function editPhase()
    {
        if (!$this->canManagePhases) return;
        
        $this->validate([
            'editPhaseName' => 'required|string|max:100',
            'editPhaseDescription' => 'required|string',
            'editPhaseStartDate' => 'required|date',
            'editPhaseEndDate' => 'required|date|after_or_equal:editPhaseStartDate',
        ]);
        
        $phase = CasePhase::where('id', $this->editPhaseId)->where('legal_case_id', $this->case->id)->first();
        if (!$phase) {
             session()->flash('error', 'Invalid phase selected.');
             return;
        }
        
        $phase->update([
            'name' => $this->editPhaseName,
            'description' => $this->editPhaseDescription,
            'start_date' => $this->editPhaseStartDate,
            'end_date' => $this->editPhaseEndDate,
        ]);
        
        // TODO: Notify client
        
        $this->resetEditForm();
        $this->loadPhases();
        $this->checkNavigationAvailability();
        $this->checkIfLastPhase();
        $this->dispatch('close-modal', 'edit-phase-modal');
        session()->flash('success', 'Phase updated successfully!');
    }
    
    private function resetEditForm()
    {
        $this->editPhaseId = null;
        $this->editPhaseName = '';
        $this->editPhaseDescription = '';
        $this->editPhaseStartDate = '';
        $this->editPhaseEndDate = '';
    }
    
    public function addPhase()
    {
        if (!$this->canManagePhases) return;
        
        $this->validate([
            'newPhaseName' => 'required|string|max:100',
            'newPhaseDescription' => 'required|string',
            'newPhaseStartDate' => 'required|date',
            'newPhaseEndDate' => 'required|date|after_or_equal:newPhaseStartDate',
        ]);
        
        $isFirstPhase = $this->phases->isEmpty();
        $maxOrder = $isFirstPhase ? 0 : (CasePhase::where('legal_case_id', $this->case->id)->max('order') ?? 0);
            
        $phase = CasePhase::create([
            'legal_case_id' => $this->case->id,
            'name' => $this->newPhaseName,
            'description' => $this->newPhaseDescription,
            'start_date' => $this->newPhaseStartDate,
            'end_date' => $this->newPhaseEndDate,
            'is_current' => $isFirstPhase, // First phase is current
            'is_completed' => false,
            'order' => $maxOrder + 1,
        ]);
        
        // TODO: Add initial update/note if provided
        // if (!empty($this->newPhaseUpdate)) {
        //     CaseUpdate::create([...]);
        // }

        // TODO: Notify client

        $this->resetNewPhaseForm();
        $this->loadPhases();
        $this->checkNavigationAvailability();
        $this->checkIfLastPhase();

        // If it was the first phase, set it as current
        if ($isFirstPhase) {
             $this->setCurrentPhase($phase->id);
        }
        
        $this->dispatch('close-modal', 'add-phase-modal');
        session()->flash('success', 'New phase added successfully!');
    }
    
    private function resetNewPhaseForm()
    {
        $this->newPhaseName = '';
        $this->newPhaseDescription = '';
        $this->newPhaseStartDate = '';
        $this->newPhaseEndDate = '';
        $this->newPhaseUpdate = '';
    }

    public function setCurrentPhase($phaseId)
    {
        if (!$this->canManagePhases) return;

        CasePhase::where('legal_case_id', $this->case->id)->update(['is_current' => false]);
        $newCurrentPhase = CasePhase::find($phaseId);
        if ($newCurrentPhase && $newCurrentPhase->legal_case_id === $this->case->id) {
            $newCurrentPhase->update(['is_current' => true, 'is_completed' => false]); // Ensure it's not marked completed
        } else {
             session()->flash('error', 'Could not set current phase.');
        }
        $this->loadPhases();
        $this->checkNavigationAvailability();
        $this->checkIfLastPhase();
    }
    
    public function completePhase($phaseId)
    {
        if (!$this->canManagePhases) return;
        
        $phase = CasePhase::where('id', $phaseId)->where('legal_case_id', $this->case->id)->first();
        if ($phase) {
            $phase->update(['is_completed' => true, 'is_current' => false]);
            // Optionally set the next phase as current if it exists
            $nextPhase = CasePhase::where('legal_case_id', $this->case->id)
                                ->where('order', $phase->order + 1)
                                ->first();
            if ($nextPhase) {
                 $this->setCurrentPhase($nextPhase->id);
            }
            $this->loadPhases();
            $this->checkNavigationAvailability();
            $this->checkIfLastPhase();
            session()->flash('success', 'Phase marked as complete!');
        } else {
             session()->flash('error', 'Could not complete phase.');
        }
    }
    
    public function updatePhase()
    {
        if (!$this->canManagePhases || !$this->selectedPhaseId) {
            session()->flash('error', 'Cannot update phase. You might not have permission or no phase is selected.');
            // Modal remains open for user to see this error, if it was already open.
            return;
        }

        $this->validate(['phaseUpdateText' => 'required|string|min:10']);

        $phase = CasePhase::find($this->selectedPhaseId);

        if ($phase && $phase->legal_case_id === $this->case->id) {
            DB::beginTransaction();
            try {
                CaseUpdate::create([
                    'legal_case_id' => $this->case->id,
                    'user_id' => Auth::id(),
                    'title' => 'Phase Update: ' . $phase->name,
                    'content' => $this->phaseUpdateText,
                    'update_type' => 'phase_update',
                    'visibility' => 'both', // Default, adjust if needed
                    'is_client_visible' => true // Default, adjust if needed
                ]);

                DB::commit();
                session()->flash('success', 'Phase update added successfully!');
                
                $this->phaseUpdateText = ''; // Reset form field
                
                $this->loadPhases(); // Reload all phases and currentPhase with updates
                $this->dispatch('close-modal', 'update-phase-modal');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error adding phase update for case ' . $this->case->id . ', phase ' . $phase->id . ': ' . $e->getMessage());
                session()->flash('error', 'An unexpected error occurred while adding the update. Please try again.');
                // Keep modal open for this kind of error.
            }
        } else {
            session()->flash('error', 'Could not add update: Selected phase is invalid or does not belong to this case.');
            // Keep modal open for this kind of error.
        }
    }
    
    // Method to set the phase for which update is being added
    public function selectPhaseForUpdate($phaseId)
    {
        if (!$this->canManagePhases) return;
        $this->selectedPhaseId = $phaseId;
        $this->phaseUpdateText = '';
        // $this->dispatch('open-modal', 'update-phase-modal'); // Removed as Alpine handles this now
    }

    public function render()
    {
        // Reload phases in case external changes occurred (e.g., updates added)
        // $this->loadPhases(); 
        // $this->checkNavigationAvailability();
        return view('livewire.components.case-phase-tracker');
    }

    /**
     * Check if the current phase is the last phase
     */
    public function checkIfLastPhase()
    {
        if (!$this->currentPhase || $this->phases->isEmpty()) {
            $this->isLastPhase = false;
            return;
        }
        
        // If the case is already completed/closed, don't show the "close case" button
        if ($this->case->status === LegalCase::STATUS_COMPLETED || $this->case->status === LegalCase::STATUS_CLOSED) {
            $this->isLastPhase = false;
            return;
        }
        
        $currentPhaseOrder = $this->currentPhase->order;
        $maxPhaseOrder = $this->phases->max('order');
        
        // Only show the Close Case button if we're at the last phase AND all previous phases are completed
        if ($currentPhaseOrder === $maxPhaseOrder) {
            // Check if all previous phases are completed
            $allPreviousPhasesCompleted = $this->phases
                ->where('order', '<', $maxPhaseOrder)
                ->every(function($phase) {
                    return $phase->is_completed;
                });
            
            $this->isLastPhase = $allPreviousPhasesCompleted;
        } else {
            $this->isLastPhase = false;
        }
    }
    
    /**
     * Check if the current user is the primary lawyer for this case
     * Only primary lawyers are allowed to close the case
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
    
    /**
     * Close the case
     */
    public function closeCase()
    {
        if (!$this->canManagePhases) {
            session()->flash('error', 'You do not have permission to close this case.');
            return;
        }
        
        // Check if user is primary lawyer
        if (!$this->isPrimaryLawyer) {
            session()->flash('error', 'Only the primary lawyer can close this case.');
            $this->dispatch('close-modal', 'close-case-modal');
            return;
        }
        
        $this->validate([
            'caseCloseNote' => 'required|string|min:10',
        ]);
        
        DB::beginTransaction();
        try {
            // Mark all phases as completed
            CasePhase::where('legal_case_id', $this->case->id)
                ->update(['is_completed' => true]);
            
            // Update case status to completed/closed
            $this->case->update([
                'status' => LegalCase::STATUS_COMPLETED,
                'closed_at' => now(),
                'archived' => true
            ]);
            
            // Add a case update for the closing note
            CaseUpdate::create([
                'legal_case_id' => $this->case->id,
                'user_id' => Auth::id(),
                'update_type' => 'case_closed',
                'content' => $this->caseCloseNote,
                'is_client_visible' => true,
            ]);
            
            DB::commit();
            
            session()->flash('success', 'Case has been closed successfully!');
            $this->dispatch('close-modal', 'close-case-modal');
            
            // Reload data
            $this->loadPhases();
            $this->checkNavigationAvailability();
            $this->checkIfLastPhase();
            
            // Notify client that case has been closed
            if (class_exists('App\Services\NotificationService')) {
                try {
                    \App\Services\NotificationService::caseClosed($this->case);
                } catch (\Exception $e) {
                    // Just log the error but don't fail the case closure
                    \Log::warning('Failed to notify client about case closure: ' . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error closing case: ' . $e->getMessage());
        }
    }

    public function openCloseCaseModal()
    {
        $this->dispatch('open-modal', 'close-case-modal');
    }

    public function resetCloseForm()
    {
        $this->caseCloseNote = '';
    }
} 