<?php

namespace App\Livewire;

use App\Models\LegalService;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Home extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedService = '';
    public $selectedLocation = '';
    public $role = '';
    public $sortBy = 'rating';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedService' => ['except' => ''],
        'selectedLocation' => ['except' => ''],
        'role' => ['except' => ''],
        'sortBy' => ['except' => 'rating'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedService()
    {
        $this->resetPage();
    }

    public function updatingSelectedLocation()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Get all active services for practice areas
        $legalServices = LegalService::where('status', 'active')
            ->with('categories')
            ->get();

        // Group services into practice areas based on their category
        $practiceAreas = $legalServices->groupBy('category');
        
        // Get provider listings
        $providers = User::whereRelation('role', 'name', 'lawyer')
            ->with('profile', 'role')
            ->paginate(6);
            
        return view('livewire.home', [
            'practiceAreas' => $practiceAreas,
            'providers' => $providers,
        ])->layout('layouts.guest');
    }
} 