<?php

namespace App\Livewire\Lawyers\Components;

use Livewire\Component;
use App\Models\LawFirmProfile;

class LawfirmCard extends Component
{
    public $lawFirm;

    public function mount($lawFirm)
    {
        $this->lawFirm = $lawFirm;
    }
    
    public function render()
    {
        return view('livewire.lawyers.components.lawfirm-card');
    }
} 