<?php

namespace App\Livewire\LawFirm;

use App\Models\LegalCase;
use App\Models\LegalService;
use App\Models\User;
use App\Models\Consultation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class StartCase extends Component
{
    use WithFileUploads;

    public $title;
    public $description;
    public $consultation_id;
    public $client_id;
    public $service_id;
    public $contract;
    
    public $clientName;
    public $clientEmail;
    
    protected $rules = [
        'title' => 'required|min:5|max:255',
        'description' => 'required|min:10',
        'service_id' => 'required|exists:legal_services,id',
        'client_id' => 'required|exists:users,id',
        'contract' => 'nullable|file|max:10240|mimes:pdf'
    ];

    protected $messages = [
        'title.required' => 'Please provide a title for the case.',
        'description.required' => 'Please describe the case in detail.',
        'service_id.required' => 'Please select the type of legal service needed.',
        'client_id.required' => 'Please select a client for this case.',
        'contract.max' => 'The contract file must not be larger than 10MB.',
        'contract.mimes' => 'The contract must be a PDF file.'
    ];
    
    public function mount($consultation = null)
    {
        if ($consultation) {
            $this->consultation_id = $consultation;
            $consultation = Consultation::findOrFail($this->consultation_id);
            
            // Pre-fill the form with consultation data
            $this->client_id = $consultation->client_id;
            $this->service_id = $consultation->service_id;
            $this->title = 'Case for ' . $consultation->service->name ?? 'Legal Service';
            $this->description = $consultation->notes ?? '';
            
            // Get client details
            $client = User::findOrFail($this->client_id);
            $this->clientName = $client->name;
            $this->clientEmail = $client->email;
            
            // If client has a profile, use the name from there
            if ($client->clientProfile) {
                $this->clientName = $client->clientProfile->first_name . ' ' . $client->clientProfile->last_name;
            }
        }
    }
    
    public function createCase()
    {
        $this->validate();
        
        try {
            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'status' => LegalCase::STATUS_ACCEPTED,
                'lawyer_id' => Auth::id(), // Law firm is the lawyer
                'client_id' => $this->client_id,
                'service_id' => $this->service_id,
                'case_number' => LegalCase::generateCaseNumber(),
                'contract_status' => LegalCase::CONTRACT_STATUS_PENDING,
                'consultation_id' => $this->consultation_id
            ];
            
            // Create the case
            $case = LegalCase::create($data);
            
            // If contract was uploaded
            if ($this->contract) {
                $path = $this->contract->store('contracts', 'public');
                $case->contract_path = $path;
                $case->contract_status = LegalCase::CONTRACT_STATUS_SENT;
                $case->status = LegalCase::STATUS_CONTRACT_SENT;
                $case->save();
                
                // Create contract action record
                $case->contractActions()->create([
                    'action_type' => 'sent',
                    'user_id' => Auth::id(),
                    'notes' => 'Contract sent to client'
                ]);
                
                // Create a case update
                $case->caseUpdates()->create([
                    'title' => 'Contract Sent',
                    'content' => 'Contract has been sent to the client for review and signature.',
                    'user_id' => Auth::id(),
                    'visibility' => 'both'
                ]);
            }
            
            // If a consultation was associated, mark it as converted to a case
            if ($this->consultation_id) {
                $consultation = Consultation::find($this->consultation_id);
                if ($consultation) {
                    $consultation->update([
                        'status' => 'completed',
                        'can_start_case' => false
                    ]);
                }
            }
            
            session()->flash('message', 'Case created successfully! The client will be notified.');
            
            return redirect()->route('law-firm.cases');
        } catch (\Exception $e) {
            session()->flash('error', 'There was an error creating the case: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        $services = LegalService::active()->orderBy('name')->get();
        
        return view('livewire.law-firm.start-case', [
            'services' => $services,
        ]);
    }
} 