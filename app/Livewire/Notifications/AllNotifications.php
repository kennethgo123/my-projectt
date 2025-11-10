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
        $notification = auth()->user()->notifications()->find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-updated');
            
            // Get action URL from notification data
            $actionUrl = $this->getNotificationActionUrl($notification);
            
            if ($actionUrl) {
                return redirect($actionUrl);
            }
        }
        
        return null;
    }
    
    /**
     * Get the action URL for a notification
     */
    private function getNotificationActionUrl($notification)
    {
        if (!$notification || !$notification->data) {
            return null;
        }
        
        $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
        
        if (!is_array($data)) {
            $data = [];
        }
        
        // Check for action_url in data
        if (isset($data['action_url']) && !empty($data['action_url'])) {
            return $data['action_url'];
        }
        
        // Check for link in data (for AppNotification compatibility)
        if (isset($data['link']) && !empty($data['link'])) {
            return $data['link'];
        }
        
        // Get notification type from data or notification type field
        $type = $data['type'] ?? $notification->type ?? null;
        
        // Generate action URL based on notification type and data
        if ($type) {
            return $this->generateActionUrlFromType($type, $data);
        }
        
        return null;
    }
    
    /**
     * Generate action URL based on notification type
     */
    private function generateActionUrlFromType($type, $data)
    {
        $user = auth()->user();
        
        switch ($type) {
            case 'case_task_created':
            case 'case_task_updated':
            case 'case_task_deleted':
            case 'task_status_changed':
                if (isset($data['case_id'])) {
                    if ($user->hasRole('lawyer')) {
                        return route('lawyer.case.setup', $data['case_id']);
                    } elseif ($user->hasRole('client')) {
                        return route('client.case.overview', $data['case_id']);
                    } elseif ($user->hasRole('law_firm')) {
                        return route('law-firm.case-details', $data['case_id']);
                    }
                }
                break;
                
            case 'case_event_created':
            case 'case_event_updated':
            case 'case_event_deleted':
                if (isset($data['case_id'])) {
                    if ($user->hasRole('lawyer')) {
                        return route('lawyer.case.setup', $data['case_id']);
                    } elseif ($user->hasRole('client')) {
                        return route('client.case.overview', $data['case_id']);
                    } elseif ($user->hasRole('law_firm')) {
                        return route('law-firm.case-details', $data['case_id']);
                    }
                }
                break;
                
            case 'case_activated':
            case 'case_updated':
            case 'case_closed':
            case 'case_phase_update':
                if (isset($data['case_id'])) {
                    if ($user->hasRole('lawyer')) {
                        return route('lawyer.case.setup', $data['case_id']);
                    } elseif ($user->hasRole('client')) {
                        return route('client.case.overview', $data['case_id']);
                    } elseif ($user->hasRole('law_firm')) {
                        return route('law-firm.case-details', $data['case_id']);
                    }
                }
                break;
                
            case 'consultation_request':
            case 'consultation_accepted':
            case 'consultation_declined':
            case 'consultation_completed':
                if (isset($data['consultation_id'])) {
                    if ($user->hasRole('lawyer')) {
                        return route('lawyer.consultations.show', $data['consultation_id']);
                    } elseif ($user->hasRole('client')) {
                        return route('client.consultations.show', $data['consultation_id']);
                    }
                }
                break;
                
            case 'new_message':
                if (isset($data['conversation_id'])) {
                    return route('messages.show', $data['conversation_id']);
                }
                break;
                
            case 'invoice_created':
            case 'invoice_sent':
            case 'invoice_paid':
                if (isset($data['invoice_id'])) {
                    if ($user->hasRole('lawyer')) {
                        return route('lawyer.invoices.show', $data['invoice_id']);
                    } elseif ($user->hasRole('client')) {
                        return route('client.invoices.show', $data['invoice_id']);
                    }
                }
                break;
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
        $notification = auth()->user()->notifications()->find($notificationId);
        
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
        $notifications = auth()->user()->notifications;
        
        foreach ($notifications as $notification) {
            $notification->delete();
        }
        
        $this->dispatch('notification-updated');
        $this->resetPage();
    }
    
    public function render()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(10);
        $unreadCount = auth()->user()->unreadNotifications()->count();
        
        // Add action URLs to each notification
        $notifications->getCollection()->transform(function ($notification) {
            $notification->action_url = $this->getNotificationActionUrl($notification);
            return $notification;
        });
        
        return view('livewire.notifications.all-notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ])->layout('layouts.app', [
            'title' => 'All Notifications'
        ]);
    }
}
