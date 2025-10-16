<?php

namespace App\Livewire\Admin;

use App\Models\Report;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientReportManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $category = '';
    public $reportedType = '';
    public $selectedReport = null;
    public $showViewModal = false;
    public $showEditModal = false;
    public $adminNotes = '';
    public $newStatus = '';
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'category' => ['except' => ''],
        'reportedType' => ['except' => ''],
    ];

    protected $rules = [
        'adminNotes' => 'required|string|max:2000',
        'newStatus' => 'required|in:pending,under_review,resolved,dismissed',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingReportedType()
    {
        $this->resetPage();
    }

    public function viewReport($reportId)
    {
        // Check permission
        if (!Auth::user()->hasPermission('view_client_reports')) {
            session()->flash('error', 'You do not have permission to view client reports.');
            return;
        }

        $this->selectedReport = Report::where('id', $reportId)
            ->with([
                'reporter.clientProfile',
                'reportedUser.lawyerProfile',
                'reportedUser.lawFirmProfile',
                'reviewer',
                'investigationCase'
            ])
            ->first();
        
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedReport = null;
    }

    public function editReport($reportId)
    {
        // Check permission
        if (!Auth::user()->hasPermission('review_client_reports')) {
            session()->flash('error', 'You do not have permission to review client reports.');
            return;
        }

        $this->selectedReport = Report::find($reportId);
        $this->adminNotes = $this->selectedReport->admin_notes ?? '';
        $this->newStatus = $this->selectedReport->status;
        $this->showEditModal = true;
    }

    public function updateReport()
    {
        // Check permission
        if (!Auth::user()->hasPermission('review_client_reports')) {
            session()->flash('error', 'You do not have permission to review client reports.');
            return;
        }

        $this->validate();

        try {
            $this->selectedReport->update([
                'status' => $this->newStatus,
                'admin_notes' => $this->adminNotes,
                'reviewed_at' => now(),
                'reviewed_by' => Auth::id(),
            ]);

            session()->flash('success', 'Report updated successfully.');
            $this->closeEditModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating report: ' . $e->getMessage());
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedReport = null;
        $this->adminNotes = '';
        $this->newStatus = '';
        $this->resetValidation();
    }

    public function investigateReport($reportId)
    {
        // Check permission
        if (!Auth::user()->hasPermission('view_client_reports')) {
            session()->flash('error', 'You do not have permission to investigate reports.');
            return;
        }

        // Redirect to investigation dashboard
        return redirect()->route('admin.investigation.dashboard', ['reportId' => $reportId]);
    }

    public function downloadDocument($documentPath)
    {
        // Check permission
        if (!Auth::user()->hasPermission('view_report_documents')) {
            session()->flash('error', 'You do not have permission to view report documents.');
            return;
        }

        if (Storage::disk('public')->exists($documentPath)) {
            return Storage::disk('public')->download($documentPath);
        } else {
            session()->flash('error', 'Document not found.');
        }
    }

    public function markAsUnderReview($reportId)
    {
        // Check permission
        if (!Auth::user()->hasPermission('review_client_reports')) {
            session()->flash('error', 'You do not have permission to review client reports.');
            return;
        }

        $report = Report::find($reportId);
        $report->update([
            'status' => 'under_review',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        session()->flash('success', 'Report marked as under review.');
    }

    public function resolveReport($reportId)
    {
        // Check permission
        if (!Auth::user()->hasPermission('resolve_client_reports')) {
            session()->flash('error', 'You do not have permission to resolve client reports.');
            return;
        }

        $report = Report::find($reportId);
        $report->update([
            'status' => 'resolved',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        session()->flash('success', 'Report marked as resolved.');
    }

    public function dismissReport($reportId)
    {
        // Check permission
        if (!Auth::user()->hasPermission('resolve_client_reports')) {
            session()->flash('error', 'You do not have permission to dismiss client reports.');
            return;
        }

        $report = Report::find($reportId);
        $report->update([
            'status' => 'dismissed',
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        session()->flash('success', 'Report dismissed.');
    }

    public function getReportStats()
    {
        return [
            'total' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'under_review' => Report::where('status', 'under_review')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'dismissed' => Report::where('status', 'dismissed')->count(),
            'this_month' => Report::whereMonth('created_at', now()->month)->count(),
        ];
    }

    public function render()
    {
        // Check permission
        if (!Auth::user()->hasPermission('view_client_reports')) {
            return view('livewire.admin.unauthorized')->layout('components.layouts.admin');
        }

        $reports = Report::query()
            ->with([
                'reporter.clientProfile',
                'reportedUser.lawyerProfile',
                'reportedUser.lawFirmProfile',
                'reviewer',
                'investigationCase'
            ])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('reporter_name', 'like', '%' . $this->search . '%')
                      ->orWhere('reported_name', 'like', '%' . $this->search . '%')
                      ->orWhere('reporter_email', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function($query) {
                $query->where('status', $this->status);
            })
            ->when($this->category, function($query) {
                $query->where('category', $this->category);
            })
            ->when($this->reportedType, function($query) {
                $query->where('reported_type', $this->reportedType);
            })
            ->latest()
            ->paginate(15);

        $stats = $this->getReportStats();

        return view('livewire.admin.client-report-management', [
            'reports' => $reports,
            'stats' => $stats
        ])->layout('components.layouts.admin');
    }
}
