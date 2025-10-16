<?php

namespace App\Livewire\LawFirm;

use App\Models\LegalCase;
use App\Models\ContractAction;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CaseDetails extends Component
{
    use WithFileUploads;

    public LegalCase $case;
    public $activeTab = 'overview';
    
    public function mount(LegalCase $case)
    {
        // Verify law firm has access to this case
        $lawFirmId = Auth::id();
        $lawyerId = $case->lawyer_id;
        
        // Check if this lawyer belongs to the law firm through firm_id relationship
        $hasAccess = $lawyerId === $lawFirmId || // Direct ownership
            DB::table('users')->where('id', $lawyerId)
                ->where('firm_id', $lawFirmId)
                ->exists(); // Lawyer belongs to this law firm
        
        if (!$hasAccess) {
            abort(403, 'You are not authorized to view this case.');
        }
        
        $this->case = $case->load([
            'client.clientProfile',
            'lawyer.lawyerProfile',
            'caseUpdates',
            'contractActions'
        ]);
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.law-firm.case-details');
    }
} 