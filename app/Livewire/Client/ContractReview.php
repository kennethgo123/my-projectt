<?php

namespace App\Livewire\Client;

use App\Models\LegalCase;
use App\Models\ContractAction;
use App\Models\User;
use App\Notifications\ContractActionNotification;
use App\Services\NotificationService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

class ContractReview extends Component
{
    public LegalCase $case;
    public $signature;
    public $signatureText;
    public $agreementChecked = false;
    
    // New properties for modals
    public $showRejectModal = false;
    public $rejectionReason = '';
    public $showRequestChangesModal = false;
    public $requestedChanges = '';
    
    protected $rules = [
        'signature' => 'required',
        'signatureText' => 'required|min:3|max:255',
        'agreementChecked' => 'accepted',
    ];

    protected $messages = [
        'signature.required' => 'Please provide your signature by drawing in the signature box.',
        'signatureText.required' => 'Please type your full legal name.',
        'signatureText.min' => 'Your full legal name must be at least 3 characters.',
        'agreementChecked.accepted' => 'You must agree to the terms and conditions before signing.',
    ];

    public function mount(LegalCase $case)
    {
        // Ensure the user has permission to view this case
        if ($case->client_id !== Auth::id() || !in_array($case->status, [LegalCase::STATUS_CONTRACT_SENT, LegalCase::STATUS_CONTRACT_REVISED_SENT])) {
            session()->flash('error', 'This contract is not available for review or action at this time.');
            return redirect()->route('client.cases');
        }
        
        $this->case = $case->load([
            'lawyer',
            'caseUpdates',
            'contractActions'
        ]);
    }

    public function submitSignature()
    {
        // Validate both signature and text
        $this->validate();
        
        if (empty($this->signature)) {
            $this->addError('signature', 'Please provide your signature.');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Process signature
            $signaturePath = $this->saveSignature();
            
            // Create contract action
            $this->createContractAction($signaturePath);
            
            // Update case status
            $this->updateCaseStatus($signaturePath);
            
            // Create a fallback notification - don't try to use the notification system
            $this->createFallbackNotification();
            
            // Commit the transaction
            DB::commit();
            
            session()->flash('success', 'Contract has been successfully signed and accepted.');
            return redirect()->route('client.cases.show', $this->case->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract signing failed', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to sign contract: ' . $e->getMessage());
        }
    }
    
