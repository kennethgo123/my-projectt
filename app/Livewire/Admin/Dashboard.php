<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'approved_users' => User::where('status', 'approved')->count(),
            'lawyers' => User::whereHas('role', fn($q) => $q->where('name', 'lawyer'))->count(),
            'law_firms' => User::whereHas('role', fn($q) => $q->where('name', 'law_firm'))->count(),
            'clients' => User::whereHas('role', fn($q) => $q->where('name', 'client'))->count(),
        ];

        return view('livewire.admin.dashboard', [
            'stats' => $stats
        ])->layout('components.layouts.admin');
    }
} 