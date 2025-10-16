<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Law Firm Dashboard</h1>
            <p class="mt-2 text-gray-600">Overview of your firm's activities and schedule.</p>
        </div>

        <!-- Subscription Status Card -->
        <div class="mb-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Your Firm's Subscription Plan</h2>
                        @php
                            $activeSubscription = auth()->user()->activeSubscription;
                        @endphp
                        
                        @if($activeSubscription)
                            <div class="mt-2 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activeSubscription->plan->name === 'Free' ? 'bg-gray-100 text-gray-800' : ($activeSubscription->plan->name === 'Pro' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $activeSubscription->plan->name }} Plan
                                </span>
                                <span class="ml-2 text-sm text-gray-500">
                                    ({{ ucfirst($activeSubscription->billing_cycle) }} billing)
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                @if($activeSubscription->ends_at)
                                    Valid until {{ $activeSubscription->ends_at->format('M d, Y') }}
                                @endif
                            </p>
                        @else
                            <div class="mt-2 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Free Plan
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Limited visibility in search results</p>
                        @endif
                    </div>
                    
                    @if(!$activeSubscription || $activeSubscription->plan->name === 'Free')
                        <div class="flex flex-col items-end">
                            <p class="text-sm text-gray-600 mb-3">Upgrade your firm's plan to increase visibility and attract more clients!</p>
                            <div class="text-sm text-gray-600 mb-3">
                                <ul class="list-disc pl-5">
                                    <li>Higher placement in search results</li>
                                    <li>Featured firm badge</li>
                                    <li>Increased visibility for all your lawyers</li>
                                </ul>
                            </div>
                            <a href="{{ route('account.subscription') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Upgrade Your Firm's Plan
                            </a>
                        </div>
                    @elseif($activeSubscription && $activeSubscription->plan->name !== 'Free')
                        <div>
                            <a href="{{ route('account.subscription') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                Manage Subscription
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards (Optional - can be adjusted for firm-level stats) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-green-500">
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-700">Active Cases (Firm)</h2>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $activeCasesCount }}</p>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-yellow-500">
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-700">Pending Cases (Firm)</h2>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $pendingCasesCount }}</p>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-blue-500">
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-700">Completed Cases (Firm)</h2>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $completedCasesCount }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content: Calendar and Consultations -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Consultations and Deadlines Side by Side -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Consultations Section -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-indigo-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700">Firm Consultations</h3>
                    </div>
                    
                    <div x-data="{ tab: 'pending' }">
                        <div class="border-b border-gray-200 mb-6">
                            <div class="flex space-x-8">
                                <button @click="tab = 'pending'" 
                                        class="pb-2 px-1 text-center focus:outline-none transition-all duration-200" 
                                        :class="tab === 'pending' ? 'border-b-2 border-indigo-500 text-indigo-600 font-medium' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                    <span>Pending ({{ count($pendingConsultations) }})</span>
                                </button>
                                <button @click="tab = 'upcoming'" 
                                        class="pb-2 px-1 text-center focus:outline-none transition-all duration-200" 
                                        :class="tab === 'upcoming' ? 'border-b-2 border-indigo-500 text-indigo-600 font-medium' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                    <span>Upcoming ({{ count($upcomingConsultations) }})</span>
                                </button>
                            </div>
                        </div>

                        <div x-show="tab === 'pending'" class="transition duration-200 ease-in-out">
                            @if(count($pendingConsultations) > 0)
                                <div class="space-y-3">
                                    @foreach($pendingConsultations as $consultation)
                                        <div class="bg-white border border-gray-200 rounded-md p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        Client: {{ $consultation->client->clientProfile->first_name ?? $consultation->client->name }} {{ $consultation->client->clientProfile->last_name ?? '' }}
                                                    </p>
                                                    <p class="text-xs text-gray-600">Lawyer: {{ $consultation->lawyer->name ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        @if($consultation->created_at)
                                                            Requested on {{ $consultation->created_at->format('M d, Y') }}
                                                        @else
                                                            Requested on (Date not available)
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-yellow-600 mt-1">{{ ucfirst($consultation->consultation_type) }}</p>
                                                </div>
                                                <a href="{{ route('law-firm.consultations') }}" class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-900">Review</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No pending consultation requests for the firm.</p>
                            @endif
                        </div>

                        <div x-show="tab === 'upcoming'" class="transition duration-200 ease-in-out">
                            @if(count($upcomingConsultations) > 0)
                                <div class="space-y-3">
                                    @foreach($upcomingConsultations as $consultation)
                                        <div class="bg-white border border-gray-200 rounded-md p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                         Client: {{ $consultation->client->clientProfile->first_name ?? $consultation->client->name }} {{ $consultation->client->clientProfile->last_name ?? '' }}
                                                    </p>
                                                    <p class="text-xs text-gray-600">Lawyer: {{ $consultation->lawyer->name ?? 'N/A' }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ $consultation->selected_date ? $consultation->selected_date->format('M d, Y, g:i a') : 'Date TBD' }}
                                                    </p>
                                                    <p class="text-xs text-indigo-600 mt-1">{{ ucfirst($consultation->consultation_type) }}</p>
                                                </div>
                                                <a href="{{ route('law-firm.consultations') }}" class="inline-flex items-center text-xs font-medium text-indigo-600 hover:text-indigo-900">View</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No upcoming consultations for the firm.</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('law-firm.consultations') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                            Manage all firm consultations
                            <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Deadlines Section -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-red-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700">Firm Deadlines</h3>
                    </div>
                    
                    <!-- Simple Tab Navigation -->
                    <div x-data="{ tab: 'today' }">
                        <div class="border-b border-gray-200 mb-6">
                            <div class="flex space-x-8">
                                <button @click="tab = 'today'" 
                                        class="pb-2 px-1 text-center focus:outline-none transition-all duration-200"
                                        :class="tab === 'today' ? 'border-b-2 border-red-500 text-red-600 font-medium' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Today ({{ count($todayDeadlines) }})</span>
                                    </div>
                                </button>
                                <button @click="tab = 'week'" 
                                        class="pb-2 px-1 text-center focus:outline-none transition-all duration-200"
                                        :class="tab === 'week' ? 'border-b-2 border-red-500 text-red-600 font-medium' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span>This Week ({{ count($weekDeadlines) }})</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Today's Deadlines Tab Content -->
                        <div x-show="tab === 'today'" class="transition duration-200 ease-in-out">
                            @if(count($todayDeadlines) > 0)
                                <div class="space-y-3">
                                    @foreach($todayDeadlines as $deadline)
                                        <div class="bg-white border border-gray-200 rounded-md p-4 hover:bg-red-50 transition duration-150">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $deadline['title'] }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">Due: {{ $deadline['formatted_date'] }}</p>
                                                    <p class="text-xs text-red-600 mt-1">Case: {{ $deadline['case_title'] }}</p>
                                                    @if(isset($deadline['lawyer_name']))
                                                    <p class="text-xs text-gray-600 mt-1">Assigned to: {{ $deadline['lawyer_name'] }}</p>
                                                    @endif
                                                </div>
                                                <a href="{{ $deadline['url'] }}" class="inline-flex items-center text-xs font-medium text-red-600 hover:text-red-900">
                                                    View
                                                    <svg class="ml-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 px-4 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-500">No firm deadlines for today</p>
                                </div>
                            @endif
                        </div>

                        <!-- This Week's Deadlines Tab Content -->
                        <div x-show="tab === 'week'" class="transition duration-200 ease-in-out">
                            @if(count($weekDeadlines) > 0)
                                <div class="space-y-3">
                                    @foreach($weekDeadlines as $deadline)
                                        <div class="bg-white border border-gray-200 rounded-md p-4 hover:bg-red-50 transition duration-150">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $deadline['title'] }}</p>
                                                    <p class="text-xs text-gray-500 mt-1">Due: {{ $deadline['formatted_date'] }}</p>
                                                    <p class="text-xs text-red-600 mt-1">Case: {{ $deadline['case_title'] }}</p>
                                                    @if(isset($deadline['lawyer_name']))
                                                    <p class="text-xs text-gray-600 mt-1">Assigned to: {{ $deadline['lawyer_name'] }}</p>
                                                    @endif
                                                </div>
                                                <a href="{{ $deadline['url'] }}" class="inline-flex items-center text-xs font-medium text-red-600 hover:text-red-900">
                                                    View
                                                    <svg class="ml-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 px-4 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-500">No firm deadlines for this week</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- View all deadlines link -->
                    <div class="mt-6">
                        <a href="{{ route('law-firm.cases') }}?show_deadlines=true" class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-900">
                            View all firm deadlines
                            <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar (full width) -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Firm Schedule</h3>
                <div id="calendar" class="min-h-[500px]"></div>
                <!-- Calendar Legend -->
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Legend:</h4>
                    <div class="flex flex-wrap gap-3">
                        <div class="flex items-center">
                            <span class="inline-block w-3 h-3 mr-1.5 rounded-sm" style="background-color: #4f46e5;"></span>
                            <span class="text-xs text-gray-600">Consultations</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-3 h-3 mr-1.5 rounded-sm" style="background-color: #3b82f6;"></span>
                            <span class="text-xs text-gray-600">Case Events</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-3 h-3 mr-1.5 rounded-sm" style="background-color: #10b981;"></span>
                            <span class="text-xs text-gray-600">Your Tasks</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-3 h-3 mr-1.5 rounded-sm" style="background-color: #dc2626;"></span>
                            <span class="text-xs text-gray-600">Case Deadlines</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Firm Management Section (Keep or adjust as needed) -->
        <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Firm Management</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white border rounded-lg overflow-hidden shadow p-6">
                    <h3 class="font-medium text-gray-700 text-lg mb-4">Lawyers in Your Firm</h3>
                    <p class="text-gray-600 mb-4">Manage lawyers associated with your law firm.</p>
                    <div class="mt-4">
                        @if(auth()->user()->status === 'approved')
                            <a href="{{ route('law-firm.lawyers') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Manage Lawyers
                            </a>
                            <a href="{{ route('law-firm.lawyers.create') }}" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add New Lawyer
                            </a>
                        @else
                             <p class="text-sm text-red-600">Your firm is not yet approved to manage lawyers.</p>
                        @endif
                    </div>
                </div>
                <div class="bg-white border rounded-lg overflow-hidden shadow p-6">
                    <h3 class="font-medium text-gray-700 text-lg mb-4">Firm Statistics & Settings</h3>
                     <!-- Add other firm specific stats or links here -->
                    <p class="text-gray-600 mb-4">View firm performance or manage firm settings.</p>
                     <a href="{{ route('law-firm.optimize-profile') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        Optimize Firm Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <style>
        /* Calendar styles - copied from lawyer dashboard */
        #calendar { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .fc-daygrid-day { min-height: 100px; }
        .fc-event-title { font-size: 0.65rem !important; font-weight: normal !important; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; display: block !important; -webkit-line-clamp: 2; -webkit-box-orient: vertical; white-space: normal !important; padding: 0 !important; margin: 1px 4px !important; opacity: 0.9 !important; visibility: visible !important; color: #4b5563 !important; }
        .fc-daygrid-day-number, .fc-col-header-cell-cushion { font-size: 0.85rem; }
        .fc-daygrid-event-dot { border-width: 3px !important; }
        .fc-event { margin-bottom: 2px !important; min-height: auto !important; padding: 0 !important; display: block !important; }
        .fc-button-primary { font-size: 0.8rem !important; }
        .fc-day-today { background-color: rgba(96, 165, 250, 0.1) !important; }
        .fc-daygrid-event { padding: 0 !important; }
        .fc-event-main, .fc-daygrid-event-harness { overflow: visible !important; }
        .fc-event-time { display: inline-block !important; font-size: 0.6rem !important; font-weight: 500 !important; padding: 0 2px !important; margin-right: 2px !important; opacity: 0.85 !important; }
        .consultation-event { border-left: 2px solid #4f46e5 !important; background-color: rgba(79, 70, 229, 0.05) !important; }
        .event-event { border-left: 2px solid #3b82f6 !important; background-color: rgba(59, 130, 246, 0.05) !important; }
        .task-event { border-left: 2px solid #10b981 !important; background-color: rgba(16, 185, 129, 0.05) !important; }
        .deadline-event { border-left: 2px solid #dc2626 !important; background-color: rgba(220, 38, 38, 0.05) !important; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) { // Check if calendar element exists
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: @json($events),
                    height: 'auto',
                    eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
                    eventClick: function(info) {
                        if (info.event.url) {
                            window.location.href = info.event.url;
                            info.jsEvent.preventDefault();
                        }
                    },
                    eventDidMount: function(info) {
                        if (info.view.type === 'dayGridMonth') {
                            const eventType = info.event.extendedProps.type || 'event';
                            info.el.classList.add(`${eventType}-event`);
                            const titleEl = info.el.querySelector('.fc-event-title');
                            if (titleEl) {
                                let title = titleEl.textContent;
                                if (eventType === 'consultation') {
                                    title = title.replace('Consult: ', ''); // Adjusted prefix
                                } else if (eventType === 'task') {
                                    title = title.replace('Task: ', '');
                                } else if (eventType === 'deadline') {
                                    title = title.replace('Deadline: ', '');
                                }
                                titleEl.textContent = title;
                            }
                        }
                    }
                });
                calendar.render();
                document.addEventListener('livewire:navigated', () => {
                    calendar.updateSize();
                });
            } else {
                console.error("Calendar element #calendar not found.");
            }
        });
    </script>
    @endpush
</div>