    protected function saveSignature()
    {
        try {
            // Process the signature data
            $signatureImage = str_replace('data:image/png;base64,', '', $this->signature);
            $signatureImage = str_replace(' ', '+', $signatureImage);
            $signatureData = base64_decode($signatureImage);
            
            // Generate a unique filename
            $fileName = 'signature_' . $this->case->id . '_' . time() . '.png';
            $path = 'signatures/' . $fileName;
            
            // Save the file
            Storage::disk('public')->put($path, $signatureData);
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to save signature', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to save signature: ' . $e->getMessage());
        }
    }
    
    protected function createContractAction($signaturePath)
    {
        try {
            // Create the contract action directly using the model
            $action = new ContractAction();
            $action->legal_case_id = $this->case->id;
            $action->action_type = 'accepted';
            $action->actor_type = User::class;
            $action->actor_id = Auth::id();
            $action->details = 'Contract accepted by client. Signed with name: ' . $this->signatureText;
            $action->signature_path = $signaturePath;
            $action->save();
            
            return $action;
        } catch (\Exception $e) {
            Log::error('Failed to create contract action', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to create contract action: ' . $e->getMessage());
        }
    }
    
    protected function updateCaseStatus($signaturePath)
    {
        try {
            $this->case->status = LegalCase::STATUS_CONTRACT_SIGNED;
            $this->case->contract_status = LegalCase::CONTRACT_STATUS_SIGNED;
            $this->case->contract_signed_at = now();
            $this->case->signature_path = $signaturePath;
            $this->case->lawyer_response_required = true;
            $this->case->save();
            
            // Log success for debugging
            Log::info('Case status and signature path updated successfully', [
                'case_id' => $this->case->id,
                'signature_path' => $signaturePath
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update case status', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to update case status: ' . $e->getMessage());
        }
    }
    
    protected function createFallbackNotification()
    {
        try {
            // Create a record in the case_updates table as a fallback
            DB::table('case_updates')->insert([
                'legal_case_id' => $this->case->id,
                'title' => 'Contract Signed',
                'content' => 'Client has signed and accepted the contract. Signature name: ' . $this->signatureText,
                'visibility' => 'lawyer',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Double-check that signature_path is set in the legal_cases table
            // Ensure that if the signature_path is not already set in the case model, we get it from the contract action
            if (empty($this->case->signature_path)) {
                $contractAction = ContractAction::where('legal_case_id', $this->case->id)
                    ->where('action_type', 'accepted')
                    ->whereNotNull('signature_path')
                    ->latest()
                    ->first();
                
                if ($contractAction && $contractAction->signature_path) {
                    // Update the legal_cases table with the signature path
                    DB::table('legal_cases')
                        ->where('id', $this->case->id)
                        ->update([
                            'lawyer_response_required' => true,
                            'signature_path' => $contractAction->signature_path
                        ]);
                    
                    Log::info('Signature path updated via fallback method', [
                        'case_id' => $this->case->id,
                        'signature_path' => $contractAction->signature_path
                    ]);
                }
            } else {
                // Just update lawyer_response_required flag
                DB::table('legal_cases')
                    ->where('id', $this->case->id)
                    ->update(['lawyer_response_required' => true]);
            }
                
            Log::info('Fallback notification created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create fallback notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Methods for Reject Contract Modal
    public function openRejectModal()
    {
        $this->resetValidation();
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function submitRejection()
    {
        $this->validate(['rejectionReason' => 'required|min:10|max:2000'], [
            'rejectionReason.required' => 'Please provide a reason for rejecting the contract.',
            'rejectionReason.min' => 'The rejection reason must be at least 10 characters.',
            'rejectionReason.max' => 'The rejection reason cannot exceed 2000 characters.',
        ]);

        try {
            DB::beginTransaction();

            $this->case->status = LegalCase::STATUS_CONTRACT_REJECTED_BY_CLIENT;
            $this->case->rejection_reason = $this->rejectionReason;
            $this->case->contract_status = LegalCase::CONTRACT_STATUS_REJECTED;
            $this->case->lawyer_response_required = true;
            $this->case->save();

            ContractAction::create([
                'legal_case_id' => $this->case->id,
                'action_type' => 'contract_rejected_by_client',
                'actor_type' => User::class,
                'actor_id' => Auth::id(),
                'details' => 'Contract rejected by client. Reason: ' . $this->rejectionReason,
            ]);

            // Notify Lawyer
            if ($this->case->lawyer) {
                try {
                    NotificationService::contractRejectedByClient($this->case, Auth::user(), $this->rejectionReason);
                } catch (\Exception $notificationException) {
                    Log::error('Failed to send contract rejection notification', [
                        'case_id' => $this->case->id,
                        'error' => $notificationException->getMessage()
                    ]);
                    // Optionally, flash a non-critical error to the user or just log it
                }
            }

            DB::commit();
            $this->showRejectModal = false;
            session()->flash('success', 'Contract has been rejected. The legal professional has been notified.');
            return redirect()->route('client.cases.show', $this->case->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract rejection submission failed', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to reject contract: ' . $e->getMessage());
        }
    }

    // Methods for Request Changes Modal
    public function openRequestChangesModal()
    {
        $this->resetValidation();
        $this->requestedChanges = '';
        $this->showRequestChangesModal = true;
    }

    public function submitRequestedChanges()
    {
        $this->validate(['requestedChanges' => 'required|min:10|max:5000'], [
            'requestedChanges.required' => 'Please specify the changes you are requesting.',
            'requestedChanges.min' => 'Your change request must be at least 10 characters.',
            'requestedChanges.max' => 'The change request cannot exceed 5000 characters.',
        ]);

        try {
            DB::beginTransaction();

            $this->case->status = LegalCase::STATUS_CHANGES_REQUESTED_BY_CLIENT;
            $this->case->requested_changes_details = $this->requestedChanges;
            $this->case->contract_status = LegalCase::CONTRACT_STATUS_NEGOTIATING;
            $this->case->lawyer_response_required = true;
            $this->case->save();

            ContractAction::create([
                'legal_case_id' => $this->case->id,
                'action_type' => 'changes_requested_by_client',
                'actor_type' => User::class,
                'actor_id' => Auth::id(),
                'details' => 'Client requested changes to the contract. Details: ' . $this->requestedChanges,
            ]);

            // Notify Lawyer
            if ($this->case->lawyer) {
                try {
                    NotificationService::contractChangesRequestedByClient($this->case, Auth::user(), $this->requestedChanges);
                } catch (\Exception $notificationException) {
                    Log::error('Failed to send contract changes requested notification', [
                        'case_id' => $this->case->id,
                        'error' => $notificationException->getMessage()
                    ]);
                    // Optionally, flash a non-critical error to the user or just log it
                }
            }

            DB::commit();
            $this->showRequestChangesModal = false;
            session()->flash('success', 'Your request for changes has been submitted. The legal professional has been notified.');
            return redirect()->route('client.cases.show', $this->case->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Contract change request submission failed', [
                'case_id' => $this->case->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to submit change request: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.client.contract-review');
    }
} 