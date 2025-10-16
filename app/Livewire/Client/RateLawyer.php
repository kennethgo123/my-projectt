<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\LawyerRating;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RateLawyer extends Component
{
    public $legalCase;
    public $caseId;
    public $showRatingModal = false;
    public $rating = 0;
    public $feedback = '';
    
    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'feedback' => 'required|string|min:5|max:1000',
    ];
    
    // Listen for events to open the rating modal
    protected $listeners = [
        'openRatingModal' => 'openModal'
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
        $this->legalCase = LegalCase::with(['lawyer.lawyerProfile'])
            ->where('id', $this->caseId)
            ->where('client_id', Auth::id())
            ->where(function($query) {
                $query->where('status', LegalCase::STATUS_CLOSED)
                    ->orWhere('status', LegalCase::STATUS_COMPLETED)
                    ->orWhereNotNull('closed_at');
            })
            ->firstOrFail();
    }
    
    public function openModal($caseId)
    {
        $this->caseId = $caseId;
        $this->loadCase();
        
        // Check if the client has already rated this case
        $existingRating = LawyerRating::where('legal_case_id', $this->caseId)
            ->where('client_id', Auth::id())
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
        $rating = LawyerRating::updateOrCreate(
            [
                'client_id' => Auth::id(),
                'lawyer_id' => $this->legalCase->lawyer_id,
                'legal_case_id' => $this->legalCase->id,
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
                        $this->legalCase->lawyer_id,
                        $this->legalCase->id,
                        $this->legalCase->title,
                        $this->rating
                    );
                } catch (\Exception $e) {
                    // Just log the error but don't fail the rating submission
                    \Log::warning('Failed to create notification for lawyer rating: ' . $e->getMessage());
                }
            }
            
            $this->dispatch('show-message', message: 'Thank you for rating your lawyer!', type: 'success');
            $this->closeModal();
        } else {
            $this->dispatch('show-message', message: 'Failed to submit your rating. Please try again.', type: 'error');
        }
    }
    
    public function render()
    {
        return view('livewire.client.rate-lawyer');
    }
}
