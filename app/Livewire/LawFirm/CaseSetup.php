<?php

namespace App\Livewire\LawFirm;

use App\Models\LegalCase;
use App\Models\CasePhase;
use App\Models\CaseEvent;
use App\Models\CaseTask;
use App\Models\CaseDocument;
use App\Models\AppNotification;
use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\On;
use App\Models\User;
use App\Models\LawFirmLawyer;
use App\Models\CaseUpdate;
use App\Notifications\CaseUpdatedNotification;

class CaseSetup extends Component
{
    use WithFileUploads;

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
    
    // Edit Event Properties
    public $editEventId = null;
    public $editEventTitle = '';
    public $editEventDescription = '';
    public $editEventDate = '';
    public $editEventTime = '';
    public $editEventLocation = '';
    public $editEvent_type = '';
    
    // Task management
    public $tasks;
    public $newTaskTitle = '';
    public $newTaskDescription = '';
    public $newTaskDueDate = '';
    public $newTaskAssignedTo = 'client'; // Default to client
    
    // Edit Task Properties
    public $editTaskId = null;
    public $editTaskTitle = '';
    public $editTaskDescription = '';
    public $editTaskDueDate = '';
    public $editTaskAssignedTo = '';
    
    // Document management
    public $documents;
    public $newDocument;
    public $newDocumentTitle = '';
    public $newDocumentDescription = '';
    
    // Close case properties
    public $caseCloseNote = '';
    
    public $isPrimaryLawyer = false; // Flag to track if current user is primary lawyer
    
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
        
        'caseCloseNote' => 'required|string|min:5|max:1000',
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
        $currentFirmUser = Auth::user();
        $firmProfile = $currentFirmUser->lawFirmProfile;

        if (!$firmProfile) {
            abort(403, 'You are not authorized to access this page as you do not have a law firm profile.');
        }

        $isAuthorized = false;

        // Check if the case is assigned to a lawyer who is part of the current law firm
        if ($case->lawyer_id) {
            $isMember = LawFirmLawyer::where('law_firm_profile_id', $firmProfile->id)
                                     ->where('user_id', $case->lawyer_id)
                                     ->where('status', 'approved') // Ensure the lawyer is an active member
                                     ->exists();
            if ($isMember) {
                $isAuthorized = true;
            }
        }

        // Fallback: Check if the case might have a direct (but non-standard) law_firm_owner_id field 
        // or if the current firm user is the direct lawyer_id (less likely for a firm context but covering bases)
        if (!$isAuthorized && property_exists($case, 'law_firm_owner_id') && $case->law_firm_owner_id === $currentFirmUser->id) {
             $isAuthorized = true;
        } else if (!$isAuthorized && $case->lawyer_id === $currentFirmUser->id) {
            // This case handles if a firm user *is* the lawyer_id, which is unusual but possible.
            // More importantly, it acts as a safeguard if the above firm lawyer check fails due to data inconsistency.
            // For a firm context, the LawFirmLawyer check is primary.
            $isAuthorized = true; 
        }
                         
        if (!$isAuthorized) {
            // Log the attempt for auditing if needed
            // Log::warning('Unauthorized case access attempt by Law Firm User ID: ' . $currentFirmUser->id . ' for Case ID: ' . $case->id);
            abort(403, 'You are not authorized to set up this case.');
        }
        
        $this->case = $case;
        $this->isPrimaryLawyer = $this->checkIfPrimaryLawyer(); // Set the property here
        
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
                            
        // Load case data
        $this->loadCaseData();
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

