<div class="relative" x-data="{ open: @entangle('isOpen') }">
    <!-- Notification Bell Button -->
    <button 
        wire:click="toggleDropdown"
        class="relative p-1 text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 block h-3 w-3 rounded-full bg-red-500 ring-2 ring-white"></span>
            <span class="notification-count hidden">{{ $unreadCount }}</span>
        @else
            <span class="notification-count hidden">0</span>
        @endif
    </button>

    <!-- Notification Dropdown -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        @click.away="open = false"
        class="absolute right-0 mt-2 w-120 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        style="margin-top: 24px; left: -200px; width: 28rem;"
    >
        <div class="px-6 py-3 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-medium text-gray-900">Notifications</h3>
                @if($notifications->count() > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                    >
                        Mark all as read
                    </button>
                @endif
            </div>
        </div>

        <div class="max-h-90 overflow-y-auto">
            @forelse($notifications as $notification)
                <div 
                    x-data="{ dismissing: false }" 
                    x-show="!dismissing" 
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="transform opacity-100 translate-x-0"
                    x-transition:leave-end="transform opacity-0 translate-x-full"
                    class="px-6 py-4 hover:bg-gray-50 {{ $notification->is_read ? 'opacity-75' : '' }}"
                >
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            @if(isset($notification->data['type']))
                                @if($notification->data['type'] === 'consultation_request')
                                    <span class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                @elseif($notification->data['type'] === 'consultation_accepted')
                                    <span class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                @elseif($notification->data['type'] === 'consultation_declined')
                                    <span class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                @elseif($notification->data['type'] === 'consultation_completed')
                                    <span class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                @elseif($notification->data['type'] === 'meeting_link_updated')
                                    <span class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                @elseif($notification->data['type'] === 'case_started')
                                    <span class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </span>
                                @elseif($notification->data['type'] === 'new_message')
                                    <span class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                @endif
                            @else
                                <span class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <div class="flex justify-between items-start">
                                <a 
                                    href="{{ $notification->data['action_url'] ?? '#' }}"
                                    wire:click.prevent="markAsRead('{{ $notification->id }}')"
                                    class="text-base font-medium text-gray-900 hover:text-indigo-600"
                                >
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </a>
                                <button
                                    @click="dismissing = true; setTimeout(() => { $wire.dismissNotification('{{ $notification->id }}') }, 300)"
                                    class="text-gray-400 hover:text-gray-600 ml-2 focus:outline-none"
                                    title="Dismiss notification"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $notification->data['message'] ?? 'You have a new notification.' }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-9 w-9 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-2">No notifications yet.</p>
                </div>
            @endforelse
        </div>

        @if($notifications->count() > 0)
            <div class="py-2 border-t border-gray-100">
                <a href="{{ route('notifications.all') }}" class="block px-6 py-3 text-sm text-center font-medium text-indigo-600 hover:bg-gray-50">
                    View all notifications
                </a>
            </div>
        @endif
    </div>
</div>
