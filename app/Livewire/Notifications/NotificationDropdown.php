<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Extensions\DatabaseNotification;

class NotificationDropdown extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->user();
        $this->unreadCount = $user->unreadDatabaseNotifications()->count();
        $this->notifications = $user->databaseNotifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id' => $notification->id,
                    'message' => $data['message'] ?? $data['title'] ?? '',
                    'action_url' => $data['action_url'] ?? '#',
                    'created_at' => $notification->created_at->diffForHumans(),
                    'read' => !is_null($notification->read_at),
                ];
            });
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->databaseNotifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        $notifications = auth()->user()->unreadDatabaseNotifications;
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
        $this->loadNotifications();
    }

    #[On('notification-received')]
    public function handleNewNotification()
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notifications.notification-dropdown');
    }
} 