<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationRequested extends Notification implements ShouldQueue
{
    use Queueable;

    protected $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $client = $this->consultation->client;
        
        return (new MailMessage)
            ->subject('New Consultation Request')
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have received a new consultation request from ' . $client->name)
            ->line('Consultation Type: ' . ucfirst($this->consultation->consultation_type))
            ->line('Description: ' . $this->consultation->description)
            ->action('View Request', route('lawyer.cases'))
            ->line('Please respond to this request within 48 hours.');
    }

    public function toArray($notifiable): array
    {
        return [
            'consultation_id' => $this->consultation->id,
            'client_name' => $this->consultation->client->name,
            'consultation_type' => $this->consultation->consultation_type,
            'message' => 'New consultation request from ' . $this->consultation->client->name,
            'action_url' => route('lawyer.cases'),
        ];
    }
} 