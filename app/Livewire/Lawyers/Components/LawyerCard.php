<?php

namespace App\Livewire\Lawyers\Components;

use Livewire\Component;
use App\Models\LawyerProfile;
use App\Models\LawFirmLawyer;

class LawyerCard extends Component
{
    public $lawyer;
    public $type = 'lawyer';

    public function mount($lawyer)
    {
        $this->lawyer = $lawyer;
        
        // Determine type based on instance
        if ($lawyer instanceof LawFirmLawyer) {
            $this->type = 'firmLawyer';
        } else {
            $this->type = 'lawyer';
        }
    }
    
    public function render()
    {
        return view('livewire.lawyers.components.lawyer-card');
    }
} 