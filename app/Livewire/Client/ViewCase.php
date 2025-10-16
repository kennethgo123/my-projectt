<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\ContractAction;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ViewCase extends Component
{
    use WithFileUploads;

    public LegalCase $case;
    public $signature;
    public $showSignModal = false;
    public $showNegotiateModal = false;
    public $negotiationTerms = '';
    public $agreementConfirmed = false;

    protected $rules = [
        'signature' => 'required|image|max:2048',
        'negotiationTerms' => 'required|min:10',
        'agreementConfirmed' => 'accepted'
    ];

    public function mount(LegalCase $case)
    {
        if ($case->client_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this case.');
        }
        
        $this->case = $case->load([
            'lawyer.lawyerProfile',
            'caseUpdates',
            'contractActions',
            'consultation'
        ]);
    }

    public function openSignModal()
    {
        if ($this->case->status !== 'contract_sent' || $this->case->contract_status !== 'sent') {
            session()->flash('error', 'Contract is not available for signing at this time.');
            return;
        }
        $this->showSignModal = true;
    }

    public function openNegotiateModal()
    {
        if ($this->case->status !== 'contract_sent' || $this->case->contract_status !== 'sent') {
            session()->flash('error', 'Contract is not available for negotiation at this time.');
            return;
        }
        $this->showNegotiateModal = true;
    }

    public function signContract()
    {
        $this->validate();

        try {
            $signaturePath = $this->signature->store('signatures', 'public');

            ContractAction::create([
                'case_id' => $this->case->id,
                'action_type' => 'sign',
                'signature_path' => $signaturePath,
                'performed_by' => Auth::id()
            ]);

            $this->case->update([
                'status' => 'contract_signed',
                'contract_status' => 'signed',
                'contract_signed_at' => now()
            ]);

            session()->flash('message', 'Contract signed successfully.');
            $this->showSignModal = false;
            $this->signature = null;
            $this->agreementConfirmed = false;
            
            return redirect()->route('client.case.view', $this->case->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to sign contract. Please try again.');
        }
    }

    public function negotiateContract()
    {
        $this->validate([
            'negotiationTerms' => 'required|min:10'
        ]);

        try {
            ContractAction::create([
                'case_id' => $this->case->id,
                'action_type' => 'negotiate',
                'negotiation_terms' => $this->negotiationTerms,
                'performed_by' => Auth::id()
            ]);

            $this->case->update([
                'status' => 'contract_negotiating',
                'contract_status' => 'negotiating'
            ]);

            session()->flash('message', 'Negotiation terms submitted successfully.');
            $this->showNegotiateModal = false;
            $this->negotiationTerms = '';
            
            return redirect()->route('client.case.view', $this->case->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to submit negotiation terms. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.client.view-case');
    }
} 