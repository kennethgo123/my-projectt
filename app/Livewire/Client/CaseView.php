<?php

namespace App\Livewire\Client; // Changed namespace to Client

use App\Models\LegalCase;
use App\Models\CasePhase;
use App\Models\CaseEvent;
use App\Models\CaseTask;
use App\Models\CaseDocument;
use App\Models\AppNotification;
use App\Models\Invoice;
use App\Notifications\CaseUpdatedNotification;
use App\Services\NotificationService;
use App\Services\PayMongoService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\On;
use App\Models\User;

class CaseView extends Component // Changed class name
{
    use WithFileUploads;

    public LegalCase $case;
    public $activeTab = 'phases'; // Default to overview for client? or keep as phases?
    
    // Calendar Properties
    public $calendarYear;
    public $calendarMonth;
    public $markedDates = []; // ['YYYY-MM-DD' => 'task'/'event'/'both']
    
    // Phase management (Client view will be read-only for phases)
    public $phases;
    // Remove phase creation properties
    // public $newPhaseName = '';
    // public $newPhaseDescription = '';
    // public $newPhaseStartDate = '';
    // public $newPhaseEndDate = '';
    public $currentPhaseId = null;
    
    // Event management (Client view will be read-only for events)
    public $events;
    // Event management methods for clients
    public $newEventTitle = '';
    public $newEventDescription = '';
    public $newEventDate = '';
    public $newEventTime = '';
    public $newEventLocation = '';
    public $newEvent_type = 'meeting';
    
    // Edit Event properties
    public $editEventId = null;
    public $editEventTitle = '';
    public $editEventDescription = '';
    public $editEventDate = '';
    public $editEventTime = '';
    public $editEventLocation = '';
    public $editEvent_type = '';
    
    // Task management (Clients can add/edit tasks assigned to them or unassigned)
    public $tasks;
    public $newTaskTitle = '';
    public $newTaskDescription = '';
    public $newTaskDueDate = '';
    // public $newTaskAssignedTo = 'client'; // Client can only add tasks for themselves (or unassigned if logic allows)
    
    // Edit Task properties - ensure client can only edit their tasks
    public $editTaskId = null;
    public $editTaskTitle = '';
    public $editTaskDescription = '';
    public $editTaskDueDate = '';
    
    // Court details (read-only for client)
    public $courtLevelMain = '';
    public $courtLevelSpecific = '';
    // public $editTaskAssignedTo = ''; 

    // Document management (Client view will be read-only for documents for now, or restricted upload)
    public $documents;
    // public $newDocument;
    // public $newDocumentTitle = '';
    // public $newDocumentDescription = '';
    
    // Invoice management
    public $invoices;
    public $showViewInvoiceModal = false;
    public $selectedInvoice = null;
    
    // Simplified rules for client (mainly for adding tasks)
    protected $rules = [
        'newTaskTitle' => 'required|string|max:100',
        'newTaskDescription' => 'required|string',
        'newTaskDueDate' => 'required|date|after_or_equal:today',
        // 'newTaskAssignedTo' => 'required|in:client', // Client tasks are implicitly for them

        'editTaskTitle' => 'required|string|max:100',
        'editTaskDescription' => 'required|string',
        'editTaskDueDate' => 'required|date|after_or_equal:today',
        // 'editTaskAssignedTo' => 'required|in:client',
        
        // Add validation rules for events
        'newEventTitle' => 'required|string|max:100',
        'newEventDescription' => 'required|string',
        'newEventDate' => 'required|date|after_or_equal:today',
        'newEventTime' => 'required',
        'newEventLocation' => 'nullable|string|max:100',
        'newEvent_type' => 'required|string|in:meeting,hearing,deadline,other',
        
        'editEventTitle' => 'required|string|max:100',
        'editEventDescription' => 'required|string',
        'editEventDate' => 'required|date|after_or_equal:today',
        'editEventTime' => 'required',
        'editEventLocation' => 'nullable|string|max:100',
        'editEvent_type' => 'required|string|in:meeting,hearing,deadline,other',
    ];

    protected function messages()
    {
        return [
            // 'newPhaseEndDate.after_or_equal' => 'The end date must be after or equal to the start date.',
            // 'newDocument.max' => 'The document must not be larger than 10MB.',
        ];
    }

