<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class Services extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Service::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            });

        return view('livewire.services', [
            'services' => $query->paginate(12),
            'categories' => Category::all()
        ]);
    }
} 