<?php

namespace App\Livewire\LawFirm;

use App\Models\LegalCase;
use App\Models\Consultation;
use App\Models\User;
use App\Models\LawFirmLawyer;
use App\Models\CaseTask;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class Dashboard extends Component
{
    public $activeCasesCount = 0; // This might be for the whole firm or removed
    public $pendingCasesCount = 0;  // This might be for the whole firm or removed
    public $completedCasesCount = 0; // This might be for the whole firm or removed
    public $upcomingConsultations = [];
    public $pendingConsultations = [];
    public $events = [];
    public $todayDeadlines = [];
    public $weekDeadlines = [];

    public function mount()
    {
        $firm = Auth::user()->lawFirmProfile;
        if (!$firm) {
            // Handle case where user is not a firm or profile doesn't exist
            // You might want to redirect or show an error
            return;
        }

        // Get all lawyer user IDs associated with this firm
        $lawyerUserIds = LawFirmLawyer::where('law_firm_profile_id', $firm->id)
                            ->whereHas('user', function ($query) {
                                $query->where('status', 'approved'); // Only active lawyers
                            })
                            ->pluck('user_id')
                            ->toArray();

        // If no lawyers, then nothing to show for consultations/calendar
        if (empty($lawyerUserIds)) {
            $this->upcomingConsultations = collect();
            $this->pendingConsultations = collect();
            $this->events = [];
            // Potentially load firm-level stats if needed here
            $this->activeCasesCount = LegalCase::whereIn('lawyer_id', $lawyerUserIds)->where('status', LegalCase::STATUS_ACTIVE)->count();
            $this->pendingCasesCount = LegalCase::whereIn('lawyer_id', $lawyerUserIds)->where('status', LegalCase::STATUS_PENDING)->count();
            $this->completedCasesCount = LegalCase::whereIn('lawyer_id', $lawyerUserIds)->where('status', LegalCase::STATUS_COMPLETED)->count();
            return;
        }

        // Count cases by status for the firm
        $this->activeCasesCount = LegalCase::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', LegalCase::STATUS_ACTIVE)
            ->count();
            
        $this->pendingCasesCount = LegalCase::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', LegalCase::STATUS_PENDING)
            ->count();
            
        $this->completedCasesCount = LegalCase::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', LegalCase::STATUS_COMPLETED)
            ->count();

        // Get upcoming consultations for the firm
        $this->upcomingConsultations = Consultation::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', 'accepted')
            ->whereNotNull('selected_date')
            ->where('selected_date', '>=', Carbon::now()->startOfDay())
            ->orderBy('selected_date')
            ->take(5) // Show a bit more for the firm view
            ->get();

        if ($this->upcomingConsultations->isEmpty()) {
            $this->upcomingConsultations = Consultation::whereIn('lawyer_id', $lawyerUserIds)
                ->where('status', 'confirmed')
                ->whereNotNull('selected_date')
                ->where('selected_date', '>=', Carbon::now()->startOfDay())
                ->orderBy('selected_date')
                ->take(5)
                ->get();
        }

        // Get pending consultation requests for the firm
        $this->pendingConsultations = Consultation::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5) // Show a bit more for the firm view
            ->get();

        // Load deadlines
        $this->loadDeadlines($lawyerUserIds);
        
        // Prepare calendar events
        $this->prepareCalendarEvents($lawyerUserIds);
    }
    
    protected function loadDeadlines($lawyerUserIds)
    {
        $today = Carbon::now()->startOfDay();
        $endOfToday = Carbon::now()->endOfDay();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        // Get case IDs for all active cases for this firm
        $caseIds = LegalCase::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', '!=', LegalCase::STATUS_COMPLETED)
            ->where('status', '!=', LegalCase::STATUS_CANCELLED)
            ->where('status', '!=', LegalCase::STATUS_CLOSED)
            ->pluck('id');
            
        // Get tasks due today
        $todayTasks = CaseTask::whereIn('legal_case_id', $caseIds)
            ->where('is_completed', false)
            ->where('due_date', '>=', $today)
            ->where('due_date', '<=', $endOfToday)
            ->orderBy('due_date')
            ->get();
            
        // Format today's tasks as deadlines
        foreach ($todayTasks as $task) {
            $case = LegalCase::find($task->legal_case_id);
            $assignedUser = null;
            if ($task->assigned_to_id) {
                $assignedUser = User::find($task->assigned_to_id);
            }
            
            $this->todayDeadlines[] = [
                'id' => 'task_' . $task->id,
                'title' => $task->title,
                'formatted_date' => Carbon::parse($task->due_date)->format('g:i A'),
                'case_title' => $case ? $case->title : 'Unknown Case',
                'lawyer_name' => $assignedUser ? ($assignedUser->lawyerProfile ? 
                    $assignedUser->lawyerProfile->first_name . ' ' . $assignedUser->lawyerProfile->last_name : 
                    $assignedUser->name) : 'Unassigned',
                'url' => route('law-firm.case.setup', $task->legal_case_id) . '?tab=tasks_management'
            ];
        }
        
        // Get case deadlines for today
        $todayCaseDeadlines = LegalCase::whereIn('id', $caseIds)
            ->where('deadline', '>=', $today)
            ->where('deadline', '<=', $endOfToday)
            ->get();
            
        foreach ($todayCaseDeadlines as $case) {
            $lawyer = null;
            if ($case->lawyer_id) {
                $lawyer = User::find($case->lawyer_id);
            }
            
            $this->todayDeadlines[] = [
                'id' => 'case_' . $case->id,
                'title' => 'Case Deadline: ' . $case->title,
                'formatted_date' => Carbon::parse($case->deadline)->format('g:i A'),
                'case_title' => $case->title,
                'lawyer_name' => $lawyer ? ($lawyer->lawyerProfile ? 
                    $lawyer->lawyerProfile->first_name . ' ' . $lawyer->lawyerProfile->last_name : 
                    $lawyer->name) : 'Unassigned',
                'url' => route('law-firm.case.setup', $case->id)
            ];
        }
        
        // Get tasks due this week (excluding today)
        $weekTasks = CaseTask::whereIn('legal_case_id', $caseIds)
            ->where('is_completed', false)
            ->where('due_date', '>', $endOfToday)
            ->where('due_date', '<=', $endOfWeek)
            ->orderBy('due_date')
            ->get();
            
        // Format week's tasks as deadlines
        foreach ($weekTasks as $task) {
            $case = LegalCase::find($task->legal_case_id);
            $assignedUser = null;
            if ($task->assigned_to_id) {
                $assignedUser = User::find($task->assigned_to_id);
            }
            
            $this->weekDeadlines[] = [
                'id' => 'task_' . $task->id,
                'title' => $task->title,
                'formatted_date' => Carbon::parse($task->due_date)->format('D, M d'),
                'case_title' => $case ? $case->title : 'Unknown Case',
                'lawyer_name' => $assignedUser ? ($assignedUser->lawyerProfile ? 
                    $assignedUser->lawyerProfile->first_name . ' ' . $assignedUser->lawyerProfile->last_name : 
                    $assignedUser->name) : 'Unassigned',
                'url' => route('law-firm.case.setup', $task->legal_case_id) . '?tab=tasks_management'
            ];
        }
        
        // Get case deadlines for the week (excluding today)
        $weekCaseDeadlines = LegalCase::whereIn('id', $caseIds)
            ->where('deadline', '>', $endOfToday)
            ->where('deadline', '<=', $endOfWeek)
            ->get();
            
        foreach ($weekCaseDeadlines as $case) {
            $lawyer = null;
            if ($case->lawyer_id) {
                $lawyer = User::find($case->lawyer_id);
            }
            
            $this->weekDeadlines[] = [
                'id' => 'case_' . $case->id,
                'title' => 'Case Deadline: ' . $case->title,
                'formatted_date' => Carbon::parse($case->deadline)->format('D, M d'),
                'case_title' => $case->title,
                'lawyer_name' => $lawyer ? ($lawyer->lawyerProfile ? 
                    $lawyer->lawyerProfile->first_name . ' ' . $lawyer->lawyerProfile->last_name : 
                    $lawyer->name) : 'Unassigned',
                'url' => route('law-firm.case.setup', $case->id)
            ];
        }
        
        // Sort the week deadlines by due date
        usort($this->weekDeadlines, function($a, $b) {
            return strtotime($a['formatted_date']) - strtotime($b['formatted_date']);
        });
    }
    
    protected function prepareCalendarEvents($lawyerUserIds)
    {
        if (empty($lawyerUserIds)) {
            $this->events = [];
            return;
        }

        // Get consultations for calendar
        $consultations = Consultation::whereIn('lawyer_id', $lawyerUserIds)
            ->whereNotNull('selected_date') // Ensure selected_date is not null
            ->where('selected_date', '>=', Carbon::now()->subDays(30))
            ->where('selected_date', '<=', Carbon::now()->addDays(60))
            ->where('status', '!=', 'completed') // Exclude completed consultations
            ->get();
            
        foreach ($consultations as $consultation) {
            if ($consultation->selected_date) { // Double check selected_date
                $clientName = 'Client';
                if ($consultation->client && $consultation->client->clientProfile) {
                    $clientName = $consultation->client->clientProfile->first_name . ' ' . $consultation->client->clientProfile->last_name;
                } elseif ($consultation->client) {
                    $clientName = $consultation->client->name ?? 'Client';
                }
                
                $lawyerName = $consultation->lawyer->name ?? 'Lawyer'; // Add lawyer name

                $this->events[] = [
                    'id' => $consultation->id,
                    'title' => 'Consult: ' . $clientName . ' w/ ' . $lawyerName,
                    'start' => $consultation->selected_date->format('Y-m-d\TH:i:s'),
                    'end' => $consultation->selected_date->addHours(1)->format('Y-m-d\TH:i:s'),
                    'url' => route('law-firm.consultations'), // Link to firm's consultation management
                    'backgroundColor' => '#4f46e5', 
                    'borderColor' => '#4338ca',
                    'extendedProps' => [
                        'formattedTime' => $consultation->selected_date->format('g:i A'),
                        'type' => 'consultation'
                    ]
                ];
            }
        }
        
        $cases = LegalCase::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', '!=', LegalCase::STATUS_COMPLETED)
            ->where('status', '!=', LegalCase::STATUS_CANCELLED)
            ->where('status', '!=', LegalCase::STATUS_CLOSED)
            ->whereNotNull('deadline') // Ensure deadline is not null
            ->where('deadline', '>=', Carbon::now()->subDays(30))
            ->where('deadline', '<=', Carbon::now()->addDays(60))
            ->get();
            
        foreach ($cases as $case) {
            if ($case->deadline) { // Double check deadline
                $lawyerName = $case->lawyer->name ?? 'Lawyer';
                $this->events[] = [
                    'id' => 'case_' . $case->id,
                    'title' => 'Deadline: ' . $case->title . ' (' . $lawyerName . ')',
                    'start' => Carbon::parse($case->deadline)->format('Y-m-d\TH:i:s'),
                    'end' => Carbon::parse($case->deadline)->addHours(1)->format('Y-m-d\TH:i:s'),
                    'url' => route('law-firm.case.setup', $case->id),
                    'backgroundColor' => '#dc2626',
                    'borderColor' => '#b91c1c',
                    'extendedProps' => [
                        'formattedTime' => Carbon::parse($case->deadline)->format('g:i A'),
                        'type' => 'deadline'
                    ]
                ];
            }
        }
        
        $caseIds = LegalCase::whereIn('lawyer_id', $lawyerUserIds)
            ->where('status', '!=', LegalCase::STATUS_COMPLETED)
            ->where('status', '!=', LegalCase::STATUS_CANCELLED)
            ->where('status', '!=', LegalCase::STATUS_CLOSED)
            ->pluck('id')->toArray();
            
        if (!empty($caseIds)) {
            $caseEvents = \App\Models\CaseEvent::whereIn('legal_case_id', $caseIds)
                ->whereNotNull('start_datetime') // Ensure start_datetime is not null
                ->where('start_datetime', '>=', Carbon::now()->subDays(30))
                ->where('start_datetime', '<=', Carbon::now()->addDays(60))
                ->get();
                
            foreach ($caseEvents as $event) {
                if ($event->start_datetime) { // Double check start_datetime
                    $case = $event->legalCase; // Access relationship
                    $lawyerName = $case && $case->lawyer ? ($case->lawyer->name ?? 'Lawyer') : 'Lawyer';
                    $this->events[] = [
                        'id' => 'event_' . $event->id,
                        'title' => $event->title . ' (' . ($case ? $case->title : 'Case') . ' - ' . $lawyerName . ')',
                        'start' => $event->start_datetime->format('Y-m-d\TH:i:s'),
                        'end' => $event->start_datetime->addHours(1)->format('Y-m-d\TH:i:s'),
                        'url' => route('law-firm.case.setup', $event->legal_case_id),
                        'backgroundColor' => '#3b82f6', 
                        'borderColor' => '#2563eb',
                        'description' => $event->description,
                        'location' => $event->location,
                        'extendedProps' => [
                            'formattedTime' => $event->start_datetime->format('g:i A'),
                            'type' => 'event'
                        ]
                    ];
                }
            }
            
            $caseTasks = \App\Models\CaseTask::whereIn('legal_case_id', $caseIds)
                ->where(function($query) use ($lawyerUserIds) {
                    // Ensure tasks are assigned to lawyers within the firm
                    $query->whereIn('assigned_to_id', $lawyerUserIds)
                          ->orWhereIn('assigned_to', $lawyerUserIds); // Assuming assigned_to can also store user_id
                })
                ->where('is_completed', false)
                ->whereNotNull('due_date') // Ensure due_date is not null
                ->where('due_date', '>=', Carbon::now()->subDays(30))
                ->where('due_date', '<=', Carbon::now()->addDays(60))
                ->get();
                
            foreach ($caseTasks as $task) {
                if ($task->due_date) { // Double check due_date
                    $case = $task->legalCase; // Access relationship
                    $assignee = User::find($task->assigned_to_id ?: $task->assigned_to);
                    $assigneeName = $assignee ? ($assignee->name ?? 'Lawyer') : 'Lawyer';

                    $this->events[] = [
                        'id' => 'task_' . $task->id,
                        'title' => 'Task: ' . $task->title . ' (' . ($case ? $case->title : 'Case') . ' - ' . $assigneeName . ')',
                        'start' => Carbon::parse($task->due_date)->format('Y-m-d\T00:00:00'),
                        'allDay' => true,
                        'url' => route('law-firm.case.setup', $task->legal_case_id) . '?tab=tasks_management',
                        'backgroundColor' => '#10b981', 
                        'borderColor' => '#059669',
                        'description' => $task->description,
                        'extendedProps' => [
                            'formattedDate' => Carbon::parse($task->due_date)->format('M d, Y'),
                            'type' => 'task'
                        ]
                    ];
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.law-firm.dashboard')
            ->layout('layouts.app'); // Assuming a general app layout exists
    }
}
