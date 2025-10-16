<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $totalConsultations = 0;
    public $thisWeekConsultations = 0;
    public $activeCases = 0;
    public $completedCases = 0;
    
    public $upcomingConsultations;
    public $recentActivity = [];
    
    public function mount()
    {
        $this->loadStats();
        $this->loadUpcomingConsultations();
        $this->loadRecentActivity();
    }
    
    private function loadStats()
    {
        $clientId = Auth::id();
        
        // Total consultations
        $this->totalConsultations = Consultation::where('client_id', $clientId)->count();
        
        // This week consultations
        $this->thisWeekConsultations = Consultation::where('client_id', $clientId)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
            
        // Active cases
        $this->activeCases = LegalCase::where('client_id', $clientId)
            ->whereIn('status', ['active', 'contract_sent', 'contract_signed'])
            ->count();
            
        // Completed cases
        $this->completedCases = LegalCase::where('client_id', $clientId)
            ->whereIn('status', ['completed', 'closed'])
            ->count();
    }
    
    private function loadUpcomingConsultations()
    {
        $this->upcomingConsultations = Consultation::where('client_id', Auth::id())
            ->where('status', 'accepted')
            ->whereNotNull('selected_date')
            ->where('selected_date', '>=', Carbon::now())
            ->with(['lawyer.lawyerProfile', 'lawyer.lawFirmProfile'])
            ->orderBy('selected_date')
            ->take(3)
            ->get();
    }
    
    private function loadRecentActivity()
    {
        $clientId = Auth::id();
        $activities = [];
        
        // Recent consultation confirmations
        $recentConsultations = Consultation::where('client_id', $clientId)
            ->where('status', 'accepted')
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->with(['lawyer.lawyerProfile', 'lawyer.lawFirmProfile'])
            ->latest('updated_at')
            ->take(3)
            ->get();
            
        foreach ($recentConsultations as $consultation) {
            $lawyerName = $this->getLawyerName($consultation->lawyer);
            $activities[] = [
                'type' => 'consultation_confirmed',
                'message' => "Consultation confirmed with {$lawyerName}",
                'time' => $consultation->updated_at->diffForHumans(),
                'icon' => 'calendar',
                'color' => 'green'
            ];
        }
        
        // Recent case updates
        $recentCases = LegalCase::where('client_id', $clientId)
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->with(['lawyer.lawyerProfile', 'lawyer.lawFirmProfile'])
            ->latest('updated_at')
            ->take(3)
            ->get();
            
        foreach ($recentCases as $case) {
            $lawyerName = $this->getLawyerName($case->lawyer);
            $activities[] = [
                'type' => 'case_update',
                'message' => "Case update: {$case->title}",
                'time' => $case->updated_at->diffForHumans(),
                'icon' => 'document',
                'color' => 'blue'
            ];
        }
        
        // Sort by most recent
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        $this->recentActivity = array_slice($activities, 0, 5);
    }
    
    private function getLawyerName($lawyer)
    {
        if (!$lawyer) return 'Unknown Lawyer';
        
        if ($lawyer->lawyerProfile) {
            return $lawyer->lawyerProfile->first_name . ' ' . $lawyer->lawyerProfile->last_name;
        }
        
        if ($lawyer->lawFirmProfile) {
            return $lawyer->lawFirmProfile->firm_name;
        }
        
        return $lawyer->name ?? 'Unknown Lawyer';
    }
    
    public function render()
    {
        return view('livewire.client.dashboard')
            ->layout('components.layouts.app', [
                'header' => 'Dashboard',
                'title' => 'Client Dashboard'
            ]);
    }
} 