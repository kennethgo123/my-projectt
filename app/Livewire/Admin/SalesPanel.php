<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class SalesPanel extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $dateRange = '';

    // Properties for filtering by date
    public $startDate = '';
    public $endDate = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Get all invoices for the sales panel
        $invoicesQuery = Invoice::with(['client.clientProfile', 'lawyer.lawyerProfile', 'lawyer.lawFirmProfile', 'legalCase'])
            ->orderBy('created_at', 'desc');
            
        // Apply search filter
        if (!empty($this->search)) {
            $invoicesQuery->where(function ($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client.clientProfile', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('lawyer.lawyerProfile', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('lawyer.lawFirmProfile', function ($q) {
                        $q->where('firm_name', 'like', '%' . $this->search . '%');
                    });
            });
        }
        
        // Apply status filter
        if (!empty($this->filterStatus)) {
            $invoicesQuery->where('status', $this->filterStatus);
        }
        
        // Apply date filter
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $invoicesQuery->whereBetween('issue_date', [$this->startDate, $this->endDate]);
        }
        
        $invoices = $invoicesQuery->paginate(10);
        
        // Calculate total sales and platform commission (4%)
        $allInvoices = Invoice::all();
        $totalInvoiced = $allInvoices->sum('total');
        $totalPaid = $allInvoices->where('status', Invoice::STATUS_PAID)->sum('total');
        $platformCommission = $totalPaid * 0.04; // 4% of paid invoices
        
        $salesStats = [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'platform_commission' => $platformCommission,
            'total_pending' => $allInvoices->where('status', Invoice::STATUS_PENDING)->sum('total'),
            'total_overdue' => $allInvoices->where('status', Invoice::STATUS_OVERDUE)->sum('total'),
        ];

        return view('livewire.admin.sales-panel', [
            'invoices' => $invoices,
            'salesStats' => $salesStats,
            'statuses' => [
                '' => 'All Statuses',
                Invoice::STATUS_DRAFT => 'Draft',
                Invoice::STATUS_PENDING => 'Pending',
                Invoice::STATUS_PAID => 'Paid',
                Invoice::STATUS_PARTIAL => 'Partial',
                Invoice::STATUS_OVERDUE => 'Overdue',
                Invoice::STATUS_CANCELLED => 'Cancelled',
            ]
        ])->layout('components.layouts.admin');
    }
} 