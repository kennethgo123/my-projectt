<div x-data="{ activeTab: 'overview' }" 
    x-on:scrolltotop.window="window.scrollTo({top: 0, behavior: 'smooth'})"
    class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-md" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-md" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Archive Notice -->
    @if($isReadOnly)
        <div class="bg-gray-100 border-l-4 border-gray-500 text-gray-700 p-4 mb-6 rounded-md shadow-md" role="alert">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
                <p class="font-medium">This case has been closed and archived. You are in view-only mode.</p>
            </div>
        </div>
    @endif

    <!-- Page Header -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div class="flex-1">
                @if($editingCase)
                    <!-- Edit Mode -->
                    <div class="space-y-4">
                        <div>
                            <label for="editCaseNumber" class="block text-sm font-medium text-gray-700 mb-1">Case Number</label>
                            <input type="text" 
                                   id="editCaseNumber"
                                   wire:model="editCaseNumber" 
                                   placeholder="Enter case number (optional)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('editCaseNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="editCaseTitle" class="block text-sm font-medium text-gray-700 mb-1">Case Title</label>
                            <input type="text" 
                                   id="editCaseTitle"
                                   wire:model="editCaseTitle" 
                                   placeholder="Enter case title"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            @error('editCaseTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex space-x-2">
                            <button type="button" 
                                    wire:click="updateCaseDetails"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save
                            </button>
                            <button type="button" 
                                    wire:click="cancelEditingCase"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Display Mode -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">
                                {{ $case->case_number ? $case->case_number . ': ' : '' }}{{ $case->title ?? 'N/A' }}
                            </h1>
                            <p class="text-sm text-gray-600 mt-1">
                                Filed: {{ $case->created_at ? $case->created_at->format('F d, Y') : 'N/A' }}
                            </p>
                        </div>
                        @if(!$isReadOnly)
                            <button type="button" 
                                    wire:click="startEditingCase"
                                    class="ml-4 inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                        @endif
                    </div>
                @endif
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                @if($case->status === 'active')
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-700 border border-green-300">
                    <svg class="w-4 h-4 mr-1.5 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    Shared with Client
                </span>
                @endif
                
                @if($case->is_pro_bono)
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-700 border border-blue-300">
                    <svg class="w-4 h-4 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 2C13 2 17 4 17 8C17 10 15.5 11.5 13.5 12.5L12 14L10.5 12.5C8.5 11.5 7 10 7 8C7 4 11 2 11 2H13Z"/>
                        <path d="M6 12C6 12 2 14 2 18C2 20 3.5 21.5 5.5 22.5L7 24L8.5 22.5C10.5 21.5 12 20 12 18C12 14 8 12 8 12H6Z"/>
                        <path d="M18 12C18 12 22 14 22 18C22 20 20.5 21.5 18.5 22.5L17 24L15.5 22.5C13.5 21.5 12 20 12 18C12 14 16 12 16 12H18Z"/>
                    </svg>
                    Pro Bono Case
                </span>
                @endif
                
                <!-- Finish Setup Button - Only visible when contract is signed but case is not active yet -->
                @if(!$isReadOnly && $case->contract_status === 'signed' && $case->status !== 'active')
                <button 
                    type="button"
                    wire:click="markSetupComplete"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span wire:loading.remove>Finish Setup</span>
                    <span wire:loading wire:target="markSetupComplete">Processing...</span>
                </button>
                @endif
                
                
                <!-- Set Pro Bono Button - Only visible to primary lawyers if case is not already pro bono and not closed -->
                @if(!$isReadOnly && !$case->is_pro_bono && ($case->status !== 'completed' && $case->status !== 'closed') && $isPrimaryLawyer)
                <button 
                    type="button"
                    wire:click="openProBonoModal"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 2C13 2 17 4 17 8C17 10 15.5 11.5 13.5 12.5L12 14L10.5 12.5C8.5 11.5 7 10 7 8C7 4 11 2 11 2H13Z"/>
                        <path d="M6 12C6 12 2 14 2 18C2 20 3.5 21.5 5.5 22.5L7 24L8.5 22.5C10.5 21.5 12 20 12 18C12 14 8 12 8 12H6Z"/>
                        <path d="M18 12C18 12 22 14 22 18C22 20 20.5 21.5 18.5 22.5L17 24L15.5 22.5C13.5 21.5 12 20 12 18C12 14 16 12 16 12H18Z"/>
                    </svg>
                    Set Case as Pro Bono
                </button>
                @endif
                
                <!-- Simple Close Case Button - Only visible to primary lawyers and if case not already closed -->
                @if(!$isReadOnly && ($case->status !== 'completed' && $case->status !== 'closed') && $isPrimaryLawyer)
                <button 
                    type="button"
                    x-data="{}"
                    @click="$dispatch('open-modal', 'simple-close-case-modal')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Close Case
                </button>
                @elseif(!$isReadOnly && ($case->status !== 'completed' && $case->status !== 'closed') && !$isPrimaryLawyer)
                <span class="inline-flex items-center px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-md">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Only the primary lawyer can close the case
                </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="#" @click.prevent="activeTab = 'overview'"
                   :class="{ 'border-blue-500 text-blue-600': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Overview
                </a>
                <a href="#" @click.prevent="activeTab = 'documents'"
                   :class="{ 'border-blue-500 text-blue-600': activeTab === 'documents', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'documents' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Documents
                </a>
                <a href="#" @click.prevent="activeTab = 'timeline'"
                   :class="{ 'border-blue-500 text-blue-600': activeTab === 'timeline', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'timeline' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Timeline
                </a>
                 <a href="#" @click.prevent="activeTab = 'hearings'"
                   :class="{ 'border-blue-500 text-blue-600': activeTab === 'hearings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'hearings' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Events <!-- Maps to Events -->
                </a>
                <a href="#" @click.prevent="activeTab = 'tasks_management'"
                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'tasks_management', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'tasks_management' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Tasks Management
                </a>
                <a href="#" @click.prevent="activeTab = 'invoices'"
                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'invoices', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'invoices' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Invoices
                </a>
            </nav>
        </div>
    </div>

    <!-- Tab Content -->
    <div>
        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Calendar -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    @php
                        $carbonDate = \Carbon\Carbon::create($calendarYear, $calendarMonth, 1);
                        $monthName = $carbonDate->format('F');
                        $year = $calendarYear;
                        $today = \Carbon\Carbon::today()->format('Y-m-d');

                        $daysInMonth = $carbonDate->daysInMonth;
                        $firstDayOfMonth = $carbonDate->dayOfWeek; // 0 (Sun) to 6 (Sat)
                    @endphp
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Calendar</h3>
                        <div class="flex items-center space-x-2">
                            {{-- Add wire:click handlers here later for navigation --}}
                            <button class="text-gray-500 hover:text-gray-700" disabled> <!-- Disabled for now -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <span class="text-sm font-medium text-gray-700">{{ $monthName }} {{ $year }}</span>
                            <button class="text-gray-500 hover:text-gray-700" disabled> <!-- Disabled for now -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-sm">
                        <span class="text-gray-500 font-medium">Su</span>
                        <span class="text-gray-500 font-medium">Mo</span>
                        <span class="text-gray-500 font-medium">Tu</span>
                        <span class="text-gray-500 font-medium">We</span>
                        <span class="text-gray-500 font-medium">Th</span>
                        <span class="text-gray-500 font-medium">Fr</span>
                        <span class="text-gray-500 font-medium">Sa</span>

                        {{-- Padding for days before the 1st of the month --}}
                        @for ($i = 0; $i < $firstDayOfMonth; $i++)
                            <span></span>
                        @endfor

                        {{-- Days of the month --}}
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $currentDateStr = sprintf('%d-%02d-%02d', $year, $calendarMonth, $day);
                                $isToday = ($currentDateStr === $today);
                                $markerType = $markedDates[$currentDateStr] ?? null; // null, 'task', 'event', 'both'
                                
                                $dayClasses = 'w-8 h-8 flex items-center justify-center mx-auto rounded-full';
                                if ($isToday) {
                                    $dayClasses .= ' bg-blue-500 text-white'; // Today's style (Highest priority)
                                } elseif ($markerType === 'both') {
                                    // Special styling for days with both events and tasks - diagonal gradient background
                                    $dayClasses .= ' text-indigo-800 font-semibold'; // Text color for both
                                    // Add a custom style for the gradient background
                                    $gradientStyle = 'background: linear-gradient(135deg, #93c5fd 50%, #86efac 50%);';
                                } elseif ($markerType === 'event') {
                                    $dayClasses .= ' bg-blue-100 text-blue-700 font-semibold'; // Event only style
                                } elseif ($markerType === 'task') {
                                    $dayClasses .= ' bg-green-100 text-green-700 font-semibold'; // Task only style
                                } else {
                                    $dayClasses .= ' text-gray-700'; // Default day style
                                }
                            @endphp
                            <span class="{{ $dayClasses }}" @if($markerType === 'both') style="{{ $gradientStyle }}" @endif>{{ $day }}</span>
                        @endfor
                    </div>
                    <!-- Calendar Legend -->
                    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-center space-x-4 text-xs text-gray-600">
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-full bg-blue-500 mr-1.5"></span> Today
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-sm bg-blue-100 mr-1.5"></span> Event
                        </div>
                         <div class="flex items-center">
                            <span class="w-3 h-3 rounded-sm bg-green-100 mr-1.5"></span> Task Due
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 rounded-sm mr-1.5" style="background: linear-gradient(135deg, #93c5fd 50%, #86efac 50%);"></span> Both
                        </div>
                    </div>
                </div>

                <!-- Invoice Notification -->
                <div class="bg-white shadow-md rounded-lg p-4 mt-4">
                    <h4 class="font-medium text-gray-700 mb-2">Invoice</h4>
                    <p class="text-sm text-gray-600">
                        @php
                            // Get all invoices for this case
                            $allInvoices = \App\Models\Invoice::where('legal_case_id', $case->id)->get();
                            
                            // Check for pending invoices, ensuring we look at all invoice statuses that need attention
                            $pendingCount = $allInvoices->whereIn('status', ['pending', 'overdue'])->count();
                            
                            // Log for debugging
                            \Illuminate\Support\Facades\Log::info('Lawyer View - Pending Invoice Count: ' . $pendingCount);
                            \Illuminate\Support\Facades\Log::info('Lawyer View - Total Invoices: ' . $allInvoices->count());
                        @endphp
                        
                        @if($pendingCount > 0)
                            There {{ $pendingCount == 1 ? 'is' : 'are' }} 
                            <span class="font-semibold text-blue-600">{{ $pendingCount }}</span> 
                            pending {{ $pendingCount == 1 ? 'invoice' : 'invoices' }} 
                            for this case. Kindly refer to the 
                            <button @click.prevent="activeTab = 'invoices'" class="text-blue-500 hover:underline">invoice page</button> 
                            for more details.
                        @else
                            There is currently no pending invoice for this case.
                        @endif
                    </p>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Upcoming Events</h3>
                        <button @click.prevent="$dispatch('open-modal', 'add-event-modal')" 
                                {{ $isReadOnly ? 'disabled' : '' }}
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-3 rounded-md text-sm
                                {{ $isReadOnly ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        @if($events && $events->count() > 0)
                            @foreach($events->take(2) as $event) {{-- Displaying first 2 events as per image --}}
                                <div class="border border-blue-300 bg-blue-50 rounded-lg p-4"> {{-- Applied blue style directly --}}
                                     @if($event->start_datetime) {{-- Check if start_datetime exists --}}
                                        <p class="font-semibold text-blue-700">{{ $event->start_datetime->format('F j, Y') }}</p> {{-- Applied blue style directly --}}
                                        <p class="text-sm font-medium text-gray-800">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ $event->start_datetime->format('g:i A') }} 
                                            {{ $event->location ? '• ' . $event->location : '' }}
                                        </p>
                                     @else
                                        <p class="font-semibold text-blue-700">Date Not Set</p> {{-- Applied blue style directly --}}
                                        <p class="text-sm font-medium text-gray-800">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ $event->location ? '• ' . $event->location : '' }}
                                        </p>
                                     @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500">No upcoming events.</p>
                        @endif
                    </div>
                </div>

                <!-- Tasks -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Tasks</h3>
                        <button @click.prevent="$dispatch('open-modal', 'add-task-modal')" 
                                {{ $isReadOnly ? 'disabled' : '' }}
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-3 rounded-md text-sm
                                {{ $isReadOnly ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>
                    <div class="space-y-3">
                        @if($tasks && $tasks->count() > 0)
                            @foreach($tasks->take(5) as $task) {{-- Displaying first 5 tasks to show more including client tasks --}}
                            <div class="flex items-start" wire:key="task-overview-{{ $task->id }}">
                                <!-- Add clickable checkbox that toggles task completion -->
                                <button 
                                    wire:click="toggleTaskCompletion({{ $task->id }})" 
                                    class="h-4 w-4 rounded border {{ $task->is_completed ? 'bg-blue-500 border-blue-500' : 'border-gray-300' }} flex items-center justify-center hover:bg-gray-100 focus:outline-none mt-1"
                                    {{ $isReadOnly ? 'disabled' : '' }}
                                >
                                    @if($task->is_completed)
                                        <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </button>
                                <div class="ml-2 flex-grow">
                                    <label class="text-sm {{ $task->is_completed ? 'line-through text-gray-500' : 'text-gray-700' }}">{{ $task->title }}</label>
                                    <p class="text-xs {{ $task->is_completed ? 'line-through' : '' }} text-gray-500">
                                        Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                        @php
                                            $isAssignedToClient = false;
                                            $isAssignedToLawyer = false;
                                            
                                            // Check new format first: assigned_to_id
                                            if (isset($task->assigned_to_id)) {
                                                if ($task->assigned_to_id == $case->client_id) {
                                                    $isAssignedToClient = true;
                                                } elseif ($task->assigned_to_id == $case->lawyer_id) {
                                                    $isAssignedToLawyer = true;
                                                }
                                            }
                                            
                                            // Check old format: assigned_to
                                            if (!$isAssignedToClient && !$isAssignedToLawyer && isset($task->assigned_to)) {
                                                if ($task->assigned_to == $case->client_id) {
                                                    $isAssignedToClient = true;
                                                } elseif ($task->assigned_to == $case->lawyer_id) {
                                                    $isAssignedToLawyer = true;
                                                }
                                            }
                                            
                                            // Check array format (if $task is an array)
                                            if (!$isAssignedToClient && !$isAssignedToLawyer && is_array($task)) {
                                                if (isset($task['assigned_to_id'])) {
                                                    if ($task['assigned_to_id'] == $case->client_id) {
                                                        $isAssignedToClient = true;
                                                    } elseif ($task['assigned_to_id'] == $case->lawyer_id) {
                                                        $isAssignedToLawyer = true;
                                                    }
                                                } elseif (isset($task['assigned_to'])) {
                                                    if ($task['assigned_to'] == $case->client_id) {
                                                        $isAssignedToClient = true;
                                                    } elseif ($task['assigned_to'] == $case->lawyer_id) {
                                                        $isAssignedToLawyer = true;
                                                    }
                                                }
                                            }
                                        @endphp
                                    </p>
                                    
                                    <!-- Add Assignment Badge -->
                                    <div class="mt-1">
                                        @if ($isAssignedToClient)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                                <svg class="mr-1 h-3 w-3 text-purple-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Assigned to Client
                                            </span>
                                        @elseif ($isAssignedToLawyer)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                <svg class="mr-1 h-3 w-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                </svg>
                                                Assigned to You
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
                                @if($task->is_completed)
                                <span class="text-xs text-white bg-green-500 px-2 py-0.5 rounded-full ml-2 self-center">Done</span>
                                @endif
                            </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500">No tasks assigned.</p>
                        @endif
                        
                        @if($tasks && $tasks->count() > 5)
                            <div class="mt-3 text-center">
                                <button @click="activeTab = 'tasks_management'" 
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    View All {{ $tasks->count() }} Tasks →
                                </button>
                            </div>
                        @elseif($tasks && $tasks->count() > 0)
                            <div class="mt-3 text-center">
                                <button @click="activeTab = 'tasks_management'" 
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Manage Tasks →
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Middle Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Court Details Section -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Court Details</h3>
                        @if(!$isReadOnly)
                        <button type="button" @click="$dispatch('open-modal', 'edit-court-details-modal')" class="text-blue-600 hover:text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        @endif
                    </div>
                    <div class="space-y-3">
                        <div class="flex flex-col mb-2">
                            <span class="text-sm font-medium text-gray-500">Court Level</span>
                            <span class="text-gray-800 font-medium">
                                @if($case->court_level_main)
                                    {{ $case->court_level_main }}
                                @else
                                    <span class="text-gray-400 italic">Not specified</span>
                                @endif
                            </span>
                        </div>
                        
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-500">Specific Court</span>
                            <span class="text-gray-800 font-semibold">
                                @if($case->court_level_specific)
                                    @if($case->court_level_specific == 'RTC')
                                        Regional Trial Court (RTC)
                                    @elseif($case->court_level_specific == 'MeTC')
                                        Metropolitan Trial Court (MeTC)
                                    @elseif($case->court_level_specific == 'MTCC')
                                        Municipal Trial Court in Cities (MTCC)
                                    @elseif($case->court_level_specific == 'MTC')
                                        Municipal Trial Court (MTC)
                                    @elseif($case->court_level_specific == 'MCTC')
                                        Municipal Circuit Trial Court (MCTC)
                                    @else
                                        {{ $case->court_level_specific }}
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Not specified</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Case Progression Monitor (and current phase details) -->
                 <!-- The CasePhaseTracker component should contain the phase timeline, current phase details, and update phase button as per its own template -->
                <livewire:components.case-phase-tracker :caseId="$case->id" :readOnly="$isReadOnly" wire:key="case-phase-tracker-{{ $case->id }}" id="case-phase-tracker" />

                <!-- Client Details Section -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Client Details</h3>
                    <div class="space-y-3">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-500">Client Name</span>
                            <span class="text-gray-800 font-medium">{{ $case->client->clientProfile->first_name ?? 'N/A' }} {{ $case->client->clientProfile->last_name ?? '' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-500">Client Email</span>
                            <span class="text-gray-800">{{ $case->client->email ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Tab -->
        <div x-show="activeTab === 'documents'" x-cloak>
            @if(isset($case))
                @include('livewire.lawyer.partials.case-documents', ['documents' => $documents, 'isReadOnly' => $isReadOnly])
            @else
                <p>Loading documents...</p>
            @endif
        </div>

        <!-- Timeline Tab (integrates CasePhaseTracker) -->
        <div x-show="activeTab === 'timeline'" x-cloak>
             <livewire:components.case-phase-tracker :caseId="$case->id" :readOnly="$isReadOnly" wire:key="case-phase-tracker-timeline-{{ $case->id }}" />
        </div>
        
        <!-- Hearings Tab (integrates Events) -->
        <div x-show="activeTab === 'hearings'" x-cloak>
            @if(isset($case))
                @include('livewire.lawyer.partials.case-events', ['events' => $events, 'isReadOnly' => $isReadOnly])
            @else
                <p>Loading hearings...</p>
            @endif
        </div>

        <!-- Notes Tab (Placeholder) -->
        <div x-show="activeTab === 'notes'" x-cloak>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800">Case Notes</h3>
                <p class="mt-4 text-gray-600">This section will display case notes. Functionality to be implemented.</p>
                {{-- Add notes management UI here --}}
            </div>
        </div>

        <!-- Expenses Tab (Placeholder) -->
        <div x-show="activeTab === 'expenses'" x-cloak>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800">Case Expenses</h3>
                <p class="mt-4 text-gray-600">This section will display case expenses. Functionality to be implemented.</p>
                {{-- Add expenses management UI here --}}
            </div>
        </div>
        
        <!-- Client Access Tab (Placeholder) -->
        <div x-show="activeTab === 'client_access'" x-cloak>
            <div class="bg-white shadow-md rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800">Client Access Management</h3>
                <p class="mt-4 text-gray-600">This section will manage client access permissions. Functionality to be implemented.</p>
                {{-- Add client access management UI here --}}
            </div>
        </div>
        
        <!-- Tasks Management Tab (integrates Tasks) -->
        <div x-show="activeTab === 'tasks_management'" x-cloak>
             @if(isset($case))
                @include('livewire.lawyer.partials.case-tasks', ['tasks' => $tasks, 'isReadOnly' => $isReadOnly])
            @else
                <p>Loading tasks...</p>
            @endif
        </div>

        <!-- Invoices Tab -->
        <div x-show="activeTab === 'invoices'" x-cloak>
            <div 
                x-data="{}" 
                @invoice-modal-toggle.window="$dispatch('open-invoice-modal')"
                class="relative"
                style="position: static;">
                <livewire:lawyer.case-invoices-improved :case="$case" wire:key="case-invoices-improved-{{ $case->id }}" />
            </div>
        </div>

    </div>

    <!-- MODALS AREA -->
    <!-- Add Task Modal -->
    <div x-data="{ shown: false }" x-on:open-modal.window="$event.detail == 'add-task-modal' ? shown = true : null" x-on:close-modal.window="$event.detail == 'add-task-modal' ? shown = false : null" x-show="shown" x-cloak class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title-add-task" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-add-task">
                                Add New Task
                            </h3>
                            <button type="button" x-on:click="shown = false; $wire.call('resetTaskForm')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2"><p class="text-sm text-gray-500">Please provide the details for the new task.</p></div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <form wire:submit.prevent="addTask">
                        <div class="space-y-4">
                            <div>
                                <label for="newTaskTitleModal" class="block text-sm font-medium text-gray-700">Task Title</label>
                                <div class="mt-1"><input type="text" id="newTaskTitleModal" wire:model.defer="newTaskTitle" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('newTaskTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="newTaskDescriptionModal" class="block text-sm font-medium text-gray-700">Description</label>
                                <div class="mt-1"><textarea id="newTaskDescriptionModal" wire:model.defer="newTaskDescription" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea></div>
                                @error('newTaskDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="newTaskDueDateModal" class="block text-sm font-medium text-gray-700">Due Date</label>
                                <div class="mt-1"><input type="date" id="newTaskDueDateModal" wire:model.defer="newTaskDueDate" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('newTaskDueDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="newTaskAssignedToModal" class="block text-sm font-medium text-gray-700">Assign To</label>
                                <div class="mt-1">
                                    <select id="newTaskAssignedToModal" wire:model.defer="newTaskAssignedTo" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="client">Client</option>
                                        <option value="lawyer">Lawyer (Me)</option>
                                    </select>
                                </div>
                                @error('newTaskAssignedTo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">Add Task</button>
                            <button type="button" x-on:click="shown = false; $wire.call('resetTaskForm')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div x-data="{ shown: false }" x-on:open-modal.window="$event.detail == 'add-event-modal' ? shown = true : null" x-on:close-modal.window="$event.detail == 'add-event-modal' ? shown = false : null" x-show="shown" x-cloak class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title-add-event" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-add-event">
                                Add New Event
                            </h3>
                            <button type="button" x-on:click="shown = false; $wire.call('resetEventForm')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2"><p class="text-sm text-gray-500">Please provide the details for the new case event.</p></div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <form wire:submit.prevent="addEvent">
                        <div class="space-y-4">
                            <div>
                                <label for="newEventTitleModal" class="block text-sm font-medium text-gray-700">Event Title</label>
                                <div class="mt-1"><input type="text" id="newEventTitleModal" wire:model.defer="newEventTitle" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('newEventTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="newEventDescriptionModal" class="block text-sm font-medium text-gray-700">Description</label>
                                <div class="mt-1"><textarea id="newEventDescriptionModal" wire:model.defer="newEventDescription" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea></div>
                                @error('newEventDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="newEventDateModal" class="block text-sm font-medium text-gray-700">Date</label>
                                    <div class="mt-1"><input type="date" id="newEventDateModal" wire:model.defer="newEventDate" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                    @error('newEventDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="newEventTimeModal" class="block text-sm font-medium text-gray-700">Time</label>
                                    <div class="mt-1"><input type="time" id="newEventTimeModal" wire:model.defer="newEventTime" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                    @error('newEventTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="newEvent_typeModal" class="block text-sm font-medium text-gray-700">Event Type</label>
                                <div class="mt-1">
                                    <select id="newEvent_typeModal" wire:model.defer="newEvent_type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="meeting">Meeting</option>
                                        <option value="hearing">Hearing</option>
                                        <option value="deadline">Deadline</option>
                                        <option value="consultation">Consultation</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                @error('newEvent_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="newEventLocationModal" class="block text-sm font-medium text-gray-700">Location (optional)</label>
                                <div class="mt-1"><input type="text" id="newEventLocationModal" wire:model.defer="newEventLocation" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('newEventLocation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">Add Event</button>
                            <button type="button" x-on:click="shown = false; $wire.call('resetEventForm')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div x-data="{ shown: false }" x-on:open-modal.window="$event.detail == 'edit-task-modal' ? shown = true : null" x-on:close-modal.window="$event.detail == 'edit-task-modal' ? shown = false : null" x-show="shown" x-cloak class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title-edit-task" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-edit-task">
                                Edit Task
                            </h3>
                            <button type="button" x-on:click="shown = false; $wire.call('resetEditTaskForm')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2"><p class="text-sm text-gray-500">Update the task details below.</p></div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <form wire:submit.prevent="updateTask">
                        <div class="space-y-4">
                            <div>
                                <label for="editTaskTitleModal" class="block text-sm font-medium text-gray-700">Task Title</label>
                                <div class="mt-1"><input type="text" id="editTaskTitleModal" wire:model.defer="editTaskTitle" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('editTaskTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="editTaskDescriptionModal" class="block text-sm font-medium text-gray-700">Description</label>
                                <div class="mt-1"><textarea id="editTaskDescriptionModal" wire:model.defer="editTaskDescription" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea></div>
                                @error('editTaskDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="editTaskDueDateModal" class="block text-sm font-medium text-gray-700">Due Date</label>
                                <div class="mt-1"><input type="date" id="editTaskDueDateModal" wire:model.defer="editTaskDueDate" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('editTaskDueDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="editTaskAssignedToModal" class="block text-sm font-medium text-gray-700">Assign To</label>
                                <div class="mt-1">
                                    <select id="editTaskAssignedToModal" wire:model.defer="editTaskAssignedTo" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="client">Client</option>
                                        <option value="lawyer">Lawyer (Me)</option>
                                    </select>
                                </div>
                                @error('editTaskAssignedTo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">Update Task</button>
                            <button type="button" x-on:click="shown = false; $wire.call('resetEditTaskForm')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div x-data="{ shown: false }" x-on:open-modal.window="$event.detail == 'edit-event-modal' ? shown = true : null" x-on:close-modal.window="$event.detail == 'edit-event-modal' ? shown = false : null" x-show="shown" x-cloak class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title-edit-event" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="shown" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="mt-3 text-center sm:mt-5">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title-edit-event">
                                Edit Event
                            </h3>
                            <button type="button" x-on:click="shown = false; $wire.call('resetEditEventForm')" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2"><p class="text-sm text-gray-500">Update the event details below.</p></div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6">
                    <form wire:submit.prevent="updateEvent">
                        <div class="space-y-4">
                            <div>
                                <label for="editEventTitleModal" class="block text-sm font-medium text-gray-700">Event Title</label>
                                <div class="mt-1"><input type="text" id="editEventTitleModal" wire:model.defer="editEventTitle" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('editEventTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="editEventDescriptionModal" class="block text-sm font-medium text-gray-700">Description</label>
                                <div class="mt-1"><textarea id="editEventDescriptionModal" wire:model.defer="editEventDescription" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea></div>
                                @error('editEventDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="editEventDateModal" class="block text-sm font-medium text-gray-700">Date</label>
                                    <div class="mt-1"><input type="date" id="editEventDateModal" wire:model.defer="editEventDate" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                    @error('editEventDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="editEventTimeModal" class="block text-sm font-medium text-gray-700">Time</label>
                                    <div class="mt-1"><input type="time" id="editEventTimeModal" wire:model.defer="editEventTime" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                    @error('editEventTime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label for="editEvent_typeModal" class="block text-sm font-medium text-gray-700">Event Type</label>
                                <div class="mt-1">
                                    <select id="editEvent_typeModal" wire:model.defer="editEvent_type" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                        <option value="meeting">Meeting</option>
                                        <option value="hearing">Hearing</option>
                                        <option value="deadline">Deadline</option>
                                        <option value="consultation">Consultation</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                @error('editEvent_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="editEventLocationModal" class="block text-sm font-medium text-gray-700">Location (optional)</label>
                                <div class="mt-1"><input type="text" id="editEventLocationModal" wire:model.defer="editEventLocation" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></div>
                                @error('editEventLocation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">Update Event</button>
                            <button type="button" x-on:click="shown = false; $wire.call('resetEditEventForm')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Direct Close Case Modal -->
    <x-modal name="simple-close-case-modal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Close Case
            </h2>
            
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
                    <x-secondary-button x-on:click="$dispatch('close')" type="button">
                        Cancel
                    </x-secondary-button>
                    <x-danger-button type="submit" class="ml-3" wire:loading.attr="disabled">
                        Close Case
                    </x-danger-button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Pro Bono Confirmation Modal -->
    @if($showProBonoConfirmation)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13 2C13 2 17 4 17 8C17 10 15.5 11.5 13.5 12.5L12 14L10.5 12.5C8.5 11.5 7 10 7 8C7 4 11 2 11 2H13Z"/>
                                <path d="M6 12C6 12 2 14 2 18C2 20 3.5 21.5 5.5 22.5L7 24L8.5 22.5C10.5 21.5 12 20 12 18C12 14 8 12 8 12H6Z"/>
                                <path d="M18 12C18 12 22 14 22 18C22 20 20.5 21.5 18.5 22.5L17 24L15.5 22.5C13.5 21.5 12 20 12 18C12 14 16 12 16 12H18Z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Set Case as Pro Bono
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you certain you wish to proceed with making this case pro bono? Please note that this action is irreversible.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <label for="proBonoNote" class="block text-sm font-medium text-gray-700">
                            Reason for Pro Bono Status
                        </label>
                        <textarea 
                            id="proBonoNote" 
                            wire:model.defer="proBonoNote" 
                            rows="3" 
                            placeholder="Please explain why this case is being marked as pro bono..."
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('proBonoNote') border-red-500 @enderror">
                        </textarea>
                        @error('proBonoNote') 
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p> 
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            This note will be added to the case records and visible to the client.
                        </p>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        wire:click="setProBono"
                        wire:loading.attr="disabled"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="setProBono">Set as Pro Bono</span>
                        <span wire:loading wire:target="setProBono">Processing...</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="cancelProBono"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Court Details Modal -->
    <div x-data="{ show: false }" x-on:open-modal.window="if ($event.detail === 'edit-court-details-modal') show = true" x-on:close-modal.window="if ($event.detail === 'edit-court-details-modal') show = false" x-show="show" class="fixed inset-0 z-20 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Court Details</h3>
                        <button type="button" @click="show = false" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form wire:submit.prevent="updateCourtDetails">
                        <div class="space-y-4">
                            <!-- Court Level Main -->
                            <div>
                                <label for="courtLevelMain" class="block text-sm font-medium text-gray-700">Court Level</label>
                                <select id="courtLevelMain" wire:model.live="courtLevelMain" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">-- Select Court Level --</option>
                                    <option value="First Level Courts">First Level Courts</option>
                                    <option value="Second Level Courts">Second Level Courts</option>
                                    <option value="Appellate Courts">Appellate Courts</option>
                                    <option value="Highest Court">Highest Court</option>
                                </select>
                                @error('courtLevelMain') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Court Level Specific -->
                            <div>
                                <label for="courtLevelSpecific" class="block text-sm font-medium text-gray-700">Specific Court</label>
                                <select id="courtLevelSpecific" wire:model="courtLevelSpecific" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" {{ empty($courtLevelMain) ? 'disabled' : '' }}>
                                    <option value="">-- Select Specific Court --</option>
                                    
                                    @if($courtLevelMain === 'First Level Courts')
                                        <option value="MeTC">Metropolitan Trial Court (MeTC)</option>
                                        <option value="MTCC">Municipal Trial Court in Cities (MTCC)</option>
                                        <option value="MTC">Municipal Trial Court (MTC)</option>
                                        <option value="MCTC">Municipal Circuit Trial Court (MCTC)</option>
                                    @elseif($courtLevelMain === 'Second Level Courts')
                                        <option value="RTC">Regional Trial Court (RTC)</option>
                                    @elseif($courtLevelMain === 'Appellate Courts')
                                        <option value="Court of Appeals">Court of Appeals</option>
                                        <option value="Sandiganbayan">Sandiganbayan</option>
                                        <option value="Court of Tax Appeals">Court of Tax Appeals</option>
                                    @elseif($courtLevelMain === 'Highest Court')
                                        <option value="Supreme Court">Supreme Court of the Philippines</option>
                                    @endif
                                </select>
                                @error('courtLevelSpecific') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                                Save
                            </button>
                            <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 