<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\LawyerRating;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RateTeamLawyer extends Component
{
    public $legalCase;
    public $caseId;
    public $showRatingModal = false;
    public $rating = 0;
    public $feedback = '';
    public $teamLawyers = [];
    public $selectedLawyerId;
    
    protected $rules = [
        'selectedLawyerId' => 'required|integer|exists:users,id',
        'rating' => 'required|integer|min:1|max:5',
        'feedback' => 'required|string|min:5|max:1000',
    ];
    
    // Listen for events to open the rating modal
    protected $listeners = [
        'openTeamRatingModal' => 'openModal'
    ];
    
    public function mount($caseId = null)
    {
        $this->caseId = $caseId;
        
        if ($this->caseId) {
            $this->loadCase();
        }
    }
    
    public function loadCase()
    {
        $this->legalCase = LegalCase::with([
                'lawyer.lawyerProfile',
                'teamLawyers.lawyerProfile',
                'teamLawyers.lawFirmLawyer'
            ])
            ->where('id', $this->caseId)
            ->where('client_id', Auth::id())
            ->where(function($query) {
                $query->where('status', LegalCase::STATUS_CLOSED)
                    ->orWhere('status', LegalCase::STATUS_COMPLETED)
                    ->orWhereNotNull('closed_at');
            })
            ->firstOrFail();
            
        // Load all lawyers assigned to the case
        $this->teamLawyers = [];
        foreach ($this->legalCase->teamLawyers as $lawyer) {
            $name = $this->getLawyerName($lawyer);
            $isPrimary = $lawyer->pivot->is_primary;
            $role = $lawyer->pivot->role ?: ($isPrimary ? 'Primary Lawyer' : 'Team Member');
            
            $this->teamLawyers[] = [
                'id' => $lawyer->id,
                'name' => $name,
                'role' => $role,
                'is_primary' => $isPrimary
            ];
        }
    }
    
    protected function getLawyerName($lawyer)
    {
        if ($lawyer->lawyerProfile) {
            return $lawyer->lawyerProfile->first_name . ' ' . $lawyer->lawyerProfile->last_name;
        } elseif ($lawyer->lawFirmLawyer) {
            return $lawyer->lawFirmLawyer->first_name . ' ' . $lawyer->lawFirmLawyer->last_name;
        } else {
            return $lawyer->name;
        }
    }
    
    public function openModal($caseId)
    {
        $this->caseId = $caseId;
        $this->loadCase();
        
        if (empty($this->teamLawyers)) {
            session()->flash('error', 'No lawyers found for this case.');
            return;
        }
        
        // Reset form
        $this->reset(['selectedLawyerId', 'rating', 'feedback']);
        $this->showRatingModal = true;
    }
    
    public function updatedSelectedLawyerId()
    {
        // If user selects a lawyer, check if they've already rated them
        if ($this->selectedLawyerId) {
            $existingRating = LawyerRating::where('legal_case_id', $this->caseId)
                ->where('client_id', Auth::id())
                ->where('lawyer_id', $this->selectedLawyerId)
                ->first();
                
            if ($existingRating) {
                // Pre-fill the form with existing rating data
                $this->rating = $existingRating->rating;
                $this->feedback = $existingRating->feedback;
            } else {
                // Reset form for new rating
                $this->reset(['rating', 'feedback']);
            }
        }
    }
    
    public function closeModal()
    {
        $this->showRatingModal = false;
        $this->reset(['selectedLawyerId', 'rating', 'feedback']);
    }
    
    public function setRating($value)
    {
        $this->rating = $value;
    }
    
    public function submitRating()
    {
        $this->validate();
        
        // Ensure the lawyer exists
        $lawyer = User::find($this->selectedLawyerId);
        if (!$lawyer) {
            $this->dispatch('show-message', message: 'Selected lawyer not found.', type: 'error');
            return;
        }
        
        // Create or update the rating
        $rating = LawyerRating::updateOrCreate(
            [
                'client_id' => Auth::id(),
                'lawyer_id' => $this->selectedLawyerId,
                'legal_case_id' => $this->caseId,
            ],
            [
                'rating' => $this->rating,
                'feedback' => $this->feedback,
                'rated_at' => now(),
            ]
        );
        
        if ($rating) {
            // Notify the lawyer about the new rating
            if (class_exists('App\Services\NotificationService')) {
                try {
                    NotificationService::lawyerRated(
                        $this->selectedLawyerId,
                        $this->caseId,
                        $this->legalCase->title,
                        $this->rating
                    );
                } catch (\Exception $e) {
                    // Just log the error but don't fail the rating submission
                    \Log::warning('Failed to create notification for lawyer rating: ' . $e->getMessage());
                }
            }
            
            $this->dispatch('show-message', message: 'Thank you for rating the lawyer!', type: 'success');
            $this->closeModal();
        } else {
            $this->dispatch('show-message', message: 'Failed to submit your rating. Please try again.', type: 'error');
        }
    }
    
    public function render()
    {
        return view('livewire.client.rate-team-lawyer');
    }
} 