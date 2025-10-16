<?php

namespace App\Livewire\Lawyers;

use App\Models\LawyerProfile as Profile;
use App\Models\Message;
use Livewire\Component;
use Illuminate\Support\Facades\Redirect;

class LawyerProfile extends Component
{
    public $lawyer;
    public $message = '';
    public $showMessageForm = false;

    public function mount(Profile $lawyer)
    {
        $this->lawyer = $lawyer;
    }

    public function startChat()
    {
        // Redirect to the chat page with the lawyer's user ID
        if (!auth()->check() || !auth()->user()->isClient()) {
            session()->flash('error', 'You must be logged in as a client to message a lawyer.');
            return;
        }

        return Redirect::route('messages.chat', ['userId' => $this->lawyer->user_id]);
    }

    public function sendMessage()
    {
        $this->validate([
            'message' => 'required|min:1|max:1000'
        ]);

        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->lawyer->user_id,
            'content' => $this->message
        ]);

        $this->message = '';
        $this->showMessageForm = false;
        session()->flash('message', 'Message sent successfully!');
    }

    public function render()
    {
        return view('livewire.lawyers.lawyer-profile', [
            'bio' => $this->lawyer->bio
        ]);
    }
} 