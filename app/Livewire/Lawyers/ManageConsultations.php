<?php

namespace App\Livewire\Lawyers;

use App\Models\Consultation;
use App\Models\LegalCase;
use App\Models\BlockedTimeSlot;
use App\Services\NotificationService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\LawyerAvailability;
use Carbon\Carbon;
use App\Models\AvailabilitySlot;
use Illuminate\Support\Facades\Storage;

class ManageConsultations extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Tab control
    public $activeTab = 'consultations';
    public $canSetAvailability = true;

    // Selected consultation and action states
    public $selectedConsultation = null;
    public $selectedDates = [];
    public $customMeetingLink = '';
    public $declineReason = '';
    public $consultationResults = '';
    public $meetingNotes = '';
    public $caseTitle = '';
    public $caseDescription = '';
    public $contractDocument = null;
    public $consultationDocument = null;
    public $debugMessage = 'Initial state';

    // Modal states
    public $showDeclineModal = false;
    public $showCompleteModal = false;
    public $showStartCaseModal = false;
    public $showCustomLinkModal = false;

    // Properties for Review Contract Modal
    public $showReviewContractModal = false;
    public $reviewCaseTitle = '';
    public $reviewCaseDescription = '';
    public $reviewContractPath = '';
    public $selectedConsultationForReview = null;

    public $googleMeetLink = '';
    public $showGoogleMeetInput = [];

    protected $listeners = [
        'refreshConsultations' => '$refresh',
        'refreshSingleConsultation' => 'refreshConsultation'
    ];

    protected $rules = [
        'declineReason' => 'required|min:10',
        'customMeetingLink' => 'nullable|url',
        'consultationResults' => 'required|min:10',
        'meetingNotes' => 'nullable|min:10',
        'caseTitle' => 'required|min:5|max:255',
        'caseDescription' => 'required|min:10',
        'contractDocument' => 'required|file|max:10240|mimes:pdf',
        'consultationDocument' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png'
    ];

    public function mount()
    {
        // Initialize any required data
        $this->showGoogleMeetInput = [];
        
        // Check if lawyer can set availability
        $user = auth()->user();
        $this->canSetAvailability = true;
        
        if ($user && $user->firm_id) {
            $lawFirm = \App\Models\User::find($user->firm_id);
            if ($lawFirm && $lawFirm->lawFirmProfile && !$lawFirm->lawFirmProfile->allow_lawyer_availability) {
                $this->canSetAvailability = false;
                
                // If trying to access 'availability' tab but not allowed, switch to consultations
                if ($this->activeTab === 'availability') {
                    $this->activeTab = 'consultations';
                    session()->flash('error', 'Access to manage availability has been restricted by your firm. Kindly refer to your firm for details.');
                }
            }
        }
    }

    public function updated($name, $value)
    {
        // Debug when property is updated
        if (strpos($name, 'selectedDates.') === 0) {
            session()->flash('message', 'Date selected: ' . $value);
        }
    }

    public function showCustomLinkForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->showCustomLinkModal = true;
    }

    public function saveCustomMeetingLink($consultationId = null)
    {
        // Validate custom meeting link
        $this->validate([
            'customMeetingLink' => 'required|url'
        ]);
        
        // Determine which consultation to update (inline or modal)
        $id = $consultationId ?: $this->selectedConsultation;
        $consultation = Consultation::findOrFail($id);
        
        // Check if the lawyer is either directly assigned, assigned through a law firm, or belongs to the law firm
        $user = auth()->user();
        $isAuthorized = $consultation->lawyer_id === $user->id || 
                        $consultation->specific_lawyer_id === $user->id ||
                        ($user->firm_id && $consultation->lawyer_id === $user->firm_id);

        if (!$isAuthorized) {
            session()->flash('error', 'You are not authorized to update this consultation.');
            return;
        }

        // Update the meeting link
        $consultation->update([
            'meeting_link' => $this->customMeetingLink
        ]);

        // Send notification to the client
        NotificationService::consultationLinkUpdated($consultation);

        // Reset input state and hide modal
        $this->customMeetingLink = '';
        $this->selectedConsultation = null;
        $this->showCustomLinkModal = false;
        session()->flash('message', 'Meeting link updated successfully.');
        $this->dispatch('notification-received');
    }

    public function acceptConsultation($consultationId)
    {   
        $consultation = Consultation::findOrFail($consultationId);
        
        if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
            session()->flash('error', 'You are not authorized to accept this consultation.');
            return;
        }

        // Check if there is a selected date for this consultation
        if (empty($this->selectedDates) || !isset($this->selectedDates[$consultationId]) || empty($this->selectedDates[$consultationId])) {
            // Try to get the first preferred date as a fallback
            $preferredDates = json_decode($consultation->preferred_dates);
            if (!empty($preferredDates)) {
                $selectedDate = $preferredDates[0];
            } else {
                session()->flash('error', 'No preferred dates available. Cannot accept consultation.');
                return;
            }
        } else {
            $selectedDate = $this->selectedDates[$consultationId];
        }

        // Parse the selected date to get start and end times
        $startDateTime = \Carbon\Carbon::parse($selectedDate);
        $endDateTime = $startDateTime->copy()->addHour(); // Default 1-hour consultation
        
        // If consultation already has specific start/end times, use those
        if ($consultation->start_time && $consultation->end_time) {
            $startDateTime = \Carbon\Carbon::parse($consultation->start_time);
            $endDateTime = \Carbon\Carbon::parse($consultation->end_time);
        }

        // Check for time slot conflicts
        $lawyerId = $consultation->specific_lawyer_id ?: $consultation->lawyer_id;
        if (\App\Models\BlockedTimeSlot::hasConflict($lawyerId, $startDateTime, $endDateTime)) {
            session()->flash('error', 'This time slot conflicts with another consultation or blocked time. Please choose a different time.');
            return;
        }

        // First, accept with a default or custom meeting link
        $consultation->update([
            'status' => 'accepted',
            'selected_date' => $selectedDate,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'meeting_link' => $consultation->consultation_type === 'Online Consultation'
                ? ($this->customMeetingLink ?: $this->generateMeetingLink())
                : null
        ]);

        // Create blocked time slot to prevent double booking
        \App\Models\BlockedTimeSlot::createForConsultation($consultation);

        // If custom link was used, reset it
        $this->customMeetingLink = '';

        // Send notification to the client
        NotificationService::consultationAccepted($consultation);

        // Dispatch Livewire event for real-time updates
        $this->dispatch('notification-received');
        $this->dispatch('consultation-accepted');
        
        // If this is an online consultation, prompt to provide a custom link
        if ($consultation->consultation_type === 'Online Consultation') {
            $this->selectedConsultation = $consultationId;
            $this->showCustomLinkModal = true;
            session()->flash('message', 'Consultation accepted. You can now provide a custom meeting link or use the automatically generated one.');
        } else {
            session()->flash('message', 'Consultation request accepted successfully.');
        }
    }

    public function showDeclineForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->showDeclineModal = true;
    }

    public function declineConsultation()
    {
        $this->validate([
            'declineReason' => 'required|min:10'
        ]);

        $consultation = Consultation::findOrFail($this->selectedConsultation);
        
        if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
            session()->flash('error', 'You are not authorized to decline this consultation.');
            return;
        }

        $consultation->update([
            'status' => 'declined',
            'decline_reason' => $this->declineReason
        ]);

        // Remove any blocked time slots for this consultation
        if ($consultation->blockedTimeSlot) {
            $consultation->blockedTimeSlot->delete();
        }

        // Send notification to the client
        NotificationService::consultationDeclined($consultation);

        $this->showDeclineModal = false;
        $this->declineReason = '';
        $this->selectedConsultation = null;

        // Dispatch Livewire event for real-time updates
        $this->dispatch('notification-received');
        $this->dispatch('consultation-declined');
        
        session()->flash('message', 'Consultation request declined.');
    }

    public function showCompleteForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->consultationResults = '';
        $this->meetingNotes = '';
        $this->showCompleteModal = true;
    }

    public function markConsultationComplete()
    {
        // Simple validation
        if (empty($this->consultationResults)) {
            session()->flash('error', 'Please provide consultation results before completing.');
            return;
        }

        if (strlen($this->consultationResults) < 10) {
            session()->flash('error', 'Please provide more detailed consultation results.');
            return;
        }

        try {
            $consultation = Consultation::find($this->selectedConsultation);
            
            if (!$consultation) {
                session()->flash('error', 'Consultation not found.');
                return;
            }

            // Check authorization
            if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
                session()->flash('error', 'You are not authorized to complete this consultation.');
                return;
            }

            // Update consultation
            $consultation->status = 'completed';
            $consultation->is_completed = true;
            $consultation->consultation_results = $this->consultationResults;
            $consultation->meeting_notes = $this->meetingNotes;
            $consultation->can_start_case = true;
            $consultation->save();

            // Handle document upload if provided
            if ($this->consultationDocument) {
                $path = $this->consultationDocument->store('consultation-documents', 'public');
                $consultation->consultation_document_path = $path;
                $consultation->save();
            }

            // Remove blocked time slot
            if ($consultation->blockedTimeSlot) {
                $consultation->blockedTimeSlot->delete();
            }

            // Send notification
            try {
                NotificationService::consultationCompleted($consultation);
            } catch (\Exception $e) {
                \Log::warning('Failed to send completion notification: ' . $e->getMessage());
            }

            // Reset form and close modal
            $this->showCompleteModal = false;
            $this->consultationResults = '';
            $this->meetingNotes = '';
            $this->consultationDocument = null;

            session()->flash('message', 'Consultation completed successfully! You can now start a case with this client.');

        } catch (\Exception $e) {
            \Log::error('Error completing consultation', [
                'error' => $e->getMessage(),
                'consultation_id' => $this->selectedConsultation,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('error', 'Failed to complete consultation. Please try again.');
        }
    }

    public function openStartCaseModal($consultationId)
    {
        $consultation = Consultation::find($consultationId);

        if (!$consultation) {
            session()->flash('error', 'Consultation not found.');
            return;
        }

        if ($consultation->status !== 'completed') {
            session()->flash('error', 'Consultation must be completed first.');
            return;
        }
        
        // Check authorization
        if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
            session()->flash('error', 'You are not authorized to start a case for this consultation.');
            return;
        }

        $this->selectedConsultation = $consultationId;
        $this->caseTitle = '';
        $this->caseDescription = '';
        $this->contractDocument = null;
        $this->showStartCaseModal = true;
    }

    public function createNewCase()
    {
        // Simple validation
        if (empty($this->caseTitle)) {
            session()->flash('error', 'Please provide a case title.');
            return;
        }

        if (empty($this->caseDescription)) {
            session()->flash('error', 'Please provide a case description.');
            return;
        }

        if (!$this->contractDocument) {
            session()->flash('error', 'Please upload a contract document.');
            return;
        }

        try {
            $consultation = Consultation::find($this->selectedConsultation);
            
            if (!$consultation) {
                session()->flash('error', 'Consultation not found.');
                return;
            }

            // Check authorization
            if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
                session()->flash('error', 'You are not authorized to start a case for this consultation.');
                return;
            }

            // Check if case already exists
            $existingCase = LegalCase::where('consultation_id', $consultation->id)->first();
            if ($existingCase) {
                session()->flash('error', 'A case already exists for this consultation.');
                return;
            }

            // Store contract document
            $contractPath = $this->contractDocument->store('contracts', 'public');

            // Create the case
            $case = LegalCase::create([
                'consultation_id' => $consultation->id,
                'client_id' => $consultation->client_id,
                'lawyer_id' => auth()->id(),
                'title' => $this->caseTitle,
                'description' => $this->caseDescription,
                'status' => 'contract_sent',
                'case_completion' => 'pending',
                'contract_path' => $contractPath,
                'contract_status' => 'sent',
                'case_number' => 'CASE-' . date('Ymd') . '-' . uniqid()
            ]);

            // Send notification
            try {
                NotificationService::caseStarted($case);
            } catch (\Exception $e) {
                \Log::warning('Failed to send case notification: ' . $e->getMessage());
            }

            // Reset form and close modal
            $this->showStartCaseModal = false;
            $this->caseTitle = '';
            $this->caseDescription = '';
            $this->contractDocument = null;

            session()->flash('message', 'Case created successfully! The client has been notified.');

        } catch (\Exception $e) {
            \Log::error('Error creating case', [
                'error' => $e->getMessage(),
                'consultation_id' => $this->selectedConsultation,
                'user_id' => auth()->id()
            ]);
            
            session()->flash('error', 'Failed to create case. Please try again.');
        }
    }

    public function showReviewContractModal($consultationId)
    {
        $consultation = Consultation::with('case.client.clientProfile')->findOrFail($consultationId); // Eager load client profile for display if needed
        if ($consultation->case) {
            $this->selectedConsultationForReview = $consultation;
            $this->reviewCaseTitle = $consultation->case->title;
            $this->reviewCaseDescription = $consultation->case->description;
            $this->reviewContractPath = $consultation->case->contract_path;
            $this->resetValidation();
            $this->showReviewContractModal = true;
        } else {
            session()->flash('error', 'No case contract found for this consultation.');
            $this->selectedConsultationForReview = null; // Ensure it's reset
        }
    }

    protected function generateMeetingLink()
    {
        // This is a placeholder. In a real implementation, 
        // this would integrate with a video conferencing service
        return 'https://meet.lexcav.com/' . uniqid();
    }

    public function showGoogleMeetLinkInput($consultationId)
    {
        $this->showGoogleMeetInput[$consultationId] = true;
        $consultation = Consultation::findOrFail($consultationId);
        $this->googleMeetLink = $consultation->meeting_link; // Pre-fill with current link
    }

    public function addMeetingLink($consultationId)
    {
        // Add debugging to see if the method is being called and with what value
        \Illuminate\Support\Facades\Log::info('Adding meeting link', [
            'consultation_id' => $consultationId,
            'google_meet_link' => $this->googleMeetLink
        ]);

        $this->validate([
            'googleMeetLink' => 'required|url',
        ], [
            'googleMeetLink.required' => 'Please enter a meeting link.',
            'googleMeetLink.url' => 'Please enter a valid URL (e.g., https://meet.google.com/xxx or https://zoom.us/j/xxx).',
        ]);

        $consultation = Consultation::findOrFail($consultationId);
        
        // Check if the lawyer is either directly assigned, assigned through a law firm, or belongs to the law firm
        $user = auth()->user();
        $isAuthorized = $consultation->lawyer_id === $user->id || 
                        $consultation->specific_lawyer_id === $user->id ||
                        ($user->firm_id && $consultation->lawyer_id === $user->firm_id);

        if (!$isAuthorized) {
            session()->flash('error', 'You are not authorized to update this consultation.');
            return;
        }
        
        if ($consultation->consultation_type !== 'Online Consultation' || $consultation->status !== 'accepted') {
            session()->flash('error', 'Meeting links can only be added to accepted online consultations.');
            return;
        }
        
        // Update the consultation with the provided meeting link
        $consultation->update([
            'meeting_link' => $this->googleMeetLink
        ]);
        
        // Send notification to the client
        NotificationService::consultationLinkUpdated($consultation);
        
        // Reset the input and hide the input field
        $this->googleMeetLink = '';
        $this->showGoogleMeetInput[$consultationId] = false;
        
        session()->flash('message', 'Meeting link updated successfully.');
        $this->dispatch('notification-received');
    }

    public function refreshConsultation($consultationId)
    {
        // This method will be called by the frontend to refresh a specific consultation
        // The component itself will be refreshed through Livewire's automatic rendering
        \Illuminate\Support\Facades\Log::info('Refreshing consultation', [
            'consultation_id' => $consultationId
        ]);
    }

    public function updateMeetingLink($consultationId)
    {
        // Add debugging to see if the method is being called
        \Illuminate\Support\Facades\Log::info('Direct method called for meeting link update', [
            'consultation_id' => $consultationId,
            'google_meet_link' => $this->googleMeetLink
        ]);

        // Validate the link
        $this->validate([
            'googleMeetLink' => 'required|url',
        ], [
            'googleMeetLink.required' => 'Please enter a meeting link.',
            'googleMeetLink.url' => 'Please enter a valid URL.',
        ]);

        try {
            // Find the consultation
            $consultation = Consultation::findOrFail($consultationId);
            
            // Check authorization
            if ($consultation->lawyer_id !== auth()->id() && $consultation->specific_lawyer_id !== auth()->id()) {
                session()->flash('error', 'You are not authorized to update this consultation.');
                return;
            }
            
            // Check consultation type and status
            if ($consultation->consultation_type !== 'Online Consultation' || $consultation->status !== 'accepted') {
                session()->flash('error', 'Meeting links can only be added to accepted online consultations.');
                return;
            }
            
            // Store the link before updating
            $newLink = $this->googleMeetLink;
            
            // Directly update the meeting link
            $consultation->meeting_link = $newLink;
            $consultation->save();
            
            // Reload the consultation to ensure we have the latest data
            $consultation = Consultation::findOrFail($consultationId);
            
            // Send notification
            NotificationService::consultationLinkUpdated($consultation);
            
            // Reset input but keep the consultation model updated in the view
            $this->googleMeetLink = '';
            $this->showGoogleMeetInput[$consultationId] = false;
            
            session()->flash('message', 'Meeting link successfully updated!');
            
            // Trigger a full component refresh to ensure the UI shows the updated link
            $this->dispatch('refreshConsultations');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating meeting link', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to update meeting link: ' . $e->getMessage());
        }
        
        $this->dispatch('notification-received');
    }

    public function render()
    {
        $userId = auth()->id();
        
        // Get consultations where the lawyer is either directly assigned (lawyer_id)
        // or specifically assigned by a law firm (specific_lawyer_id)
        $consultations = Consultation::where(function($query) use ($userId) {
                // Directly assigned to the lawyer
                $query->where('lawyer_id', $userId)
                // OR specifically assigned to the lawyer by a law firm
                ->orWhere('specific_lawyer_id', $userId);
            })
            ->with(['client.clientProfile', 'lawyer.lawFirmProfile', 'case']) // Eager load the 'case' relationship
            ->latest()
            ->paginate(10);

        return view('livewire.lawyers.manage-consultations', [
            'consultations' => $consultations,
            'activeTab' => $this->activeTab
        ])->layout('components.layouts.app', [
            'header' => 'Manage Consultations',
            'title' => 'Manage Consultations'
        ]);
    }
} 