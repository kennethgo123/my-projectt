<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Tabs -->
            <div class="mb-6">
                <div class="sm:hidden">
                    <label for="tabs" class="sr-only">Select a tab</label>
                    <select id="tabs" wire:model.live="activeTab" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="firm">Firm Availability</option>
                        <option value="lawyers">Lawyer Availability</option>
                    </select>
                </div>
                <div class="hidden sm:block">
                    <nav class="flex space-x-4 border-b" aria-label="Tabs">
                        <button wire:click="$set('activeTab', 'firm')" class="px-3 py-2 text-sm font-medium rounded-md {{ $activeTab === 'firm' ? 'bg-indigo-100 text-indigo-700 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Firm Availability
                        </button>
                        <button wire:click="$set('activeTab', 'lawyers')" class="px-3 py-2 text-sm font-medium rounded-md {{ $activeTab === 'lawyers' ? 'bg-indigo-100 text-indigo-700 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Lawyer Availability
                        </button>
                    </nav>
                </div>
            </div>
            
            <!-- Permission Setting for Lawyer Availability -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                @if (session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif
                
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="allowLawyerAvailability" 
                        wire:model.live="allowLawyerAvailability" 
                        wire:change="toggleAllowLawyerAvailability"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="allowLawyerAvailability" class="ml-2 block text-sm text-gray-900">
                        Allow lawyers under your firm to set their consultation time availability?
                    </label>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    If checked, lawyers under your firm can set their own availability. If unchecked, only firm administrators can manage lawyer availability.
                </p>
            </div>
            
            @if($activeTab === 'firm')
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Set Firm Availability</h2>
                    <!-- Firm availability management (similar to lawyer availability) -->
                    @livewire('lawyer.manage-availability', ['lawyerId' => auth()->id()])
                </div>
            @elseif($activeTab === 'lawyers')
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">Manage Lawyer Availability</h2>
                    @livewire('law-firm.manage-availability')
                </div>
            @endif
        </div>
    </div>
</div> 