    public function mount(LegalCase $case)
    {
        // Ensure the logged-in client is associated with this case
        if ($case->client_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this case.');
        }
        
        $this->case = $case;
        
        $this->phases = collect();
        $this->events = collect();
        $this->tasks = collect();
        $this->documents = collect();
        
        $this->calendarYear = now()->year;
        $this->calendarMonth = now()->month;
        
        // Load court details
        $this->loadCourtDetails();

        $this->loadCaseData();
    }
    
    private function loadCaseData()
    {
        $this->phases = CasePhase::where('legal_case_id', $this->case->id)
            ->orderBy('order')
            ->get();
            
        if ($this->phases->count() > 0) {
            $currentPhase = $this->phases->where('is_current', true)->first();
            $this->currentPhaseId = $currentPhase ? $currentPhase->id : $this->phases->first()->id; // Fallback to first if none marked current
        } else {
            $this->currentPhaseId = null;
        }
        
        $this->events = CaseEvent::where('legal_case_id', $this->case->id)
            ->orderBy('start_datetime') // Corrected from event_date
            ->get();
            
        // Load tasks from case_tasks table
        $this->tasks = CaseTask::where('legal_case_id', $this->case->id)
            ->orderBy('due_date')
            ->get();
            
        // Ensure all tasks have both assigned_to and assigned_to_id set properly
        foreach ($this->tasks as $task) {
            // If assigned_to_id is set but assigned_to isn't, update assigned_to
            if (isset($task->assigned_to_id) && (!isset($task->assigned_to) || empty($task->assigned_to))) {
                $task->assigned_to = $task->assigned_to_id;
            }
            // If assigned_to is set but assigned_to_id isn't, update assigned_to_id
            elseif (isset($task->assigned_to) && (!isset($task->assigned_to_id) || empty($task->assigned_to_id))) {
                $task->assigned_to_id = $task->assigned_to;
            }
        }
        
        // Also load legacy tasks from client_tasks JSON field if any exist
        if (!empty($this->case->client_tasks) && is_array($this->case->client_tasks)) {
            foreach ($this->case->client_tasks as $legacyTask) {
                // Check if this is already a properly modeled task that's been migrated
                if (isset($legacyTask['id']) && $this->tasks->contains('id', $legacyTask['id'])) {
                    continue;
                }
                
                // Create a proper model instance for legacy task
                $task = new CaseTask([
                    'legal_case_id' => $this->case->id,
                    'title' => $legacyTask['description'] ?? 'Untitled Task',
                    'description' => $legacyTask['description'] ?? '',
                    'due_date' => $legacyTask['due_date'] ?? null,
                    'assigned_to_type' => 'App\Models\User',
                    'assigned_to_id' => Auth::id(),
                    'assigned_to' => Auth::id(),  // Set both fields for compatibility
                    'is_completed' => $legacyTask['completed'] ?? false,
                ]);
                
                // Push to collection without saving to DB
                $this->tasks->push($task);
            }
        }
        
        $this->documents = CaseDocument::where('legal_case_id', $this->case->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Load invoices
        $this->invoices = $this->case->invoices()
            ->where('client_id', Auth::id())
            ->whereIn('status', ['pending', 'partial', 'paid', 'overdue'])
            ->with(['items', 'payments'])
            ->latest()
            ->get();

        $this->prepareCalendarData();
    }

    private function prepareCalendarData()
    {
        $this->markedDates = [];
        $startDate = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        if ($this->tasks) {
            foreach ($this->tasks as $task) {
                if ($task->due_date) { // Ensure due_date is not null
                    $dueDate = Carbon::parse($task->due_date);
                    if ($dueDate->between($startDate, $endDate)) {
                        $dateStr = $dueDate->format('Y-m-d');
                        $this->markedDates[$dateStr] = isset($this->markedDates[$dateStr]) && $this->markedDates[$dateStr] !== 'task' ? 'both' : 'task';
                    }
                }
            }
        }

        if ($this->events) {
            foreach ($this->events as $event) {
                if ($event->start_datetime) { // Ensure start_datetime is not null
                     $eventDate = Carbon::parse($event->start_datetime);
                    if ($eventDate->between($startDate, $endDate)) {
                        $dateStr = $eventDate->format('Y-m-d');
                        $this->markedDates[$dateStr] = isset($this->markedDates[$dateStr]) && $this->markedDates[$dateStr] !== 'event' ? 'both' : 'event';
                    }
                }
            }
        }
    }
    
    // REMOVE PHASE MANAGEMENT METHODS FOR CLIENT
    // public function addPhase() { ... }
    // public function setCurrentPhase($phaseId) { ... }
    // public function completePhase($phaseId) { ... }
    // public function prepareEditPhase($phaseId) { ... }
    // public function updatePhase() { ... }
    // public function deletePhase($phaseId) { ... }


    // EVENT MANAGEMENT METHODS FOR CLIENT
    public function addEvent()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot add events to a closed case.', type: 'error');
            return;
        }

