<?php

namespace App\Livewire\Lawyer;

use App\Models\LegalCase;
use App\Models\CasePhase;
use App\Models\CaseTask;
use App\Models\CaseEvent;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CasePhaseManager extends Component
{
    public LegalCase $case;
    public $phases = [];
    public $currentPhase = null;
    public $showPhaseModal = false;
    public $showEventModal = false;
    public $showTaskModal = false;
    
    // Phase form
    public $phaseName;
    public $phaseDescription;
    public $phaseStartDate;
    public $phaseEndDate;
    
    // Event form
    public $eventTitle;
    public $eventDescription;
    public $eventStartDateTime;
    public $eventEndDateTime;
    public $eventLocation;
    public $eventType;
    public $isEventShared = true;
    
    // Task form
    public $taskTitle;
    public $taskDescription;
    public $taskDueDate;
    public $taskAssignedTo = 'client';
    public $selectedPhaseId;

    protected $rules = [
        'phaseName' => 'required|string|max:255',
        'phaseDescription' => 'nullable|string',
        'phaseStartDate' => 'required|date',
        'phaseEndDate' => 'required|date|after:phaseStartDate',
        
        'eventTitle' => 'required|string|max:255',
        'eventDescription' => 'nullable|string',
        'eventStartDateTime' => 'required|date|after_or_equal:today',
        'eventEndDateTime' => 'nullable|date|after:eventStartDateTime',
        'eventLocation' => 'nullable|string',
        'eventType' => 'required|string',
        
        'taskTitle' => 'required|string|max:255',
        'taskDescription' => 'nullable|string',
        'taskDueDate' => 'required|date|after_or_equal:today',
        'taskAssignedTo' => 'required|in:client,lawyer',
        'selectedPhaseId' => 'required|exists:case_phases,id'
    ];

    public function mount(LegalCase $case)
    {
        $this->case = $case;
        $this->loadPhases();
    }

    public function loadPhases()
    {
        $this->phases = CasePhase::where('legal_case_id', $this->case->id)
            ->orderBy('order')
            ->with(['tasks', 'events'])
            ->get();
    }

    public function showAddPhaseModal()
    {
        $this->resetPhaseForm();
        $this->showPhaseModal = true;
    }

    public function showAddEventModal()
    {
        $this->resetEventForm();
        $this->showEventModal = true;
    }

    public function showAddTaskModal()
    {
        $this->resetTaskForm();
        $this->showTaskModal = true;
    }

    public function savePhase()
    {
        $this->validate([
            'phaseName' => 'required',
            'phaseStartDate' => 'required|date',
            'phaseEndDate' => 'required|date|after:phaseStartDate'
        ]);

        CasePhase::create([
            'legal_case_id' => $this->case->id,
            'name' => $this->phaseName,
            'description' => $this->phaseDescription,
            'start_date' => $this->phaseStartDate,
            'end_date' => $this->phaseEndDate,
            'order' => $this->phases->count()
        ]);

        $this->loadPhases();
        $this->showPhaseModal = false;
        $this->resetPhaseForm();
        
        session()->flash('message', 'Phase added successfully.');
    }

    public function saveEvent()
    {
        $this->validate([
            'eventTitle' => 'required',
            'eventStartDateTime' => 'required|date|after_or_equal:today',
            'eventType' => 'required'
        ]);

        CaseEvent::create([
            'legal_case_id' => $this->case->id,
            'title' => $this->eventTitle,
            'description' => $this->eventDescription,
            'start_datetime' => $this->eventStartDateTime,
            'end_datetime' => $this->eventEndDateTime,
            'location' => $this->eventLocation,
            'event_type' => $this->eventType,
            'is_shared_with_client' => $this->isEventShared
        ]);

        $this->showEventModal = false;
        $this->resetEventForm();
        
        session()->flash('message', 'Event added successfully.');
    }

    public function saveTask()
    {
        $this->validate([
            'taskTitle' => 'required',
            'taskDueDate' => 'required|date|after_or_equal:today',
            'selectedPhaseId' => 'required'
        ]);

        CaseTask::create([
            'legal_case_id' => $this->case->id,
            'case_phase_id' => $this->selectedPhaseId,
            'title' => $this->taskTitle,
            'description' => $this->taskDescription,
            'due_date' => $this->taskDueDate,
            'assigned_to_type' => $this->taskAssignedTo,
            'assigned_to_id' => $this->taskAssignedTo === 'client' ? $this->case->client_id : $this->case->lawyer_id
        ]);

        $this->loadPhases();
        $this->showTaskModal = false;
        $this->resetTaskForm();
        
        session()->flash('message', 'Task added successfully.');
    }

    private function resetPhaseForm()
    {
        $this->phaseName = '';
        $this->phaseDescription = '';
        $this->phaseStartDate = '';
        $this->phaseEndDate = '';
    }

    private function resetEventForm()
    {
        $this->eventTitle = '';
        $this->eventDescription = '';
        $this->eventStartDateTime = '';
        $this->eventEndDateTime = '';
        $this->eventLocation = '';
        $this->eventType = '';
        $this->isEventShared = true;
    }

    private function resetTaskForm()
    {
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskDueDate = '';
        $this->taskAssignedTo = 'client';
        $this->selectedPhaseId = null;
    }

    public function render()
    {
        return view('livewire.lawyer.case-phase-manager');
    }
} 