<?php

namespace App\Livewire\LawFirm;

use App\Models\User;
use App\Models\LawFirmLawyer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManageAvailability extends Component
{
    public $lawyers = [];
    public $selectedLawyerId = null;

    public function mount()
    {
        $this->loadFirmLawyers();
    }

    public function loadFirmLawyers()
    {
        // Using LawFirmLawyer model to get lawyers, just like in ManageLawyers.php
        $lawFirmProfileId = auth()->user()->lawFirmProfile->id;
        $lawFirmLawyers = LawFirmLawyer::where('law_firm_profile_id', $lawFirmProfileId)->with('user')->get();
        
        // Transform to the format needed for the dropdown (user_id is the key)
        $this->lawyers = $lawFirmLawyers->map(function($lawFirmLawyer) {
            return [
                'id' => $lawFirmLawyer->user_id,
                'name' => "{$lawFirmLawyer->first_name} {$lawFirmLawyer->last_name}",
                'email' => $lawFirmLawyer->user->email ?? 'No email'
            ];
        });
    }
    
    public function updatedSelectedLawyerId($value)
    {
        // This lifecycle hook is called when selectedLawyerId changes.
        // It helps in re-rendering the child component correctly if needed.
    }

    public function render()
    {
        return view('livewire.law-firm.manage-availability');
    }
} 