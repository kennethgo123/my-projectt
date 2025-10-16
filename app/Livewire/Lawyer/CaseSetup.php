<?php

namespace App\Livewire\Lawyer;

use App\Models\LegalCase;
use App\Models\CasePhase;
use App\Models\CaseEvent;
use App\Models\CaseTask;
use App\Models\CaseDocument;
use App\Models\AppNotification;
use App\Notifications\CaseUpdatedNotification;
use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\User;

class CaseSetup extends Component
{
    use WithFileUploads, WithPagination;

    public LegalCase $case;
    public $activeTab = 'phases';
    public $isReadOnly = false;
    public $casePhaseTrackerId = 'case-phase-tracker';
    
    // Calendar Properties
    public $calendarYear;
    public $calendarMonth;
    public $markedDates = []; // ['YYYY-MM-DD' => 'task'/'event'/'both']
    
    // Court Details
    public $courtLevelMain = '';
    public $courtLevelSpecific = '';
    
    // Phase management
    public $phases;
    public $newPhaseName = '';
    public $newPhaseDescription = '';
    public $newPhaseStartDate = '';
    public $newPhaseEndDate = '';
    public $currentPhaseId = null;
    
    // Event management
    public $events;
    public $newEventTitle = '';
    public $newEventDescription = '';
    public $newEventDate = '';
    public $newEventTime = '';
    public $newEventLocation = '';
    public $newEvent_type = 'meeting'; // Added event type, default to meeting
    // New properties for editing events
    public $editEventId = null;
    public $editEventTitle = '';
    public $editEventDescription = '';
    public $editEventDate = '';
    public $editEventTime = '';
    public $editEventLocation = '';
    public $editEvent_type = ''; // Added event type for edit
    
    // Task management
    public $tasks;
    public $newTaskTitle = '';
    public $newTaskDescription = '';
    public $newTaskDueDate = '';
    public $newTaskAssignedTo = 'client'; // Default to client
    // New properties for editing tasks
    public $editTaskId = null;
    public $editTaskTitle = '';
    public $editTaskDescription = '';
    public $editTaskDueDate = '';
    public $editTaskAssignedTo = ''; // Keep track of original assignment
    public $editTaskStatus = 'pending';

    // Document management
    public $documents;
    public $newDocument;
    public $newDocumentTitle = '';
    public $newDocumentDescription = '';
    
    // Case closing
    public $caseCloseNote = '';
    
    // Pro Bono functionality
    public $showProBonoConfirmation = false;
    public $proBonoNote = '';
    
    public $isPrimaryLawyer = false; // Flag to track if current user is primary lawyer
    
    // Case editing properties
    public $editingCase = false;
    public $editCaseNumber = '';
    public $editCaseTitle = '';
    
    protected $rules = [
        'newPhaseName' => 'required|string|max:100',
        'newPhaseDescription' => 'required|string',
        'newPhaseStartDate' => 'required|date',
        'newPhaseEndDate' => 'required|date|after_or_equal:newPhaseStartDate',
        
        // Court Details validation
        'courtLevelMain' => 'nullable|string',
        'courtLevelSpecific' => 'nullable|string',
        
        'newEventTitle' => 'required|string|max:100',
        'newEventDescription' => 'required|string',
        'newEventDate' => 'required|date|after_or_equal:today',
        'newEventTime' => 'required',
        'newEventLocation' => 'nullable|string|max:255',
        'newEvent_type' => 'required|string|max:50',
        
        'newTaskTitle' => 'required|string|max:100',
        'newTaskDescription' => 'required|string',
        'newTaskDueDate' => 'required|date|after_or_equal:today',
        'newTaskAssignedTo' => 'required|in:client,lawyer',
        
        'newDocumentTitle' => 'required|string|max:100',
        'newDocumentDescription' => 'nullable|string',
        'newDocument' => 'required|file|max:10240', // 10MB max

        // Updated validation rules for editing
        'editEventTitle' => 'required|string|max:100',
        'editEventDescription' => 'required|string',
        'editEventDate' => 'required|date|after_or_equal:today',
        'editEventTime' => 'required',
        'editEventLocation' => 'nullable|string|max:255',
        'editEvent_type' => 'required|string|max:50',

        'editTaskTitle' => 'required|string|max:100',
        'editTaskDescription' => 'required|string',
        'editTaskDueDate' => 'required|date|after_or_equal:today',
        'editTaskAssignedTo' => 'required|in:client,lawyer',
        'editTaskStatus' => 'required|in:pending,in_progress,completed,blocked',
        
        // Case closing
        'caseCloseNote' => 'required|string|min:10',
        
        // Pro Bono
        'proBonoNote' => 'required|string|min:10|max:500',
        
        // Case editing
        'editCaseNumber' => 'nullable|string|max:50',
        'editCaseTitle' => 'required|string|max:255',
    ];

