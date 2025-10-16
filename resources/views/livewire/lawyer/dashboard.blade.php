<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Page header -->
        <div class="mb-8 bg-gradient-to-r from-green-50 via-white to-green-50 rounded-lg p-6 border-l-4 border-green-500">
            <h1 class="text-3xl font-bold text-gray-900">Lawyer Dashboard</h1>
            <p class="mt-2 text-gray-600">Welcome to your dashboard. Here's an overview of your practice.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Active Cases -->
            <div class="bg-gradient-to-br from-green-50 to-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-green-500 hover:shadow-2xl transition-shadow duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-700">Active Cases</h2>
                        <p class="mt-1 text-3xl font-bold text-green-700">{{ $activeCasesCount }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('lawyer.cases') }}" class="text-green-600 hover:text-green-800 font-medium text-sm transition-colors duration-200">
                        View all active cases →
                    </a>
                </div>
            </div>

            <!-- Pending Cases -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-yellow-500 hover:shadow-2xl hover:border-l-green-400 transition-all duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-700">Pending Cases</h2>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $pendingCasesCount }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('lawyer.cases') }}" class="text-yellow-600 hover:text-green-700 font-medium text-sm transition-colors duration-200">
                        View all pending cases →
                    </a>
                </div>
            </div>

            <!-- Completed Cases -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-l-4 border-blue-500 hover:shadow-2xl hover:border-l-green-400 transition-all duration-300">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-700">Completed Cases</h2>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $completedCasesCount }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('lawyer.cases') }}" class="text-blue-600 hover:text-green-700 font-medium text-sm transition-colors duration-200">
                        View completed cases →
                    </a>
                </div>
            </div>
        </div>

        <!-- Subscription Status Card -->
        @if(!auth()->user()->belongsToLawFirm())
        <div class="mb-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Your Subscription Plan</h2>
                        @php
                            $lawFirm = auth()->user()->lawFirm;
                            $activeSubscription = auth()->user()->activeSubscription;
                            $usingFirmSubscription = false;
                            
                            if ($lawFirm && !$activeSubscription) {
                                $firmSubscription = $lawFirm->user->activeSubscription;
                                if ($firmSubscription && $firmSubscription->plan->name !== 'Free') {
                                    $activeSubscription = $firmSubscription;
                                    $usingFirmSubscription = true;
                                }
                            }
                        @endphp
                        
                        @if($activeSubscription)
                            <div class="mt-2 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $activeSubscription->plan->name === 'Free' ? 'bg-gray-100 text-gray-800' : ($activeSubscription->plan->name === 'Pro' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $activeSubscription->plan->name }} Plan
                                </span>
                                @if($usingFirmSubscription)
                                    <span class="ml-2 text-sm text-gray-500">(Via your law firm)</span>
                                @else
                                    <span class="ml-2 text-sm text-gray-500">
                                        ({{ ucfirst($activeSubscription->billing_cycle) }} billing)
                                    </span>
                                @endif
                            </div>
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
                            <p class="text-sm text-gray-600 mb-3">Upgrade your subscription to reach more clients!</p>
                            <a href="{{ route('account.subscription') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Upgrade Your Plan
                            </a>
                        </div>
                    @elseif($activeSubscription && !$usingFirmSubscription)
                        <div>
                            <a href="{{ route('account.subscription') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                Manage Subscription
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Consultations and Deadlines Side by Side -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Consultations Section - Simplified Design -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 hover:shadow-2xl transition-shadow duration-300">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700">Consultations</h3>
                    </div>
                    
                    <!-- Simple Tab Navigation -->
                    <div x-data="{ tab: 'pending' }">
                        <div class="border-b border-gray-200 mb-6">
                            <div class="flex space-x-8">
                                <button @click="tab = 'pending'" 
                                        class="pb-2 px-1 text-center focus:outline-none transition-all duration-200"
                                        :class="tab === 'pending' ? 'border-b-2 border-green-500 text-green-600 font-medium' : 'text-gray-500 hover:text-green-600 hover:border-green-300'">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Pending ({{ count($pendingConsultations) }})</span>
                                    </div>
                                </button>
                                <button @click="tab = 'upcoming'" 
                                        class="pb-2 px-1 text-center focus:outline-none transition-all duration-200"
                                        :class="tab === 'upcoming' ? 'border-b-2 border-green-500 text-green-600 font-medium' : 'text-gray-500 hover:text-green-600 hover:border-green-300'">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Upcoming ({{ count($upcomingConsultations) }})</span>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Pending Tab Content -->
                        <div x-show="tab === 'pending'" class="transition duration-200 ease-in-out">
                            @if(count($pendingConsultations) > 0)
                                <div class="space-y-3">
                                    @foreach($pendingConsultations as $consultation)
                                        <div class="bg-white border border-gray-200 rounded-md p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        @if($consultation->client->clientProfile)
                                                            {{ $consultation->client->clientProfile->first_name }} {{ $consultation->client->clientProfile->last_name }}
                                                        @else
                                                            {{ $consultation->client->name ?? 'Client' }}
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        @if($consultation->created_at)
                                                            Requested on {{ $consultation->created_at->format('M d, Y') }}
                                                        @else
                                                            Requested on (Date not available)
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-yellow-600 mt-1">
                                                        {{ ucfirst($consultation->consultation_type) }}
                                                    </p>
                                                </div>
                                                <a href="{{ route('lawyer.consultations') }}" class="inline-flex items-center text-xs font-medium text-green-600 hover:text-green-800 transition-colors duration-200">
                                                    Review
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
                                    <p class="text-sm text-gray-500">No pending consultation requests</p>
                                </div>
                            @endif
                        </div>

                        <!-- Upcoming Tab Content -->
                        <div x-show="tab === 'upcoming'" class="transition duration-200 ease-in-out">
                            @if(count($upcomingConsultations) > 0)
                                <div class="space-y-3">
                                    @foreach($upcomingConsultations as $consultation)
                                        <div class="bg-white border border-gray-200 rounded-md p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        @if($consultation->client->clientProfile)
                                                            {{ $consultation->client->clientProfile->first_name }} {{ $consultation->client->clientProfile->last_name }}
                                                        @else
                                                            {{ $consultation->client->name ?? 'Client' }}
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ $consultation->selected_date->format('M d, Y, g:i a') }}
                                                    </p>
                                                    <p class="text-xs text-indigo-600 mt-1">
                                                        {{ ucfirst($consultation->consultation_type) }}
                                                    </p>
                                                </div>
                                                <a href="{{ route('lawyer.consultations') }}" class="inline-flex items-center text-xs font-medium text-green-600 hover:text-green-800 transition-colors duration-200">
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
                                    <p class="text-sm text-gray-500">No upcoming consultations</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Link to all consultations -->
                    <div class="mt-6">
                        <a href="{{ route('lawyer.consultations') }}" class="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-800 transition-colors duration-200">
                            Manage all consultations
                            <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Deadlines Section -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg class="h-6 w-6 text-red-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700">Deadlines</h3>
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
                                    <p class="text-sm text-gray-500">No deadlines for today</p>
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
                                    <p class="text-sm text-gray-500">No deadlines for this week</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- View all deadlines link -->
                    <div class="mt-6">
                        <a href="{{ route('lawyer.cases') }}?show_deadlines=true" class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-900">
                            View all deadlines
                            <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar (full width) -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 hover:shadow-2xl transition-shadow duration-300 border-t-2 border-green-400">
                <div class="flex items-center mb-4">
                    <svg class="h-6 w-6 text-green-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700">Your Schedule</h3>
                </div>
                
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
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    
    <style>
        /* Calendar container */
        #calendar {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        /* Day cells */
        .fc-daygrid-day {
            min-height: 100px;
        }
        
        /* Reduce text size in calendar */
        .fc-event-title {
            font-size: 0.65rem !important;
            font-weight: normal !important;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
            display: block !important; /* Ensure title is displayed */
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            white-space: normal !important;
            padding: 0 !important;
            margin: 1px 4px !important;
            opacity: 0.9 !important;
            visibility: visible !important;
            color: #4b5563 !important;
        }
        
        .fc-daygrid-day-number, .fc-col-header-cell-cushion {
            font-size: 0.85rem;
        }
        
        /* Make dots smaller for multi-day events */
        .fc-daygrid-event-dot {
            border-width: 3px !important;
        }
        
        /* Add some spacing between events */
        .fc-event {
            margin-bottom: 2px !important;
            min-height: auto !important;
            padding: 0 !important;
            display: block !important;
        }
        
        /* Fix month/week/day button text size */
        .fc-button-primary {
            font-size: 0.8rem !important;
        }
        
        /* Make today's date more visible */
        .fc-day-today {
            background-color: rgba(96, 165, 250, 0.1) !important;
        }
        
        /* Make events more compact */
        .fc-daygrid-event {
            padding: 0 !important;
        }
        
        /* Reset any hidden titles */
        .fc-event-main, .fc-daygrid-event-harness {
            overflow: visible !important;
        }
        
        /* Time display - modified for clarity */
        .fc-event-time {
            display: inline-block !important;
            font-size: 0.6rem !important;
            font-weight: 500 !important;
            padding: 0 2px !important;
            margin-right: 2px !important;
            opacity: 0.85 !important;
        }
        
        /* Different event types */
        .consultation-event {
            border-left: 2px solid #4f46e5 !important;
            background-color: rgba(79, 70, 229, 0.05) !important;
        }
        
        .event-event {
            border-left: 2px solid #3b82f6 !important;
            background-color: rgba(59, 130, 246, 0.05) !important;
        }
        
        .task-event {
            border-left: 2px solid #10b981 !important;
            background-color: rgba(16, 185, 129, 0.05) !important;
        }
        
        .deadline-event {
            border-left: 2px solid #dc2626 !important;
            background-color: rgba(220, 38, 38, 0.05) !important;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: @json($events),
                height: 'auto',
                eventTimeFormat: { // customize the time display
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short' // ensures 'AM'/'PM' is displayed
                },
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                        info.jsEvent.preventDefault(); // prevents browser from following the link
                    }
                },
                // Improve display of events
                eventDidMount: function(info) {
                    // Apply styling based on event type
                    if (info.view.type === 'dayGridMonth') {
                        const eventType = info.event.extendedProps.type || 'event';
                        info.el.classList.add(`${eventType}-event`);
                        
                        // Get time and title elements
                        const timeEl = info.el.querySelector('.fc-event-time');
                        const titleEl = info.el.querySelector('.fc-event-title');
                        
                        if (titleEl) {
                            // Clean up title to remove redundant prefixes
                            let title = titleEl.textContent;
                            // For consultations: show name followed by "(Consultation)"
                            if (eventType === 'consultation') {
                                title = title.replace('Consultation: ', '');
                                title = title + ' (Consultation)';
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
            
            // Ensure calendar is properly rendered and re-rendered when the tab becomes visible
            document.addEventListener('livewire:navigated', () => {
                calendar.updateSize();
            });
        });
    </script>
    @endpush
</div>
