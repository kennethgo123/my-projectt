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

    <!-- Page Header -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $case->case_number ? 'Case No. ' . $case->case_number . ': ' : '' }}{{ $case->title ?? 'N/A' }}
                </h1>
                <p class="text-sm text-gray-600 mt-1">
                    Filed: {{ $case->created_at ? $case->created_at->format('F d, Y') : 'N/A' }}
                </p>
            </div>
             <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium 
                    {{ $case->isClosed() ? 'bg-gray-500 text-white border border-gray-600' : 
                    ($case->status === 'active' ? 'bg-green-500 text-white border border-green-600' : 'bg-gray-100 text-gray-700 border border-gray-300') }}">
                    <svg class="w-4 h-4 mr-1.5 {{ $case->isClosed() ? 'text-white' : 
                    ($case->status === 'active' ? 'text-white' : 'text-gray-500') }}" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="3" />
                    </svg>
                    {{ $case->isClosed() ? 'Closed' : ($case->status === 'active' ? 'Active' : ($case->status ? ucfirst($case->status) : 'Unknown')) }}
                </span>
            </div>
        </div>
        
        @if($case->isClosed())
        <div class="mt-4 bg-gray-100 border border-gray-300 rounded-md p-3 text-gray-700">
            <p class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                This case has been closed. You can view case information but cannot make changes.
            </p>
        </div>
        @endif
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
                    Tasks
                </a>
                <a href="#" @click.prevent="activeTab = 'invoices'"
                   :class="{ 'border-blue-500 text-blue-600': activeTab === 'invoices', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'invoices' }"
                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Invoices
                </a>
                 {{-- Removing Notes, Expenses, Client Access for client view --}}
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
                        $firstDayOfMonth = $carbonDate->dayOfWeek;
                    @endphp
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Calendar</h3>
                        <div class="flex items-center space-x-2">
                            <button wire:click="previousMonth" class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <span class="text-sm font-medium text-gray-700 w-24 text-center">{{ $monthName }} {{ $year }}</span>
                            <button wire:click="nextMonth" class="text-gray-500 hover:text-gray-700 p-1 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                             <button wire:click="goToToday" class="ml-2 text-sm text-blue-600 hover:text-blue-800 focus:outline-none">Today</button>
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

                        @for ($i = 0; $i < $firstDayOfMonth; $i++)
                            <span></span>
                        @endfor

                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $currentDateStr = sprintf('%d-%02d-%02d', $year, $calendarMonth, $day);
                                $isToday = ($currentDateStr === $today);
                                $markerType = $markedDates[$currentDateStr] ?? null;
                                $dayClasses = 'w-8 h-8 flex items-center justify-center mx-auto rounded-full text-sm';
                                
                                if ($isToday) {
                                    $dayClasses .= ' bg-blue-500 text-white font-bold';
                                } elseif ($markerType === 'both') {
                                    // Special styling for days with both events and tasks - gradient background
                                    $dayClasses .= ' text-indigo-800 font-semibold';
                                    // Add a custom style for the gradient background
                                    $gradientStyle = 'background: linear-gradient(135deg, #93c5fd 50%, #86efac 50%);';
                                } elseif ($markerType === 'event') {
                                    $dayClasses .= ' bg-blue-100 text-blue-700 font-semibold';
                                } elseif ($markerType === 'task') {
                                    $dayClasses .= ' bg-green-100 text-green-700 font-semibold';
                                } else {
                                    $dayClasses .= ' text-gray-700 hover:bg-gray-100';
                                }
                            @endphp
                            <span class="{{ $dayClasses }}" @if($markerType === 'both') style="{{ $gradientStyle }}" @endif>{{ $day }}</span>
                        @endfor
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-center space-x-4 text-xs text-gray-600">
                        <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-1.5"></span> Today</div>
                        <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-blue-100 mr-1.5"></span> Event</div>
                        <div class="flex items-center"><span class="w-3 h-3 rounded-sm bg-green-100 mr-1.5"></span> Task Due</div>
                        <div class="flex items-center"><span class="w-3 h-3 rounded-sm mr-1.5" style="background: linear-gradient(135deg, #93c5fd 50%, #86efac 50%);"></span> Both</div>
                    </div>
                </div>

                <!-- Invoice Notification -->
                <div class="bg-white shadow-md rounded-lg p-4 mt-4">
                    <h4 class="font-medium text-gray-700 mb-2">Invoice</h4>
                    <p class="text-sm text-gray-600">
                        @php
                            // Check for pending invoices, ensuring we look at the actual collection count
                            $pendingCount = isset($invoices) ? $invoices->where('status', 'pending')->count() : 0;
                            
                            // If we didn't find any, also check for overdue invoices which should be paid
                            if ($pendingCount == 0) {
                                $pendingCount = isset($invoices) ? $invoices->where('status', 'overdue')->count() : 0;
                            }
                            
                            // Log for debugging
                            \Illuminate\Support\Facades\Log::info('Pending Invoice Count: ' . $pendingCount);
                            \Illuminate\Support\Facades\Log::info('Total Invoices: ' . (isset($invoices) ? $invoices->count() : 0));
                        @endphp
                        
                        @if($pendingCount > 0)
                            There {{ $pendingCount == 1 ? 'is' : 'are' }} 
                            <span class="font-semibold text-blue-600">{{ $pendingCount }}</span> 
                            pending {{ $pendingCount == 1 ? 'invoice' : 'invoices' }} 
                            for this case. Kindly refer to the 
                            <button @click.prevent="activeTab = 'invoices'; $wire.changeTab('invoices')" class="text-blue-500 hover:underline">invoice page</button> 
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
                            {{ $case->isClosed() ? 'disabled' : '' }}
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-3 rounded-md text-sm
                            {{ $case->isClosed() ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        @if($upcomingEvents && $upcomingEvents->count() > 0)
                            @forelse($upcomingEvents->take(3) as $event) {{-- Displaying first 3 events --}}
                                <div class="border border-blue-200 bg-blue-50 rounded-lg p-4">
                                     @if($event->start_datetime)
                                        <p class="font-semibold text-blue-700">{{ $event->start_datetime->format('F j, Y') }}</p>
                                        <p class="text-sm font-medium text-gray-800 mt-1">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-600 mt-0.5">
                                            {{ $event->start_datetime->format('g:i A') }} 
                                            {{ $event->location ? '• ' . $event->location : '' }}
                                        </p>
                                     @else
                                        <p class="font-semibold text-blue-700">Date Not Set</p>
                                        <p class="text-sm font-medium text-gray-800 mt-1">{{ $event->title }}</p>
                                        <p class="text-xs text-gray-600 mt-0.5">
                                            {{ $event->location ? '• ' . $event->location : '' }}
                                        </p>
                                     @endif
                                </div>
                            @empty
                                 <p class="text-sm text-gray-500">No upcoming events.</p>
                            @endforelse
                        @else
                            <p class="text-sm text-gray-500">No upcoming events scheduled.</p>
                        @endif
                    </div>
                </div>

                <!-- Tasks -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Tasks</h3>
                        <button @click.prevent="$dispatch('open-modal', 'add-task-modal')" 
                            {{ $case->isClosed() ? 'disabled' : '' }}
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-3 rounded-md text-sm
                            {{ $case->isClosed() ? 'opacity-50 cursor-not-allowed' : '' }}">
                             <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>
                    <div class="space-y-3">
                        @if($recentTasks && $recentTasks->count() > 0)
                            @forelse($recentTasks->take(3) as $task) {{-- Displaying first 3 tasks --}}
                                <div class="flex items-start justify-between" wire:key="task-overview-client-{{ $task->id }}">
                                    <div class="flex items-start">
                                        {{-- Make checkbox clickable for task toggling --}}
                                        @php
                                            $isAssignedToLawyer = false;
                                            
                                            // Check new format first: assigned_to_id
                                            if (isset($task->assigned_to_id) && $task->assigned_to_id === $case->lawyer_id) {
                                                $isAssignedToLawyer = true;
                                            }
                                            
                                            // Check old format: assigned_to
                                            if (!$isAssignedToLawyer && isset($task->assigned_to) && $task->assigned_to == $case->lawyer_id) {
                                                $isAssignedToLawyer = true;
                                            }
                                        @endphp
                                        <button 
                                            wire:click="toggleTaskCompletion({{ $task->id }})" 
                                            class="h-4 w-4 border {{ $task->is_completed ? 'bg-green-500 border-green-500' : 'border-gray-300' }} rounded mt-1 mr-2 flex-shrink-0 focus:outline-none"
                                            {{ $case->isClosed() || $isAssignedToLawyer ? 'disabled' : '' }}
                                        >
                                           @if($task->is_completed)
                                                <svg class="w-3 h-3 text-white mx-auto my-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            @endif
                                        </button>
                                        <div>
                                            <label for="task_{{ $task->id }}" class="text-sm text-gray-700 {{ $task->is_completed ? 'line-through text-gray-500' : '' }}">{{ $task->title }}</label>
                                            <p class="text-xs text-gray-500">Due: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'N/A' }}</p>
                                            
                                            <!-- Add Assignment Badge -->
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
                                            
                                            <div class="mt-1">
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
                                    </div>
                                    {{-- Removed edit/delete buttons from overview tab --}}
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No pending tasks.</p>
                            @endforelse
                        @else
                            <p class="text-sm text-gray-500">No tasks found.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Court Details -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Court Details</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Court Level</h4>
                            <div class="text-gray-800 border border-gray-200 rounded-md px-3 py-2 bg-gray-50">
                                @if($courtLevelMain)
                                    {{ $courtLevelMain }}
                                @else
                                    <span class="text-gray-400 italic">Not specified</span>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Specific Court</h4>
                            <div class="text-gray-800 border border-gray-200 rounded-md px-3 py-2 bg-gray-50 font-semibold">
                                @if($courtLevelSpecific)
                                    @if($courtLevelSpecific == 'RTC')
                                        Regional Trial Court (RTC)
                                    @elseif($courtLevelSpecific == 'MeTC')
                                        Metropolitan Trial Court (MeTC)
                                    @elseif($courtLevelSpecific == 'MTCC')
                                        Municipal Trial Court in Cities (MTCC)
                                    @elseif($courtLevelSpecific == 'MTC')
                                        Municipal Trial Court (MTC)
                                    @elseif($courtLevelSpecific == 'MCTC')
                                        Municipal Circuit Trial Court (MCTC)
                                    @else
                                        {{ $courtLevelSpecific }}
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Not specified</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Case Progression Monitor (Client read-only version) -->
                <livewire:components.case-phase-tracker :caseId="$case->id" :readOnly="true" wire:key="case-phase-tracker-client-{{ $case->id }}" />
                
                {{-- Client Updates section might be relevant here, or under a specific tab --}}
                {{-- <div class="bg-white shadow-md rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Client Updates</h3>
                    <div class="space-y-4">
                         Existing update items 
                    </div>
                </div> --}}
            </div>
        </div>

        <!-- Documents Tab -->
        <div x-show="activeTab === 'documents'" x-cloak>
            {{-- Include a read-only or restricted version of documents partial --}}
            @include('livewire.client.partials.case-documents', ['documents' => $documents, 'caseId' => $case->id])
        </div>

        <!-- Timeline Tab (integrates Read-Only CasePhaseTracker) -->
        <div x-show="activeTab === 'timeline'" x-cloak>
             <livewire:components.case-phase-tracker :caseId="$case->id" :readOnly="true" wire:key="case-phase-tracker-timeline-client-{{ $case->id }}" />
        </div>
        
        <!-- Hearings Tab (integrates Read-Only Events) -->
        <div x-show="activeTab === 'hearings'" x-cloak>
            {{-- Include a read-only version of events partial --}}
            @include('livewire.client.partials.case-events', ['events' => $events])
        </div>

        <!-- Tasks Management Tab -->
        <div x-show="activeTab === 'tasks_management'" x-cloak>
             {{-- Include the tasks partial, allowing adding/editing for client --}}
            @include('livewire.client.partials.case-tasks', ['tasks' => $tasks])
        </div>
        
        <!-- Invoices Tab -->
        <div x-show="activeTab === 'invoices'" x-cloak>
            @include('livewire.client.partials.case-invoices', ['invoices' => $invoices])
        </div>
        
         {{-- Removed Notes, Expenses, Client Access tabs for client view --}}

    </div>

    <!-- Modals -->
    {{-- Add Task Modal --}}
    <x-modal name="add-task-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Task
                </h2>
                <button type="button" class="text-gray-400 hover:text-gray-500" x-on:click="$dispatch('close')">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="addTask">
                <div class="space-y-4">
                    <div>
                        <label for="newTaskTitle" class="block text-sm font-medium text-gray-700">Task Title</label>
                        <input type="text" id="newTaskTitle" wire:model.defer="newTaskTitle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newTaskTitle') border-red-500 @enderror">
                        @error('newTaskTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="newTaskDescription" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="newTaskDescription" wire:model.defer="newTaskDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newTaskDescription') border-red-500 @enderror"></textarea>
                        @error('newTaskDescription') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="newTaskDueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" id="newTaskDueDate" wire:model.defer="newTaskDueDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newTaskDueDate') border-red-500 @enderror">
                        @error('newTaskDueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    {{-- Assignment is automatic for client --}}
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit" class="ml-3" wire:loading.attr="disabled">
                        Add Task
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
    
    {{-- Edit Task Modal --}}
     <x-modal name="edit-task-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit Task
                </h2>
                <button type="button" class="text-gray-400 hover:text-gray-500" x-on:click="$dispatch('close')">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="updateTask">
                <div class="space-y-4">
                    <div>
                        <label for="editTaskTitle" class="block text-sm font-medium text-gray-700">Task Title</label>
                        <input type="text" id="editTaskTitle" wire:model.defer="editTaskTitle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editTaskTitle') border-red-500 @enderror">
                        @error('editTaskTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="editTaskDescription" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="editTaskDescription" wire:model.defer="editTaskDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editTaskDescription') border-red-500 @enderror"></textarea>
                        @error('editTaskDescription') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="editTaskDueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                        <input type="date" id="editTaskDueDate" wire:model.defer="editTaskDueDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editTaskDueDate') border-red-500 @enderror">
                        @error('editTaskDueDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    {{-- Assignment is not editable by client --}}
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')" wire:click="resetEditTaskForm">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit" class="ml-3" wire:loading.attr="disabled">
                        Update Task
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
    
    {{-- Add Event Modal --}}
    <x-modal name="add-event-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Add New Event
                </h2>
                <button type="button" class="text-gray-400 hover:text-gray-500" x-on:click="$dispatch('close')">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="addEvent">
                <div class="space-y-4">
                    <div>
                        <label for="newEventTitle" class="block text-sm font-medium text-gray-700">Event Title</label>
                        <input type="text" id="newEventTitle" wire:model.defer="newEventTitle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newEventTitle') border-red-500 @enderror">
                        @error('newEventTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="newEventDescription" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="newEventDescription" wire:model.defer="newEventDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newEventDescription') border-red-500 @enderror"></textarea>
                        @error('newEventDescription') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="newEvent_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                        <select id="newEvent_type" wire:model.defer="newEvent_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('newEvent_type') border-red-500 @enderror">
                            <option value="meeting">Meeting</option>
                            <option value="hearing">Hearing</option>
                            <option value="deadline">Deadline</option>
                            <option value="other">Other</option>
                        </select>
                        @error('newEvent_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="newEventDate" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" id="newEventDate" wire:model.defer="newEventDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newEventDate') border-red-500 @enderror">
                            @error('newEventDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="newEventTime" class="block text-sm font-medium text-gray-700">Time</label>
                            <input type="time" id="newEventTime" wire:model.defer="newEventTime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newEventTime') border-red-500 @enderror">
                            @error('newEventTime') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="newEventLocation" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" id="newEventLocation" wire:model.defer="newEventLocation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('newEventLocation') border-red-500 @enderror">
                        @error('newEventLocation') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')" wire:click="resetEventForm">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit" class="ml-3" wire:loading.attr="disabled">
                        Add Event
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Edit Event Modal --}}
    <x-modal name="edit-event-modal" focusable>
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit Event
                </h2>
                <button type="button" class="text-gray-400 hover:text-gray-500" x-on:click="$dispatch('close')">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form wire:submit.prevent="updateEvent">
                <div class="space-y-4">
                    <div>
                        <label for="editEventTitle" class="block text-sm font-medium text-gray-700">Event Title</label>
                        <input type="text" id="editEventTitle" wire:model.defer="editEventTitle" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editEventTitle') border-red-500 @enderror">
                        @error('editEventTitle') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="editEventDescription" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="editEventDescription" wire:model.defer="editEventDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editEventDescription') border-red-500 @enderror"></textarea>
                        @error('editEventDescription') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="editEvent_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                        <select id="editEvent_type" wire:model.defer="editEvent_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('editEvent_type') border-red-500 @enderror">
                            <option value="meeting">Meeting</option>
                            <option value="hearing">Hearing</option>
                            <option value="deadline">Deadline</option>
                            <option value="other">Other</option>
                        </select>
                        @error('editEvent_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="editEventDate" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" id="editEventDate" wire:model.defer="editEventDate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editEventDate') border-red-500 @enderror">
                            @error('editEventDate') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="editEventTime" class="block text-sm font-medium text-gray-700">Time</label>
                            <input type="time" id="editEventTime" wire:model.defer="editEventTime" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editEventTime') border-red-500 @enderror">
                            @error('editEventTime') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label for="editEventLocation" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" id="editEventLocation" wire:model.defer="editEventLocation" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('editEventLocation') border-red-500 @enderror">
                        @error('editEventLocation') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')" wire:click="resetEditEventForm">
                        Cancel
                    </x-secondary-button>
                    <x-primary-button type="submit" class="ml-3" wire:loading.attr="disabled">
                        Update Event
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Document Upload Modal (If client upload is allowed) - Simplified version needed --}}
    {{-- <x-modal name="upload-document-modal" focusable>
        ...
    </x-modal> --}}

    @stack('modals')

    {{-- Livewire listeners for modal actions --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-edit-task-modal', (event) => {
                // Use Alpine to dispatch event to open the modal
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-task-modal' }));
            });
            Livewire.on('close-edit-task-modal', (event) => {
                 window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-task-modal' }));
            });
            Livewire.on('trigger-delete-task', (event) => {
                if (confirm('Are you sure you want to delete this task?')) {
                    @this.call('deleteTask', event.id);
                }
            });
            
            // Event related listeners
            Livewire.on('open-edit-event-modal', (event) => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-event-modal' }));
            });
            Livewire.on('close-edit-event-modal', (event) => {
                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'edit-event-modal' }));
            });
            Livewire.on('trigger-delete-event', (event) => {
                if (confirm('Are you sure you want to delete this event?')) {
                    @this.call('deleteEvent', event.id);
                }
            });
            
            Livewire.on('show-message', (event) => {
                // Basic alert, replace with a nicer notification system if available
                alert(event.message);
            });
        });
    </script>

</div> 