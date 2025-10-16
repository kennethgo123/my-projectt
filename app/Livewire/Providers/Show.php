<?php

namespace App\Livewire\Providers;

use App\Models\User;
use Livewire\Component;

class Show extends Component
{
    public User $provider;

    public function mount(User $user)
    {
        $this->provider = $user;
    }

    public function render()
    {
        return view('livewire.providers.show', [
            'provider' => $this->provider->load(['role', 'lawyerProfile', 'lawFirmProfile', 'services']),
        ])->layout('components.layouts.app');
    }
} 