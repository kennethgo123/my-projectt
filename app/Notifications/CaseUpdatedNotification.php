<?php

namespace App\Notifications;

use App\Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CaseUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'laravel_notifications';

    protected $case;
    protected $title;
    protected $description;

    /**
     * Create a new notification instance.
     */
    public function __construct(LegalCase $case, string $title, string $description)
    {
        $this->case = $case;
        $this->title = $title;
        $this->description = $description;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on Your Legal Case')
            ->line('There has been an update to your legal case.')
            ->line('Title: ' . $this->title)
            ->line('Details: ' . $this->description)
            ->action('View Case Details', route('client.cases'))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'case_id' => $this->case->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => 'case_update',
            'link' => route('client.cases'),
        ];
    }
    
    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'case_id' => $this->case->id,
            'case_number' => $this->case->case_number,
            'title' => $this->title,
            'message' => $this->description,
        ];
    }
} 