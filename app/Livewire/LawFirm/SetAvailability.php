<?php

namespace App\Livewire\LawFirm;

use App\Models\LawyerAvailability;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SetAvailability extends Component
{
    public $activeTab = 'firm';
    public $allowLawyerAvailability = true;
    
    public function mount()
    {
        $lawFirmProfile = auth()->user()->lawFirmProfile;
        if ($lawFirmProfile) {
            $this->allowLawyerAvailability = $lawFirmProfile->allow_lawyer_availability;
        }
    }
    
    public function toggleAllowLawyerAvailability()
    {
        $lawFirmProfile = auth()->user()->lawFirmProfile;
        if ($lawFirmProfile) {
            $lawFirmProfile->update([
                'allow_lawyer_availability' => $this->allowLawyerAvailability
            ]);
            
            session()->flash('message', 'Lawyer availability setting updated successfully!');
        }
    }
    
    public function render()
    {
        return view('livewire.law-firm.set-availability');
    }
} 