        $this->validate([
            'newEventTitle' => 'required|string|max:100',
            'newEventDescription' => 'required|string',
            'newEventDate' => 'required|date|after_or_equal:today',
            'newEventTime' => 'required',
            'newEvent_type' => 'required|string|in:meeting,hearing,deadline,other',
        ]);

        // Combine date and time into a start_datetime
        $startDatetime = $this->newEventDate ? Carbon::parse($this->newEventDate . ' ' . $this->newEventTime) : null;
        
        $event = CaseEvent::create([
            'legal_case_id' => $this->case->id,
            'title' => $this->newEventTitle,
            'description' => $this->newEventDescription,
            'start_datetime' => $startDatetime,
            'location' => $this->newEventLocation,
            'event_type' => $this->newEvent_type,
            'created_by_id' => Auth::id(),
        ]);

        $this->resetEventForm();
        $this->loadCaseData(); // Reload data to reflect new event
        $this->dispatch('show-message', message: 'Event added successfully!', type: 'success');
        $this->dispatch('close-modal', 'add-event-modal');
        $this->dispatch('scrollToTop');

        // Notify Lawyer
        if ($this->case->lawyer) {
            NotificationService::createSystemNotification(
                'case_event_created',
                $this->case->lawyer->id,
                'New Event Added by Client',
                'Client added a new event: ' . $this->newEventTitle,
                [
                    'case_id' => $this->case->id,
                    'event_id' => $event->id,
                    'link' => route('lawyer.case.setup', $this->case->id)
                ]
            );
        }
    }
    
    public function prepareEditEvent($eventId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot edit events in a closed case.', type: 'error');
            return;
        }

        $event = CaseEvent::where('id', $eventId)
                        ->where('legal_case_id', $this->case->id)
                        ->firstOrFail();

        $this->editEventId = $event->id;
        $this->editEventTitle = $event->title;
        $this->editEventDescription = $event->description;
        $this->editEvent_type = $event->event_type ?? 'meeting';
        
        if ($event->start_datetime) {
            $this->editEventDate = Carbon::parse($event->start_datetime)->format('Y-m-d');
            $this->editEventTime = Carbon::parse($event->start_datetime)->format('H:i');
        }
        
        $this->editEventLocation = $event->location;
        $this->dispatch('open-modal', 'edit-event-modal');
    }

    public function updateEvent()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot update events in a closed case.', type: 'error');
            return;
        }

        $this->validate([
            'editEventTitle' => 'required|string|max:100',
            'editEventDescription' => 'required|string',
            'editEventDate' => 'required|date|after_or_equal:today',
            'editEventTime' => 'required',
            'editEvent_type' => 'required|string|in:meeting,hearing,deadline,other',
        ]);

        if ($this->editEventId) {
            $event = CaseEvent::where('id', $this->editEventId)
                            ->where('legal_case_id', $this->case->id)
                            ->firstOrFail();
            
            // Combine date and time into a start_datetime
            $startDatetime = null;
            if ($this->editEventDate && $this->editEventTime) {
                $startDatetime = Carbon::parse($this->editEventDate . ' ' . $this->editEventTime);
            }
            
            $event->update([
                'title' => $this->editEventTitle,
                'description' => $this->editEventDescription,
                'start_datetime' => $startDatetime,
                'location' => $this->editEventLocation,
                'event_type' => $this->editEvent_type,
            ]);

            $this->resetEditEventForm();
            $this->loadCaseData();
            $this->dispatch('show-message', message: 'Event updated successfully!', type: 'success');
            $this->dispatch('close-modal', 'edit-event-modal');
            $this->dispatch('scrollToTop');

            // Notify Lawyer
            if ($this->case->lawyer) {
                NotificationService::createSystemNotification(
                    'case_event_updated',
                    $this->case->lawyer->id,
                    'Event Updated by Client',
                    'Client updated an event: ' . $event->title,
                    [
                        'case_id' => $this->case->id,
                        'event_id' => $event->id,
                        'link' => route('lawyer.case.setup', $this->case->id)
                    ]
                );
            }
        }
    }

    #[On('delete-event')]
    public function deleteEvent($eventId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot delete events in a closed case.', type: 'error');
            return;
        }

        $event = CaseEvent::where('id', $eventId)
                        ->where('legal_case_id', $this->case->id)
                        ->first();

        if ($event) {
            $eventTitle = $event->title;
            $deletedEventId = $event->id;
            $event->delete();
            $this->loadCaseData();
            $this->dispatch('show-message', message: 'Event deleted successfully!', type: 'success');
            
            // Notify Lawyer
            if ($this->case->lawyer) {
                NotificationService::createSystemNotification(
                    'case_event_deleted',
                    $this->case->lawyer->id,
                    'Event Deleted by Client',
                    'Client deleted an event: ' . $eventTitle,
                    [
                        'case_id' => $this->case->id,
                        'event_id' => $deletedEventId,
                        'link' => route('lawyer.case.setup', $this->case->id)
                    ]
                );
            }
        } else {
            $this->dispatch('show-message', message: 'Event not found or you are not authorized to delete it.', type: 'error');
        }
    }

    public function resetEventForm()
    {
        $this->newEventTitle = '';
        $this->newEventDescription = '';
        $this->newEventDate = '';
        $this->newEventTime = '';
        $this->newEventLocation = '';
        $this->newEvent_type = 'meeting';
    }

    public function resetEditEventForm()
    {
        $this->editEventId = null;
        $this->editEventTitle = '';
        $this->editEventDescription = '';
        $this->editEventDate = '';
        $this->editEventTime = '';
        $this->editEventLocation = '';
        $this->editEvent_type = '';
    }

    // TASK MANAGEMENT - Clients can add tasks (implicitly for themselves or as per logic)
    public function addTask()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot add tasks to a closed case.', type: 'error');
            return;
        }

        $this->validate([
            'newTaskTitle' => 'required|string|max:100',
            'newTaskDescription' => 'required|string',
            'newTaskDueDate' => 'required|date|after_or_equal:today',
        ]);

        $task = CaseTask::create([
            'legal_case_id' => $this->case->id,
            'title' => $this->newTaskTitle,
            'description' => $this->newTaskDescription,
            'due_date' => $this->newTaskDueDate,
            'assigned_to_id' => Auth::id(), // Automatically assign to the client
            'assigned_to_type' => 'App\Models\User', // Assuming client is a User model
            'created_by_id' => Auth::id(),
            'assigned_by' => Auth::id(), // Adding the missing field
            'status' => 'pending',
        ]);

        $this->resetTaskForm();
        $this->loadCaseData(); // Reload data to reflect new task
        $this->dispatch('show-message', message: 'Task added successfully!', type: 'success');
        $this->dispatch('close-modal', 'add-task-modal');
        $this->dispatch('scrollToTop');

        // Notify Lawyer
        if ($this->case->lawyer) {
            NotificationService::createSystemNotification(
                'case_task_created',
                $this->case->lawyer->id,
                'New Task Added by Client',
                'Client added a new task: ' . $this->newTaskTitle,
                [
                    'case_id' => $this->case->id,
                    'task_id' => $task->id,
                    'link' => route('lawyer.case.setup', $this->case->id)
                ]
            );
        }
    }
    
    // Clients can edit tasks assigned to them.
    public function prepareEditTask($taskId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot edit tasks in a closed case.', type: 'error');
            return;
        }

        $task = CaseTask::where('id', $taskId)
                        ->where('legal_case_id', $this->case->id)
                        // ->where('assigned_to_id', Auth::id()) // Client can only edit their tasks
                        ->firstOrFail();

        $this->editTaskId = $task->id;
        $this->editTaskTitle = $task->title;
        $this->editTaskDescription = $task->description;
        $this->editTaskDueDate = $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null;
        // $this->editTaskAssignedTo = $task->assigned_to_type === 'App\Models\User' && $task->assigned_to_id === Auth::id() ? 'client' : 'lawyer';
        $this->dispatch('open-edit-task-modal');
    }

    public function updateTask()
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot update tasks in a closed case.', type: 'error');
            return;
        }

        $this->validate([
            'editTaskTitle' => 'required|string|max:100',
            'editTaskDescription' => 'required|string',
            'editTaskDueDate' => 'required|date|after_or_equal:today',
        ]);

        if ($this->editTaskId) {
            $task = CaseTask::where('id', $this->editTaskId)
                            ->where('legal_case_id', $this->case->id)
                            // ->where('assigned_to_id', Auth::id()) // Ensure client is editing their own task
                            ->firstOrFail();
            
            $task->update([
                'title' => $this->editTaskTitle,
                'description' => $this->editTaskDescription,
                'due_date' => $this->editTaskDueDate,
                // assigned_to_id and assigned_to_type remain unchanged for client edits
            ]);

            $this->resetEditTaskForm();
            $this->loadCaseData();
            $this->dispatch('show-message', message: 'Task updated successfully!', type: 'success');
            $this->dispatch('close-edit-task-modal');
            $this->dispatch('scrollToTop');

            // Notify Lawyer
            if ($this->case->lawyer) {
                NotificationService::createSystemNotification(
                    'case_task_updated',
                    $this->case->lawyer->id,
                    'Task Updated by Client',
                    'Client updated a task: ' . $task->title,
                    [
                        'case_id' => $this->case->id,
                        'task_id' => $task->id,
                        'link' => route('lawyer.case.setup', $this->case->id)
                    ]
                );
            }
        }
    }

    // Clients can delete tasks they created or are assigned to them (adjust logic as needed)
    #[On('delete-task')]
    public function deleteTask($taskId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot delete tasks in a closed case.', type: 'error');
            return;
        }

        $task = CaseTask::where('id', $taskId)
                        ->where('legal_case_id', $this->case->id)
                        // ->where(function($query){ // Client can delete own tasks
                        //     $query->where('created_by_id', Auth::id())
                        //           ->orWhere('assigned_to_id', Auth::id());
                        // })
                        ->first();

        if ($task) {
            $task->delete();
            $this->loadCaseData();
            $this->dispatch('show-message', message: 'Task deleted successfully!', type: 'success');
            // Notify Lawyer
            if ($this->case->lawyer) {
                NotificationService::createSystemNotification(
                    'case_task_deleted',
                    $this->case->lawyer->id,
                    'Task Deleted by Client',
                    'Client deleted a task: ' . $task->title,
                    [
                        'case_id' => $this->case->id,
                        'task_id' => $task->id,
                        'link' => route('lawyer.case.setup', $this->case->id)
                    ]
                );
            }
        } else {
            $this->dispatch('show-message', message: 'Task not found or you are not authorized to delete it.', type: 'error');
        }
    }

    public function resetTaskForm()
    {
        $this->newTaskTitle = '';
        $this->newTaskDescription = '';
        $this->newTaskDueDate = '';
    }

    public function resetEditTaskForm()
    {
        $this->editTaskId = null;
        $this->editTaskTitle = '';
        $this->editTaskDescription = '';
        $this->editTaskDueDate = '';
        // $this->editTaskAssignedTo = '';
    }
    
    /**
     * Toggle the completion status of a task
     */
    public function toggleTaskCompletion($taskId)
    {
        // Check if case is closed
        if ($this->case->isClosed()) {
            $this->dispatch('show-message', message: 'Cannot update tasks in a closed case.', type: 'error');
            return;
        }

        $task = CaseTask::where('id', $taskId)
                      ->where('legal_case_id', $this->case->id)
                      ->first();

        if ($task) {
            // Check if task is assigned to lawyer - if so, client cannot toggle it
            $isAssignedToLawyer = false;
            
            // Check new format first: assigned_to_id
            if (isset($task->assigned_to_id) && $task->assigned_to_id === $this->case->lawyer_id) {
                $isAssignedToLawyer = true;
            }
            
            // Check old format: assigned_to
            if (!$isAssignedToLawyer && isset($task->assigned_to) && $task->assigned_to == $this->case->lawyer_id) {
                $isAssignedToLawyer = true;
            }
            
            if ($isAssignedToLawyer) {
                $this->dispatch('show-message', message: 'You cannot modify tasks assigned to your lawyer.', type: 'error');
                return;
            }

            $newStatus = !$task->is_completed;
            $task->is_completed = $newStatus;
            $task->save();

            // Notify lawyer about task status change
            if ($this->case->lawyer_id) {
                $statusText = $newStatus ? 'completed' : 'reopened';
                NotificationService::taskStatusChanged($task, $statusText, auth()->user(), User::find($this->case->lawyer_id));
            }

            // Success message
            $this->dispatch('show-message', 
                message: 'Task marked as ' . ($newStatus ? 'completed' : 'incomplete'), 
                type: 'success'
            );
        }
    }
    
    // DOCUMENT MANAGEMENT (Read-only for client, or limited upload if desired later)
    // public function uploadDocument() { ... }
    // #[On('delete-document')]
    // public function deleteDocument($documentId) { ... }
    // public function resetDocumentForm() { ... }


    // Tabs
    public function changeTab($tabName)
    {
        $this->activeTab = $tabName;
        if ($tabName === 'phases' || $tabName === 'overview') { // Reload data if switching to tabs that might have changed
            $this->loadCaseData();
        }
    }

    // Calendar navigation
    public function previousMonth()
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarYear = $date->year;
        $this->calendarMonth = $date->month;
        $this->prepareCalendarData();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarYear = $date->year;
        $this->calendarMonth = $date->month;
        $this->prepareCalendarData();
    }
    
    public function goToToday()
    {
        $this->calendarYear = now()->year;
        $this->calendarMonth = now()->month;
        $this->prepareCalendarData();
    }


    public function render()
    {
        // Ensure data is loaded for render, especially if switching tabs via query string
        // This might be redundant if changeTab always calls loadCaseData for relevant tabs
        if (in_array($this->activeTab, ['phases', 'overview', 'timeline', 'events', 'tasks'])) {
            $this->loadCaseData(); // Ensure tasks are loaded before preparing calendar data
            $this->prepareCalendarData(); // Recalculate marked dates as tasks/events might change
        }

        // Filter upcoming events for the overview tab
        $upcomingEvents = $this->events ? $this->events->where('start_datetime', '>=', now())->sortBy('start_datetime') : collect();
        
        // Ensure tasks are loaded for the overview tab
        $allTasks = $this->tasks ?? collect();
        // Show all tasks instead of only incomplete ones, but sort by due date
        $recentTasks = $allTasks->sortBy('due_date');


        return view('livewire.client.case-view', [ 
            'upcomingEvents' => $upcomingEvents,
            'recentTasks' => $recentTasks, // Pass all loaded tasks to the view
        ])->layout('components.layouts.app'); // Use the layouts.app component as the layout
    }

    // INVOICE METHODS
    
    public function viewInvoice($invoiceId)
    {
        $this->selectedInvoice = $this->case->invoices()
            ->where('id', $invoiceId)
            ->where('client_id', Auth::id())
            ->with(['items', 'payments', 'lawyer'])
            ->firstOrFail();
            
        $this->showViewInvoiceModal = true;
    }
    
    public function closeViewInvoiceModal()
    {
        $this->showViewInvoiceModal = false;
    }
    
    public function payWithGCash($invoiceId)
    {
        $invoice = $this->case->invoices()
            ->where('id', $invoiceId)
            ->where('client_id', Auth::id())
            ->firstOrFail();
        
        $payMongoService = new PayMongoService();
        $result = $payMongoService->createSource($invoice, 'gcash');
        
        if ($result['success'] && isset($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        } else {
            session()->flash('payment_status', 'error');
            session()->flash('payment_message', $result['message'] ?? 'Failed to create payment source');
            return redirect()->back();
        }
    }
    
    public function payWithCard($invoiceId)
    {
        $invoice = $this->case->invoices()
            ->where('id', $invoiceId)
            ->where('client_id', Auth::id())
            ->firstOrFail();
        
        $payMongoService = new PayMongoService();
        $result = $payMongoService->createPaymentIntent($invoice);
        
        if ($result['success']) {
            // Store payment intent data in session
            session([
                'payment_intent_id' => $result['intent_id'],
                'client_key' => $result['client_key'],
                'invoice_id' => $invoice->id
            ]);
            
            // Redirect to a custom card payment page
            return redirect()->route('client.payment.card', ['invoice' => $invoice->id]);
        } else {
            session()->flash('payment_status', 'error');
            session()->flash('payment_message', $result['message'] ?? 'Failed to create payment intent');
            return redirect()->back();
        }
    }

    /**
     * Load court details from the case (read-only for client)
     */
    private function loadCourtDetails()
    {
        $this->courtLevelMain = $this->case->court_level_main ?? '';
        $this->courtLevelSpecific = $this->case->court_level_specific ?? '';
    }
} 