    protected function messages()
    {
        return [
            'newPhaseEndDate.after_or_equal' => 'The end date must be after or equal to the start date.',
            'newDocument.max' => 'The document must not be larger than 10MB.',
        ];
    }

    public function mount(LegalCase $case)
    {
        $userId = Auth::id();
        // Check if user is either the primary lawyer or a team member
        $isAuthorized = $case->lawyer_id === $userId || 
                         $case->teamLawyers()->where('user_id', $userId)->exists();
                         
        if (!$isAuthorized) {
            abort(403, 'You are not authorized to set up this case.');
        }
        
        $this->case = $case->load('client.clientProfile');
        
        // Initialize collections
        $this->phases = collect();
        $this->events = collect();
        $this->tasks = collect();
        $this->documents = collect();
        
        // Initialize Calendar to current month/year
        $this->calendarYear = now()->year;
        $this->calendarMonth = now()->month;
        $this->markedDates = [];
        
        // Load court details
        $this->loadCourtDetails();
        
        // Check if case is in read-only mode (closed or completed)
        $this->isReadOnly = $case->status === LegalCase::STATUS_CLOSED || 
                            $case->status === LegalCase::STATUS_COMPLETED ||
                            $case->closed_at !== null;
                            
        // Check if the current user is the primary lawyer for this case
        $this->isPrimaryLawyer = $this->checkIfPrimaryLawyer();
        
        $this->loadCaseData();
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
    
    // Check if the current user is authorized for this case
    private function isAuthorized()
    {
        $userId = Auth::id();
        return $this->case->lawyer_id === $userId || 
               $this->case->teamLawyers()->where('user_id', $userId)->exists();
    }
    
    private function loadCaseData()
    {
        // Load phases
        $this->phases = CasePhase::where('legal_case_id', $this->case->id)
            ->orderBy('order')
            ->get();
            
        // Set current phase only if there are phases
        if ($this->phases->count() > 0) {
            $currentPhase = CasePhase::where('legal_case_id', $this->case->id)
                ->where('is_current', true)
                ->first();
                
            $this->currentPhaseId = $currentPhase ? $currentPhase->id : null;
        } else {
            $this->currentPhaseId = null;
        }
        
        // Load events
        try {
            $this->events = CaseEvent::where('legal_case_id', $this->case->id)
                ->orderBy('event_date')
                ->get();
        } catch (\Exception $e) {
            $this->events = collect();
        }
            
        // Load tasks
        try {
            $this->tasks = CaseTask::where('legal_case_id', $this->case->id)
                ->orderBy('due_date')
                ->orderBy('created_at')
                ->get();
        } catch (\Exception $e) {
            $this->tasks = collect();
        }
            
        // Load documents
        try {
            $this->documents = CaseDocument::where('legal_case_id', $this->case->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            $this->documents = collect();
        }

        // Prepare calendar data after tasks/events are loaded
        $this->prepareCalendarData();
    }

    // New method to prepare marked dates for the calendar
    private function prepareCalendarData()
    {
        $this->markedDates = [];
        $startDate = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Mark Tasks
        if ($this->tasks) {
            foreach ($this->tasks as $task) {
                if ($task->due_date && $task->due_date->between($startDate, $endDate)) {
                    $dateStr = $task->due_date->format('Y-m-d');
                    $this->markedDates[$dateStr] = isset($this->markedDates[$dateStr]) ? 'both' : 'task';
                }
            }
        }

        // Mark Events
        if ($this->events) {
            foreach ($this->events as $event) {
                if ($event->start_datetime && $event->start_datetime->between($startDate, $endDate)) {
                    $dateStr = $event->start_datetime->format('Y-m-d');
                    $this->markedDates[$dateStr] = isset($this->markedDates[$dateStr]) ? 'both' : 'event';
                }
            }
        }
    }
    
    public function addPhase()
    {
        $this->validate([
            'newPhaseName' => 'required|string|max:100',
            'newPhaseDescription' => 'required|string',
            'newPhaseStartDate' => 'required|date',
            'newPhaseEndDate' => 'required|date|after_or_equal:newPhaseStartDate',
        ]);
        
        // Check if this is the first phase
        $isFirstPhase = $this->phases->count() === 0;
        
        DB::beginTransaction();
        try {
            // Get the max order value if any phases exist
            $maxOrder = 0;
            if (!$isFirstPhase) {
                $maxOrder = CasePhase::where('legal_case_id', $this->case->id)->max('order') ?? 0;
            }
            
            $phase = CasePhase::create([
                'legal_case_id' => $this->case->id,
                'name' => $this->newPhaseName,
                'description' => $this->newPhaseDescription,
                'start_date' => $this->newPhaseStartDate,
                'end_date' => $this->newPhaseEndDate,
                'is_current' => $isFirstPhase, // First phase is current by default
                'is_completed' => false,
                'order' => $maxOrder + 1, // Ensure phases are ordered correctly
            ]);
            
            DB::commit();
            
            // Reset form
            $this->newPhaseName = '';
            $this->newPhaseDescription = '';
            $this->newPhaseStartDate = '';
            $this->newPhaseEndDate = '';
            
            // Reload data
            $this->loadCaseData();
            
            session()->flash('success', 'Phase added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to add phase: ' . $e->getMessage());
        }
    }
    
    public function setCurrentPhase($phaseId)
    {
        DB::beginTransaction();
        try {
            // Reset all phases
            CasePhase::where('legal_case_id', $this->case->id)
                ->update(['is_current' => false]);
                
            // Set the selected phase as current
            CasePhase::where('id', $phaseId)
                ->where('legal_case_id', $this->case->id)
                ->update(['is_current' => true]);
                
            $this->currentPhaseId = $phaseId;
            
            DB::commit();
            
            // Emit an event to trigger UI updates
            $this->dispatch('phaseUpdated');
            
            session()->flash('success', 'Current phase updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to update current phase: ' . $e->getMessage());
        }
    }
    
    public function completePhase($phaseId)
    {
        DB::beginTransaction();
        try {
            CasePhase::where('id', $phaseId)
                ->where('legal_case_id', $this->case->id)
                ->update(['is_completed' => true]);
                
            DB::commit();
            
            // Reload data
            $this->loadCaseData();
            
            session()->flash('success', 'Phase marked as completed!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to complete phase: ' . $e->getMessage());
        }
    }
    
    public function addEvent()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot add events to a closed case.');
            return;
        }

        $this->validate([
            'newEventTitle' => 'required|string|max:100',
            'newEventDescription' => 'required|string',
            'newEventDate' => 'required|date|after_or_equal:today',
            'newEventTime' => 'required', // Format H:i expected from input type=time
            'newEventLocation' => 'nullable|string|max:255',
            'newEvent_type' => 'required|string|max:50',
        ]);
        
        try {
            // Combine date and time
            $startDateTime = Carbon::parse($this->newEventDate . ' ' . $this->newEventTime)->toDateTimeString();

            $event = CaseEvent::create([
                'legal_case_id' => $this->case->id,
                'title' => $this->newEventTitle,
                'description' => $this->newEventDescription,
                'start_datetime' => $startDateTime, // Added
                'location' => $this->newEventLocation,
                'created_by' => Auth::id(),
                'event_type' => $this->newEvent_type, // Added
            ]);
            
            $this->resetEventForm(); // Resets the date/time inputs
            $this->loadCaseData();
            session()->flash('success', 'Event added successfully!');

            // Notify client about the new event
            if ($this->case->client_id) {
                NotificationService::createSystemNotification(
                    'case_event_created',
                    $this->case->client_id,
                    'New Event Added by Lawyer',
                    'Your lawyer added a new event: ' . $this->newEventTitle,
                    [
                        'case_id' => $this->case->id,
                        'event_id' => $event->id,
                        'link' => route('client.case.overview', $this->case->id)
                    ]
                );
            }
            
            // Close modal and scroll to top
            $this->dispatch('close-modal', 'add-event-modal');
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add event: ' . $e->getMessage());
            Log::error('Add event error: ' . $e->getMessage()); // Add logging
        }
    }
    
