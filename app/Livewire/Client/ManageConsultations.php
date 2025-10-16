<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Consultation;
use App\Models\LegalCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

class ManageConsultations extends Component
{
    use WithFileUploads;
    
    public $showStartCaseModal = false;
    public $showResultsModal = false;
    public $selectedConsultation = null;
    public $caseTitle;
    public $caseDescription;
    public $caseDocument = null;

    protected $rules = [
        'caseTitle' => 'required|min:5|max:255',
        'caseDescription' => 'required|min:10',
        'caseDocument' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
    ];

    protected $messages = [
        'caseTitle.required' => 'Please provide a title for your case.',
        'caseDescription.required' => 'Please describe your case in detail.',
        'caseDocument.max' => 'The document should not be larger than 10MB.',
        'caseDocument.mimes' => 'The document must be a PDF, DOC, or DOCX file.',
    ];

    public function viewConsultationResults(Consultation $consultation)
    {
        // Ensure the consultation belongs to the authenticated user
        if ($consultation->client_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to view these results.');
            return;
        }

        if ($consultation->status !== 'completed') {
            session()->flash('error', 'No results available for this consultation yet.');
            return;
        }

        $this->selectedConsultation = $consultation;
        $this->showResultsModal = true;
    }

    public function showStartCaseForm(Consultation $consultation)
    {
        // Ensure the consultation belongs to the authenticated user
        if ($consultation->client_id !== Auth::id()) {
            session()->flash('error', 'You are not authorized to perform this action.');
            return;
        }
        
        // Check if consultation is properly completed
        if (!$consultation->is_completed) {
            session()->flash('error', 'The consultation needs to be marked as complete before starting a case.');
            return;
        }
        
        // Check if lawyer has provided consultation results
        if (empty($consultation->consultation_results)) {
            session()->flash('error', 'Your lawyer needs to provide consultation results before you can start a case.');
            return;
        }
        
        // Check if lawyer has enabled starting a case
        if (!$consultation->can_start_case) {
            session()->flash('error', 'Your lawyer has not enabled case creation for this consultation yet.');
            return;
        }

        $this->selectedConsultation = $consultation;
        $this->caseTitle = '';
        $this->caseDescription = '';
        $this->showStartCaseModal = true;
        $this->showResultsModal = false;
    }

    public function startCase()
    {
        $this->validate();

        if (!$this->selectedConsultation) {
            session()->flash('error', 'No consultation selected.');
            return;
        }

        try {
            // Create case data array
            $caseData = [
                'title' => $this->caseTitle,
                'description' => $this->caseDescription,
                'status' => LegalCase::STATUS_PENDING,
                'lawyer_id' => $this->selectedConsultation->lawyer_id,
                'client_id' => Auth::id(),
                'case_number' => LegalCase::generateCaseNumber(),
                'contract_status' => LegalCase::CONTRACT_STATUS_PENDING,
                'consultation_id' => $this->selectedConsultation->id
            ];
            
            // If there's a service_id field on the consultation, include it
            if (isset($this->selectedConsultation->service_id)) {
                $caseData['service_id'] = $this->selectedConsultation->service_id;
            }

            // Handle document upload if provided
            if ($this->caseDocument) {
                $documentPath = $this->caseDocument->store('case-documents', 'public');
                $caseData['client_document_path'] = $documentPath;
            }

            // Create the case
            $case = LegalCase::create($caseData);

            session()->flash('message', 'Case request sent successfully! The lawyer will be notified and will review your request.');
            $this->showStartCaseModal = false;
            $this->reset(['selectedConsultation', 'caseTitle', 'caseDescription', 'caseDocument']);

            return redirect()->route('client.cases');
        } catch (\Exception $e) {
            Log::error("Error creating case: " . $e->getMessage());
            session()->flash('error', 'Could not create the case. Please try again.');
        }
    }

    public function render()
    {
        $consultations = Consultation::where('client_id', Auth::id())
            ->with(['lawyer.lawyerProfile', 'lawyer.lawFirmLawyer', 'lawyer.lawFirmProfile', 'case'])
            ->latest()
            ->get();
            
        return view('livewire.client.manage-consultations', [
            'consultations' => $consultations
        ]);
    }
}