    // Prepare marked dates for the calendar
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
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot add phases to a closed case.');
            return;
        }
        
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
                'order' => $maxOrder + 1,
                'is_current' => $isFirstPhase // If it's the first phase, make it current
            ]);
            
            DB::commit();
            
            // Reset form fields
            $this->newPhaseName = '';
            $this->newPhaseDescription = '';
            $this->newPhaseStartDate = '';
            $this->newPhaseEndDate = '';
            
            // Reload data
            $this->loadCaseData();
            
            session()->flash('success', 'Phase added successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add phase error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to add phase: ' . $e->getMessage());
        }
    }
    
    public function setCurrentPhase($phaseId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot update phases in a closed case.');
            return;
        }
        
        DB::beginTransaction();
        try {
            // First, unset current phase flag for all phases
            CasePhase::where('legal_case_id', $this->case->id)
                ->update(['is_current' => false]);
                
            // Then set the new current phase
            $phase = CasePhase::findOrFail($phaseId);
            $phase->update(['is_current' => true]);
            
            // Update the currentPhaseId
            $this->currentPhaseId = $phaseId;
            
            DB::commit();
            
            // Emit event for phase tracker to update
            $this->dispatch('phaseUpdated');
            
            session()->flash('success', 'Current phase updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to update current phase: ' . $e->getMessage());
        }
    }
    
    public function completePhase($phaseId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            session()->flash('error', 'Cannot update phases in a closed case.');
            return;
        }
        
        try {
            $phase = CasePhase::findOrFail($phaseId);
            $phase->update(['is_completed' => true]);
            
            // Emit event for phase tracker to update
            $this->dispatch('phaseUpdated');
            
            session()->flash('success', 'Phase marked as completed successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to mark phase as completed: ' . $e->getMessage());
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
            'newEventTime' => 'required',
            'newEventLocation' => 'nullable|string|max:255',
            'newEvent_type' => 'required|string|max:50',
        ]);
        
        try {
            // Format the date and time
            $dateTimeString = $this->newEventDate . ' ' . $this->newEventTime;
            $dateTime = Carbon::createFromFormat('Y-m-d H:i', $dateTimeString);
            
            CaseEvent::create([
                'legal_case_id' => $this->case->id,
                'title' => $this->newEventTitle,
                'description' => $this->newEventDescription,
                'event_date' => $this->newEventDate,
                'start_datetime' => $dateTime,
                'location' => $this->newEventLocation,
                'event_type' => $this->newEvent_type,
                'created_by' => Auth::id(),
            ]);
            
            $this->resetEventForm(); // Resets the date/time inputs
            $this->loadCaseData();
            session()->flash('success', 'Event added successfully!');
            
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
                
            CaseTask::create([
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
            
            // Close modal and scroll to top
            $this->dispatch('close-modal', 'add-task-modal');
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add task: ' . $e->getMessage());
            Log::error('Add task error: ' . $e->getMessage());
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
    
    public function uploadDocument()
    {
        $this->validate([
            'newDocumentTitle' => 'required|string|max:100',
            'newDocumentDescription' => 'nullable|string',
            'newDocument' => 'required|file|max:10240', // 10MB max
        ]);
        
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
                'is_shared' => true
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
            $event = CaseEvent::findOrFail($this->editEventId);
            
            // Make sure this event belongs to this case
            if ($event->legal_case_id !== $this->case->id) {
                throw new \Exception('Permission denied to edit this event.');
            }
            
            // Format the date and time
            $dateTimeString = $this->editEventDate . ' ' . $this->editEventTime;
            $dateTime = Carbon::createFromFormat('Y-m-d H:i', $dateTimeString);
            
            $event->update([
                'title' => $this->editEventTitle,
                'description' => $this->editEventDescription,
                'event_date' => $this->editEventDate,
                'start_datetime' => $dateTime,
                'location' => $this->editEventLocation,
                'event_type' => $this->editEvent_type,
                'updated_by' => Auth::id(),
            ]);
            
            $this->resetEditEventForm();
            $this->loadCaseData();
            $this->dispatch('close-modal', 'edit-event-modal');
            session()->flash('success', 'Event updated successfully!');
            
            // Scroll to top
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update event: ' . $e->getMessage());
        }
    }
    
    // Method to reset edit event form
    public function resetEditEventForm()
    {
        $this->editEventId = null;
        $this->editEventTitle = '';
        $this->editEventDescription = '';
        $this->editEventDate = '';
        $this->editEventTime = '';
        $this->editEventLocation = '';
        $this->editEvent_type = '';
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
            $this->editTaskDueDate = $task->due_date ? $task->due_date->format('Y-m-d') : null;
            
            // Determine if assigned to client or lawyer
            $this->editTaskAssignedTo = ($task->assigned_to_id === $this->case->client_id) ? 'client' : 'lawyer';
            
            $this->dispatch('open-modal', 'edit-task-modal');
        } else {
            session()->flash('error', 'Task not found or permission denied.');
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
        ]);
        
        try {
            $task = CaseTask::findOrFail($this->editTaskId);
            
            // Make sure this task belongs to this case
            if ($task->legal_case_id !== $this->case->id) {
                throw new \Exception('Permission denied to edit this task.');
            }
            
            // Determine the assigned ID based on selection
            $assignedId = ($this->editTaskAssignedTo === 'client') 
                ? $this->case->client_id 
                : $this->case->lawyer_id;
            
            $task->update([
                'title' => $this->editTaskTitle,
                'description' => $this->editTaskDescription,
                'due_date' => $this->editTaskDueDate,
                'assigned_to_id' => $assignedId
            ]);
            
            $this->resetEditTaskForm();
            $this->loadCaseData();
            $this->dispatch('close-modal', 'edit-task-modal');
            session()->flash('success', 'Task updated successfully!');
            
            // Scroll to top
            $this->dispatch('scrollToTop');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update task: ' . $e->getMessage());
        }
    }
    
    // Method to reset edit task form
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
                    'Task Status Updated by Law Firm',
                    'Your law firm ' . ($newStatus ? 'completed' : 'uncompleted') . ' a task: ' . $task->title,
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

    // Handle event deletion
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
                $event->delete();
                $this->loadCaseData();
                session()->flash('success', 'Event deleted successfully!');
            } else {
                session()->flash('error', 'Event not found or permission denied.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete event: ' . $e->getMessage());
        }
    }

    // Handle task deletion
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
                $task->delete();
                $this->loadCaseData();
                session()->flash('success', 'Task deleted successfully!');
            } else {
                session()->flash('error', 'Task not found or permission denied.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete task: ' . $e->getMessage());
        }
    }
    
    public function markSetupComplete()
    {
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
                    'content' => 'Your case setup has been completed. You can now track your case progress.',
                    'link' => route('client.cases'),
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
    
    /**
     * Check if the current user is the primary lawyer for this case
     * Law firms can manage cases as primary, but they may also have restrictions
     */
    private function checkIfPrimaryLawyer()
    {
        $currentFirmUser = Auth::user();
        $firmProfile = $currentFirmUser->lawFirmProfile;

        if (!$firmProfile) {
            return false; // Not a firm, or no profile
        }

        // Scenario 1: The case is directly assigned to the Law Firm's user ID.
        if ($this->case->lawyer_id === $currentFirmUser->id) {
            return true;
        }

        // Scenario 2: The case is assigned to a lawyer who is a member of this law firm.
        // In a firm context, if one of their lawyers is on the case, the firm has oversight.
        // The concept of a single "primary" lawyer becomes less rigid; the firm acts as primary.
        if ($this->case->lawyer_id) {
            $isMember = LawFirmLawyer::where('law_firm_profile_id', $firmProfile->id)
                                     ->where('user_id', $this->case->lawyer_id)
                                     ->where('status', 'approved')
                                     ->exists();
            if ($isMember) {
                return true;
            }
        }
        
        // Scenario 3: Check the case_lawyer pivot table for an explicit primary flag 
        // This might be used if multiple individual lawyers (even from different firms) are on a case,
        // and one is designated primary. Or if a specific lawyer within the firm is marked primary over others.
        // For a law firm accessing a case of one of its members, this might be redundant if the above logic (Scenario 2) is sufficient.
        $isExplicitlyPrimary = $this->case->caseLawyers()
            ->where('user_id', $currentFirmUser->id) // Check if the FIRM USER is marked as primary
            ->where('is_primary', true)
            ->exists();

        if ($isExplicitlyPrimary) {
            return true;
        }

        // If the case is assigned to one of the firm's lawyers, and that lawyer themself is marked as primary in the pivot.
        if ($this->case->lawyer_id && $isMember) { // $isMember would be true from Scenario 2 if we reached here
             $assignedLawyerIsPrimaryInPivot = $this->case->caseLawyers()
                ->where('user_id', $this->case->lawyer_id)
                ->where('is_primary', true)
                ->exists();
            if ($assignedLawyerIsPrimaryInPivot) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Close the case and mark it as completed
     */
    public function closeCase()
    {
        // Only primary lawyers or the law firm can close cases
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
            return redirect()->route('law-firm.cases');
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error closing case: ' . $e->getMessage());
            
            session()->flash('error', 'There was an error closing the case. Please try again or contact support.');
        }
    }
    
    public function render()
    {
        return view('livewire.law-firm.case-setup', [
            'client' => $this->case->client,
            'lawyer' => $this->case->lawyer,
            'statuses' => [
                LegalCase::STATUS_PENDING => 'Pending',
                LegalCase::STATUS_ACCEPTED => 'Accepted',
                LegalCase::STATUS_REJECTED => 'Rejected',
                LegalCase::STATUS_CONTRACT_SENT => 'Contract Sent',
                LegalCase::STATUS_CONTRACT_SIGNED => 'Contract Signed',
                LegalCase::STATUS_ACTIVE => 'Active',
                LegalCase::STATUS_CLOSED => 'Closed'
            ],
            'eventTypes' => [
                'meeting' => 'Meeting',
                'court_hearing' => 'Court Hearing',
                'document_filing' => 'Document Filing',
                'client_call' => 'Client Call',
                'deposition' => 'Deposition',
                'other' => 'Other'
            ]
        ]);
    }

    // Court Details Methods

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
} 