<?php

namespace App\Livewire\Admin;

use App\Models\Report;
use App\Models\InvestigationCase;
use App\Models\InvestigationAttachment;
use App\Services\InvestigationTimelineService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvestigationDashboard extends Component
{
    use WithFileUploads;
    public $report;
    public $investigation;
    public $timeline = [];
    public $interactionStats = [];
    public $redFlags = [];
    public $selectedDateRange = '30'; // Default to last 30 days
    public $showTimelineDetails = [];
    public $investigationNotes = '';
    public $newStatus = '';
    public $newPriority = '';
    public $findings = '';
    public $recommendations = '';
    
    // File upload properties
    public $attachments = [];
    public $attachmentDescription = '';
    public $attachmentType = 'document';
    public $showConfirmComplete = false;
    
    // Filters
    public $filterType = '';
    public $filterCategory = '';
    public $filterSeverity = '';
    
    protected $rules = [
        'investigationNotes' => 'required|string|max:5000',
        'newStatus' => 'required|in:assigned,in_progress,pending_review,completed,closed',
        'newPriority' => 'required|in:low,medium,high,urgent',
        'findings' => 'nullable|string|max:10000',
        'recommendations' => 'nullable|string|max:10000',
        'attachments.*' => 'file|max:10240', // 10MB max per file
        'attachmentDescription' => 'nullable|string|max:500',
        'attachmentType' => 'required|in:evidence,document,image,other',
    ];

    public function mount($reportId)
    {
        // Check permission
        if (!Auth::user()->hasPermission('view_client_reports')) {
            abort(403, 'You do not have permission to view investigations.');
        }

        $this->report = Report::with([
            'reporter.clientProfile',
            'reportedUser.lawyerProfile',
            'reportedUser.lawFirmProfile'
        ])->findOrFail($reportId);

        // Check if investigation case exists, if not create one
        $this->investigation = InvestigationCase::where('report_id', $reportId)->first();
        
        if (!$this->investigation) {
            $this->investigation = InvestigationCase::create([
                'report_id' => $reportId,
                'investigator_id' => Auth::id(),
                'status' => 'assigned',
                'priority' => 'medium',
                'assigned_at' => now(),
            ]);
        }

        // Initialize form values
        $this->investigationNotes = $this->investigation->investigation_notes ?? '';
        $this->newStatus = $this->investigation->status;
        $this->newPriority = $this->investigation->priority;
        $this->findings = $this->investigation->findings ?? '';
        $this->recommendations = $this->investigation->recommendations ?? '';

        $this->loadTimelineData();
    }

    public function loadTimelineData()
    {
        $timelineService = new InvestigationTimelineService();
        
        // Calculate date range
        $toDate = Carbon::now();
        $fromDate = $toDate->copy()->subDays((int) $this->selectedDateRange);
        
        // Get timeline data
        $this->timeline = $timelineService->getReportTimeline($this->report, $fromDate, $toDate);
        
        // Apply filters
        $this->applyFilters();
        
        // Get interaction statistics
        $this->interactionStats = $timelineService->getInteractionStats(
            $this->report->reported_user_id,
            $this->report->reporter_id
        );
        
        // Get red flags
        $this->redFlags = $timelineService->getRedFlags(
            $this->report->reported_user_id,
            $this->report->reporter_id
        );
    }

    public function applyFilters()
    {
        $filtered = $this->timeline;
        
        if ($this->filterType) {
            $filtered = $filtered->where('type', $this->filterType);
        }
        
        if ($this->filterCategory) {
            $filtered = $filtered->where('category', $this->filterCategory);
        }
        
        if ($this->filterSeverity) {
            $filtered = $filtered->where('severity', $this->filterSeverity);
        }
        
        $this->timeline = $filtered->values();
    }

    public function updatedSelectedDateRange()
    {
        $this->loadTimelineData();
    }

    public function updatedFilterType()
    {
        $this->applyFilters();
    }

    public function updatedFilterCategory()
    {
        $this->applyFilters();
    }

    public function updatedFilterSeverity()
    {
        $this->applyFilters();
    }

    public function toggleTimelineDetails($index)
    {
        if (isset($this->showTimelineDetails[$index])) {
            unset($this->showTimelineDetails[$index]);
        } else {
            $this->showTimelineDetails[$index] = true;
        }
    }

    public function startInvestigation()
    {
        if (!Auth::user()->hasPermission('review_client_reports')) {
            session()->flash('error', 'You do not have permission to manage investigations.');
            return;
        }

        $this->investigation->markAsStarted();
        $this->newStatus = 'in_progress';
        
        session()->flash('message', 'Investigation started successfully.');
    }

    public function updateInvestigation()
    {
        if (!Auth::user()->hasPermission('review_client_reports')) {
            session()->flash('error', 'You do not have permission to update investigations.');
            return;
        }

        // Check if investigation is locked
        if ($this->investigation->isLocked()) {
            session()->flash('error', 'Cannot update a completed investigation.');
            return;
        }

        // If trying to complete, show confirmation modal
        if ($this->newStatus === 'completed' && $this->investigation->status !== 'completed') {
            $this->showConfirmComplete = true;
            return;
        }

        $this->performUpdate();
    }

    public function confirmComplete()
    {
        $this->showConfirmComplete = false;
        $this->performUpdate();
    }

    public function cancelComplete()
    {
        $this->showConfirmComplete = false;
        $this->newStatus = $this->investigation->status; // Reset status
    }

    private function performUpdate()
    {
        $this->validate();

        try {
            $updateData = [
                'status' => $this->newStatus,
                'priority' => $this->newPriority,
                'investigation_notes' => $this->investigationNotes,
                'findings' => $this->findings,
                'recommendations' => $this->recommendations,
            ];

            // Set completion timestamp if status is completed
            if ($this->newStatus === 'completed' && $this->investigation->status !== 'completed') {
                $updateData['completed_at'] = now();
            }

            $this->investigation->update($updateData);

            // Update the original report status when investigation is completed
            if ($this->newStatus === 'completed' && $this->investigation->status !== 'completed') {
                $this->report->update([
                    'status' => 'resolved',
                    'admin_notes' => 'Investigation completed. ' . ($this->findings ? 'Findings: ' . $this->findings : ''),
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                ]);
            }

            session()->flash('message', 'Investigation updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating investigation: ' . $e->getMessage());
        }
    }

    public function uploadAttachments()
    {
        if (!Auth::user()->hasPermission('review_client_reports')) {
            session()->flash('error', 'You do not have permission to upload attachments.');
            return;
        }

        // Check if investigation is locked
        if ($this->investigation->isLocked()) {
            session()->flash('error', 'Cannot upload attachments to a completed investigation.');
            return;
        }

        $this->validate([
            'attachments.*' => 'required|file|max:10240', // 10MB max per file
            'attachmentDescription' => 'nullable|string|max:500',
            'attachmentType' => 'required|in:evidence,document,image,other',
        ]);

        try {
            foreach ($this->attachments as $attachment) {
                $originalFilename = $attachment->getClientOriginalName();
                $storedFilename = Str::uuid() . '.' . $attachment->getClientOriginalExtension();
                $filePath = 'investigation-attachments/' . $this->investigation->id . '/' . $storedFilename;
                
                // Store the file
                $attachment->storeAs('investigation-attachments/' . $this->investigation->id, $storedFilename, 'local');
                
                // Create database record
                InvestigationAttachment::create([
                    'investigation_case_id' => $this->investigation->id,
                    'uploaded_by' => Auth::id(),
                    'original_filename' => $originalFilename,
                    'stored_filename' => $storedFilename,
                    'file_path' => $filePath,
                    'mime_type' => $attachment->getMimeType(),
                    'file_size' => $attachment->getSize(),
                    'description' => $this->attachmentDescription,
                    'attachment_type' => $this->attachmentType,
                ]);
            }

            // Reset form
            $this->attachments = [];
            $this->attachmentDescription = '';
            $this->attachmentType = 'document';

            session()->flash('message', 'Attachments uploaded successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error uploading attachments: ' . $e->getMessage());
        }
    }

    public function deleteAttachment($attachmentId)
    {
        if (!Auth::user()->hasPermission('review_client_reports')) {
            session()->flash('error', 'You do not have permission to delete attachments.');
            return;
        }

        // Check if investigation is locked
        if ($this->investigation->isLocked()) {
            session()->flash('error', 'Cannot delete attachments from a completed investigation.');
            return;
        }

        try {
            $attachment = InvestigationAttachment::where('investigation_case_id', $this->investigation->id)
                ->findOrFail($attachmentId);
            
            $attachment->delete(); // This will also delete the physical file due to the boot method
            
            session()->flash('message', 'Attachment deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting attachment: ' . $e->getMessage());
        }
    }

    public function exportTimeline()
    {
        // This could be enhanced to export to PDF or CSV
        session()->flash('message', 'Timeline export feature will be implemented.');
    }

    public function getFilteredTimelineProperty()
    {
        return $this->timeline;
    }

    public function getTimelineTypesProperty()
    {
        return $this->timeline->pluck('type')->unique()->sort()->values()->toArray();
    }

    public function getTimelineCategoriesProperty()
    {
        return $this->timeline->pluck('category')->unique()->sort()->values()->toArray();
    }

    public function render()
    {
        // Check permission
        if (!Auth::user()->hasPermission('view_client_reports')) {
            return view('livewire.admin.unauthorized')->layout('components.layouts.admin');
        }

        $investigationAttachments = $this->investigation->attachments()
            ->with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin.investigation-dashboard', [
            'timelineCount' => count($this->timeline),
            'investigationAttachments' => $investigationAttachments,
        ])->layout('components.layouts.admin');
    }
}