<?php

namespace App\Livewire\LawFirm;

use Livewire\Component;
use App\Models\Consultation;
use App\Models\User;
use App\Models\LawFirmLawyer;
use App\Models\BlockedTimeSlot;
use App\Services\NotificationService;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ManageConsultations extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $status = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    // Variables for lawyer assignment
    public $firmLawyers = [];
    public $assignedLawyerId = null;
    
    // Variables for consultation details modal
    public $showDetailsModal = false;
    public $consultationDetails = null;
    public $selectedDate = null;
    public $customMeetingLink = '';
    public $showCustomLinkModal = false;
    public $selectedConsultation = null;
    
    // Variables for consultation completion
    public $showCompleteModal = false;
    public $consultationResults = '';
    public $meetingNotes = '';
    public $consultationDocument = null;
    
    // Variables for case creation
    public $showStartCaseModal = false;
    public $caseTitle = '';
    public $caseDescription = '';
    public $contractDocument = null;

    protected $listeners = ['refreshConsultations' => '$refresh'];
    
    // In Livewire 3, we also need to listen for dispatched events
    public function getListeners()
    {
        return [
            'refreshConsultations' => '$refresh',
            'echo:consultation.assigned,ConsultationAssigned' => '$refresh',
            'echo:consultation.updated,ConsultationUpdated' => '$refresh',
            'refresh' => '$refresh',
            'consultation-completed' => '$refresh'
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function openDetailsModal($consultationId)
    {
        // Get a fresh instance of the consultation with latest data
        $this->consultationDetails = Consultation::with([
            'client.clientProfile', 
            'lawyer.lawyerProfile'
        ])->findOrFail($consultationId);
        
        // Load firm lawyers for the dropdown in the details modal
        $this->loadFirmLawyers();
        
        // Reset selections
        $this->assignedLawyerId = null;
        $this->selectedDate = null;
        
        // Reset the view when opening the modal
        $this->showDetailsModal = true;
    }

    public function assignLawyerWithTime()
    {
        if (!$this->consultationDetails) {
            session()->flash('error', 'No consultation selected.');
            return;
        }

        if (!$this->assignedLawyerId) {
            session()->flash('error', 'Please select a lawyer or assign to firm.');
            return;
        }

        if (!$this->selectedDate) {
            session()->flash('error', 'Please select a consultation time.');
            return;
        }

        // Get a fresh instance of the consultation to prevent stale data
        $consultation = Consultation::findOrFail($this->consultationDetails->id);
        
        // Parse the selected date to get start and end times
        $startDateTime = \Carbon\Carbon::parse($this->selectedDate);
        $endDateTime = $startDateTime->copy()->addHour(); // Default 1-hour consultation
        
        // If consultation already has specific start/end times, use those
        if ($consultation->start_time && $consultation->end_time) {
            $startDateTime = \Carbon\Carbon::parse($consultation->start_time);
            $endDateTime = \Carbon\Carbon::parse($consultation->end_time);
        }
        
        // Check if assigning to firm as entity
        if ($this->assignedLawyerId === '__firm__') {
            // For firm entity assignments, use the main lawyer ID for time slot checking
            $lawyerId = $consultation->lawyer_id;
            
            // Check for time slot conflicts
            if (\App\Models\BlockedTimeSlot::hasConflict($lawyerId, $startDateTime, $endDateTime)) {
                session()->flash('error', 'This time slot conflicts with another consultation or blocked time. Please choose a different time.');
                return;
            }
            
            $updated = $consultation->update([
                'specific_lawyer_id' => null,
                'selected_date' => $this->selectedDate,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'assign_as_entity' => true,
                'status' => 'accepted',
            ]);
        } else {
            // Assigning to specific lawyer
            $lawyer = User::findOrFail($this->assignedLawyerId);
            
            // Check for time slot conflicts with the assigned lawyer
            if (\App\Models\BlockedTimeSlot::hasConflict($this->assignedLawyerId, $startDateTime, $endDateTime)) {
                session()->flash('error', 'This time slot conflicts with another consultation or blocked time for the selected lawyer. Please choose a different time.');
                return;
            }
            
            $updated = $consultation->update([
                'specific_lawyer_id' => $this->assignedLawyerId,
                'selected_date' => $this->selectedDate,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'assign_as_entity' => false,
                'status' => 'accepted',
            ]);
            
            // Send notification to the assigned lawyer
            NotificationService::consultationAssigned($consultation, $lawyer);
        }
        
        // For debugging
        if (!$updated) {
            session()->flash('error', 'Failed to update consultation. Please check if specific_lawyer_id and assign_as_entity are fillable in the Consultation model.');
            return;
        }
        
        // Create blocked time slot to prevent double booking
        \App\Models\BlockedTimeSlot::createForConsultation($consultation);
        
        // Refresh the consultation details to ensure we have the latest data
        $this->consultationDetails = Consultation::findOrFail($consultation->id);
        
        // Close modal and reset
        $this->showDetailsModal = false;
        $this->consultationDetails = null;
        $this->assignedLawyerId = null;
        $this->selectedDate = null;
        
        session()->flash('message', 'Consultation has been assigned and accepted successfully!');
        
        // Force a complete refresh to update the UI
        $this->dispatch('refreshConsultations');
    }

    public function loadFirmLawyers()
    {
        // Get current law firm ID
        $lawFirmId = auth()->user()->id;
        
        // Get all lawyers associated with this firm - checking both relationships
        $this->firmLawyers = User::where(function($query) use ($lawFirmId) {
                // Check for lawyers with law_firm_id in their profile
                $query->whereHas('lawyerProfile', function($profileQuery) use ($lawFirmId) {
                    $profileQuery->where('law_firm_id', $lawFirmId);
                })
                // OR check for lawyers with firm_id directly in users table
                ->orWhere('firm_id', $lawFirmId);
            })
            ->where('status', 'approved')
            ->whereHas('role', function($query) {
                $query->where('name', 'lawyer');
            })
            ->with('lawyerProfile')
            ->get();
            
        // If still no lawyers found, check for LawFirmLawyer relationships
        if ($this->firmLawyers->isEmpty()) {
            $lawFirmProfileId = auth()->user()->lawFirmProfile->id ?? null;
            
            if ($lawFirmProfileId) {
                $lawFirmLawyers = \App\Models\LawFirmLawyer::where('law_firm_profile_id', $lawFirmProfileId)
                    ->where('status', 'active')
                    ->with('user')
                    ->get();
                    
                $this->firmLawyers = $lawFirmLawyers->map(function($lawFirmLawyer) {
                    return $lawFirmLawyer->user;
                })->filter();
            }
        }
    }

    public function showCustomLinkForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->customMeetingLink = '';
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
        
        if (!$id) {
            session()->flash('error', 'No consultation selected.');
            return;
        }
        
        $consultation = Consultation::findOrFail($id);
        
        // Check if the law firm is authorized
        $user = auth()->user();
        if ($consultation->lawyer_id !== $user->id) {
            session()->flash('error', 'You are not authorized to update this consultation.');
            return;
        }

        try {
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
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update meeting link. Please try again.');
        }
    }

    public function acceptConsultation($consultationId)
    {   
        $consultation = Consultation::findOrFail($consultationId);
        
        // Check if the law firm is authorized
        $user = auth()->user();
        if ($consultation->lawyer_id !== $user->id) {
            session()->flash('error', 'You are not authorized to accept this consultation.');
            return;
        }

        // Check if there is a selected date for this consultation
        if (empty($this->selectedDate)) {
            // Try to get the first preferred date as a fallback
            $preferredDates = json_decode($consultation->preferred_dates);
            if (!empty($preferredDates)) {
                $selectedDate = $preferredDates[0];
            } else {
                session()->flash('error', 'No preferred dates available. Cannot accept consultation.');
                return;
            }
        } else {
            $selectedDate = $this->selectedDate;
        }

        // First, accept with a default or custom meeting link
        $consultation->update([
            'status' => 'accepted',
            'selected_date' => $selectedDate,
            'meeting_link' => $consultation->consultation_type === 'Online Consultation'
                ? ($this->customMeetingLink ?: $this->generateMeetingLink())
                : null
        ]);

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

    protected function generateMeetingLink()
    {
        // This is a placeholder. In a real implementation, 
        // this would integrate with a video conferencing service
        return 'https://meet.lexcav.com/' . uniqid();
    }

    public function showCompleteForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->consultationResults = '';
        $this->meetingNotes = '';
        $this->consultationDocument = null;
        $this->showCompleteModal = true;
    }

    public function completeConsultation()
    {
        // Validate required fields with stricter requirements
        $this->validate([
            'consultationResults' => 'required|min:20',
            'consultationDocument' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png'
        ], [
            'consultationResults.required' => 'Consultation results and findings are required before completing the consultation.',
            'consultationResults.min' => 'Please provide more detailed results and findings (at least 20 characters).'
        ]);

        $consultation = Consultation::findOrFail($this->selectedConsultation);
        
        // Check if the law firm is authorized
        $user = auth()->user();
        if ($consultation->lawyer_id !== $user->id) {
            session()->flash('error', 'You are not authorized to complete this consultation.');
            return;
        }

        // Prepare update data
        $updateData = [
            'status' => 'completed',
            'is_completed' => true,
            'consultation_results' => $this->consultationResults,
            'meeting_notes' => $this->meetingNotes,
            'can_start_case' => true
        ];

        // Store the consultation document if provided
        if ($this->consultationDocument) {
            $consultationDocPath = $this->consultationDocument->store('consultation-documents', 'public');
            $updateData['consultation_document_path'] = $consultationDocPath;
        }

        try {
            // Update the consultation record
            $consultation->update($updateData);

            // Remove blocked time slot since consultation is completed
            if ($consultation->blockedTimeSlot) {
                $consultation->blockedTimeSlot->delete();
            }

            // Send notification to the client
            NotificationService::consultationCompleted($consultation);

            $this->showCompleteModal = false;
            $this->consultationResults = '';
            $this->meetingNotes = '';
            $this->consultationDocument = null;
            
            // Dispatch Livewire event for real-time updates
            $this->dispatch('notification-received');
            $this->dispatch('consultation-completed');
            
            session()->flash('message', 'Consultation marked as completed and results saved. You can now start a case with this client.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to complete consultation. Please try again.');
        }
    }

    public function showStartCaseForm($consultationId)
    {
        $this->selectedConsultation = $consultationId;
        $this->resetValidation();
        $this->caseTitle = '';
        $this->caseDescription = '';
        $this->contractDocument = null;
        $this->showStartCaseModal = true;
    }

    public function startCase()
    {
        $this->validate([
            'caseTitle' => 'required|min:5|max:255',
            'caseDescription' => 'required|min:10',
            'contractDocument' => 'required|file|max:10240|mimes:pdf'
        ]);

        $consultation = Consultation::findOrFail($this->selectedConsultation);
        
        // Check if the law firm is authorized
        $user = auth()->user();
        if ($consultation->lawyer_id !== $user->id) {
            session()->flash('error', 'You are not authorized to start a case for this consultation.');
            return;
        }

        try {
            // Store the contract document
            $contractPath = $this->contractDocument->store('contracts', 'public');

            // Create a new case
            $case = \App\Models\LegalCase::create([
                'title' => $this->caseTitle,
                'description' => $this->caseDescription,
                'status' => 'contract_sent',
                'case_completion' => 'pending',
                'lawyer_id' => $user->id,
                'client_id' => $consultation->client_id,
                'contract_path' => $contractPath,
                'contract_status' => 'sent',
                'consultation_id' => $consultation->id,
                'case_number' => 'CASE-' . date('Ymd') . '-' . uniqid()
            ]);

            // Send notification to the client
            NotificationService::caseStarted($case);

            $this->showStartCaseModal = false;
            $this->caseTitle = '';
            $this->caseDescription = '';
            $this->contractDocument = null;
            
            session()->flash('message', 'Case created successfully! The contract has been sent to the client for review.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create case. Please try again.');
        }
    }

    public function render()
    {
        // Get current law firm ID
        $lawFirmId = auth()->user()->id;
        
        // Ensure a fresh query with no caching
        $consultationsQuery = Consultation::where(function($query) use ($lawFirmId) {
            $query->where('lawyer_id', $lawFirmId)
                ->orWhereHas('lawyer', function ($subQuery) use ($lawFirmId) {
                    $subQuery->whereHas('lawyerProfile', function ($profileQuery) use ($lawFirmId) {
                        $profileQuery->where('law_firm_id', $lawFirmId);
                    });
                })
                // OR consultations where a specific lawyer from this firm was assigned
                ->orWhereHas('specificLawyer.lawyerProfile', function($profileQuery) use ($lawFirmId) {
                    $profileQuery->where('law_firm_id', $lawFirmId);
                });
        })
        ->with([
            'client.clientProfile', 
            'lawyer.lawyerProfile', 
            'lawyer.lawFirmProfile',
            'case'
        ])
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->whereHas('client.clientProfile', function ($clientQuery) {
                      $clientQuery->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->search . '%');
                  });
            });
        })
        ->when($this->status, function ($query) {
            $query->where('status', $this->status);
        })
        ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.law-firm.manage-consultations', [
            'consultations' => $consultationsQuery->paginate(10),
        ]);
    }
} 