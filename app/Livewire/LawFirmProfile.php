<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Report;
use App\Models\LawFirmProfile as LawFirmProfileModel;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LawFirmProfile extends Component
{
    use WithFileUploads;

    public $user;
    public $lawFirm;

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

        // Ensure this is a law firm user
        if ($user->role->name !== 'law_firm' || !$user->lawFirmProfile) {
            abort(404, 'Law firm profile not found');
        }

        $this->user = $user;
        $this->lawFirm = $user->lawFirmProfile->load([
            'user.receivedLawFirmRatings' => function($query) {
                $query->where('is_visible', true);
            },
            'user.activeSubscription.plan',
            'services'
        ]);

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
            session()->flash('error', 'Only clients can report law firms.');
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

            // Create the report
            Report::create([
                'reporter_id' => Auth::id(),
                'reported_user_id' => $this->user->id,
                'reported_type' => 'law_firm',
                'reporter_name' => $this->reporterName,
                'reporter_email' => $this->reporterEmail,
                'reporter_phone' => $this->reporterPhone,
                'reported_name' => $this->lawFirm->firm_name,
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
        // Get lawyers associated with this law firm
        $lawyers = $this->lawFirm->lawyers()
            ->whereHas('user', function($q) {
                $q->where('status', 'approved')
                    ->where('profile_completed', true);
            })
            ->with([
                'user.receivedRatings' => function($query) {
                    $query->where('is_visible', true);
                },
                'services'
            ])
            ->get();

        return view('livewire.law-firm-profile', [
            'lawyers' => $lawyers
        ])->layout('layouts.app');
    }
} 