<div class="relative">
    <!-- Notification Bell Button -->
    <button wire:click="toggleDropdown" class="relative p-1 text-gray-600 hover:text-gray-900 focus:outline-none">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    @if($showDropdown)
        <div class="absolute right-0 mt-2 w-80 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead" class="text-sm text-indigo-600 hover:text-indigo-900">
                            Mark all as read
                        </button>
                    @endif
                </div>

                @if(count($notifications) > 0)
                    <div class="space-y-4">
                        @foreach($notifications as $notification)
                            <div class="flex items-start {{ !$notification['read'] ? 'bg-blue-50' : '' }} p-3 rounded-lg">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $notification['action_url'] }}" 
                                       wire:click="markAsRead('{{ $notification['id'] }}')"
                                       class="block hover:bg-gray-50">
                                        <p class="text-sm text-gray-900">{{ $notification['message'] }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $notification['created_at'] }}</p>
                                    </a>
                                </div>
                                @if(!$notification['read'])
                                    <div class="ml-3">
                                        <button wire:click="markAsRead('{{ $notification['id'] }}')" class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No notifications</p>
                @endif
            </div>
        </div>
    @endif
</div> 