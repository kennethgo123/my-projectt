<div>
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900">Case Events</h3>
        @if(!$isReadOnly ?? false)
            <button 
                x-data="{}"
                x-on:click="$dispatch('open-modal', 'add-event-modal')"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Event
            </button>
        @endif
    </div>

    @if (count($events) === 0)
        <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No events</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(!$isReadOnly ?? false)
                    Get started by adding a new event to this case.
                @else
                    This case has no events.
                @endif
            </p>
        </div>
    @else
        <!-- Events List -->
        <div class="overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Event
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        @if(!$isReadOnly ?? false)
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Edit</span>
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($events as $event)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($event->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($event->start_datetime)
                                    <div class="text-sm text-gray-900">
                                        {{ $event->start_datetime->format('M d, Y') }} 
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $event->start_datetime->format('h:i A') }}
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500">N/A</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $event->location ?: 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($event['is_completed'])
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @else
                                    @if (\Carbon\Carbon::parse($event['event_date'])->isPast())
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Past Due
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Upcoming
                                        </span>
                                    @endif
                                @endif
                            </td>
                            @if(!$isReadOnly ?? false)
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="prepareEditEvent({{ $event->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button 
                                        x-data 
                                        @click="if(confirm('Are you sure you want to delete this event?')) { $dispatch('delete-event', {{ $event->id }}) }"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div> 