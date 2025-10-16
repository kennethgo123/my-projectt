<?php

namespace App\Livewire\Admin;

use App\Models\LegalService;
use App\Models\ServiceCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceManagement extends Component
{
    use WithPagination;

    public $name = '';
    public $description = '';
    public $category = '';
    public $editingService = null;
    public $isModalOpen = false;
    
    // For handling service categories
    public $serviceCategories = [];
    public $newCategoryName = '';
    public $newCategoryDescription = '';
    public $showCategoryForm = false;
    public $currentServiceId = null;
    public $viewingCategories = false;
    
    // For editing categories
    public $editingCategory = null;
    public $editCategoryName = '';
    public $editCategoryDescription = '';

    protected $rules = [
        'name' => 'required|min:3',
        'description' => 'required|min:10',
        'category' => 'required',
    ];

    public function createService()
    {
        $this->validate();

        $service = LegalService::create([
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'status' => 'active',
        ]);

        $this->reset(['name', 'description', 'category']);
        $this->isModalOpen = false;
        session()->flash('message', 'Service created successfully.');
    }

    public function editService(LegalService $service)
    {
        $this->editingService = $service;
        $this->name = $service->name;
        $this->description = $service->description;
        $this->category = $service->category;
        $this->isModalOpen = true;
    }

    public function updateService()
    {
        $this->validate();

        $this->editingService->update([
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
        ]);

        $this->reset(['name', 'description', 'category', 'editingService']);
        $this->isModalOpen = false;
        session()->flash('message', 'Service updated successfully.');
    }

    public function toggleStatus(LegalService $service)
    {
        $service->update([
            'status' => $service->status === 'active' ? 'inactive' : 'active',
        ]);

        session()->flash('message', 'Service status updated successfully.');
    }
    
    // Methods for handling service categories
    public function viewCategories(LegalService $service)
    {
        $this->currentServiceId = $service->id;
        $this->viewingCategories = true;
        $this->loadServiceCategories();
    }
    
    public function loadServiceCategories()
    {
        if ($this->currentServiceId) {
            $service = LegalService::findOrFail($this->currentServiceId);
            $this->serviceCategories = $service->categories()->get()->toArray();
        }
    }
    
    public function toggleCategoryForm()
    {
        $this->showCategoryForm = !$this->showCategoryForm;
        $this->newCategoryName = '';
        $this->newCategoryDescription = '';
        // Reset editing state when toggling the add form
        $this->editingCategory = null;
        $this->editCategoryName = '';
        $this->editCategoryDescription = '';
    }
    
    public function addCategory()
    {
        $this->validate([
            'newCategoryName' => 'required|min:2|max:50',
            'newCategoryDescription' => 'nullable|max:500'
        ]);
        
        ServiceCategory::create([
            'legal_service_id' => $this->currentServiceId,
            'name' => $this->newCategoryName,
            'description' => $this->newCategoryDescription,
        ]);
        
        $this->newCategoryName = '';
        $this->newCategoryDescription = '';
        $this->showCategoryForm = false;
        $this->loadServiceCategories();
        
        session()->flash('message', 'Category added successfully.');
    }
    
    public function editCategory($categoryId)
    {
        $category = ServiceCategory::findOrFail($categoryId);
        $this->editingCategory = $category;
        $this->editCategoryName = $category->name;
        $this->editCategoryDescription = $category->description;
        $this->showCategoryForm = true;
    }
    
    public function updateCategory()
    {
        $this->validate([
            'editCategoryName' => 'required|min:2|max:50',
            'editCategoryDescription' => 'nullable|max:500'
        ]);
        
        $this->editingCategory->update([
            'name' => $this->editCategoryName,
            'description' => $this->editCategoryDescription,
        ]);
        
        $this->editingCategory = null;
        $this->editCategoryName = '';
        $this->editCategoryDescription = '';
        $this->showCategoryForm = false;
        $this->loadServiceCategories();
        
        session()->flash('message', 'Category updated successfully.');
    }
    
    public function cancelCategoryEdit()
    {
        $this->editingCategory = null;
        $this->editCategoryName = '';
        $this->editCategoryDescription = '';
        $this->showCategoryForm = false;
    }
    
    public function deleteCategory($categoryId)
    {
        ServiceCategory::destroy($categoryId);
        $this->loadServiceCategories();
        session()->flash('message', 'Category deleted successfully.');
    }
    
    public function closeCategories()
    {
        $this->viewingCategories = false;
        $this->currentServiceId = null;
        $this->serviceCategories = [];
        $this->showCategoryForm = false;
        $this->editingCategory = null;
    }

    public function render()
    {
        return view('livewire.admin.service-management', [
            'services' => LegalService::latest()->paginate(10),
            'currentService' => $this->currentServiceId ? LegalService::find($this->currentServiceId) : null,
        ])->layout('components.layouts.admin');
    }
} 