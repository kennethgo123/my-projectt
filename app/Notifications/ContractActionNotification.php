<?php

namespace App\Notifications;

use App\Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractActionNotification extends Notification implements ShouldQueue
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
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(LegalCase $case, string $title, string $message)
    {
        $this->case = $case;
        $this->title = $title;
        $this->message = $message;
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
            ->subject($this->title . ' - Case #' . $this->case->case_number)
            ->line($this->message)
            ->line('Case: ' . $this->case->title)
            ->line('Case Number: ' . $this->case->case_number)
            ->action('View Case Details', route('lawyer.cases.show', $this->case->id))
            ->line('Please review and take appropriate action.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
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
            'message' => $this->message,
            'action_url' => route('lawyer.cases.show', $this->case->id)
        ];
    }
} 