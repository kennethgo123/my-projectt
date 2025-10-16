<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6 font-raleway">Consultation Availability</h2>
                
                @if (session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                
                <div class="mb-8">
                    <p class="mb-2 text-gray-700 font-open-sans">
                        Set your regular availability for client consultations. When clients book with you, they'll be able to select from these time slots.
                    </p>
                </div>
                
                <!-- Add New Time Slot Form -->
                <div class="bg-gray-50 p-6 rounded-lg mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 font-raleway">Add New Time Slot</h3>
                    
                    <form wire:submit.prevent="addTimeSlot" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="newDay" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Day of Week</label>
                                <select id="newDay" 
                                    wire:model.defer="newDay" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md font-open-sans">
                                    @foreach($days as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                                @error('newDay') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="newStartTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Start Time</label>
                                <input type="time" 
                                    id="newStartTime" 
                                    wire:model.defer="newStartTime" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                @error('newStartTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="newEndTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">End Time</label>
                                <input type="time" 
                                    id="newEndTime" 
                                    wire:model.defer="newEndTime" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                @error('newEndTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <!-- Lunch Break Section -->
                        <div class="mt-4">
                            <div class="flex items-center">
                                <input id="newHasLunchBreak" 
                                    type="checkbox" 
                                    wire:model.defer="newHasLunchBreak" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="newHasLunchBreak" class="ml-2 block text-sm text-gray-900 font-open-sans">
                                    Include lunch break
                                </label>
                            </div>
                            
                            @if($newHasLunchBreak)
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="newLunchStartTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Lunch Start Time</label>
                                    <input type="time" 
                                        id="newLunchStartTime" 
                                        wire:model.defer="newLunchStartTime" 
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                    @error('newLunchStartTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="newLunchEndTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Lunch End Time</label>
                                    <input type="time" 
                                        id="newLunchEndTime" 
                                        wire:model.defer="newLunchEndTime" 
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                    @error('newLunchEndTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-open-sans">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Add Time Slot
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Edit Time Slot Form (Conditionally shown) -->
                @if($editId)
                <div class="bg-yellow-50 p-6 rounded-lg mb-8 border border-yellow-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 font-raleway">Edit Time Slot</h3>
                    
                    <form wire:submit.prevent="updateTimeSlot" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="editDay" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Day of Week</label>
                                <select id="editDay" 
                                    wire:model.defer="editDay" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md font-open-sans">
                                    @foreach($days as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                                @error('editDay') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="editStartTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Start Time</label>
                                <input type="time" 
                                    id="editStartTime" 
                                    wire:model.defer="editStartTime" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                @error('editStartTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="editEndTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">End Time</label>
                                <input type="time" 
                                    id="editEndTime" 
                                    wire:model.defer="editEndTime" 
                                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                @error('editEndTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <!-- Lunch Break Section -->
                        <div class="mt-4">
                            <div class="flex items-center">
                                <input id="editHasLunchBreak" 
                                    type="checkbox" 
                                    wire:model.defer="editHasLunchBreak" 
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="editHasLunchBreak" class="ml-2 block text-sm text-gray-900 font-open-sans">
                                    Include lunch break
                                </label>
                            </div>
                            
                            @if($editHasLunchBreak)
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="editLunchStartTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Lunch Start Time</label>
                                    <input type="time" 
                                        id="editLunchStartTime" 
                                        wire:model.defer="editLunchStartTime" 
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                    @error('editLunchStartTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="editLunchEndTime" class="block text-sm font-medium text-gray-700 mb-1 font-open-sans">Lunch End Time</label>
                                    <input type="time" 
                                        id="editLunchEndTime" 
                                        wire:model.defer="editLunchEndTime" 
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md font-open-sans">
                                    @error('editLunchEndTime') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="resetEditForm" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-open-sans">
                                Cancel
                            </button>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-open-sans">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                @endif
                
                <!-- Current Availability Table -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4 font-raleway">Current Availability</h3>
                    
                    @if(count($availabilities) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-open-sans">
                                            Day
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-open-sans">
                                            Time Slot
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-open-sans">
                                            Lunch Break
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider font-open-sans">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider font-open-sans">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($availabilities as $availability)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 font-open-sans">
                                                {{ $availability['day_of_week'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-open-sans">
                                                {{ \Carbon\Carbon::parse($availability['start_time'])->format('g:i A') }} - 
                                                {{ \Carbon\Carbon::parse($availability['end_time'])->format('g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-open-sans">
                                                @if($availability['has_lunch_break'] && $availability['lunch_start_time'] && $availability['lunch_end_time'])
                                                    <span class="text-orange-600">
                                                        {{ \Carbon\Carbon::parse($availability['lunch_start_time'])->format('g:i A') }} - 
                                                        {{ \Carbon\Carbon::parse($availability['lunch_end_time'])->format('g:i A') }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">None</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-open-sans">
                                                @if($availability['is_available'])
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Available
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Unavailable
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <button 
                                                        wire:click="toggleAvailability({{ $availability['id'] }})" 
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                        title="{{ $availability['is_available'] ? 'Mark as unavailable' : 'Mark as available' }}">
                                                        @if($availability['is_available'])
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        @endif
                                                    </button>
                                                    <button 
                                                        wire:click="prepareEdit({{ $availability['id'] }})" 
                                                        class="text-blue-600 hover:text-blue-900"
                                                        title="Edit time slot">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </button>
                                                    <button 
                                                        wire:click="deleteTimeSlot({{ $availability['id'] }})" 
                                                        class="text-red-600 hover:text-red-900"
                                                        title="Delete time slot"
                                                        onclick="return confirm('Are you sure you want to delete this time slot?')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 font-raleway">No availability set</h3>
                            <p class="mt-1 text-sm text-gray-500 font-open-sans">Get started by setting up your first time slot.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Help text -->
                <div class="mt-8 bg-blue-50 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 text-sm text-blue-700 font-open-sans">
                            <h4 class="font-medium font-raleway">About Consultation Availability</h4>
                            <p class="mt-1">
                                Clients can now choose from your available time slots when booking consultations. 
                                You can still manually accept or decline these bookings from your consultation management page.
                            </p>
                            <p class="mt-1">
                                You can add multiple time slots for each day and include lunch breaks to block specific times. 
                                Make sure to avoid overlapping times. Time slots during lunch breaks will be automatically unavailable to clients.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
