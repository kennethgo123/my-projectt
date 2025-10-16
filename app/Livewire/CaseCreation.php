<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\LegalCase;
use App\Models\CaseCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CaseCreation extends Component
{
    public $title;
    public $case_type;
    public $selectedCategories = [];
    public $categoryDescriptions = [];
    
    // Fields to store client and consultation data if needed
    public $client_id;
    public $consultation_id;
    
    protected $rules = [
        'title' => 'required|string|min:3|max:255',
        'case_type' => 'nullable|string|max:100',
        'selectedCategories' => 'required|array|min:1',
        'selectedCategories.*' => 'exists:categories,id',
        'categoryDescriptions.*' => 'nullable|string',
    ];
    
    protected $messages = [
        'title.required' => 'Please provide a title for your case.',
        'selectedCategories.required' => 'Please select at least one category.',
        'selectedCategories.min' => 'Please select at least one category.',
    ];
    
    public function mount($client_id = null, $consultation_id = null)
    {
        $this->client_id = $client_id;
        $this->consultation_id = $consultation_id;
    }
    
    public function updatedSelectedCategories()
    {
        // Initialize description fields for newly selected categories
        foreach ($this->selectedCategories as $categoryId) {
            if (!isset($this->categoryDescriptions[$categoryId])) {
                $this->categoryDescriptions[$categoryId] = '';
            }
        }
        
        // Remove descriptions for categories that are no longer selected
        foreach ($this->categoryDescriptions as $categoryId => $description) {
            if (!in_array($categoryId, $this->selectedCategories)) {
                unset($this->categoryDescriptions[$categoryId]);
            }
        }
    }
    
    public function createCase()
    {
        $this->validate();
        
        // Create the case
        $case = LegalCase::create([
            'title' => $this->title,
            'case_type' => $this->case_type,
            'client_id' => $this->client_id ?? Auth::id(),
            'consultation_id' => $this->consultation_id,
            'case_number' => LegalCase::generateCaseNumber(),
            'status' => LegalCase::STATUS_PENDING,
        ]);
        
        // Create category relationships with descriptions
        foreach ($this->selectedCategories as $categoryId) {
            CaseCategory::create([
                'legal_case_id' => $case->id,
                'category_id' => $categoryId,
                'description' => $this->categoryDescriptions[$categoryId] ?? null,
            ]);
        }
        
        session()->flash('message', 'Case created successfully!');
        
        // Redirect to the case view or another appropriate page
        return redirect()->route('client.case.view', $case->id);
    }
    
    public function render()
    {
        return view('livewire.case-creation', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
