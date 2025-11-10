<div>
    <!-- Flash Messages -->
    @if (session()->has('error'))
        <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <!-- Case Progression Monitor -->
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Case Progression Monitor</h3>
                <p class="text-sm text-gray-500">Track progress and case updates</p>
            </div>
            @if($canManagePhases && !$readOnly)
                <button 
                    type="button"
                    x-data="{}"
                    @click="$dispatch('open-modal', 'add-phase-modal')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-500 hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-400"
                >
                    + Phase
                </button>
            @endif
        </div>

        @if($phases->isEmpty())
            <div class="text-center w-full py-4">
                <p class="text-sm text-gray-500">No phases have been added yet.</p>
            </div>
        @else
            <!-- Phase Timeline -->
            <div class="relative pb-8">
                <!-- Container for the timeline -->
                <div class="relative px-4">
                    <!-- Base line (gray) -->
                    <div class="absolute top-4 left-0 right-0 h-2 bg-gray-200"></div>
                    
                    <!-- Progress line (blue) -->
                    @php
                        $totalPhases = $phases->count();
                        $progressWidth = 0; // Default to 0

                        if ($totalPhases > 1) {
                            $currentPhaseIndex = -1;
                            if ($currentPhase) {
                                // Find the 0-based index of the current phase
                                $currentPhaseIndex = $phases->search(function($p) use ($currentPhase) {
                                    return $p->id === $currentPhase->id;
                                });
                            }

                            if ($currentPhaseIndex !== -1) {
                                // Progress extends to the current phase's position
                                $progressPercentage = ($currentPhaseIndex / ($totalPhases - 1)) * 100;
                            } else {
                                // Fallback: if no current phase is explicitly set
                                $allCompleted = $phases->every(function($p) { return $p->is_completed; });
                                if ($allCompleted) {
                                    $progressPercentage = 100; // All phases completed
                                } else {
                                    $progressPercentage = 0; // No current phase, not all completed
                                }
                            }
                            $progressWidth = max(0, min(100, $progressPercentage)); // Clamp between 0 and 100
                        } elseif ($totalPhases === 1 && $currentPhase && $currentPhase->id === $phases->first()->id) {
                            // Single phase which is current: progress line is 0 as there are no segments.
                            $progressWidth = 0;
                        }
                    @endphp
                    
                    <div class="absolute top-4 left-0 h-2 bg-blue-500 transition-all duration-300" style="width: {{ $progressWidth }}%;"></div>
                    
                    <!-- Phase dots -->
                    <div class="relative flex justify-between">
                        @foreach ($phases as $index => $phase)
                            @php
                                $isCompleted = $phase->is_completed;
                                $isCurrent = $phase->is_current;
                                
                                if ($isCompleted) {
                                    $dotClass = 'bg-blue-500 border-blue-500';
                                    $textClass = 'text-blue-500';
                                } elseif ($isCurrent) {
                                    $dotClass = 'bg-blue-500 border-blue-500';
                                    $textClass = 'text-blue-500';
                                } else {
                                    $dotClass = 'bg-white border-gray-200';
                                    $textClass = 'text-gray-500';
                                }
                            @endphp
                            
                            <div class="flex flex-col items-center">
                                <!-- Phase dot -->
                                <button 
                                    type="button"
                                    x-data="{}"
                                    @click="$wire.setCurrentPhase({{ $phase->id }})"
                                    @if(!$canManagePhases || $readOnly) disabled @endif
                                    class="relative flex h-10 w-10 items-center justify-center rounded-full border-2 {{ $dotClass }} {{ ($canManagePhases && !$readOnly) ? 'cursor-pointer hover:border-blue-600' : 'cursor-default' }}"
                                >
                                    @if (!$isCompleted) {{-- Show number if not completed --}}
                                        <span class="{{ $isCurrent ? 'text-white' : 'text-gray-700' }}">{{ $index + 1 }}</span>
                                    @else {{-- Optionally show checkmark for completed --}}
                                         <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    @endif
                                </button>
                                
                                <!-- Phase name -->
                                <span class="mt-2 text-sm font-medium {{ $textClass }}">{{ $phase->name }}</span>
                                <!-- Phase dates -->
                                @if($phase->start_date)
                                    <span class="mt-1 text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($phase->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($phase->end_date)->format('M d, Y') }}
                                    </span>
                                @endif
                                
                                @if($canManagePhases && !$readOnly)
                                    <div class="mt-1 flex items-center justify-center space-x-2">
                                        <button 
                                            type="button"
                                            x-data="{}"
                                            @click="$wire.prepareEditPhase({{ $phase->id }}); $nextTick(() => $dispatch('open-modal', 'edit-phase-modal'))"
                                            class="text-xs text-blue-600 hover:text-blue-800"
                                        >
                                            Edit
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button 
                                            type="button"
                                            x-data="{}"
                                            @click="$wire.prepareDeletePhase({{ $phase->id }}); $nextTick(() => $dispatch('open-modal', 'delete-phase-modal'))"
                                            class="text-xs text-red-600 hover:text-red-800"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Current Phase Details -->
                @if($currentPhase)
                    <div class="mt-8">
                        <h4 class="text-xl font-semibold text-gray-900">Current Phase: {{ $currentPhase->name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Started: {{ $currentPhase->start_date ? \Carbon\Carbon::parse($currentPhase->start_date)->format('F j, Y') : 'N/A' }}
                            • Est. completion: {{ $currentPhase->end_date ? \Carbon\Carbon::parse($currentPhase->end_date)->format('F j, Y') : 'N/A' }}
                        </p>
                        
                        <!-- Phase Update Section -->
                        <div class="mt-6 bg-gray-50 rounded-lg p-4">
                            <h5 class="font-medium text-gray-900 mb-2">Phase Details</h5>
                            
                            <!-- Phase Description -->
                            <div class="text-sm text-gray-700 whitespace-pre-line mb-4">
                                {{ $currentPhase->description ?? 'No details provided.' }}
                            </div>

                            <!-- Phase Updates/Notes -->
                            <div class="space-y-4 mt-4">
                                <h6 class="font-medium text-gray-800 text-sm">Updates</h6>
                                @if(!empty($currentPhaseUpdates) && $currentPhaseUpdates->isNotEmpty())
                                    @foreach($currentPhaseUpdates as $update)
                                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
                                            <div class="text-sm text-gray-700 whitespace-pre-line">
                                                {{ $update->content }}
                                            </div>
                                            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                                                <span>By: {{ $update->creator_name }}</span>
                                                <span>{{ \Carbon\Carbon::parse($update->created_at)->format('M j, Y g:i A') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-sm text-gray-500 italic">No updates available for this phase.</p>
                                @endif
                            </div>

                            @if($canManagePhases && !$readOnly)
                                <div class="mt-4">
                                    <button 
                                        type="button"
                                        x-data="{}"
                                        @click="$wire.selectPhaseForUpdate({{ $currentPhase->id }}); $nextTick(() => $dispatch('open-modal', 'update-phase-modal'))"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Update Phase
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Client Updates Section -->
        <div class="mt-8">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Recent Updates</h4>
            <p class="text-sm text-gray-500 mb-4">Latest updates regarding case phases.</p>
            
            <div class="space-y-4">
                @forelse($phases->sortByDesc('updated_at')->take(3) as $phase)
                    <div class="bg-white border rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="inline-flex items-center justify-center h-10 w-10 rounded-full {{ $phase->is_completed ? 'bg-blue-100 text-blue-600' : ($phase->is_current ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600') }}">
                                   @if($phase->is_completed)
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                   @elseif($phase->is_current)
                                       <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.414L11 10.586V6z" clip-rule="evenodd"></path></svg>
                                   @else
                                        <span class="text-lg font-semibold">{{ $loop->remaining + 1 }}</span>
                                   @endif
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    Phase: {{ $phase->name }}
                                    <span class="text-xs text-gray-500 ml-2">
                                        {{ \Carbon\Carbon::parse($phase->updated_at)->format('M j, Y') }}
                                    </span>
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ Str::limit($phase->description, 150) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <p class="text-gray-500">No updates available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($canManagePhases && !$readOnly)
        <div class="mt-4 flex justify-end">
            @if($isPrimaryLawyer)
            <button 
                type="button"
                x-data="{}"
                @click="$dispatch('open-modal', 'close-case-modal')"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Close Case
            </button>
            @else
            <span class="inline-flex items-center px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-md">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Only the primary lawyer can close the case
            </span>
            @endif
        </div>
    @endif

    <!-- MODALS SECTION -->
    <!-- Each modal is defined once and references the associated Livewire method -->

    <!-- Success Modal (must be rendered BEFORE other modals to ensure proper z-index stacking) -->
    <div
        x-data="{
            show: @entangle('showSuccessModal').live,
        }"
        x-show="show"
        x-cloak
        style="display: none; position: fixed; inset: 0; z-index: 99999 !important;"
        x-init="
            $watch('show', show => {
                if (show) {
                    // Force z-index on show
                    $el.style.setProperty('z-index', '99999', 'important');
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            });
        "
        x-on:keydown.escape.window="show = false; $wire.closeSuccessModal()"
    >
        <!-- Backdrop -->
        <div x-show="show" 
             style="position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.75); z-index: 99998 !important;"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Content Container -->
        <div style="position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; z-index: 99999 !important; pointer-events: none;">
            <div x-show="show" 
                 style="pointer-events: auto;"
                 class="bg-white rounded-lg overflow-hidden shadow-2xl transform transition-all w-full max-w-md mx-auto"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="p-6">
                    <div class="flex justify-center items-center mb-4">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            Phase Added Successfully!
                        </h3>
                        <p class="text-sm text-gray-500 mb-6">
                            The phase has been added to your case timeline.
                        </p>
                        <div class="flex justify-center">
                            <button 
                                type="button"
                                wire:click="closeSuccessModal"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Proceed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Phase Modal -->
    <x-modal name="add-phase-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Phase
                </h2>
                <button type="button" @click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Error Message in Modal -->
            @if (session()->has('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-3 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <form wire:submit.prevent="addPhase">
                <div class="space-y-4">
                    <div>
                        <label for="selectedPhaseTemplate" class="block text-sm font-medium text-gray-700">Select Phase</label>
                        <select id="selectedPhaseTemplate" wire:model.live="selectedPhaseTemplate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('selectedPhaseTemplate') border-red-500 @enderror">
                            <option value="">-- Select a phase --</option>
                            <option value="PRE_LITIGATION">Pre-Litigation</option>
                            <option value="FILING_INITIATION">Filing/Initiation</option>
                            <option value="PRELIMINARY_PROCEEDINGS">Preliminary Proceedings</option>
                            <option value="PRE_TRIAL">Pre-Trial</option>
                            <option value="TRIAL">Trial</option>
                            <option value="POST_TRIAL">Post-Trial</option>
                            <option value="POST_JUDGMENT_REMEDIES">Post-Judgment Remedies</option>
                            <option value="ENFORCEMENT_EXECUTION">Enforcement/Execution</option>
                            <option value="OTHER">Other (Type Manually)</option>
                        </select>
                        @error('selectedPhaseTemplate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if($selectedPhaseTemplate === 'OTHER')
                    <div>
                        <label for="customPhaseName" class="block text-sm font-medium text-gray-700">Phase Name</label>
                        <input type="text" id="customPhaseName" wire:model.defer="customPhaseName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('customPhaseName') border-red-500 @enderror" placeholder="Enter phase name">
                        @error('customPhaseName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    @elseif($selectedPhaseTemplate && $selectedPhaseTemplate !== 'OTHER')
                    <div>
                        <label for="newPhaseName" class="block text-sm font-medium text-gray-700">Phase Name</label>
                        <input type="text" id="newPhaseName" wire:model="newPhaseName" readonly class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm" disabled>
                    </div>
                    @endif

                    <div>
                        <label for="newPhaseDescription" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="newPhaseDescription" wire:model.defer="newPhaseDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newPhaseDescription') border-red-500 @enderror" placeholder="Phase description will be auto-filled when you select a phase"></textarea>
                        @error('newPhaseDescription') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">You can modify the description if needed.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="newPhaseStartDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="newPhaseStartDate" wire:model.defer="newPhaseStartDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newPhaseStartDate') border-red-500 @enderror">
                            @error('newPhaseStartDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="newPhaseEndDate" class="block text-sm font-medium text-gray-700">Est. End Date</label>
                            <input type="date" id="newPhaseEndDate" wire:model.defer="newPhaseEndDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newPhaseEndDate') border-red-500 @enderror">
                            @error('newPhaseEndDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button type="button" @click="$dispatch('close')" class="mr-3">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        Add Phase
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Edit Phase Modal -->
    <x-modal name="edit-phase-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit Phase
                </h2>
                <button type="button" @click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Error Message in Modal -->
            @if (session()->has('error'))
                <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-3 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <form wire:submit.prevent="editPhase">
                <div class="space-y-4">
                    <div>
                        <label for="editSelectedPhaseTemplate" class="block text-sm font-medium text-gray-700">Select Phase</label>
                        <select id="editSelectedPhaseTemplate" wire:model.live="editSelectedPhaseTemplate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editSelectedPhaseTemplate') border-red-500 @enderror">
                            <option value="">-- Select a phase --</option>
                            <option value="PRE_LITIGATION">Pre-Litigation</option>
                            <option value="FILING_INITIATION">Filing/Initiation</option>
                            <option value="PRELIMINARY_PROCEEDINGS">Preliminary Proceedings</option>
                            <option value="PRE_TRIAL">Pre-Trial</option>
                            <option value="TRIAL">Trial</option>
                            <option value="POST_TRIAL">Post-Trial</option>
                            <option value="POST_JUDGMENT_REMEDIES">Post-Judgment Remedies</option>
                            <option value="ENFORCEMENT_EXECUTION">Enforcement/Execution</option>
                            <option value="OTHER">Other (Type Manually)</option>
                        </select>
                        @error('editSelectedPhaseTemplate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    @if($editSelectedPhaseTemplate === 'OTHER')
                    <div>
                        <label for="editCustomPhaseName" class="block text-sm font-medium text-gray-700">Phase Name</label>
                        <input type="text" id="editCustomPhaseName" wire:model.defer="editCustomPhaseName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editCustomPhaseName') border-red-500 @enderror" placeholder="Enter phase name">
                        @error('editCustomPhaseName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    @elseif($editSelectedPhaseTemplate && $editSelectedPhaseTemplate !== 'OTHER')
                    <div>
                        <label for="editPhaseName" class="block text-sm font-medium text-gray-700">Phase Name</label>
                        <input type="text" id="editPhaseName" wire:model="editPhaseName" readonly class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 sm:text-sm" disabled>
                    </div>
                    @endif

                    <div>
                        <label for="editPhaseDescription" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="editPhaseDescription" wire:model.defer="editPhaseDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editPhaseDescription') border-red-500 @enderror" placeholder="Phase description will be auto-filled when you select a phase"></textarea>
                        @error('editPhaseDescription') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">You can modify the description if needed.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="editPhaseStartDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="editPhaseStartDate" wire:model.defer="editPhaseStartDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editPhaseStartDate') border-red-500 @enderror">
                            @error('editPhaseStartDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="editPhaseEndDate" class="block text-sm font-medium text-gray-700">Est. End Date</label>
                            <input type="date" id="editPhaseEndDate" wire:model.defer="editPhaseEndDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editPhaseEndDate') border-red-500 @enderror">
                            @error('editPhaseEndDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button type="button" @click="$dispatch('close')" class="mr-3">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        Save Changes
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Update Phase Modal -->
    <x-modal name="update-phase-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Update Phase
                </h2>
                <button type="button" @click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="updatePhase">
                <div>
                    <label for="phaseUpdateText" class="block text-sm font-medium text-gray-700">Update Note</label>
                    <textarea id="phaseUpdateText" wire:model.defer="phaseUpdateText" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('phaseUpdateText') border-red-500 @enderror"></textarea>
                    @error('phaseUpdateText') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">Add an update note that will be visible to the client.</p>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button type="button" @click="$dispatch('close')" class="mr-3">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit">
                        Save Update
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Delete Phase Modal -->
    <x-modal name="delete-phase-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Phase
                </h2>
                <button type="button" @click="$dispatch('close'); $wire.resetDeleteForm()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <p class="mb-4 text-sm text-gray-600">
                Are you sure you want to delete the phase <strong>"{{ $deletePhaseName }}"</strong>? This action cannot be undone and will remove all associated data.
            </p>
            
            <div class="mt-6 flex justify-end">
                <x-secondary-button type="button" @click="$dispatch('close'); $wire.resetDeleteForm()" class="mr-3">
                    Cancel
                </x-secondary-button>
                <x-danger-button wire:click="confirmDeletePhase">
                    Delete Phase
                </x-danger-button>
            </div>
        </div>
    </x-modal>

    <!-- Close Case Modal -->
    <x-modal name="close-case-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Close Case
                </h2>
                <button type="button" @click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <p class="mb-4 text-sm text-gray-600">
                You are about to close this case. This will mark the case as completed and archive it.
                This action cannot be undone easily.
            </p>
            
            <form wire:submit.prevent="closeCase">
                <div>
                    <label for="caseCloseNote" class="block text-sm font-medium text-gray-700">Closing Note</label>
                    <textarea id="caseCloseNote" wire:model.defer="caseCloseNote" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('caseCloseNote') border-red-500 @enderror"></textarea>
                    @error('caseCloseNote') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">Add a closing note that will be visible to the client.</p>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button type="button" @click="$dispatch('close')" class="mr-3">
                        Cancel
                    </x-secondary-button>
                    <x-danger-button type="submit">
                        Close Case
                    </x-danger-button>
                </div>
            </form>
        </div>
    </x-modal>
</div> 