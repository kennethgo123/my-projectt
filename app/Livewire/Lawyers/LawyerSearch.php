<?php

namespace App\Livewire\Lawyers;

use App\Models\LawyerProfile;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class LawyerSearch extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    public $minBudget = '';
    public $maxBudget = '';
    public $city = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'minBudget' => ['except' => ''],
        'maxBudget' => ['except' => ''],
        'city' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = LawyerProfile::query()
            ->whereHas('user', function ($query) {
                $query->where('status', 'approved');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->whereHas('services', function ($query) {
                    $query->where('category_id', $this->selectedCategory);
                });
            })
            ->when($this->minBudget, function ($query) {
                $query->where('min_budget', '>=', $this->minBudget);
            })
            ->when($this->maxBudget, function ($query) {
                $query->where('max_budget', '<=', $this->maxBudget);
            })
            ->when($this->city, function ($query) {
                $query->where('city', $this->city);
            });

        return view('livewire.lawyers.lawyer-search', [
            'lawyers' => $query->paginate(12),
            'categories' => Category::all(),
            'cities' => [
                'Cavite City',
                'Dasmarinas',
                'General Trias',
                'Imus',
                'Tagaytay',
                'Trece Martires',
                'Bacoor'
            ]
        ]);
    }
} 