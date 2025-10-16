<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserStatusManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $showDeactivateModal = false;
    public $showReactivateModal = false;
    public $selectedUserId;
    public $deactivationReason = '';

    protected $rules = [
        'deactivationReason' => 'required|min:10|max:500',
    ];

    protected $messages = [
        'deactivationReason.required' => 'Please provide a reason for deactivation.',
        'deactivationReason.min' => 'The deactivation reason must be at least 10 characters.',
        'deactivationReason.max' => 'The deactivation reason cannot exceed 500 characters.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function showDeactivateModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->deactivationReason = '';
        $this->showDeactivateModal = true;
    }

    public function closeDeactivateModal()
    {
        $this->showDeactivateModal = false;
        $this->selectedUserId = null;
        $this->deactivationReason = '';
        $this->resetValidation();
    }

    public function showReactivateModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->showReactivateModal = true;
    }

    public function closeReactivateModal()
    {
        $this->showReactivateModal = false;
        $this->selectedUserId = null;
    }

    public function deactivateUser()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $user = User::findOrFail($this->selectedUserId);
            $user->status = 'inactive';
            $user->deactivation_reason = $this->deactivationReason;
            $user->deactivated_at = now();
            $user->save();

            DB::commit();

            session()->flash('message', 'User account has been deactivated successfully.');
            $this->closeDeactivateModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to deactivate user account. Please try again.');
        }
    }

    public function reactivateUser()
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($this->selectedUserId);
            $user->status = 'active';
            $user->deactivation_reason = null;
            $user->deactivated_at = null;
            $user->save();

            DB::commit();

            session()->flash('message', 'User account has been reactivated successfully.');
            $this->closeReactivateModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to reactivate user account. Please try again.');
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('clientProfile', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('lawyerProfile', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('lawFirmProfile', function ($q) {
                        $q->where('firm_name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('role', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->with(['role', 'clientProfile', 'lawyerProfile', 'lawFirmProfile'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.user-status-management', [
            'users' => $users
        ])->layout('components.layouts.admin');
    }
} 