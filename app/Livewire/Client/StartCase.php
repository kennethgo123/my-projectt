<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\LegalService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StartCase extends Component
{
    public $title;
    public $description;
    public $lawyer_id;
    public $service_id;
    
    public $lawyerName;
    public $lawyerEmail;
    
    protected $rules = [
        'title' => 'required|min:5|max:255',
        'description' => 'required|min:10',
        'service_id' => 'required|exists:legal_services,id',
        'lawyer_id' => 'required|exists:users,id',
    ];

    protected $messages = [
        'title.required' => 'Please provide a title for your case.',
        'description.required' => 'Please describe your case in detail.',
        'service_id.required' => 'Please select the type of legal service you need.',
        'lawyer_id.required' => 'Please select a lawyer for your case.',
    ];
    
    public function mount($lawyer_id = null)
    {
        $this->lawyer_id = $lawyer_id;
        
        if ($this->lawyer_id) {
            $lawyer = User::findOrFail($this->lawyer_id);
            $this->lawyerName = $lawyer->name;
            $this->lawyerEmail = $lawyer->email;
        }
    }
    
    public function createCase()
    {
        $this->validate();
        
        try {
            $case = LegalCase::create([
                'title' => $this->title,
                'description' => $this->description,
                'status' => LegalCase::STATUS_PENDING,
                'lawyer_id' => $this->lawyer_id,
                'client_id' => Auth::id(),
                'service_id' => $this->service_id,
                'case_number' => LegalCase::generateCaseNumber(),
                'contract_status' => LegalCase::CONTRACT_STATUS_PENDING
            ]);
            
            session()->flash('message', 'Case created successfully! The lawyer will be notified and will review your case.');
            
            return redirect()->route('client.cases');
        } catch (\Exception $e) {
            session()->flash('error', 'There was an error creating your case. Please try again.');
        }
    }
    
    public function render()
    {
        $services = LegalService::active()->orderBy('name')->get();
        
        return view('livewire.client.start-case', [
            'services' => $services,
        ])->layout('components.layouts.app');
    }
} 