    public function addTask()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot add tasks to a closed case.');
            return;
        }

        $this->validate([
            'newTaskTitle' => 'required|string|max:100',
            'newTaskDescription' => 'required|string',
            'newTaskDueDate' => 'required|date|after_or_equal:today',
            'newTaskAssignedTo' => 'required|in:client,lawyer',
        ]);
        
        try {
            $assignedId = ($this->newTaskAssignedTo === 'client') 
                ? $this->case->client_id 
                : $this->case->lawyer_id;
                
            $task = CaseTask::create([
                'legal_case_id' => $this->case->id,
                'title' => $this->newTaskTitle,
                'description' => $this->newTaskDescription,
                'due_date' => $this->newTaskDueDate,
                'status' => 'pending',
                'assigned_to_type' => 'App\Models\User',
                'assigned_to_id' => $assignedId,
                'assigned_by' => Auth::id(),
            ]);
            
            $this->resetTaskForm();
            $this->loadCaseData();
            session()->flash('success', 'Task added successfully!');
            
            // Notify client if the task is assigned to them or if they should be aware of it
            if ($this->case->client_id) {
                $notificationMessage = ($this->newTaskAssignedTo === 'client')
                    ? 'Your lawyer assigned you a new task: ' . $this->newTaskTitle
                    : 'Your lawyer added a new task to your case: ' . $this->newTaskTitle;
                    
                NotificationService::createSystemNotification(
                    'case_task_created',
                    $this->case->client_id,
                    'New Task ' . ($this->newTaskAssignedTo === 'client' ? 'Assigned to You' : 'Added'),
                    $notificationMessage,
                    [
                        'case_id' => $this->case->id,
                        'task_id' => $task->id,
                        'link' => route('client.case.overview', $this->case->id)
                    ]
                );
            }
            
            // Close modal and scroll to top
            $this->dispatch('close-modal', 'add-task-modal');
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add task: ' . $e->getMessage());
            Log::error('Add task error: ' . $e->getMessage());
        }
    }
    
    public function uploadDocument()
    {
        $this->validate([
            'newDocumentTitle' => 'required|string|max:100',
            'newDocumentDescription' => 'nullable|string',
            'newDocument' => 'required|file|max:10240', // 10MB max
        ]);
        
        // Check authorization
        if (!$this->isAuthorized()) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }
        
        try {
            // Upload file
            $path = $this->newDocument->store('case-documents/' . $this->case->id, 'public');
            
            // Create document record
            $document = CaseDocument::create([
                'legal_case_id' => $this->case->id,
                'title' => $this->newDocumentTitle,
                'description' => $this->newDocumentDescription,
                'file_path' => $path,
                'file_name' => $this->newDocument->getClientOriginalName(),
                'file_size' => $this->newDocument->getSize(),
                'file_type' => $this->newDocument->getMimeType(),
                'uploaded_by_id' => Auth::id(),
                'uploaded_by_type' => User::class,
            ]);
            
            // Reset form
            $this->newDocumentTitle = '';
            $this->newDocumentDescription = '';
            $this->newDocument = null;
            
            // Reload data
            $this->loadCaseData();
            
            session()->flash('success', 'Document uploaded successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }
    
    public function markSetupComplete()
    {
        if ($this->case->lawyer_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to update this case.');
            return;
        }
        
        try {
            // Update the case status to completed setup
            $this->case->update([
                'status' => 'active',
                'setup_completed' => true,
            ]);
            
            // Notify the client
            try {
                // Create a notification using AppNotification model
                AppNotification::create([
                    'user_id' => $this->case->client_id,
                    'type' => 'case_setup_completed',
                    'title' => 'Case Setup Completed',
                    'content' => 'Your lawyer has completed the setup of your case. You can now track your case progress.',
                    'link' => route('client.cases.show', $this->case->id),
                    'is_read' => false
                ]);
                
                // Optionally, trigger real-time notification if that system is in place
                try {
                    event(new \App\Events\NotificationReceived($this->case->client_id));
                } catch (\Exception $e) {
                    Log::warning('Failed to dispatch notification event: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                // Just log the error but don't fail the update
                Log::warning('Failed to create notification: ' . $e->getMessage());
            }
            
            session()->flash('success', 'Case setup has been marked as complete and is now visible to the client.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to mark case setup as complete: ' . $e->getMessage());
        }
    }
    
    // Method to reset task form fields
    public function resetTaskForm()
    {
        $this->newTaskTitle = '';
        $this->newTaskDescription = '';
        $this->newTaskDueDate = '';
        $this->newTaskAssignedTo = 'client'; // Reset to default
        $this->resetErrorBag(); // Clear validation errors
    }

    // Method to reset event form fields
    public function resetEventForm()
    {
        $this->newEventTitle = '';
        $this->newEventDescription = '';
        $this->newEventDate = '';
        $this->newEventTime = '';
        $this->newEventLocation = '';
        $this->newEvent_type = 'meeting'; // Reset type
        $this->resetErrorBag(); // Clear validation errors
    }

    // Method to prepare event editing
    public function prepareEditEvent($eventId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot edit events in a closed case.');
            return;
        }

        $event = CaseEvent::where('id', $eventId)->where('legal_case_id', $this->case->id)->first();
        if ($event && $event->start_datetime) { // Check if start_datetime exists
            $this->editEventId = $event->id;
            $this->editEventTitle = $event->title;
            $this->editEventDescription = $event->description;
            // Split start_datetime back into date and time for the form
            $this->editEventDate = $event->start_datetime->format('Y-m-d');
            $this->editEventTime = $event->start_datetime->format('H:i');
            $this->editEventLocation = $event->location;
            $this->editEvent_type = $event->event_type; // Load event type
            $this->dispatch('open-modal', 'edit-event-modal');
        } else {
            session()->flash('error', 'Event not found, has no start time, or permission denied.');
        }
    }

    // Method to update event
    public function updateEvent()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot update events in a closed case.');
            return;
        }

        $this->validate([
            'editEventTitle' => 'required|string|max:100',
            'editEventDescription' => 'required|string',
            'editEventDate' => 'required|date|after_or_equal:today',
            'editEventTime' => 'required',
            'editEventLocation' => 'nullable|string|max:255',
            'editEvent_type' => 'required|string|max:50',
        ]);
        
        try {
            if ($this->editEventId) {
                $event = CaseEvent::where('id', $this->editEventId)
                    ->where('legal_case_id', $this->case->id)
                    ->firstOrFail();
                
                // Combine date and time for start_datetime
                $startDateTime = Carbon::parse($this->editEventDate . ' ' . $this->editEventTime)->toDateTimeString();
                
                $event->update([
                    'title' => $this->editEventTitle,
                    'description' => $this->editEventDescription,
                    'start_datetime' => $startDateTime,
                    'location' => $this->editEventLocation,
                    'event_type' => $this->editEvent_type,
                ]);
                
                $this->resetEditEventForm();
                $this->loadCaseData();
                session()->flash('success', 'Event updated successfully!');
                
                // Notify client about the updated event
                if ($this->case->client_id) {
                    NotificationService::createSystemNotification(
                        'case_event_updated',
                        $this->case->client_id,
                        'Event Updated by Lawyer',
                        'Your lawyer updated an event: ' . $event->title,
                        [
                            'case_id' => $this->case->id,
                            'event_id' => $event->id,
                            'link' => route('client.case.overview', $this->case->id)
                        ]
                    );
                }
                
                // Close modal and scroll to top
                $this->dispatch('close-modal', 'edit-event-modal');
                $this->dispatch('scrollToTop');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update event: ' . $e->getMessage());
            Log::error('Update event error: ' . $e->getMessage());
        }
    }

    // Method to reset event edit form
    public function resetEditEventForm()
    {
        $this->editEventId = null;
        $this->editEventTitle = '';
        $this->editEventDescription = '';
        $this->editEventDate = '';
        $this->editEventTime = '';
        $this->editEventLocation = '';
        $this->editEvent_type = ''; // Reset type
        $this->resetErrorBag();
    }

    // Method to prepare task editing
    public function prepareEditTask($taskId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot edit tasks in a closed case.');
            return;
        }

        $task = CaseTask::where('id', $taskId)->where('legal_case_id', $this->case->id)->first();
        if ($task) {
            $this->editTaskId = $task->id;
            $this->editTaskTitle = $task->title;
            $this->editTaskDescription = $task->description;
            $this->editTaskDueDate = $task->due_date->format('Y-m-d');
            // Determine assignment type based on assigned_to ID
            $this->editTaskAssignedTo = ($task->assigned_to_id == $this->case->client_id) ? 'client' : 'lawyer';

            $this->dispatch('open-modal', 'edit-task-modal');
        } else {
            session()->flash('error', 'Task not found or you do not have permission to edit it.');
        }
    }

    // Method to update task
    public function updateTask()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot update tasks in a closed case.');
            return;
        }

        $this->validate([
            'editTaskTitle' => 'required|string|max:100',
            'editTaskDescription' => 'required|string',
            'editTaskDueDate' => 'required|date|after_or_equal:today',
            'editTaskAssignedTo' => 'required|in:client,lawyer',
            'editTaskStatus' => 'required|in:pending,in_progress,completed,blocked',
        ]);
        
        try {
            if ($this->editTaskId) {
                $task = CaseTask::where('id', $this->editTaskId)
                    ->where('legal_case_id', $this->case->id)
                    ->firstOrFail();
                
                $assignedId = ($this->editTaskAssignedTo === 'client') 
                    ? $this->case->client_id 
                    : $this->case->lawyer_id;
                
                $task->update([
                    'title' => $this->editTaskTitle,
                    'description' => $this->editTaskDescription,
                    'due_date' => $this->editTaskDueDate,
                    'status' => $this->editTaskStatus,
                    'assigned_to_type' => 'App\Models\User',
                    'assigned_to_id' => $assignedId,
                ]);
                
                $this->resetEditTaskForm();
                $this->loadCaseData();
                session()->flash('success', 'Task updated successfully!');
                
                // Notify client if the task was assigned to them or updated
                if ($this->case->client_id) {
                    $isClientTask = $assignedId == $this->case->client_id;
                    $notificationMessage = $isClientTask
                        ? 'Your lawyer updated a task assigned to you: ' . $task->title
                        : 'Your lawyer updated a task on your case: ' . $task->title;
                        
                    NotificationService::createSystemNotification(
                        'case_task_updated',
                        $this->case->client_id,
                        'Task Updated by Lawyer',
                        $notificationMessage,
                        [
                            'case_id' => $this->case->id,
                            'task_id' => $task->id,
                            'link' => route('client.case.overview', $this->case->id)
                        ]
                    );
                }
                
                // Close modal and scroll to top
                $this->dispatch('close-modal', 'edit-task-modal');
                $this->dispatch('scrollToTop');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update task: ' . $e->getMessage());
            Log::error('Update task error: ' . $e->getMessage());
        }
    }

    // Method to reset task edit form
    public function resetEditTaskForm()
    {
        $this->editTaskId = null;
        $this->editTaskTitle = '';
        $this->editTaskDescription = '';
        $this->editTaskDueDate = '';
        $this->editTaskAssignedTo = '';
        $this->resetErrorBag();
    }
    
    /**
     * Toggle the completion status of a task
     */
    public function toggleTaskCompletion($taskId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot update tasks in a closed case.');
            return;
        }

        $task = CaseTask::where('id', $taskId)
                      ->where('legal_case_id', $this->case->id)
                      ->first();

        if ($task) {
            $newStatus = !$task->is_completed;
            $task->update([
                'is_completed' => $newStatus,
                'completed_at' => $newStatus ? now() : null,
            ]);
            
            $this->loadCaseData();
            session()->flash('success', $newStatus ? 'Task marked as completed!' : 'Task marked as pending.');

            // Notify client
            if ($this->case->client_id) {
                NotificationService::createSystemNotification(
                    'case_task_updated',
                    $this->case->client_id,
                    'Task Status Updated by Lawyer',
                    'Your lawyer ' . ($newStatus ? 'completed' : 'uncompleted') . ' a task: ' . $task->title,
                    [
                        'case_id' => $this->case->id,
                        'task_id' => $task->id,
                        'link' => route('client.case.overview', $this->case->id)
                    ]
                );
            }
        } else {
            session()->flash('error', 'Task not found or you are not authorized to update it.');
        }
    }
    
    // Method to delete an event
    #[On('delete-event')]
    public function deleteEvent($eventId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot delete events in a closed case.');
            return;
        }

        try {
            $event = CaseEvent::where('id', $eventId)
                ->where('legal_case_id', $this->case->id)
                ->first();
            
            if ($event) {
                $eventTitle = $event->title;
                $event->delete();
                
                $this->loadCaseData();
                session()->flash('success', 'Event deleted successfully!');
                
                // Notify client about the deleted event
                if ($this->case->client_id) {
                    NotificationService::createSystemNotification(
                        'case_event_deleted',
                        $this->case->client_id,
                        'Event Deleted by Lawyer',
                        'Your lawyer deleted an event: ' . $eventTitle,
                        [
                            'case_id' => $this->case->id,
                            'link' => route('client.case.overview', $this->case->id)
                        ]
                    );
                }
            } else {
                session()->flash('error', 'Event not found or you are not authorized to delete it.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete event: ' . $e->getMessage());
            Log::error('Delete event error: ' . $e->getMessage());
        }
    }

    // Method to delete a task
    #[On('delete-task')]
    public function deleteTask($taskId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot delete tasks in a closed case.');
            return;
        }

        try {
            $task = CaseTask::where('id', $taskId)
                ->where('legal_case_id', $this->case->id)
                ->first();
            
            if ($task) {
                $taskTitle = $task->title;
                $wasClientTask = $task->assigned_to_id == $this->case->client_id;
                $task->delete();
                
                $this->loadCaseData();
                session()->flash('success', 'Task deleted successfully!');
                
                // Notify client about the deleted task
                if ($this->case->client_id) {
                    $notificationMessage = $wasClientTask
                        ? 'Your lawyer deleted a task that was assigned to you: ' . $taskTitle
                        : 'Your lawyer deleted a task from your case: ' . $taskTitle;
                        
                    NotificationService::createSystemNotification(
                        'case_task_deleted',
                        $this->case->client_id,
                        'Task Deleted by Lawyer',
                        $notificationMessage,
                        [
                            'case_id' => $this->case->id,
                            'link' => route('client.case.overview', $this->case->id)
                        ]
                    );
                }
            } else {
                session()->flash('error', 'Task not found or you are not authorized to delete it.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete task: ' . $e->getMessage());
            Log::error('Delete task error: ' . $e->getMessage());
        }
    }

    // Method to close and archive a case
    public function closeCase()
    {
        // Only primary lawyers can close cases
        if (!$this->isPrimaryLawyer) {
            session()->flash('error', 'Only the primary lawyer can close this case.');
            return;
        }
        
        $this->validate([
            'caseCloseNote' => 'required|string|min:5|max:1000',
        ]);

        try {
            // Get fresh instance of the case to avoid stale data
            $case = LegalCase::findOrFail($this->case->id);
            
            // Update case status and add closing note
            $case->update([
                'status' => LegalCase::STATUS_COMPLETED,
                'closed_at' => now(),
                'closing_note' => $this->caseCloseNote,
            ]);
            
            // Create an update in the case activity log
            $case->caseUpdates()->create([
                'title' => 'Case Closed',
                'content' => $this->caseCloseNote,
                'visibility' => 'both', // Visible to both lawyer and client
                'user_id' => auth()->id(),
            ]);
            
            // Send notification to client using NotificationService
            try {
                \App\Services\NotificationService::caseClosed($case);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error sending case closed notification: ' . $e->getMessage());
            }
            
            session()->flash('success', 'Case has been successfully closed and archived.');
            
            // Reset the close case note
            $this->caseCloseNote = '';
            
            // Redirect to case listing
            return redirect()->route('lawyer.cases');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error closing case: ' . $e->getMessage());
            
            session()->flash('error', 'There was an error closing the case. Please try again or contact support.');
        }
    }

    /**
     * Show pro bono confirmation modal
     */
    public function openProBonoModal()
    {
        // Only primary lawyers can set cases as pro bono
        if (!$this->isPrimaryLawyer) {
            session()->flash('error', 'Only the primary lawyer can set this case as pro bono.');
            return;
        }
        
        // Check if case is already pro bono
        if ($this->case->is_pro_bono) {
            session()->flash('error', 'This case is already marked as pro bono.');
            return;
        }
        
        // Check if case is closed
        if (in_array($this->case->status, ['completed', 'closed']) || $this->case->closed_at !== null) {
            session()->flash('error', 'Cannot modify pro bono status of a closed case.');
            return;
        }
        
        $this->showProBonoConfirmation = true;
        $this->proBonoNote = '';
    }

    /**
     * Cancel pro bono confirmation
     */
    public function cancelProBono()
    {
        $this->showProBonoConfirmation = false;
        $this->proBonoNote = '';
        $this->resetErrorBag();
    }

    /**
     * Set case as pro bono
     */
    public function setProBono()
    {
        $this->validate([
            'proBonoNote' => 'required|string|min:10|max:500',
        ]);

        try {
            DB::beginTransaction();
            
            // Update the case
            $this->case->update([
                'is_pro_bono' => true,
                'pro_bono_set_at' => now(),
                'pro_bono_note' => $this->proBonoNote,
            ]);
            
            // Create a case update for the pro bono status change
            $this->createCaseUpdate(
                'Case Set as Pro Bono', 
                'This case has been marked as pro bono: ' . $this->proBonoNote
            );
            
            DB::commit();
            
            // Reset form and close modal
            $this->showProBonoConfirmation = false;
            $this->proBonoNote = '';
            
            session()->flash('success', 'Case has been successfully marked as pro bono.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting case as pro bono: ' . $e->getMessage());
            session()->flash('error', 'Failed to set case as pro bono. Please try again.');
        }
    }

    /**
     * Load court details from the case
     */
    private function loadCourtDetails()
    {
        $this->courtLevelMain = $this->case->court_level_main ?? '';
        $this->courtLevelSpecific = $this->case->court_level_specific ?? '';
    }


    /**
     * Update court details
     */
    public function updateCourtDetails()
    {
        $this->validate([
            'courtLevelMain' => 'nullable|string',
            'courtLevelSpecific' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            
            $this->case->court_level_main = $this->courtLevelMain;
            $this->case->court_level_specific = $this->courtLevelSpecific;
            $this->case->save();
            
            // Create a case update for the court details change
            $this->createCaseUpdate('Court details updated', 'Court level was updated.');
            
            DB::commit();
            
            $this->dispatch('close-modal', 'edit-court-details-modal');
            session()->flash('success', 'Court details updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating court details: ' . $e->getMessage());
            session()->flash('error', 'Failed to update court details. Please try again.');
        }
    }
    
    /**
     * Create a case update to track changes
     */
    private function createCaseUpdate($title, $description)
    {
        try {
            $update = new \App\Models\CaseUpdate([
                'legal_case_id' => $this->case->id,
                'user_id' => Auth::id(),
                'title' => $title,
                'content' => $description,
            ]);
            $update->save();
            
            // Notify the client about the update
            if ($this->case->client) {
                $this->case->client->notify(new CaseUpdatedNotification($this->case, $title, $description));
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create case update: ' . $e->getMessage());
            return false;
        }
    }

    public function startEditingCase()
    {
        // Check if case is closed
        if ($this->isReadOnly) {
            session()->flash('error', 'Cannot edit a closed case.');
            return;
        }
        
        $this->editingCase = true;
        $this->editCaseNumber = $this->case->case_number ?? '';
        $this->editCaseTitle = $this->case->title ?? '';
    }
    
    public function cancelEditingCase()
    {
        $this->editingCase = false;
        $this->editCaseNumber = '';
        $this->editCaseTitle = '';
        $this->resetErrorBag(['editCaseNumber', 'editCaseTitle']);
    }
    
    public function updateCaseDetails()
    {
        // Check if case is closed
        if ($this->isReadOnly) {
            session()->flash('error', 'Cannot edit a closed case.');
            return;
        }
        
        $this->validate([
            'editCaseNumber' => 'nullable|string|max:50',
            'editCaseTitle' => 'required|string|max:255',
        ]);
        
        try {
            DB::beginTransaction();
            
            $this->case->update([
                'case_number' => $this->editCaseNumber ?: null,
                'title' => $this->editCaseTitle,
            ]);
            
            DB::commit();
            
            $this->editingCase = false;
            $this->editCaseNumber = '';
            $this->editCaseTitle = '';
            
            session()->flash('success', 'Case details updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating case details: ' . $e->getMessage());
            session()->flash('error', 'Failed to update case details. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.lawyer.case-setup', [
            'phases' => $this->phases,
            'events' => $this->events,
            'tasks' => $this->tasks,
            'documents' => $this->documents,
            // Pass calendar data to view
            'calendarYear' => $this->calendarYear,
            'calendarMonth' => $this->calendarMonth,
            'markedDates' => $this->markedDates, 
        ]);
    }
} 