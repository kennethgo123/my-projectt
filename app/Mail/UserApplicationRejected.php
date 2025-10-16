<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserApplicationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $rejectionReason;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $rejectionReason
     * @return void
     */
    public function __construct(User $user, $rejectionReason)
    {
        $this->user = $user;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-application-rejected')
                    ->subject('Your Application Status Update');
    }
} 