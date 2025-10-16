<?php

namespace App\Http\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class PendingUsers extends Component
{
    public $users;

    public function mount()
    {
        $this->users = User::with([
            'lawFirmLawyer.lawFirm',
            'lawFirmProfile',
            'clientProfile'
        ])->where('status', 'pending')->get();
    }

    public function render()
    {
        return view('livewire.admin.pending-users');
    }
} 