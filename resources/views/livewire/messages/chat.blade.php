<div class="flex h-screen bg-gray-100" wire:poll.10s>
    <!-- Contacts Sidebar -->
    <div class="w-1/4 bg-white border-r">
        <div class="p-4 border-b sticky top-0 bg-white z-10">
            <h2 class="text-xl font-semibold">Messages</h2>
            <div class="mt-2 relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search contacts..." 
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-2 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
            @if($unreadCount > 0)
                <div class="mt-2 text-sm text-blue-600">
                    {{ $unreadCount }} unread {{ Str::plural('message', $unreadCount) }}
                </div>
            @endif
        </div>
        <div class="overflow-y-auto h-[calc(100vh-145px)]">
            @if($contacts->isEmpty())
                <div class="p-4 text-center text-gray-500">
                    @if(auth()->user()->isClient())
                        <p>You haven't started any conversations yet.</p>
                        <p class="mt-2 text-sm">Visit a lawyer's profile to send a message.</p>
                        
                        @if($availableContacts->isNotEmpty())
                            <div class="mt-6 border-t pt-4">
                                <h3 class="font-medium text-gray-700 mb-3">Suggested Contacts</h3>
                                @foreach($availableContacts as $contact)
                                    <div wire:click="startConversation({{ $contact->id }})" 
                                        class="p-3 hover:bg-gray-50 cursor-pointer mb-2 rounded-lg border border-gray-200 transition-colors">
                                        <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    @if($contact->isLawyer() && $contact->lawyerProfile && $contact->lawyerProfile->photo_path)
                                                        <img class="w-10 h-10 rounded-full object-cover" 
                                                            src="{{ Storage::url($contact->lawyerProfile->photo_path) }}" 
                                                            alt="{{ $contact->lawyerProfile->first_name }}">
                                                    @elseif($contact->isLawyer() && $contact->lawFirmLawyer && $contact->lawFirmLawyer->photo_path)
                                                        <img class="w-10 h-10 rounded-full object-cover" 
                                                            src="{{ Storage::url($contact->lawFirmLawyer->photo_path) }}" 
                                                            alt="{{ $contact->lawFirmLawyer->first_name }}">
                                                    @elseif($contact->isLawFirm() && $contact->lawFirmProfile && $contact->lawFirmProfile->photo_path)
                                                        <img class="w-10 h-10 rounded-full object-cover" 
                                                            src="{{ Storage::url($contact->lawFirmProfile->photo_path) }}" 
                                                            alt="{{ $contact->lawFirmProfile->firm_name }}">
                                                    @elseif($contact->profile_photo_path)
                                                        <img class="w-10 h-10 rounded-full object-cover" 
                                                            src="{{ Storage::url($contact->profile_photo_path) }}" 
                                                            alt="{{ $contact->name }}">
                                                    @else
                                                        <span class="text-gray-600 text-sm font-medium">
                                                            @if($contact->isLawyer() && $contact->lawyerProfile)
                                                                {{ substr($contact->lawyerProfile->first_name, 0, 1) }}
                                                            @elseif($contact->isLawyer() && $contact->lawFirmLawyer)
                                                                {{ substr($contact->lawFirmLawyer->first_name, 0, 1) }}
                                                            @elseif($contact->isLawFirm() && $contact->lawFirmProfile)
                                                                {{ substr($contact->lawFirmProfile->firm_name, 0, 1) }}
                                                            @else
                                                                {{ substr($contact->name, 0, 1) }}
                                                            @endif
                                                        </span>
                                                    @endif
                            </div>
                        </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if($contact->isLawyer() && $contact->lawyerProfile)
                                                        {{ $contact->lawyerProfile->first_name }} {{ $contact->lawyerProfile->last_name }}
                                                    @elseif($contact->isLawyer() && $contact->lawFirmLawyer)
                                                        {{ $contact->lawFirmLawyer->first_name }} {{ $contact->lawFirmLawyer->last_name }}
                                                    @elseif($contact->isLawFirm() && $contact->lawFirmProfile)
                                                        {{ $contact->lawFirmProfile->firm_name }}
                                                    @else
                                {{ $contact->name }}
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $contact->isLawyer() ? 'Lawyer' : ($contact->isLawFirm() ? 'Law Firm' : $contact->role->name) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
                            </div>
                        @endif
                    @elseif(auth()->user()->isLawyer() || auth()->user()->isLawFirm())
                        <p>You haven't started any conversations with clients yet.</p>
                        <p class="mt-2 text-sm">Clients will appear here when they message you.</p>
                    @else
                        <p>No conversations found.</p>
                    @endif
                </div>
            @else
                @foreach($contacts as $contact)
                    <div wire:click="startConversation({{ $contact->id }})" 
                        class="p-4 hover:bg-gray-50 cursor-pointer {{ $recipient && $recipient->id === $contact->id ? 'bg-gray-100' : '' }}">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    @if($contact->isLawyer() && $contact->lawyerProfile && $contact->lawyerProfile->photo_path)
                                        <img class="w-12 h-12 rounded-full object-cover" 
                                            src="{{ Storage::url($contact->lawyerProfile->photo_path) }}" 
                                            alt="{{ $contact->lawyerProfile->first_name }}">
                                    @elseif($contact->isLawyer() && $contact->lawFirmLawyer && $contact->lawFirmLawyer->photo_path)
                                        <img class="w-12 h-12 rounded-full object-cover" 
                                            src="{{ Storage::url($contact->lawFirmLawyer->photo_path) }}" 
                                            alt="{{ $contact->lawFirmLawyer->first_name }}">
                                    @elseif($contact->isClient() && $contact->clientProfile && $contact->clientProfile->photo_path)
                                        <img class="w-12 h-12 rounded-full object-cover" 
                                            src="{{ Storage::url($contact->clientProfile->photo_path) }}" 
                                            alt="{{ $contact->clientProfile->first_name }}">
                                    @elseif($contact->isLawFirm() && $contact->lawFirmProfile && $contact->lawFirmProfile->photo_path)
                                        <img class="w-12 h-12 rounded-full object-cover" 
                                            src="{{ Storage::url($contact->lawFirmProfile->photo_path) }}" 
                                            alt="{{ $contact->lawFirmProfile->firm_name }}">
                                    @elseif($contact->profile_photo_path)
                                        <img class="w-12 h-12 rounded-full object-cover" 
                                            src="{{ Storage::url($contact->profile_photo_path) }}" 
                                            alt="{{ $contact->name }}">
                                    @else
                                        <span class="text-gray-600 text-lg font-medium">
                                            @if($contact->isLawyer() && $contact->lawyerProfile)
                                                {{ substr($contact->lawyerProfile->first_name, 0, 1) }}
                                            @elseif($contact->isLawyer() && $contact->lawFirmLawyer)
                                                {{ substr($contact->lawFirmLawyer->first_name, 0, 1) }}
                                            @elseif($contact->isClient() && $contact->clientProfile)
                                                {{ substr($contact->clientProfile->first_name, 0, 1) }}
                                            @elseif($contact->isLawFirm() && $contact->lawFirmProfile)
                                                {{ substr($contact->lawFirmProfile->firm_name, 0, 1) }}
                                            @else
                                                {{ substr($contact->name, 0, 1) }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="flex justify-between items-start">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        @if($contact->isLawyer() && $contact->lawyerProfile)
                                            {{ $contact->lawyerProfile->first_name }} {{ $contact->lawyerProfile->last_name }}
                                        @elseif($contact->isLawyer() && $contact->lawFirmLawyer)
                                            {{ $contact->lawFirmLawyer->first_name }} {{ $contact->lawFirmLawyer->last_name }}
                                        @elseif($contact->isClient() && $contact->clientProfile)
                                            {{ $contact->clientProfile->first_name }} {{ $contact->clientProfile->last_name }}
                                        @elseif($contact->isLawFirm() && $contact->lawFirmProfile)
                                            {{ $contact->lawFirmProfile->firm_name }}
                                        @else
                                            {{ $contact->name }}
                                        @endif
                                    </p>
                                    @php
                                        $unreadCount = auth()->user()->receivedMessages()
                                            ->where('sender_id', $contact->id)
                                            ->whereNull('read_at')
                                            ->count();
                                    @endphp
                                    @if($unreadCount > 0)
                                        <span class="ml-2 inline-flex items-center justify-center h-5 w-5 bg-blue-500 text-white text-xs rounded-full">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                                @php
                                    $latestMessage = \App\Models\Message::where(function($q) use ($contact) {
                                        $q->where('sender_id', auth()->id())
                                            ->where('receiver_id', $contact->id);
                                        })
                                        ->orWhere(function($q) use ($contact) {
                                            $q->where('sender_id', $contact->id)
                                            ->where('receiver_id', auth()->id());
                                        })
                                        ->latest()
                                        ->first();
                                @endphp
                                @if($latestMessage)
                                    <p class="text-xs text-gray-500 truncate {{ $unreadCount > 0 ? 'font-semibold' : '' }}">
                                        @if($latestMessage->sender_id === auth()->id())
                                            <span class="text-gray-400">You: </span>
                                        @endif
                                        @if($latestMessage->attachment_path)
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-1">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                                </svg>
                                                Attachment
                                            </span>
                                        @else
                                            {{ Str::limit($latestMessage->content, 20) }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $latestMessage->getReadableTimestamp() }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Chat Area -->
    <div class="flex-1 flex flex-col">
        @if($recipient)
            <!-- Chat Header -->
            <div class="p-4 border-b bg-white sticky top-0 z-10">
                <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                            @if($recipient->isLawyer() && $recipient->lawyerProfile && $recipient->lawyerProfile->photo_path)
                                <img class="w-12 h-12 rounded-full object-cover" 
                                    src="{{ Storage::url($recipient->lawyerProfile->photo_path) }}" 
                                    alt="{{ $recipient->lawyerProfile->first_name }}">
                            @elseif($recipient->isLawyer() && $recipient->lawFirmLawyer && $recipient->lawFirmLawyer->photo_path)
                                <img class="w-12 h-12 rounded-full object-cover" 
                                    src="{{ Storage::url($recipient->lawFirmLawyer->photo_path) }}" 
                                    alt="{{ $recipient->lawFirmLawyer->first_name }}">
                            @elseif($recipient->isClient() && $recipient->clientProfile && $recipient->clientProfile->photo_path)
                                <img class="w-12 h-12 rounded-full object-cover" 
                                    src="{{ Storage::url($recipient->clientProfile->photo_path) }}" 
                                    alt="{{ $recipient->clientProfile->first_name }}">
                            @elseif($recipient->isLawFirm() && $recipient->lawFirmProfile && $recipient->lawFirmProfile->photo_path)
                                <img class="w-12 h-12 rounded-full object-cover" 
                                    src="{{ Storage::url($recipient->lawFirmProfile->photo_path) }}" 
                                    alt="{{ $recipient->lawFirmProfile->firm_name }}">
                            @elseif($recipient->profile_photo_path)
                                <img class="w-12 h-12 rounded-full object-cover" 
                                    src="{{ Storage::url($recipient->profile_photo_path) }}" 
                                    alt="{{ $recipient->name }}">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-gray-600 text-lg font-medium">
                                        @if($recipient->isLawyer() && $recipient->lawyerProfile)
                                            {{ substr($recipient->lawyerProfile->first_name, 0, 1) }}
                                        @elseif($recipient->isLawyer() && $recipient->lawFirmLawyer)
                                            {{ substr($recipient->lawFirmLawyer->first_name, 0, 1) }}
                                        @elseif($recipient->isClient() && $recipient->clientProfile)
                                            {{ substr($recipient->clientProfile->first_name, 0, 1) }}
                                        @elseif($recipient->isLawFirm() && $recipient->lawFirmProfile)
                                            {{ substr($recipient->lawFirmProfile->firm_name, 0, 1) }}
                                        @else
                                            {{ substr($recipient->name, 0, 1) }}
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-medium">
                                @if($recipient->isLawyer() && $recipient->lawyerProfile)
                                    {{ $recipient->lawyerProfile->first_name }} {{ $recipient->lawyerProfile->last_name }}
                                @elseif($recipient->isLawyer() && $recipient->lawFirmLawyer)
                                    {{ $recipient->lawFirmLawyer->first_name }} {{ $recipient->lawFirmLawyer->last_name }}
                                @elseif($recipient->isClient() && $recipient->clientProfile)
                                    {{ $recipient->clientProfile->first_name }} {{ $recipient->clientProfile->last_name }}
                                @elseif($recipient->isLawFirm() && $recipient->lawFirmProfile)
                                    {{ $recipient->lawFirmProfile->firm_name }}
                                @else
                                    {{ $recipient->name }}
                                @endif
                            </h3>
                            <p class="text-xs text-gray-500">
                                {{ $recipient->isLawyer() ? 'Lawyer' : ($recipient->isClient() ? 'Client' : $recipient->role->name) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50" id="message-container" 
                 x-data="{}" 
                 x-init="$wire.on('scrollMessagesToBottom', () => { 
                    $el.scrollTop = $el.scrollHeight; 
                 });
                 $nextTick(() => { $el.scrollTop = $el.scrollHeight; });"
                 x-on:scroll-to-bottom.window="$el.scrollTop = $el.scrollHeight">
                @if($conversation->isEmpty())
                    <div class="text-center text-gray-400 py-4">
                        No messages yet. Start the conversation!
                    </div>
                @else
                @foreach($conversation as $message)
                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="flex flex-col {{ $message->sender_id === auth()->id() ? 'items-end' : 'items-start' }} max-w-[70%]">
                                <div class="{{ $message->sender_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-white border' }} rounded-lg px-4 py-2">
                                    @if($message->content)
                                        <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                                    @endif
                                    
                                    @if($message->attachment_path)
                                        <div class="mt-2">
                                            @php
                                                $extension = pathinfo(Storage::url($message->attachment_path), PATHINFO_EXTENSION);
                                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            @endphp
                                            
                                            @if($isImage)
                                                <a href="{{ Storage::url($message->attachment_path) }}" target="_blank">
                                                    <img src="{{ Storage::url($message->attachment_path) }}" alt="Attachment" class="max-w-full max-h-48 rounded">
                                                </a>
                                            @else
                                                <a href="{{ Storage::url($message->attachment_path) }}" target="_blank" class="flex items-center space-x-2 {{ $message->sender_id === auth()->id() ? 'text-blue-100' : 'text-blue-500' }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                                                    </svg>
                                                    <span>Download attachment</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                    
                            <p class="text-xs {{ $message->sender_id === auth()->id() ? 'text-blue-100' : 'text-gray-500' }} mt-1">
                                        {{ $message->getReadableTimestamp() }}
                                    </p>
                                </div>
                                
                                @if($message->sender_id === auth()->id())
                                    <div class="text-xs text-gray-500 mt-1">
                                        @if($message->read_at)
                                            <span>Read</span>
                                        @else
                                            <span>Sent</span>
                                        @endif
                                    </div>
                                @endif
                        </div>
                    </div>
                @endforeach
                @endif
            </div>

            <!-- Message Input -->
            <div class="border-t p-4 bg-white">
                @if($showAttachmentPreview && $attachment)
                    <div class="mb-3 p-2 border rounded flex items-center justify-between bg-gray-50">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                            </svg>
                            <span class="text-sm truncate max-w-xs">{{ $attachment->getClientOriginalName() }}</span>
                        </div>
                        <button wire:click="removeAttachment" class="text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
                
                <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                    <div class="flex-1 relative">
                    <input type="text" 
                           wire:model="message" 
                               class="w-full rounded-full border-gray-300 pr-10 focus:border-blue-500 focus:ring-blue-500 text-sm py-3"
                           placeholder="Type your message...">
                        
                        <label for="file-upload" class="absolute right-3 top-2.5 cursor-pointer text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
                            </svg>
                        </label>
                        <input id="file-upload" type="file" wire:model="attachment" class="hidden" />
                    </div>
                    
                    <button type="submit" 
                            class="p-3 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                    </button>
                </form>
                
                <div class="text-xs text-gray-500 mt-2" wire:loading wire:target="attachment">
                    Uploading attachment...
                </div>
                
                <div class="text-xs text-gray-500 mt-2" wire:loading wire:target="sendMessage">
                    Sending message...
                </div>
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center bg-gray-50 p-8">
                <div class="text-center max-w-md">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-300 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">Your Messages</h3>
                    
                    @if($contacts->isEmpty())
                        @if(auth()->user()->isClient())
                            <p class="text-gray-500 mb-4">You haven't messaged any lawyers yet.</p>
                            
                            @if($availableContacts->isNotEmpty())
                                <div class="mt-6 border-t pt-6 max-w-md mx-auto">
                                    <h3 class="font-medium text-gray-700 mb-3">Start a conversation with:</h3>
                                    <div class="space-y-4">
                                        @foreach($availableContacts->take(3) as $contact)
                                            <div wire:click="startConversation({{ $contact->id }})" 
                                                class="p-4 hover:bg-gray-100 cursor-pointer rounded-lg border border-gray-200 transition-colors flex items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                                        @if($contact->profile_photo_url)
                                                            <img src="{{ $contact->profile_photo_url }}" alt="{{ $contact->name }}" class="w-12 h-12 rounded-full object-cover">
                                                        @elseif($contact->isLawyer() && $contact->lawyerProfile && $contact->lawyerProfile->photo_path)
                                                            <img src="{{ Storage::url($contact->lawyerProfile->photo_path) }}" alt="{{ $contact->lawyerProfile->first_name }}" class="w-12 h-12 rounded-full object-cover">
                                                        @elseif($contact->isLawyer() && $contact->lawFirmLawyer && $contact->lawFirmLawyer->photo_path)
                                                            <img src="{{ Storage::url($contact->lawFirmLawyer->photo_path) }}" alt="{{ $contact->lawFirmLawyer->first_name }}" class="w-12 h-12 rounded-full object-cover">
                                                        @elseif($contact->isLawFirm() && $contact->lawFirmProfile && $contact->lawFirmProfile->photo_path)
                                                            <img src="{{ Storage::url($contact->lawFirmProfile->photo_path) }}" alt="{{ $contact->lawFirmProfile->firm_name }}" class="w-12 h-12 rounded-full object-cover">
                                                        @else
                                                            <span class="text-gray-600 text-xl font-medium">
                                                                @if($contact->isLawyer() && $contact->lawyerProfile)
                                                                    {{ substr($contact->lawyerProfile->first_name, 0, 1) }}
                                                                @elseif($contact->isLawFirm() && $contact->lawFirmProfile)
                                                                    {{ substr($contact->lawFirmProfile->firm_name, 0, 1) }}
                                                                @else
                                                                    {{ substr($contact->name, 0, 1) }}
                                                                @endif
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <p class="font-medium text-gray-900">
                                                        @if($contact->isLawyer() && $contact->lawyerProfile)
                                                            {{ $contact->lawyerProfile->first_name }} {{ $contact->lawyerProfile->last_name }}
                                                        @elseif($contact->isLawyer() && $contact->lawFirmLawyer)
                                                            {{ $contact->lawFirmLawyer->first_name }} {{ $contact->lawFirmLawyer->last_name }}
                                                        @elseif($contact->isLawFirm() && $contact->lawFirmProfile)
                                                            {{ $contact->lawFirmProfile->firm_name }}
                                                        @else
                                                            {{ $contact->name }}
                                                        @endif
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $contact->isLawyer() ? 'Lawyer' : ($contact->isLawFirm() ? 'Law Firm' : $contact->role->name) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('lawyers.search') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                    Find a Lawyer
                                </a>
                            @endif
                        @elseif(auth()->user()->isLawyer() || auth()->user()->isLawFirm())
                            <p class="text-gray-500 mb-4">Clients will appear here when they message you.</p>
                        @else
                            <p class="text-gray-500 mb-4">No messages yet.</p>
                        @endif
                    @else
                        <p class="text-gray-500 mb-4">Select a contact from the list to start messaging.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div> 