<?php

namespace App\Livewire\Shared;

use App\Models\LegalCase;
use App\Models\CaseDocument;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CaseDocuments extends Component
{
    use WithFileUploads;

    public LegalCase $case;
    public $document;
    public $documentTitle;
    public $documentDescription;
    public $showUploadModal = false;

    protected $rules = [
        'document' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        'documentTitle' => 'required|string|max:255',
        'documentDescription' => 'nullable|string'
    ];

    public function mount(LegalCase $case)
    {
        $this->case = $case;
    }

    public function showUploadDocumentModal()
    {
        $this->resetForm();
        $this->showUploadModal = true;
    }

    public function uploadDocument()
    {
        $this->validate();

        try {
            $path = $this->document->store('case-documents', 'public');
            
            CaseDocument::create([
                'legal_case_id' => $this->case->id,
                'title' => $this->documentTitle,
                'description' => $this->documentDescription,
                'file_path' => $path,
                'file_name' => $this->document->getClientOriginalName(),
                'file_size' => $this->document->getSize(),
                'file_type' => $this->document->getMimeType(),
                'uploaded_by_type' => User::class,
                'uploaded_by_id' => Auth::id(),
                'is_shared' => true
            ]);

            $this->showUploadModal = false;
            $this->resetForm();
            session()->flash('message', 'Document uploaded successfully.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    public function downloadDocument($documentId)
    {
        $document = CaseDocument::findOrFail($documentId);
        
        if ($document->legal_case_id !== $this->case->id) {
            abort(403);
        }
        
        return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
    }

    public function deleteDocument($documentId)
    {
        $document = CaseDocument::findOrFail($documentId);

        // Authorization: Ensure the document belongs to the current case
        // AND the current user is the one who uploaded it.
        if ($document->legal_case_id !== $this->case->id ||
            ($document->uploaded_by_id !== Auth::id() || $document->uploaded_by_type !== User::class)) {
            abort(403, 'You are not authorized to delete this document.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        session()->flash('message', 'Document deleted successfully.');
        $this->case->refresh(); // Refresh case to update document list if needed
    }

    private function resetForm()
    {
        $this->document = null;
        $this->documentTitle = '';
        $this->documentDescription = '';
    }

    public function render()
    {
        // For cases when component is called directly by route
        if (request()->route('case') && !isset($this->case)) {
            $this->case = \App\Models\LegalCase::findOrFail(request()->route('case'));
        }
        
        return view('livewire.shared.case-documents', [
            'documents' => CaseDocument::where('legal_case_id', $this->case->id)
                ->where('is_shared', true)
                ->latest()
                ->get()
        ]);
    }
} 