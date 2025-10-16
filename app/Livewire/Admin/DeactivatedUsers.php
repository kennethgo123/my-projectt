<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class DeactivatedUsers extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $statusFilter = '';
    public $selectedUser = null;
    public $showViewModal = false;
    public $showDeactivateModal = false;
    public $showReactivateModal = false;
    public $deactivationReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected $rules = [
        'deactivationReason' => 'required|min:5',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function viewUser($userId)
    {
        $this->selectedUser = User::where('id', $userId)
            ->with([
                'role',
                'clientProfile',
                'lawyerProfile',
                'lawyerProfile.services',
                'lawFirmProfile',
                'lawFirmProfile.services'
            ])
            ->first();
        
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedUser = null;
    }

    public function showDeactivateForm($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->deactivationReason = '';
        $this->showDeactivateModal = true;
    }

    public function closeDeactivateModal()
    {
        $this->showDeactivateModal = false;
        $this->selectedUser = null;
        $this->deactivationReason = '';
    }

    public function deactivateUser()
    {
        $this->validate();
        
        $user = $this->selectedUser;
        
        DB::transaction(function() use ($user) {
            $user->update([
                'status' => 'deactivated',
                'deactivation_reason' => $this->deactivationReason,
                'deactivated_at' => now(),
            ]);
        });
        
        $this->closeDeactivateModal();
        session()->flash('message', 'User has been deactivated successfully.');
    }

    public function showReactivateForm($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->showReactivateModal = true;
    }

    public function closeReactivateModal()
    {
        $this->showReactivateModal = false;
        $this->selectedUser = null;
    }

    public function reactivateUser()
    {
        $user = $this->selectedUser;
        
        DB::transaction(function() use ($user) {
            $user->update([
                'status' => 'approved',
                'deactivation_reason' => null,
                'deactivated_at' => null,
            ]);
        });
        
        $this->closeReactivateModal();
        session()->flash('message', 'User has been reactivated successfully.');
    }

    public function render()
    {
        $usersQuery = User::query()
            // Exclude staff users (admins, department users, super admins)
            ->where(function($query) {
                $query->where('is_staff', false)
                      ->where('is_super_admin', false)
                      ->whereDoesntHave('departments');
            })
            ->when($this->statusFilter === 'deactivated', function($query) {
                $query->where('status', 'deactivated');
            })
            ->when($this->statusFilter === 'active', function($query) {
                $query->where('status', 'approved');
            })
            ->when($this->statusFilter === '', function($query) {
                $query->whereIn('status', ['approved', 'deactivated']);
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('email', 'like', '%' . $this->search . '%')
                      ->orWhereHas('clientProfile', function($q) {
                          $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('lawyerProfile', function($q) {
                          $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('lawFirmProfile', function($q) {
                          $q->where('firm_name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->role, function($query) {
                $query->whereHas('role', function($q) {
                    $q->where('name', $this->role);
                });
            });
            
        $users = $usersQuery->with(['role'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.deactivated-users', [
            'users' => $users
        ])->layout('components.layouts.admin');
    }
} 