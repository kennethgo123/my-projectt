<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserApplicationApproved;
use App\Mail\UserApplicationRejected;
use App\Models\LawFirmLawyer;
use Livewire\Attributes\On; 

class PendingUsers extends Component
{
    use WithPagination;

    public $search = '';
    public $role = '';
    public $selectedUser = null;
    public $showViewModal = false;
    public $showRejectModal = false;
    public $rejectionReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
    ];

    protected $rules = [
        'rejectionReason' => 'required|min:10',
    ];

    // Update listeners for Livewire 3
    protected $listeners = [
        'closeViewModal',
        'closeRejectModal',
        'showRejectModal',
        'rejectUser'
    ];

    public function mount()
    {
        $this->search = request()->query('search', '');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRole()
    {
        $this->resetPage();
    }

    public function viewUser($userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->selectedUser->load([
            'role',
            'clientProfile',
            'lawyerProfile',
            'lawyerProfile.services',
            'lawFirmProfile',
            'lawFirmProfile.services',
            'lawFirmLawyer' 
        ]);
        logger()->info('Selected User Details:', $this->selectedUser->toArray());
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        logger()->info('closeViewModal method called');
        $this->showViewModal = false;
        $this->selectedUser = null;
    }

    #[On('open-reject-modal')]
    public function handleOpenRejectModal($userId)
    {
        logger()->info('handleOpenRejectModal event received with user ID: ' . $userId);
        $this->showRejectModal($userId);
    }

    // This method is now called by handleOpenRejectModal or directly by other $wire calls
    public function showRejectModal($userId)
    {
        logger()->info('showRejectModal method called with user ID: ' . $userId);
        $this->selectedUser = User::find($userId);
        if (!$this->selectedUser) {
            logger()->error('User not found for ID: ' . $userId);
            session()->flash('error', 'Could not find user to reject.');
            return;
        }
        $this->showViewModal = false; // Close view modal if open
        $this->showRejectModal = true;
        $this->rejectionReason = '';
        $this->resetValidation('rejectionReason');
        logger()->info('Modal state: ', [
            'showRejectModal' => $this->showRejectModal,
            'selectedUser' => $this->selectedUser->id
        ]);
    }

    public function closeRejectModal()
    {
        logger()->info('closeRejectModal method called');
        $this->showRejectModal = false;
        $this->rejectionReason = '';
        $this->resetValidation();
        $this->selectedUser = null; // Clear selected user when closing reject modal
    }

    public function approveUser($userId)
    {
        logger()->info('approveUser method called for user: ' . $userId);
        $user = User::findOrFail($userId);
        $user->update(['status' => 'approved']);
        
        if ($user->role->name === 'lawyer') {
            $lawFirmLawyer = LawFirmLawyer::where('user_id', $userId)->first();
            if ($lawFirmLawyer) {
                $lawFirmLawyer->update(['status' => 'approved']);
            }
        }
        
        Mail::to($user->email)->send(new UserApplicationApproved($user));
        session()->flash('message', 'User has been approved successfully.');
        $this->closeViewModal();
    }

    public function rejectUser()
    {
        logger()->info('rejectUser method called');
        $this->validate();

        if ($this->selectedUser) {
            $this->selectedUser->update([
                'status' => 'rejected',
                'rejection_reason' => $this->rejectionReason,
                'rejected_at' => now(),
                'profile_completed' => 0
            ]);

            if ($this->selectedUser->role && $this->selectedUser->role->name === 'lawyer') {
                $lawFirmLawyer = LawFirmLawyer::where('user_id', $this->selectedUser->id)->first();
                if ($lawFirmLawyer) {
                    $lawFirmLawyer->update(['status' => 'rejected']);
                }
            }

            Mail::to($this->selectedUser->email)->send(new UserApplicationRejected($this->selectedUser, $this->rejectionReason));
            session()->flash('message', 'User has been rejected successfully.');
            $this->closeRejectModal(); // This will also clear selectedUser
            $this->showViewModal = false; // Ensure view modal is also closed
        } else {
            logger()->error('No user selected for rejection');
            session()->flash('error', 'No user selected. Cannot reject.');
        }
    }

    public function render()
    {
        $users = User::with(['role', 'lawyerProfile', 'lawFirmProfile'])
            ->where('status', 'pending')
            ->when($this->role, function($query) {
                $query->whereHas('role', function($q) {
                    $q->where('name', $this->role);
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pending-users', [
            'users' => $users
        ])->layout('components.layouts.admin');
    }

    // Keeping the approve method as it might be used elsewhere or for consistency
    public function approve($userId)
    {
        logger()->info('approve method (alternative) called for user: ' . $userId);
        $this->approveUser($userId); // Delegate to the main approveUser method
    }
} 