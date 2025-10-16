<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\ContractAction;
use App\Notifications\ContractActionNotification;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CaseDetails extends Component
{
    use WithFileUploads;

    public LegalCase $case;
    public $negotiationTerms;
    public $showNegotiateModal = false;
    public $activeTab = 'overview';

    protected $rules = [
        'negotiationTerms' => 'required|min:10|max:1000'
    ];

    public function mount(LegalCase $case)
    {
        if ($case->client_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this case.');
        }
        
        $this->case = $case->load([
            'lawyer.lawyerProfile',
            'caseUpdates',
            'contractActions'
        ]);
    }

    public function openNegotiateModal()
    {
        if ($this->case->status !== 'contract_sent' || $this->case->contract_status !== 'sent') {
            session()->flash('error', 'Contract is not available for negotiation at this time.');
            return;
        }
        $this->showNegotiateModal = true;
    }

    public function directAcceptContract()
    {
        try {
            DB::beginTransaction();
            
            // Basic validation
            if ($this->case->client_id !== Auth::id()) {
                throw new \Exception('You are not authorized to accept this contract.');
            }

            if ($this->case->contract_status !== 'sent') {
                throw new \Exception('Contract is not in a state that can be accepted.');
            }
            
            // Create contract action record
            ContractAction::create([
                'legal_case_id' => $this->case->id,
                'action_type' => 'accepted',
                'actor_type' => 'client',
                'actor_id' => Auth::id(),
                'details' => 'Contract accepted by client'
            ]);

            // Update case status directly
            $this->case->update([
                'status' => 'contract_signed',
                        'contract_status' => 'signed',
                        'contract_signed_at' => now(),
                        'lawyer_response_required' => false
                    ]);

            // Notify lawyer
            try {
            $this->case->lawyer->notify(new ContractActionNotification(
                $this->case,
                    'Contract Accepted',
                    'The client has accepted the contract.'
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send lawyer notification', [
                    'case_id' => $this->case->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();
            
            session()->flash('success', 'Contract accepted successfully!');
            
            // Refresh the page to show updated status
            return redirect()->route('client.case.details', $this->case->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Illuminate\Support\Facades\Log::error('Direct contract acceptance failed', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to accept contract: ' . $e->getMessage());
        }
    }

    public function submitNegotiation()
    {
        try {
            $this->validate();

            if ($this->case->client_id !== Auth::id()) {
                throw new \Exception('You are not authorized to negotiate this contract.');
            }

            if ($this->case->contract_status !== 'sent') {
                throw new \Exception('Contract is not in a state that can be negotiated.');
            }

            DB::beginTransaction();

            // Create contract action record
            ContractAction::create([
                'legal_case_id' => $this->case->id,
                'action_type' => 'negotiating',
                'actor_type' => 'client',
                'actor_id' => Auth::id(),
                'details' => $this->negotiationTerms
            ]);

            // Update case status
            $this->case->update([
                'status' => 'pending',
                'contract_status' => 'negotiating',
                'negotiation_terms' => $this->negotiationTerms,
                'lawyer_response_required' => true
            ]);

            // Notify lawyer
            try {
            $this->case->lawyer->notify(new ContractActionNotification(
                $this->case,
                'Contract Negotiation Requested',
                    'The client has requested changes to the contract.'
                ));
        } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send lawyer notification', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage()
            ]);
            }

            DB::commit();
            
            session()->flash('success', 'Your requested changes have been submitted successfully.');
            $this->showNegotiateModal = false;
            $this->negotiationTerms = '';
            
            return redirect()->route('client.case.details', $this->case->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Illuminate\Support\Facades\Log::error('Contract negotiation failed', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to submit changes: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.client.case-details');
    }
} 