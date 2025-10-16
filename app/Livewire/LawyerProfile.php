<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Report;
use App\Models\LawyerProfile as LawyerProfileModel;
use App\Models\LawFirmLawyer;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LawyerProfile extends Component
{
    use WithFileUploads;

    public $user;
    public $lawyer;
    public $type;

    // Report modal properties
    public $showReportModal = false;
    public $reporterName = '';
    public $reporterEmail = '';
    public $reporterPhone = '';
    public $serviceDate = '';
    public $legalMatterType = '';
    public $category = '';
    public $description = '';
    public $timelineOfEvents = '';
    public $supportingDocuments = [];

    protected $rules = [
        'reporterName' => 'required|string|max:255',
        'reporterEmail' => 'required|email|max:255',
        'reporterPhone' => 'nullable|string|max:20',
        'serviceDate' => 'nullable|date|before_or_equal:today',
        'legalMatterType' => 'nullable|string|max:255',
        'category' => 'required|in:professional_misconduct,billing_disputes,communication_issues,ethical_violations,competency_concerns,other',
        'description' => 'required|string|min:50|max:2000',
        'timelineOfEvents' => 'nullable|string|max:2000',
        'supportingDocuments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240' // 10MB max per file
    ];

    public function mount(User $user)
    {
        // Check if the user is approved and profile is completed
        if ($user->status !== 'approved' || !$user->profile_completed) {
            abort(404, 'Profile not found');
        }

        $this->user = $user;

        // Determine if this is a regular lawyer or law firm lawyer
        if ($user->role->name === 'lawyer' && $user->lawyerProfile) {
            // Regular lawyer
            $this->lawyer = $user->lawyerProfile->load([
                'user.receivedRatings' => function($query) {
                    $query->where('is_visible', true);
                },
                'user.activeSubscription.plan',
                'lawFirm',
                'services'
            ]);
            $this->type = 'lawyer';
        } elseif ($user->role->name === 'law_firm') {
            // Check if this law firm has lawyers
            $lawFirmLawyer = LawFirmLawyer::where('user_id', $user->id)
                ->with([
                    'user.receivedRatings' => function($query) {
                        $query->where('is_visible', true);
                    },
                    'user.activeSubscription.plan',
                    'lawFirm',
                    'services'
                ])
                ->first();

            if ($lawFirmLawyer) {
                $this->lawyer = $lawFirmLawyer;
                $this->type = 'firmLawyer';
            } else {
                // This is a law firm profile, not an individual lawyer
                $this->lawyer = $user->lawFirmProfile->load([
                    'user.receivedLawFirmRatings' => function($query) {
                        $query->where('is_visible', true);
                    },
                    'user.activeSubscription.plan',
                    'services'
                ]);
                $this->type = 'lawFirm';
            }
        } else {
            abort(404, 'Profile not found');
        }

        // Pre-fill reporter information if user is authenticated
        if (Auth::check() && Auth::user()->role->name === 'client') {
            $clientProfile = Auth::user()->clientProfile;
            if ($clientProfile) {
                $this->reporterName = $clientProfile->first_name . ' ' . $clientProfile->last_name;
                $this->reporterEmail = Auth::user()->email;
                $this->reporterPhone = $clientProfile->phone ?? '';
            }
        }
    }

    public function openReportModal()
    {
        if (!Auth::check() || Auth::user()->role->name !== 'client') {
            session()->flash('error', 'Only clients can report lawyers.');
            return;
        }

        $this->showReportModal = true;
    }

    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->resetReportForm();
    }

    public function submitReport()
    {
        if (!Auth::check() || Auth::user()->role->name !== 'client') {
            session()->flash('error', 'Only clients can submit reports.');
            return;
        }

        $this->validate();

        try {
            // Handle file uploads
            $uploadedFiles = [];
            if (!empty($this->supportingDocuments)) {
                foreach ($this->supportingDocuments as $file) {
                    if ($file) {
                        $path = $file->store('reports/supporting-documents', 'public');
                        $uploadedFiles[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType()
                        ];
                    }
                }
            }

            // Determine reported name based on type
            $reportedName = '';
            $reportedType = 'lawyer';
            
            if ($this->type === 'lawyer') {
                $reportedName = $this->lawyer->first_name . ' ' . $this->lawyer->last_name;
            } elseif ($this->type === 'firmLawyer') {
                $reportedName = $this->lawyer->first_name . ' ' . $this->lawyer->last_name;
            } elseif ($this->type === 'lawFirm') {
                $reportedName = $this->lawyer->firm_name;
                $reportedType = 'law_firm';
            }

            // Create the report
            Report::create([
                'reporter_id' => Auth::id(),
                'reported_user_id' => $this->user->id,
                'reported_type' => $reportedType,
                'reporter_name' => $this->reporterName,
                'reporter_email' => $this->reporterEmail,
                'reporter_phone' => $this->reporterPhone,
                'reported_name' => $reportedName,
                'service_date' => $this->serviceDate ?: null,
                'legal_matter_type' => $this->legalMatterType ?: null,
                'category' => $this->category,
                'description' => $this->description,
                'timeline_of_events' => $this->timelineOfEvents ?: null,
                'supporting_documents' => !empty($uploadedFiles) ? $uploadedFiles : null,
                'status' => 'pending'
            ]);

            session()->flash('message', 'Your report has been submitted successfully. We will review it and take appropriate action.');
            $this->closeReportModal();

        } catch (\Exception $e) {
            session()->flash('error', 'There was an error submitting your report. Please try again.');
            \Log::error('Report submission error: ' . $e->getMessage());
        }
    }

    private function resetReportForm()
    {
        $this->serviceDate = '';
        $this->legalMatterType = '';
        $this->category = '';
        $this->description = '';
        $this->timelineOfEvents = '';
        $this->supportingDocuments = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.lawyer-profile')->layout('layouts.app');
    }
} 