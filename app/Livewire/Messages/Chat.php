<?php

namespace App\Livewire\Messages;

use App\Models\Message;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

class Chat extends Component
{
    use WithPagination, WithFileUploads;

    public $message = '';
    public $recipient;
    public $attachment;
    public $conversation = [];
    public $search = '';
    public $isUploading = false;
    public $showAttachmentPreview = false;
    
    protected $listeners = ['refreshMessages' => '$refresh', 'scrollToBottom'];
    
    protected $rules = [
        'message' => 'required_without:attachment|string',
        'attachment' => 'nullable|file|max:10240', // 10MB max
    ];
    
    public function mount($userId = null)
    {
        if ($userId) {
            // Load the recipient with all possible profile relationships
            $this->recipient = User::with([
                'lawyerProfile', 
                'clientProfile', 
                'lawFirmProfile',
                'lawFirmLawyer',
                'role'
            ])->findOrFail($userId);
            
            // Mark all messages from this recipient as read
            $this->markMessagesAsRead();
            
            // Load conversation messages
            $this->loadConversation();
        }
    }

    public function updatedRecipient()
    {
        if ($this->recipient) {
            // Ensure profile relationships are loaded
            if (!$this->recipient->relationLoaded('lawyerProfile') || 
                !$this->recipient->relationLoaded('clientProfile') || 
                !$this->recipient->relationLoaded('lawFirmProfile') ||
                !$this->recipient->relationLoaded('lawFirmLawyer')) {
                
                $this->recipient->load(['lawyerProfile', 'clientProfile', 'lawFirmProfile', 'lawFirmLawyer', 'role']);
            }
            
            $this->markMessagesAsRead();
            $this->loadConversation();
        }
    }
    
    public function updatedAttachment()
    {
        $this->showAttachmentPreview = true;
    }
    
    public function removeAttachment()
    {
        $this->reset('attachment');
        $this->showAttachmentPreview = false;
    }

