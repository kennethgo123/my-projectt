<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel as IlluminateDatabaseChannel;
use Illuminate\Notifications\Notification;

class DatabaseChannel extends IlluminateDatabaseChannel
{
    /**
     * The table name for the notification.
     *
     * @var string
     */
    protected $table = 'laravel_notifications';

    /**
     * Build the database record for the notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     */
    protected function buildPayload($notifiable, $notification)
    {
        return [
            'id' => $notification->id ?? \Illuminate\Support\Str::uuid()->toString(),
            'type' => get_class($notification),
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => $this->getData($notifiable, $notification),
            'read_at' => null,
            'is_read' => false,
        ];
    }
} 