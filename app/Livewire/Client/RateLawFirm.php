<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\LawFirmRating;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RateLawFirm extends Component
{
    public $legalCase;
    public $caseId;
    public $showRatingModal = false;
    public $rating = 0;
    public $feedback = '';
    public $lawFirmId = null;
    public $lawFirmName = '';
    
    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'feedback' => 'required|string|min:5|max:1000',
    ];
    
    // Listen for events to open the rating modal
    protected $listeners = [
        'openLawFirmRatingModal' => 'openModal'
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
                'lawyer.lawFirmProfile',
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
            
        // Determine the law firm
        $this->findLawFirm();
    }
    
    protected function findLawFirm()
    {
        // Case 1: Primary lawyer is a law firm
        if ($this->legalCase->lawyer->isLawFirm()) {
            $this->lawFirmId = $this->legalCase->lawyer_id;
            $this->lawFirmName = $this->legalCase->lawyer->lawFirmProfile ?
                $this->legalCase->lawyer->lawFirmProfile->firm_name :
                $this->legalCase->lawyer->name;
            return;
        }
        
        // Case 2: Primary lawyer belongs to a law firm
        if ($this->legalCase->lawyer->firm_id) {
            $this->lawFirmId = $this->legalCase->lawyer->firm_id;
            $lawFirm = User::with('lawFirmProfile')->find($this->lawFirmId);
            if ($lawFirm && $lawFirm->lawFirmProfile) {
                $this->lawFirmName = $lawFirm->lawFirmProfile->firm_name;
            } else {
                $this->lawFirmName = $lawFirm ? $lawFirm->name : 'Law Firm';
            }
            return;
        }
        
        // Case 3: Check if any team lawyer belongs to a law firm
        foreach ($this->legalCase->teamLawyers as $lawyer) {
            if ($lawyer->firm_id) {
                $this->lawFirmId = $lawyer->firm_id;
                $lawFirm = User::with('lawFirmProfile')->find($this->lawFirmId);
                if ($lawFirm && $lawFirm->lawFirmProfile) {
                    $this->lawFirmName = $lawFirm->lawFirmProfile->firm_name;
                } else {
                    $this->lawFirmName = $lawFirm ? $lawFirm->name : 'Law Firm';
                }
                return;
            }
        }
    }
    
    public function openModal($caseId)
    {
        $this->caseId = $caseId;
        $this->loadCase();
        
        if (!$this->lawFirmId) {
            session()->flash('error', 'No law firm found for this case.');
            return;
        }
        
        // Check if the client has already rated this law firm for this case
        $existingRating = LawFirmRating::where('legal_case_id', $this->caseId)
            ->where('client_id', Auth::id())
            ->where('law_firm_id', $this->lawFirmId)
            ->first();
            
        if ($existingRating) {
            // Pre-fill the form with existing rating data
            $this->rating = $existingRating->rating;
            $this->feedback = $existingRating->feedback;
        } else {
            // Reset form for new rating
            $this->reset(['rating', 'feedback']);
        }
        
        $this->showRatingModal = true;
    }
    
    public function closeModal()
    {
        $this->showRatingModal = false;
        $this->reset(['rating', 'feedback']);
    }
    
    public function setRating($value)
    {
        $this->rating = $value;
    }
    
    public function submitRating()
    {
        $this->validate();
        
        // Create or update the rating
        $rating = LawFirmRating::updateOrCreate(
            [
                'client_id' => Auth::id(),
                'law_firm_id' => $this->lawFirmId,
                'legal_case_id' => $this->caseId,
            ],
            [
                'rating' => $this->rating,
                'feedback' => $this->feedback,
                'rated_at' => now(),
            ]
        );
        
        if ($rating) {
            // Notify the law firm about the new rating
            if (class_exists('App\Services\NotificationService')) {
                try {
                    NotificationService::lawFirmRated($rating);
                } catch (\Exception $e) {
                    // Just log the error but don't fail the rating submission
                    \Log::warning('Failed to create notification for law firm rating: ' . $e->getMessage());
                }
            }
            
            $this->dispatch('show-message', message: 'Thank you for rating the law firm!', type: 'success');
            $this->closeModal();
        } else {
            $this->dispatch('show-message', message: 'Failed to submit your rating. Please try again.', type: 'error');
        }
    }
    
    public function render()
    {
        return view('livewire.client.rate-law-firm');
    }
}
