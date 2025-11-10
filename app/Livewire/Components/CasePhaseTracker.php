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
    public $selectedPhaseTemplate = '';
    public $customPhaseName = '';
    public $showSuccessModal = false;
    
    // For existing phase updates
    public $selectedPhaseId = null;
    public $phaseUpdateText = '';
    
    // For editing phases
    public $editPhaseId = null;
    public $editPhaseName = '';
    public $editPhaseDescription = '';
    public $editPhaseStartDate = '';
    public $editPhaseEndDate = '';
    public $editSelectedPhaseTemplate = '';
    public $editCustomPhaseName = '';
    
    // For deleting phases
    public $deletePhaseId = null;
    public $deletePhaseName = '';
    
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
        'selectedPhaseTemplate' => 'nullable|string',
        'customPhaseName' => 'required_if:selectedPhaseTemplate,OTHER|string|max:100',
        'editSelectedPhaseTemplate' => 'nullable|string',
        'editCustomPhaseName' => 'required_if:editSelectedPhaseTemplate,OTHER|string|max:100',
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
        // Always load phases ordered by start date (ascending), then by creation date for consistency
        // This matches the reorderPhasesByStartDate method
        $this->phases = CasePhase::where('legal_case_id', $this->case->id)
            ->orderBy('start_date', 'asc')
            ->orderBy('created_at', 'asc')
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
    
    /**
     * Reorder all phases based on their start dates
     * This ensures phases are always in chronological order
     * If start dates are the same, order by creation date (older first)
     */
    private function reorderPhasesByStartDate()
    {
        $phases = CasePhase::where('legal_case_id', $this->case->id)
            ->orderBy('start_date', 'asc')
            ->orderBy('created_at', 'asc') // If same start date, use creation order
            ->get();
        
        // Update order for each phase based on its position
        foreach ($phases as $index => $phase) {
            if ($phase->order != $index) {
                $phase->update(['order' => $index]);
            }
        }
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
        
        // Try to match the phase name with a template
        $templates = $this->getPhaseTemplates();
        $matchedTemplate = null;
        foreach ($templates as $key => $template) {
            if ($template['name'] === $phase->name) {
                $matchedTemplate = $key;
                break;
            }
        }
        
        if ($matchedTemplate) {
            $this->editSelectedPhaseTemplate = $matchedTemplate;
            $this->editCustomPhaseName = '';
        } else {
            $this->editSelectedPhaseTemplate = 'OTHER';
            $this->editCustomPhaseName = $phase->name;
        }
    }
    
    public function updatedEditSelectedPhaseTemplate($value)
    {
        if ($value && $value !== 'OTHER') {
            $templates = $this->getPhaseTemplates();
            if (isset($templates[$value])) {
                $this->editPhaseName = $templates[$value]['name'];
                $this->editPhaseDescription = $templates[$value]['description'];
            }
        } elseif ($value === 'OTHER') {
            // Keep the current custom name if switching to OTHER
            if (empty($this->editCustomPhaseName)) {
                $this->editPhaseName = '';
            } else {
                $this->editPhaseName = $this->editCustomPhaseName;
            }
            $this->editPhaseDescription = '';
        }
    }
    
    public function editPhase()
    {
        if (!$this->canManagePhases) return;
        
        // Determine phase name and description based on selection
        if ($this->editSelectedPhaseTemplate === 'OTHER') {
            $phaseName = $this->editCustomPhaseName;
            $phaseDescription = $this->editPhaseDescription;
        } elseif (!empty($this->editSelectedPhaseTemplate)) {
            $templates = $this->getPhaseTemplates();
            if (isset($templates[$this->editSelectedPhaseTemplate])) {
                $phaseName = $templates[$this->editSelectedPhaseTemplate]['name'];
                $phaseDescription = $this->editPhaseDescription ?: $templates[$this->editSelectedPhaseTemplate]['description'];
            } else {
                session()->flash('error', 'Invalid phase template selected.');
                return;
            }
        } else {
            // Fallback to manual entry
            $phaseName = $this->editPhaseName;
            $phaseDescription = $this->editPhaseDescription;
        }
        
        $this->validate([
            'editPhaseStartDate' => 'required|date',
            'editPhaseEndDate' => 'required|date|after_or_equal:editPhaseStartDate',
        ], [
            'editPhaseStartDate.required' => 'Start date is required.',
            'editPhaseEndDate.required' => 'End date is required.',
            'editPhaseEndDate.after_or_equal' => 'End date must be after or equal to start date.',
        ]);
        
        // Validate phase name
        if (empty($phaseName)) {
            session()->flash('error', 'Phase name is required.');
            return;
        }
        
        // Validate phase description
        if (empty($phaseDescription)) {
            session()->flash('error', 'Phase description is required.');
            return;
        }
        
        $phase = CasePhase::where('id', $this->editPhaseId)->where('legal_case_id', $this->case->id)->first();
        if (!$phase) {
             session()->flash('error', 'Invalid phase selected.');
             return;
        }
        
        // Check for date overlaps with OTHER phases (excluding the current phase being edited)
        $overlappingPhases = CasePhase::where('legal_case_id', $this->case->id)
            ->where('id', '!=', $this->editPhaseId)
            ->where(function($query) {
                $query->whereDate('start_date', '<=', $this->editPhaseEndDate)
                      ->whereDate('end_date', '>=', $this->editPhaseStartDate);
            })
            ->get();
        
        if ($overlappingPhases->isNotEmpty()) {
            $overlappingDates = $overlappingPhases->map(function($p) {
                return $p->name . ' (' . Carbon::parse($p->start_date)->format('M d, Y') . ' - ' . Carbon::parse($p->end_date)->format('M d, Y') . ')';
            })->join(', ');
            
            session()->flash('error', 'The phase dates overlap with existing phase(s): ' . $overlappingDates . '. Please adjust the dates to avoid overlaps.');
            return;
        }
        
        $phase->update([
            'name' => $phaseName,
            'description' => $phaseDescription,
            'start_date' => $this->editPhaseStartDate,
            'end_date' => $this->editPhaseEndDate,
        ]);
        
        // Reorder phases after editing dates
        $this->reorderPhasesByStartDate();
        
        // TODO: Notify client
        
        $this->resetEditForm();
        $this->loadPhases();
        $this->checkNavigationAvailability();
        $this->checkIfLastPhase();
        $this->dispatch('close-modal', 'edit-phase-modal');
        session()->flash('success', 'Phase updated successfully!');
    }
    
    public function prepareDeletePhase($phaseId)
    {
        if (!$this->canManagePhases) {
            session()->flash('error', 'You do not have permission to delete phases.');
            return;
        }
        
        $phase = CasePhase::where('id', $phaseId)->where('legal_case_id', $this->case->id)->first();
        if (!$phase) {
            session()->flash('error', 'Phase not found.');
            return;
        }
        
        $this->deletePhaseId = $phase->id;
        $this->deletePhaseName = $phase->name;
    }
    
    public function confirmDeletePhase()
    {
        if (!$this->canManagePhases) {
            session()->flash('error', 'You do not have permission to delete phases.');
            return;
        }
        
        if (!$this->deletePhaseId) {
            session()->flash('error', 'No phase selected for deletion.');
            return;
        }
        
        $phase = CasePhase::where('id', $this->deletePhaseId)->where('legal_case_id', $this->case->id)->first();
        if (!$phase) {
            session()->flash('error', 'Phase not found.');
            $this->resetDeleteForm();
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $phaseName = $phase->name;
            $wasCurrent = $phase->is_current;
            
            // Delete the phase
            $phase->delete();
            
            // Reorder remaining phases
            $this->reorderPhasesByStartDate();
            
            // If the deleted phase was current, set the first phase as current
            if ($wasCurrent) {
                $firstPhase = CasePhase::where('legal_case_id', $this->case->id)
                    ->orderBy('start_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->first();
                
                if ($firstPhase) {
                    $this->setCurrentPhase($firstPhase->id);
                }
            }
            
            DB::commit();
            
            $this->resetDeleteForm();
            $this->loadPhases();
            $this->checkNavigationAvailability();
            $this->checkIfLastPhase();
            
            $this->dispatch('close-modal', 'delete-phase-modal');
            session()->flash('success', 'Phase "' . $phaseName . '" has been deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting phase: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete phase. Please try again.');
        }
    }
    
    public function resetDeleteForm()
    {
        $this->deletePhaseId = null;
        $this->deletePhaseName = '';
    }
    
    private function resetEditForm()
    {
        $this->editPhaseId = null;
        $this->editPhaseName = '';
        $this->editPhaseDescription = '';
        $this->editPhaseStartDate = '';
        $this->editPhaseEndDate = '';
        $this->editSelectedPhaseTemplate = '';
        $this->editCustomPhaseName = '';
    }
    
    public function getPhaseTemplates()
    {
        return [
            'PRE_LITIGATION' => [
                'name' => 'Pre-Litigation',
                'description' => 'Demand letters, negotiation, ADR, settlement efforts'
            ],
            'FILING_INITIATION' => [
                'name' => 'Filing/Initiation',
                'description' => 'Drafting and filing of complaint/petition, payment of fees'
            ],
            'PRELIMINARY_PROCEEDINGS' => [
                'name' => 'Preliminary Proceedings',
                'description' => 'Service of summons, answer, preliminary hearings'
            ],
            'PRE_TRIAL' => [
                'name' => 'Pre-Trial',
                'description' => 'Pre-trial conference, stipulations, marking of evidence'
            ],
            'TRIAL' => [
                'name' => 'Trial',
                'description' => 'Presentation of evidence, witness examination, trial proper'
            ],
            'POST_TRIAL' => [
                'name' => 'Post-Trial',
                'description' => 'Submission of memoranda, judgment/decision'
            ],
            'POST_JUDGMENT_REMEDIES' => [
                'name' => 'Post-Judgment Remedies',
                'description' => 'Motions for reconsideration, appeal, or execution'
            ],
            'ENFORCEMENT_EXECUTION' => [
                'name' => 'Enforcement/Execution',
                'description' => 'Enforcement of judgment, writs, collection, or delivery of property'
            ],
        ];
    }
    
    public function updatedSelectedPhaseTemplate($value)
    {
        if ($value && $value !== 'OTHER') {
            $templates = $this->getPhaseTemplates();
            if (isset($templates[$value])) {
                $this->newPhaseName = $templates[$value]['name'];
                $this->newPhaseDescription = $templates[$value]['description'];
            }
        } else {
            // Reset when "OTHER" is selected or empty
            $this->newPhaseName = '';
            $this->newPhaseDescription = '';
        }
    }

    public function addPhase()
    {
        if (!$this->canManagePhases) return;
        
        // Determine phase name and description based on selection
        if ($this->selectedPhaseTemplate === 'OTHER') {
            // Use custom phase name when "Other" is selected
            $phaseName = $this->customPhaseName;
            $phaseDescription = $this->newPhaseDescription;
        } elseif (!empty($this->selectedPhaseTemplate)) {
            // Use template
            $templates = $this->getPhaseTemplates();
            if (isset($templates[$this->selectedPhaseTemplate])) {
                $phaseName = $templates[$this->selectedPhaseTemplate]['name'];
                $phaseDescription = $this->newPhaseDescription ?: $templates[$this->selectedPhaseTemplate]['description'];
            } else {
                session()->flash('error', 'Invalid phase template selected.');
                return;
            }
        } else {
            // Fallback to manual entry (existing behavior)
            $phaseName = $this->newPhaseName;
            $phaseDescription = $this->newPhaseDescription;
        }
        
        $this->validate([
            'newPhaseStartDate' => 'required|date',
            'newPhaseEndDate' => 'required|date|after_or_equal:newPhaseStartDate',
        ], [
            'newPhaseStartDate.required' => 'Start date is required.',
            'newPhaseEndDate.required' => 'End date is required.',
            'newPhaseEndDate.after_or_equal' => 'End date must be after or equal to start date.',
        ]);
        
        // Validate phase name
        if (empty($phaseName)) {
            session()->flash('error', 'Phase name is required.');
            return;
        }
        
        // Validate phase description
        if (empty($phaseDescription)) {
            session()->flash('error', 'Phase description is required.');
            return;
        }
        
        // Check for date overlaps with existing phases and get overlapping phase details
        // Two date ranges overlap if: newStart <= existingEnd AND newEnd >= existingStart
        $overlappingPhases = CasePhase::where('legal_case_id', $this->case->id)
            ->where(function($query) {
                $query->whereDate('start_date', '<=', $this->newPhaseEndDate)
                      ->whereDate('end_date', '>=', $this->newPhaseStartDate);
            })
            ->get();
        
        if ($overlappingPhases->isNotEmpty()) {
            $overlappingNames = $overlappingPhases->pluck('name')->join(', ');
            $overlappingDates = $overlappingPhases->map(function($phase) {
                return $phase->name . ' (' . Carbon::parse($phase->start_date)->format('M d, Y') . ' - ' . Carbon::parse($phase->end_date)->format('M d, Y') . ')';
            })->join(', ');
            
            session()->flash('error', 'The phase dates overlap with existing phase(s): ' . $overlappingDates . '. Please adjust the dates to avoid overlaps.');
            return;
        }
        
        $isFirstPhase = $this->phases->isEmpty();
        
        // Create the phase - we'll reorder all phases after creation
        // Use a high temporary order value so it doesn't interfere
        $maxOrder = CasePhase::where('legal_case_id', $this->case->id)->max('order') ?? -1;
        
        $phase = CasePhase::create([
            'legal_case_id' => $this->case->id,
            'name' => $phaseName,
            'description' => $phaseDescription,
            'start_date' => $this->newPhaseStartDate,
            'end_date' => $this->newPhaseEndDate,
            'is_current' => $isFirstPhase, // First phase is current
            'is_completed' => false,
            'order' => $maxOrder + 100, // Temporary high order value
        ]);

        // Reorder all phases based on start date - this will place the new phase in the correct position
        $this->reorderPhasesByStartDate();

        $this->resetNewPhaseForm();
        $this->loadPhases();
        $this->checkNavigationAvailability();
        $this->checkIfLastPhase();

        // If it was the first phase, set it as current
        if ($isFirstPhase) {
             $this->setCurrentPhase($phase->id);
        }
        
        // Show success modal - it will appear on top of the add-phase-modal
        $this->showSuccessModal = true;
    }
    
    public function closeSuccessModal()
    {
        // Close both modals
        $this->showSuccessModal = false;
        $this->dispatch('close-modal', 'phase-success-modal');
        $this->dispatch('close-modal', 'add-phase-modal');
        session()->flash('success', 'Phase added successfully!');
    }
    
    private function resetNewPhaseForm()
    {
        $this->newPhaseName = '';
        $this->newPhaseDescription = '';
        $this->newPhaseStartDate = '';
        $this->newPhaseEndDate = '';
        $this->newPhaseUpdate = '';
        $this->selectedPhaseTemplate = '';
        $this->customPhaseName = '';
        $this->showSuccessModal = false;
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