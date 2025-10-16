<div>
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900">Case Tasks</h3>
        {{-- Client can add tasks --}}
        <button 
            x-data="{}"
            x-on:click="$dispatch('open-modal', 'add-task-modal')"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Task
        </button>
    </div>

    @if (count($tasks) === 0)
        <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks yet</h3>
            <p class="mt-1 text-sm text-gray-500">You can add tasks for yourself or view tasks assigned by your lawyer here.</p>
        </div>
    @else
        <!-- Tasks List -->
        <div class="overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Task
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Due Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Assigned To
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($tasks as $task)
                        <tr wire:key="task-client-{{ $task->id }}">
                            <td class="px-6 py-4">
                                <div class="flex items-start">
                                    <!-- Add the clickable checkbox here -->
                                    <div class="flex-shrink-0 mr-3 mt-1">
                                        @php
                                            $isAssignedToClient = false;
                                            $isAssignedToLawyer = false;
                                            
                                            // Check new format first: assigned_to_id
                                            if (isset($task->assigned_to_id)) {
                                                if ($task->assigned_to_id === Auth::id()) {
                                                    $isAssignedToClient = true;
                                                } elseif ($task->assigned_to_id === $case->lawyer_id) {
                                                    $isAssignedToLawyer = true;
                                                }
                                            }
                                            
                                            // Check old format: assigned_to
                                            if (!$isAssignedToClient && !$isAssignedToLawyer && isset($task->assigned_to)) {
                                                if ($task->assigned_to == Auth::id()) {
                                                    $isAssignedToClient = true;
                                                } elseif ($task->assigned_to == $case->lawyer_id) {
                                                    $isAssignedToLawyer = true;
                                                }
                                            }
                                        @endphp
                                        
                                        <button 
                                            wire:click="toggleTaskCompletion({{ $task->id }})" 
                                            class="h-5 w-5 rounded border {{ $task->is_completed ? 'bg-blue-500 border-blue-500' : 'border-gray-300' }} flex items-center justify-center hover:bg-gray-100 focus:outline-none"
                                            {{ $case->isClosed() || $isAssignedToLawyer ? 'disabled' : '' }}
                                        >
                                            @if($task->is_completed)
                                                <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </button>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 {{ $task->is_completed ? 'line-through text-gray-500' : '' }}">{{ $task['title'] }}</div>
                                        <div class="text-sm text-gray-500 {{ $task->is_completed ? 'line-through' : '' }}">{{ Str::limit($task['description'], 50) }}</div>
                                        <!-- Add Assignment Badge -->
                                        @if ($isAssignedToClient)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                                <svg class="mr-1 h-3 w-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Assigned to You
                                            </span>
                                        @elseif ($isAssignedToLawyer)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                <svg class="mr-1 h-3 w-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                </svg>
                                                Assigned to Lawyer
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                <svg class="mr-1 h-3 w-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                                </svg>
                                                Unassigned
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if ($isAssignedToClient)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <svg class="mr-1 h-3 w-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        You
                                    </span>
                                @elseif ($isAssignedToLawyer)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="mr-1 h-3 w-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                        Lawyer
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <svg class="mr-1 h-3 w-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path>
                                        </svg>
                                        Unassigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($task->is_completed)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @else
                                    @if ($task->due_date && \Carbon\Carbon::parse($task->due_date)->isPast())
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Past Due
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Pending
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                {{-- Client can edit/delete tasks assigned to them (assuming component logic allows) --}}
                                {{-- @if($task->assigned_to_id === Auth::id()) --}}
                                    <button wire:click="prepareEditTask({{ $task->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button 
                                        wire:click="$dispatch('trigger-delete-task', { id: {{ $task->id }} })"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                {{-- @endif --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div> 