<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $role = '';
    public $selectedUser = null;
    public $showViewModal = false;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'role' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingRole()
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

    public function render()
    {
        $users = User::query()
            // Exclude staff users (admins, department users, super admins)
            ->where(function($query) {
                $query->where('is_staff', false)
                      ->where('is_super_admin', false)
                      ->whereDoesntHave('departments');
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
            ->when($this->status, function($query) {
                $query->where('status', $this->status);
            })
            ->when($this->role, function($query) {
                $query->whereHas('role', function($q) {
                    $q->where('name', $this->role);
                });
            })
            ->with(['role'])
            ->latest()
            ->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users
        ])->layout('components.layouts.admin');
    }
} 