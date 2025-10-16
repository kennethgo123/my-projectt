<?php

namespace App\Livewire\Lawyer;

use App\Models\LegalCase;
use App\Models\Consultation;
use App\Models\User;
use App\Models\CaseTask;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class Dashboard extends Component
{
    public $activeCasesCount = 0;
    public $pendingCasesCount = 0;
    public $completedCasesCount = 0;
    public $upcomingConsultations = [];
    public $pendingConsultations = [];
    public $events = [];
    public $todayDeadlines = [];
    public $weekDeadlines = [];

    public function mount()
    {
        $lawyer_id = Auth::id() ?: 8; // Default to 8 if not logged in (for testing)
        
        // Count cases by status
        $this->activeCasesCount = LegalCase::where('lawyer_id', $lawyer_id)
            ->where('status', LegalCase::STATUS_ACTIVE)
            ->count();
            
        $this->pendingCasesCount = LegalCase::where('lawyer_id', $lawyer_id)
            ->where('status', LegalCase::STATUS_PENDING)
            ->count();
            
        $this->completedCasesCount = LegalCase::where('lawyer_id', $lawyer_id)
            ->where('status', LegalCase::STATUS_COMPLETED)
            ->count();

        // Get upcoming consultations (accepted with future dates)
        // Modified query to ensure we get consultations even if they're on the current day
        $this->upcomingConsultations = Consultation::where('lawyer_id', $lawyer_id)
            ->where('status', 'accepted')
            ->whereNotNull('selected_date')
            ->where('selected_date', '>=', Carbon::now()->startOfDay())
            ->orderBy('selected_date')
            ->take(3)
            ->get();

        // In case no accepted consultations, let's check for confirmed ones as fallback
        if ($this->upcomingConsultations->isEmpty()) {
            $this->upcomingConsultations = Consultation::where('lawyer_id', $lawyer_id)
                ->where('status', 'confirmed')
                ->whereNotNull('selected_date')
                ->where('selected_date', '>=', Carbon::now()->startOfDay())
                ->orderBy('selected_date')
                ->take(3)
                ->get();
        }

        // Get pending consultation requests
        $this->pendingConsultations = Consultation::where('lawyer_id', $lawyer_id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        
        // Get today's deadlines
        $this->loadDeadlines();
            
        // Prepare events for the calendar
        $this->prepareCalendarEvents();
    }
    
    protected function loadDeadlines()
    {
        $lawyer_id = Auth::id() ?: 8; // Default to 8 if not logged in (for testing)
        $today = Carbon::now()->startOfDay();
        $endOfToday = Carbon::now()->endOfDay();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Get case IDs for all active cases for this lawyer
        $caseIds = LegalCase::where(function($query) use ($lawyer_id) {
                $query->where('lawyer_id', $lawyer_id)
                    ->orWhereHas('teamLawyers', function($q) use ($lawyer_id) {
                        $q->where('user_id', $lawyer_id);
                    });
            })
            ->where('status', '!=', LegalCase::STATUS_COMPLETED)
            ->where('status', '!=', LegalCase::STATUS_CANCELLED)
            ->where('status', '!=', LegalCase::STATUS_CLOSED)
            ->pluck('id');
            
        // Get tasks due today
        $todayTasks = CaseTask::whereIn('legal_case_id', $caseIds)
            ->where(function($query) use ($lawyer_id) {
                $query->where('assigned_to_id', $lawyer_id)
                    ->orWhere('assigned_to', $lawyer_id);
            })
            ->where('is_completed', false)
            ->where('due_date', '>=', $today)
            ->where('due_date', '<=', $endOfToday)
            ->orderBy('due_date')
            ->get();
            
        // Format today's tasks as deadlines
        foreach ($todayTasks as $task) {
            $case = LegalCase::find($task->legal_case_id);
            $this->todayDeadlines[] = [
                'id' => 'task_' . $task->id,
                'title' => $task->title,
                'formatted_date' => Carbon::parse($task->due_date)->format('g:i A'),
                'case_title' => $case ? $case->title : 'Unknown Case',
                'url' => route('lawyer.case.setup', $task->legal_case_id) . '?tab=tasks_management'
            ];
        }
        
        // Get case deadlines for today
        $todayCaseDeadlines = LegalCase::whereIn('id', $caseIds)
            ->where('deadline', '>=', $today)
            ->where('deadline', '<=', $endOfToday)
            ->get();
            
        foreach ($todayCaseDeadlines as $case) {
            $this->todayDeadlines[] = [
                'id' => 'case_' . $case->id,
                'title' => 'Case Deadline: ' . $case->title,
                'formatted_date' => Carbon::parse($case->deadline)->format('g:i A'),
                'case_title' => $case->title,
                'url' => route('lawyer.case.setup', $case->id)
            ];
        }
        
        // Get tasks due this week (excluding today)
        $weekTasks = CaseTask::whereIn('legal_case_id', $caseIds)
            ->where(function($query) use ($lawyer_id) {
                $query->where('assigned_to_id', $lawyer_id)
                    ->orWhere('assigned_to', $lawyer_id);
            })
            ->where('is_completed', false)
            ->where('due_date', '>', $endOfToday)
            ->where('due_date', '<=', $endOfWeek)
            ->orderBy('due_date')
            ->get();
            
        // Format week's tasks as deadlines
        foreach ($weekTasks as $task) {
            $case = LegalCase::find($task->legal_case_id);
            $this->weekDeadlines[] = [
                'id' => 'task_' . $task->id,
                'title' => $task->title,
                'formatted_date' => Carbon::parse($task->due_date)->format('D, M d'),
                'case_title' => $case ? $case->title : 'Unknown Case',
                'url' => route('lawyer.case.setup', $task->legal_case_id) . '?tab=tasks_management'
            ];
        }
        
        // Get case deadlines for the week (excluding today)
        $weekCaseDeadlines = LegalCase::whereIn('id', $caseIds)
            ->where('deadline', '>', $endOfToday)
            ->where('deadline', '<=', $endOfWeek)
            ->get();
            
        foreach ($weekCaseDeadlines as $case) {
            $this->weekDeadlines[] = [
                'id' => 'case_' . $case->id,
                'title' => 'Case Deadline: ' . $case->title,
                'formatted_date' => Carbon::parse($case->deadline)->format('D, M d'),
                'case_title' => $case->title,
                'url' => route('lawyer.case.setup', $case->id)
            ];
        }
        
        // Sort the week deadlines by due date
        usort($this->weekDeadlines, function($a, $b) {
            return strtotime($a['formatted_date']) - strtotime($b['formatted_date']);
        });
    }
    
    protected function prepareCalendarEvents()
    {
        $lawyer_id = Auth::id() ?: 8; // Default to 8 if not logged in (for testing)
        
        // Get consultations for calendar - exclude completed consultations
        $consultations = Consultation::where('lawyer_id', $lawyer_id)
            ->where('selected_date', '>=', Carbon::now()->subDays(30))
            ->where('selected_date', '<=', Carbon::now()->addDays(60))
            ->where('status', '!=', 'completed') // Exclude completed consultations
            ->get();
            
        foreach ($consultations as $consultation) {
            // Get client name properly
            $clientName = 'Client';
            if ($consultation->client && $consultation->client->clientProfile) {
                $clientName = $consultation->client->clientProfile->first_name . ' ' . $consultation->client->clientProfile->last_name;
            } elseif ($consultation->client) {
                $clientName = $consultation->client->name ?? 'Client';
            }
            
            $this->events[] = [
                'id' => $consultation->id,
                'title' => 'Consultation: ' . $clientName,
                'start' => $consultation->selected_date->format('Y-m-d\TH:i:s'),
                'end' => $consultation->selected_date->addHours(1)->format('Y-m-d\TH:i:s'),
                'url' => route('lawyer.consultations'),
                'backgroundColor' => '#4f46e5', // Indigo color for consultations
                'borderColor' => '#4338ca',
                'extendedProps' => [
                    'formattedTime' => $consultation->selected_date->format('g:i A'), // Ensure correct AM/PM format
                    'type' => 'consultation'
                ]
            ];
        }
        
        // Get case deadlines for calendar
        $cases = LegalCase::where('lawyer_id', $lawyer_id)
            ->where('status', '!=', LegalCase::STATUS_COMPLETED)
            ->where('status', '!=', LegalCase::STATUS_CANCELLED)
            ->where('status', '!=', LegalCase::STATUS_CLOSED)
            ->where('deadline', '>=', Carbon::now()->subDays(30))
            ->where('deadline', '<=', Carbon::now()->addDays(60))
            ->get();
            
        foreach ($cases as $case) {
            if ($case->deadline) {
                $this->events[] = [
                    'id' => 'case_' . $case->id,
                    'title' => 'Deadline: ' . $case->title,
                    'start' => Carbon::parse($case->deadline)->format('Y-m-d\TH:i:s'),
                    'end' => Carbon::parse($case->deadline)->addHours(1)->format('Y-m-d\TH:i:s'),
                    'url' => route('lawyer.case.setup', $case->id),
                    'backgroundColor' => '#dc2626', // Red color for deadlines
                    'borderColor' => '#b91c1c',
                    'extendedProps' => [
                        'formattedTime' => Carbon::parse($case->deadline)->format('g:i A'),
                        'type' => 'deadline'
                    ]
                ];
            }
        }
        
        // Get case events for calendar (from all active cases)
        $caseIds = LegalCase::where('lawyer_id', $lawyer_id)
            ->where('status', '!=', LegalCase::STATUS_COMPLETED)
            ->where('status', '!=', LegalCase::STATUS_CANCELLED)
            ->where('status', '!=', LegalCase::STATUS_CLOSED)
            ->pluck('id');
            
        $caseEvents = \App\Models\CaseEvent::whereIn('legal_case_id', $caseIds)
            ->where('start_datetime', '>=', Carbon::now()->subDays(30))
            ->where('start_datetime', '<=', Carbon::now()->addDays(60))
            ->get();
            
        foreach ($caseEvents as $event) {
            $case = LegalCase::find($event->legal_case_id);
            $this->events[] = [
                'id' => 'event_' . $event->id,
                'title' => $event->title . ' (' . ($case ? $case->title : 'Case') . ')',
                'start' => $event->start_datetime->format('Y-m-d\TH:i:s'),
                'end' => $event->start_datetime->addHours(1)->format('Y-m-d\TH:i:s'),
                'url' => route('lawyer.case.setup', $event->legal_case_id),
                'backgroundColor' => '#3b82f6', // Blue color for events
                'borderColor' => '#2563eb',
                'description' => $event->description,
                'location' => $event->location,
                'extendedProps' => [
                    'formattedTime' => $event->start_datetime->format('g:i A'),
                    'type' => 'event'
                ]
            ];
        }
        
        // Get tasks assigned to lawyer for calendar
        $caseTasks = \App\Models\CaseTask::whereIn('legal_case_id', $caseIds)
            ->where(function($query) use ($lawyer_id) {
                $query->where('assigned_to_id', $lawyer_id)
                    ->orWhere('assigned_to', $lawyer_id);
            })
            ->where('is_completed', false)
            ->where('due_date', '>=', Carbon::now()->subDays(30))
            ->where('due_date', '<=', Carbon::now()->addDays(60))
            ->get();
            
        foreach ($caseTasks as $task) {
            $case = LegalCase::find($task->legal_case_id);
            $this->events[] = [
                'id' => 'task_' . $task->id,
                'title' => 'Task: ' . $task->title . ' (' . ($case ? $case->title : 'Case') . ')',
                'start' => Carbon::parse($task->due_date)->format('Y-m-d\T00:00:00'), // All day event
                'allDay' => true,
                'url' => route('lawyer.case.setup', $task->legal_case_id) . '?tab=tasks_management',
                'backgroundColor' => '#10b981', // Green color for tasks
                'borderColor' => '#059669',
                'description' => $task->description,
                'extendedProps' => [
                    'formattedDate' => Carbon::parse($task->due_date)->format('M d, Y'),
                    'type' => 'task'
                ]
            ];
        }
    }

    public function render()
    {
        return view('livewire.lawyer.dashboard')
            ->layout('layouts.app');
    }
}
