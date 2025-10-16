<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Extensions\DatabaseNotification;

class AllNotifications extends Component
{
    use WithPagination;

    protected $listeners = ['notification-received' => '$refresh'];

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->databaseNotifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-updated');
        }
        
        // Check if the notification has an action_url in the data
        if ($notification && $notification->data && isset(json_decode($notification->data)->action_url)) {
            return redirect(json_decode($notification->data)->action_url);
        }
        
        return null;
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $notifications = auth()->user()->unreadDatabaseNotifications;
        
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
        
        $this->dispatch('notification-updated');
        $this->resetPage();
    }
    
    /**
     * Dismiss (delete) a notification
     */
    public function dismissNotification($notificationId)
    {
        $notification = auth()->user()->databaseNotifications()->find($notificationId);
        
        if ($notification) {
            $notification->delete();
            $this->dispatch('notification-updated');
        }
    }
    
    /**
     * Dismiss all notifications
     */
    public function dismissAll()
    {
        $notifications = auth()->user()->databaseNotifications;
        
        foreach ($notifications as $notification) {
            $notification->delete();
        }
        
        $this->dispatch('notification-updated');
        $this->resetPage();
    }
    
    public function render()
    {
        $notifications = auth()->user()->databaseNotifications()->latest()->paginate(10);
        $unreadCount = auth()->user()->unreadDatabaseNotifications()->count();
        
        return view('livewire.notifications.all-notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ])->layout('layouts.app', [
            'title' => 'All Notifications'
        ]);
    }
}
