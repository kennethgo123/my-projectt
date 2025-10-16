<!-- Team Management Modal -->
<div x-data="{ show: @entangle('showTeamModal') }" x-show="show" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div x-show="show" class="fixed inset-0 transition-opacity" aria-hidden="true"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div x-show="show"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Close button (X) in top right corner -->
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button @click="show = false" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Manage Case Team
                        </h3>
                        
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Add and remove lawyers to build a team for this case.
                            </p>
                        </div>
                        
                        @if (session()->has('error'))
                            <div class="mt-3 bg-red-50 border-l-4 border-red-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-9v4a1 1 0 11-2 0v-4a1 1 0 112 0zm0-4a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">
                                            {{ session('error') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if (session()->has('message'))
                            <div class="mt-3 bg-green-50 border-l-4 border-green-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-green-700">
                                            {{ session('message') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Case Information -->
                        @if($selectedCase2)
                            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                                <h4 class="text-medium font-medium text-blue-800">Case Information</h4>
                                <div class="grid grid-cols-3 gap-4 mt-2">
                                    <div>
                                        <p class="text-sm text-gray-500">Case Number:</p>
                                        <p class="text-sm font-medium">{{ $selectedCase2->case_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Title:</p>
                                        <p class="text-sm font-medium">{{ $selectedCase2->title }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Status:</p>
                                        <p class="text-sm font-medium">{{ ucfirst($selectedCase2->status) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Current Team Members -->
                            <div>
                                <h4 class="text-base font-medium text-gray-900 mb-4">Current Team Members</h4>
                                
                                @if(empty($assignedLawyers))
                                    <div class="text-sm text-gray-500 italic">
                                        No lawyers have been assigned to this case yet.
                                    </div>
                                @else
                                    <div class="overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lawyer</th>
                                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Primary</th>
                                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($assignedLawyers as $lawyer)
                                                    <tr>
                                                        <td class="px-3 py-2 whitespace-nowrap">
                                                            <div class="text-sm font-medium text-gray-900">{{ $lawyer['lawyer_name'] }}</div>
                                                            <div class="text-xs text-gray-500">Added: {{ $lawyer['assigned_at'] }}</div>
                                                        </td>
                                                        <td class="px-3 py-2 whitespace-nowrap">
                                                            <span class="text-sm text-gray-900">{{ $lawyer['role'] ?: 'None' }}</span>
                                                        </td>
                                                        <td class="px-3 py-2 whitespace-nowrap">
                                                            @if($lawyer['is_primary'])
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                    Primary
                                                                </span>
                                                            @else
                                                                <button wire:click="makePrimaryLawyer({{ $lawyer['id'] }})" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-indigo-100 hover:text-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                    Make Primary
                                                                </button>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium">
                                                            <div class="flex space-x-2">
                                                                <button wire:click="showEditLawyerRole({{ $lawyer['id'] }})" class="text-indigo-600 hover:text-indigo-900">
                                                                    Edit
                                                                </button>
                                                                @if(!$lawyer['is_primary'])
                                                                    <button wire:click="removeLawyerFromTeam({{ $lawyer['id'] }})" class="text-red-600 hover:text-red-900">
                                                                        Remove
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    
                                                    <!-- Edit Role Form (inline) -->
                                                    @if($editingLawyerId == $lawyer['id'])
                                                        <tr class="bg-gray-50">
                                                            <td colspan="4" class="px-3 py-2">
                                                                <div class="p-3 rounded-lg border border-gray-200">
                                                                    <h5 class="text-sm font-medium text-gray-900 mb-2">Edit Role for {{ $lawyer['lawyer_name'] }}</h5>
                                                                    
                                                                    <div class="space-y-3">
                                                                        <div>
                                                                            <label for="role-{{ $lawyer['id'] }}" class="block text-xs font-medium text-gray-700">
                                                                                Role
                                                                            </label>
                                                                            <input id="role-{{ $lawyer['id'] }}" wire:model="editingRole" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Lead Attorney, Co-counsel, etc.">
                                                                        </div>
                                                                        
                                                                        <div>
                                                                            <label for="notes-{{ $lawyer['id'] }}" class="block text-xs font-medium text-gray-700">
                                                                                Notes
                                                                            </label>
                                                                            <textarea id="notes-{{ $lawyer['id'] }}" wire:model="editingNotes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Additional notes about this lawyer's role"></textarea>
                                                                        </div>
                                                                        
                                                                        <div class="flex justify-end space-x-2">
                                                                            <button type="button" wire:click="$set('editingLawyerId', null)" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                                Cancel
                                                                            </button>
                                                                            <button type="button" wire:click="updateLawyerRole" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                                Save
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Add New Team Member -->
                            <div>
                                <h4 class="text-base font-medium text-gray-900 mb-4">Add Team Member</h4>
                                
                                @if(empty($availableLawyers))
                                    <div class="text-sm text-gray-500 italic">
                                        All lawyers in your firm are already assigned to this case.
                                    </div>
                                @else
                                    <div class="bg-white p-4 rounded-lg border border-gray-200">
                                        <form wire:submit.prevent="addLawyerToTeam">
                                            <div class="space-y-4">
                                                <div>
                                                    <label for="lawyer-select" class="block text-sm font-medium text-gray-700">
                                                        Select Lawyer
                                                    </label>
                                                    <select id="lawyer-select" wire:model="newLawyerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                        <option value="">-- Select a lawyer --</option>
                                                        @foreach($availableLawyers as $lawyer)
                                                            <option value="{{ $lawyer['id'] }}">
                                                                {{ $lawyer['name'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div>
                                                    <label for="role" class="block text-sm font-medium text-gray-700">
                                                        Role
                                                    </label>
                                                    <input id="role" wire:model="lawyerRole" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. Lead Attorney, Co-counsel, etc.">
                                                </div>
                                                
                                                <div>
                                                    <label for="notes" class="block text-sm font-medium text-gray-700">
                                                        Notes
                                                    </label>
                                                    <textarea id="notes" wire:model="lawyerNotes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Additional notes about this lawyer's role"></textarea>
                                                </div>
                                                
                                                <div class="pt-2">
                                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                                                        Add to Team
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="show = false" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div> 