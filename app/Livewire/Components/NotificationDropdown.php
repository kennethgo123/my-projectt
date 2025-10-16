<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use App\Models\AppNotification;
use App\Extensions\DatabaseNotification;

class NotificationDropdown extends Component
{
    public $isOpen = false;
    
    // Realtime listener for new notifications
    #[On('notification-received')]
    public function refreshNotifications()
    {
        Log::info('Notification refresh triggered for user: ' . auth()->id());
    }
    
    public function getListeners()
    {
        if (auth()->check()) {
            return [
                'echo-private:user.' . auth()->id() . ',notification.received' => 'refreshNotifications',
                'notification-received' => 'refreshNotifications'
            ];
        }
        
        return [];
    }
    
    public function toggleDropdown()
    {
        $this->isOpen = !$this->isOpen;
    }
    
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
        }
        
        // Check if the notification has an action_url in the data
        if ($notification && isset($notification->data['action_url'])) {
            return redirect($notification->data['action_url']);
        }
        
        return null;
    }
    
    public function markAllAsRead()
    {
        $notifications = auth()->user()->unreadNotifications;
        
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
    }
    
    /**
     * Dismiss (delete) a notification
     */
    public function dismissNotification($notificationId)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->delete();
        }
        
        // Return the count of remaining unread notifications
        return auth()->user()->unreadNotifications()->count();
    }
    
    public function render()
    {
        $notifications = collect();
        $unreadCount = 0;
        
        if (auth()->check()) {
            $notifications = auth()->user()->notifications()->latest()->take(5)->get();
            $unreadCount = auth()->user()->unreadNotifications()->count();
        }
        
        return view('livewire.components.notification-dropdown', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}
