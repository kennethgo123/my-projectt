<div class="space-y-6">
    <!-- Case Progression Monitor -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Case Progression Monitor</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Track progress and update clients in real-time</p>
            </div>
            <x-button wire:click="showAddPhaseModal">+ Add Phase</x-button>
        </div>
        
        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
            <!-- Phase Timeline -->
            <div class="relative">
                @foreach($phases as $index => $phase)
                    <div class="flex items-center mb-8">
                        <div class="flex items-center relative">
                            <div class="h-8 w-8 rounded-full border-2 {{ $phase->is_completed ? 'bg-green-500 border-green-500' : 'bg-blue-500 border-blue-500' }} flex items-center justify-center">
                                <span class="text-white text-sm">{{ $index + 1 }}</span>
                            </div>
                            @if(!$loop->last)
                                <div class="h-0.5 w-24 {{ $phase->is_completed ? 'bg-green-500' : 'bg-gray-300' }} ml-2"></div>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-medium text-gray-900">{{ $phase->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $phase->description }}</p>
                            <div class="mt-2 text-sm text-gray-500">
                                <span>{{ \Carbon\Carbon::parse($phase->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($phase->end_date)->format('M d, Y') }}</span>
                            </div>
                            
                            <!-- Tasks for this phase -->
                            @if($phase->tasks->isNotEmpty())
                                <div class="mt-3">
                                    <h5 class="text-sm font-medium text-gray-700">Tasks</h5>
                                    <ul class="mt-2 space-y-2">
                                        @foreach($phase->tasks as $task)
                                            <li class="flex items-center">
                                                <span class="h-2 w-2 rounded-full {{ $task->status === 'completed' ? 'bg-green-500' : 'bg-yellow-500' }}"></span>
                                                <span class="ml-2 text-sm text-gray-600">{{ $task->title }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Calendar and Tasks Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Calendar -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Calendar</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Upcoming events and deadlines</p>
                </div>
                <x-button wire:click="showAddEventModal">+ Add Event</x-button>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <!-- Calendar Component -->
                <div class="space-y-4">
                    @foreach($case->events()->orderBy('start_datetime')->get() as $event)
                        <div class="flex items-start space-x-4 p-4 rounded-lg {{ $event->start_datetime->isPast() ? 'bg-gray-50' : 'bg-blue-50' }}">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-500">
                                    <span class="text-sm font-medium leading-none text-white">{{ $event->start_datetime->format('d') }}</span>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                                <p class="text-sm text-gray-500">{{ $event->description }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $event->start_datetime->format('M d, Y h:i A') }}
                                    @if($event->end_datetime)
                                        - {{ $event->end_datetime->format('h:i A') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tasks -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Tasks</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage case tasks</p>
                </div>
                <x-button wire:click="showAddTaskModal">+ Add Task</x-button>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="space-y-4">
                    @foreach($case->tasks->sortBy('due_date') as $task)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600" 
                                    {{ $task->status === 'completed' ? 'checked' : '' }}>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                    <p class="text-xs text-gray-500">Due: {{ $task->due_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <span class="text-xs {{ $task->assigned_to_type === 'client' ? 'text-purple-600' : 'text-blue-600' }}">
                                {{ ucfirst($task->assigned_to_type) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Add Phase Modal -->
    <x-dialog-modal wire:model.live="showPhaseModal">
        <x-slot name="title">Add New Phase</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="phaseName" value="Phase Name" />
                    <x-input id="phaseName" type="text" class="mt-1 block w-full" wire:model="phaseName" />
                    <x-input-error for="phaseName" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="phaseDescription" value="Description" />
                    <x-textarea id="phaseDescription" class="mt-1 block w-full" wire:model="phaseDescription" />
                    <x-input-error for="phaseDescription" class="mt-2" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="phaseStartDate" value="Start Date" />
                        <x-input id="phaseStartDate" type="date" class="mt-1 block w-full" wire:model="phaseStartDate" />
                        <x-input-error for="phaseStartDate" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-label for="phaseEndDate" value="End Date" />
                        <x-input id="phaseEndDate" type="date" class="mt-1 block w-full" wire:model="phaseEndDate" />
                        <x-input-error for="phaseEndDate" class="mt-2" />
                    </div>
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showPhaseModal', false)" class="mr-2">Cancel</x-secondary-button>
            <x-button wire:click="savePhase">Add Phase</x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Add Event Modal -->
    <x-dialog-modal wire:model.live="showEventModal">
        <x-slot name="title">Add New Event</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="eventTitle" value="Event Title" />
                    <x-input id="eventTitle" type="text" class="mt-1 block w-full" wire:model="eventTitle" />
                    <x-input-error for="eventTitle" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="eventDescription" value="Description" />
                    <x-textarea id="eventDescription" class="mt-1 block w-full" wire:model="eventDescription" />
                    <x-input-error for="eventDescription" class="mt-2" />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-label for="eventStartDateTime" value="Start Date & Time" />
                        <x-input id="eventStartDateTime" type="datetime-local" class="mt-1 block w-full" wire:model="eventStartDateTime" />
                        <x-input-error for="eventStartDateTime" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-label for="eventEndDateTime" value="End Date & Time" />
                        <x-input id="eventEndDateTime" type="datetime-local" class="mt-1 block w-full" wire:model="eventEndDateTime" />
                        <x-input-error for="eventEndDateTime" class="mt-2" />
                    </div>
                </div>
                
                <div>
                    <x-label for="eventLocation" value="Location" />
                    <x-input id="eventLocation" type="text" class="mt-1 block w-full" wire:model="eventLocation" />
                    <x-input-error for="eventLocation" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="eventType" value="Event Type" />
                    <select id="eventType" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="eventType">
                        <option value="">Select Type</option>
                        <option value="hearing">Hearing</option>
                        <option value="meeting">Meeting</option>
                        <option value="deadline">Deadline</option>
                        <option value="other">Other</option>
                    </select>
                    <x-input-error for="eventType" class="mt-2" />
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-blue-600" wire:model="isEventShared">
                    <span class="ml-2 text-sm text-gray-600">Share with client</span>
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEventModal', false)" class="mr-2">Cancel</x-secondary-button>
            <x-button wire:click="saveEvent">Add Event</x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Add Task Modal -->
    <x-dialog-modal wire:model.live="showTaskModal">
        <x-slot name="title">Add New Task</x-slot>
        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="taskTitle" value="Task Title" />
                    <x-input id="taskTitle" type="text" class="mt-1 block w-full" wire:model="taskTitle" />
                    <x-input-error for="taskTitle" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="taskDescription" value="Description" />
                    <x-textarea id="taskDescription" class="mt-1 block w-full" wire:model="taskDescription" />
                    <x-input-error for="taskDescription" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="selectedPhaseId" value="Phase" />
                    <select id="selectedPhaseId" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="selectedPhaseId">
                        <option value="">Select Phase</option>
                        @foreach($phases as $phase)
                            <option value="{{ $phase->id }}">{{ $phase->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="selectedPhaseId" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="taskDueDate" value="Due Date" />
                    <x-input id="taskDueDate" type="date" class="mt-1 block w-full" wire:model="taskDueDate" />
                    <x-input-error for="taskDueDate" class="mt-2" />
                </div>
                
                <div>
                    <x-label for="taskAssignedTo" value="Assign To" />
                    <select id="taskAssignedTo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="taskAssignedTo">
                        <option value="client">Client</option>
                        <option value="lawyer">Lawyer</option>
                    </select>
                    <x-input-error for="taskAssignedTo" class="mt-2" />
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showTaskModal', false)" class="mr-2">Cancel</x-secondary-button>
            <x-button wire:click="saveTask">Add Task</x-button>
        </x-slot>
    </x-dialog-modal>
</div> 