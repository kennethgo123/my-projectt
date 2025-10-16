<div>
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-medium text-gray-900">Case Phases</h3>
        <button 
            x-data="{}"
            x-on:click="$dispatch('open-modal', 'add-phase-modal')"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Phase
        </button>
    </div>

    @if ($phases->isEmpty())
        <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No phases yet</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding a new phase to this case.</p>
        </div>
    @else
        <!-- Phase Timeline -->
        <div class="relative mb-8">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-between">
                @foreach ($phases as $index => $phase)
                    <div class="flex items-center">
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full {{ $phase->is_completed ? 'bg-green-500' : ($phase->is_current ? 'bg-indigo-600' : 'bg-gray-300') }} {{ $phase->is_completed ? 'text-white' : ($phase->is_current ? 'text-white' : 'text-gray-700') }}">
                            @if ($phase->is_completed)
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        <span class="ml-2 text-sm font-medium text-gray-900">{{ $phase->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Phase List -->
        <div class="mt-6 overflow-hidden border-b border-gray-200 shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Phase
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dates
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($phases as $phase)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $phase->name }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($phase->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($phase->start_date)->format('M d, Y') }} - 
                                    {{ \Carbon\Carbon::parse($phase->end_date)->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($phase->is_completed)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                @elseif ($phase->is_current)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        Current
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-3">
                                    @if (!$phase->is_current && !$phase->is_completed)
                                        <button 
                                            wire:click="setCurrentPhase({{ $phase->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Set Current
                                        </button>
                                    @endif
                                    
                                    @if ($phase->is_current && !$phase->is_completed)
                                        <button 
                                            wire:click="completePhase({{ $phase->id }})" 
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Complete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Add Phase Modal -->
    <div
        x-data="{ shown: false }"
        x-on:open-modal.window="$event.detail == 'add-phase-modal' ? shown = true : null"
        x-on:close-modal.window="$event.detail == 'add-phase-modal' ? shown = false : null"
        x-show="shown"
        x-cloak
        class="fixed z-10 inset-0 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div
                x-show="shown"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                x-show="shown"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Add New Phase
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Please fill in the details for the new case phase.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <form wire:submit.prevent="addPhase">
                        <div class="space-y-4">
                            <div>
                                <label for="newPhaseName" class="block text-sm font-medium text-gray-700">
                                    Phase Name
                                </label>
                                <div class="mt-1">
                                    <input 
                                        type="text" 
                                        id="newPhaseName" 
                                        wire:model="newPhaseName" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                                @error('newPhaseName') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="newPhaseDescription" class="block text-sm font-medium text-gray-700">
                                    Description
                                </label>
                                <div class="mt-1">
                                    <textarea 
                                        id="newPhaseDescription" 
                                        wire:model="newPhaseDescription" 
                                        rows="3" 
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    ></textarea>
                                </div>
                                @error('newPhaseDescription') 
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="newPhaseStartDate" class="block text-sm font-medium text-gray-700">
                                        Start Date
                                    </label>
                                    <div class="mt-1">
                                        <input 
                                            type="date" 
                                            id="newPhaseStartDate" 
                                            wire:model="newPhaseStartDate" 
                                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        >
                                    </div>
                                    @error('newPhaseStartDate') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="newPhaseEndDate" class="block text-sm font-medium text-gray-700">
                                        End Date
                                    </label>
                                    <div class="mt-1">
                                        <input 
                                            type="date" 
                                            id="newPhaseEndDate" 
                                            wire:model="newPhaseEndDate" 
                                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        >
                                    </div>
                                    @error('newPhaseEndDate') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button 
                                type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                            >
                                Add Phase
                            </button>
                            <button 
                                type="button"
                                x-on:click="shown = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 