    public function markMessagesAsRead()
    {
        if (!$this->recipient) return;
        
        auth()->user()->receivedMessages()
            ->where('sender_id', $this->recipient->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function sendMessage()
    {
        $this->validate();

        $data = [
            'sender_id' => auth()->id(),
            'receiver_id' => $this->recipient->id,
            'content' => $this->message ?? '', // Allow empty content if there's an attachment
        ];

        // Handle file upload if present
        if ($this->attachment) {
            $path = $this->attachment->store('message-attachments', 'public');
            $data['attachment_path'] = $path;
            $this->isUploading = false;
            $this->showAttachmentPreview = false;
        }

        // Create the message with Philippine Standard Time
        $message = Message::create($data);
        
        // Send notification to the recipient
        NotificationService::newMessage($message);

        $this->reset(['message', 'attachment']);
        $this->loadConversation();
    }
    
    public function startConversation($userId)
    {
        // Load the recipient with all possible profile relationships
        $this->recipient = User::with([
            'lawyerProfile', 
            'clientProfile', 
            'lawFirmProfile',
            'lawFirmLawyer',
            'role'
        ])->findOrFail($userId);
        
        $this->markMessagesAsRead();
        $this->loadConversation();
    }
    
    public function getContactsProperty()
    {
        // Start with a base query for existing conversations
        $existingConversations = Message::where('sender_id', auth()->id())
            ->orWhere('receiver_id', auth()->id())
            ->select('sender_id', 'receiver_id')
            ->get()
            ->flatMap(function($message) {
                return [
                    $message->sender_id !== auth()->id() ? $message->sender_id : null,
                    $message->receiver_id !== auth()->id() ? $message->receiver_id : null
                ];
            })
            ->filter()
            ->unique()
            ->toArray();
            
        // For clients, show only lawyers/law firms they've messaged with or who have messaged them
        if (auth()->user()->isClient()) {
            $query = User::whereHas('role', function($q) {
                    $q->where('name', 'lawyer')
                      ->orWhere('name', 'law_firm');
                })
                ->whereIn('id', $existingConversations);
        } 
        // For lawyers/law firms, show only clients they've messaged with or who have messaged them
        else if (auth()->user()->isLawyer() || auth()->user()->isLawFirm()) {
            $query = User::whereHas('role', function($q) {
                    $q->where('name', 'client');
                })
                ->whereIn('id', $existingConversations);
        } 
        // For admins, show all users with existing conversations
        else {
            $query = User::where('id', '!=', auth()->id())
                ->whereIn('id', $existingConversations);
        }
        
        // Apply search if provided
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('lawyerProfile', function($q) {
                      $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('clientProfile', function($q) {
                      $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('lawFirmProfile', function($q) {
                      $q->where('firm_name', 'like', '%' . $this->search . '%');
                  });
            });
        }
        
        // Sort contacts: conversations with unread messages first, then by the most recent message
        return $query->with([
                'lawyerProfile' => function($q) { $q->select('id', 'user_id', 'first_name', 'last_name', 'photo_path'); },
                'clientProfile' => function($q) { $q->select('id', 'user_id', 'first_name', 'last_name', 'photo_path'); },
                'lawFirmProfile' => function($q) { $q->select('id', 'user_id', 'firm_name', 'photo_path'); },
                'lawFirmLawyer' => function($q) { $q->select('id', 'user_id', 'first_name', 'last_name', 'photo_path'); },
                'role'
            ])
            ->orderBy('name', 'asc')
            ->withCount(['receivedMessages' => function($q) {
                $q->where('sender_id', auth()->id())
                  ->whereNull('read_at');
            }])
            ->get()
            ->sortByDesc(function($contact) {
                // Check if there are unread messages
                $hasUnread = auth()->user()->receivedMessages()
                    ->where('sender_id', $contact->id)
                    ->whereNull('read_at')
                    ->exists();
                    
                // Get the timestamp of the most recent message
                $latestMessage = Message::where(function($q) use ($contact) {
                        $q->where('sender_id', auth()->id())
                          ->where('receiver_id', $contact->id);
                    })
                    ->orWhere(function($q) use ($contact) {
                        $q->where('sender_id', $contact->id)
                          ->where('receiver_id', auth()->id());
                    })
                    ->latest()
                    ->first();
                    
                return [
                    $hasUnread ? 1 : 0,
                    $latestMessage ? $latestMessage->created_at->timestamp : 0
                ];
            });
    }
    
    private function loadConversation()
    {
        if (!$this->recipient) {
            $this->conversation = collect();
            return;
        }

        $this->conversation = Message::where(function($query) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $this->recipient->id);
            })
            ->orWhere(function($query) {
            $query->where('sender_id', $this->recipient->id)
                  ->where('receiver_id', auth()->id());
            })
            ->with([
                'sender' => function($q) {
                    $q->with(['lawyerProfile', 'clientProfile', 'lawFirmProfile', 'lawFirmLawyer']);
                },
                'receiver' => function($q) {
                    $q->with(['lawyerProfile', 'clientProfile', 'lawFirmProfile', 'lawFirmLawyer']);
                }
            ])
            ->orderBy('created_at', 'asc')
            ->get();
        
        $this->dispatch('scrollToBottom');
    }

    public function getConversationProperty()
    {
        return $this->conversation;
    }
    
    public function getUnreadCountProperty()
    {
        return auth()->user()->receivedMessages()->whereNull('read_at')->count();
    }
    
    public function getContactUnreadCountProperty()
    {
        if (!$this->recipient) {
            return 0;
        }
        
        return auth()->user()->receivedMessages()
            ->where('sender_id', $this->recipient->id)
            ->whereNull('read_at')
            ->count();
    }
    
    public function scrollToBottom()
    {
        $this->dispatch('scrollMessagesToBottom');
    }

    // Add polling for new messages every 5 seconds
    public function getListeners()
    {
        return [
            'refreshMessages' => '$refresh', 
            'scrollToBottom',
            'echo-private:messages.'.auth()->id().',MessageSent' => 'refreshMessages'
        ];
    }

    public function refreshMessages()
    {
        if ($this->recipient) {
            $this->markMessagesAsRead();
            $this->loadConversation();
        }
    }

    // Add a method to find available users to message
    public function getAvailableContactsProperty()
    {
        // For clients, show lawyers/law firms they haven't messaged yet
        if (auth()->user()->isClient()) {
            return User::whereHas('role', function($q) {
                    $q->where('name', 'lawyer')
                      ->orWhere('name', 'law_firm');
                })
                ->whereNotIn('id', $this->contacts->pluck('id')->push(auth()->id()))
                ->with([
                    'lawyerProfile' => function($q) { $q->select('id', 'user_id', 'first_name', 'last_name', 'photo_path'); },
                    'lawFirmProfile' => function($q) { $q->select('id', 'user_id', 'firm_name', 'photo_path'); },
                    'lawFirmLawyer' => function($q) { $q->select('id', 'user_id', 'first_name', 'last_name', 'photo_path'); },
                    'role'
                ])
                ->limit(5)
                ->get();
        }
        
        return collect();
    }

    public function render()
    {
        return view('livewire.messages.chat', [
            'contacts' => $this->contacts,
            'availableContacts' => $this->availableContacts,
            'conversation' => $this->conversation,
            'unreadCount' => $this->unreadCount,
            'contactUnreadCount' => $this->contactUnreadCount
        ]);
    }
} 