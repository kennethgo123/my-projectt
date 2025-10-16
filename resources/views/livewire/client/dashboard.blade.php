<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-6 mb-6 text-white">
            <h1 class="text-2xl font-bold mb-2">
                Welcome back, {{ auth()->user()->clientProfile->first_name ?? auth()->user()->name }}!
            </h1>
            <p class="text-blue-100">
                You have {{ $upcomingConsultations->count() }} upcoming consultations this week.
            </p>
        </div>

        <!-- Navigation & FAQ Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6" x-data="{ activeTab: 'overview' }">
            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Dashboard Overview
                    </button>
                    <button @click="activeTab = 'lawyers'" 
                            :class="activeTab === 'lawyers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Finding & Hiring Lawyers
                    </button>
                    <button @click="activeTab = 'payments'" 
                            :class="activeTab === 'payments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Payments & Security
                    </button>
                    <button @click="activeTab = 'disputes'" 
                            :class="activeTab === 'disputes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Disputes & Support
                    </button>
                    <button @click="activeTab = 'general'" 
                            :class="activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        General FAQ
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Overview Tab (Default) -->
                <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Welcome to LexCav</h3>
                    <p class="text-gray-600 mb-4">
                        Your one-stop platform for finding qualified legal professionals. Use the quick actions below to get started with your legal needs.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('client.nearby-lawyers') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h4 class="font-medium text-gray-900">Find Lawyers</h4>
                            <p class="text-sm text-gray-600">Browse lawyers by city or legal service</p>
                        </a>
                        <a href="{{ route('client.consultations') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h4 class="font-medium text-gray-900">My Consultations</h4>
                            <p class="text-sm text-gray-600">View and manage your consultations</p>
                        </a>
                        <a href="{{ route('messages') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h4 class="font-medium text-gray-900">Messages</h4>
                            <p class="text-sm text-gray-600">Communicate with your lawyers</p>
                        </a>
                    </div>
                </div>

                <!-- Finding & Hiring Lawyers FAQ -->
                <div x-show="activeTab === 'lawyers'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Finding & Hiring Lawyers</h3>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: How do I search for a lawyer?</h4>
                            <p class="text-gray-600">A: You can browse lawyers by city or by legal service (e.g., Criminal Law, Civil Law, Family Law). Use the "Find Lawyers" dropdown on the main menu.</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: Can I message a lawyer before hiring?</h4>
                            <p class="text-gray-600">A: Yes. You can send a direct message to lawyers to discuss your case before making any payments.</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: Can I hire more than one lawyer?</h4>
                            <p class="text-gray-600">A: Yes. You may hire multiple lawyers depending on your needs. Each engagement is handled separately for transparency.</p>
                        </div>
                    </div>
                </div>

                <!-- Payments & Security FAQ -->
                <div x-show="activeTab === 'payments'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payments & Security</h3>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: How do I pay my lawyer?</h4>
                            <p class="text-gray-600">A: All payments are made through the platform's secure payment system. Funds are held in escrow until the service is completed, then released to the lawyer.</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: What payment methods are supported?</h4>
                            <p class="text-gray-600">A: Credit/Debit Cards, Online Banking, and e-Wallets (GCash, Bank Transfer, etc.).</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: Is my payment safe?</h4>
                            <p class="text-gray-600">A: Yes. The platform uses escrow to protect clients. Lawyers only get paid when you confirm the service has been completed.</p>
                        </div>
                    </div>
                </div>

                <!-- Disputes & Support FAQ -->
                <div x-show="activeTab === 'disputes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Disputes & Support</h3>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: What if I have a dispute with my lawyer?</h4>
                            <p class="text-gray-600">A: You can open a support ticket through creating a report. Our admin team will review the case and mediate fairly between you and the lawyer.</p>
                            <div class="mt-2">
                                <p class="text-sm text-blue-600">
                                    💡 <strong>Tip:</strong> You can create a report by visiting the lawyer's profile page and clicking the "Report" button.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- General FAQ -->
                <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">General</h3>
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: Can I hire lawyers outside the platform?</h4>
                            <p class="text-gray-600">A: For your security, we strongly recommend keeping all transactions on the platform. Transactions outside are not protected by our escrow or dispute system.</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Q: How do I contact support?</h4>
                            <p class="text-gray-600">A: You can reach our support team through the contact form or by creating a report if you have issues with a lawyer.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Total Consultations -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total Consultations</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalConsultations }}</p>
                        <p class="text-xs text-gray-500">+{{ $thisWeekConsultations }} vs last month</p>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">This Week</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $thisWeekConsultations }}</p>
                        <p class="text-xs text-gray-500">+{{ $thisWeekConsultations }} vs last month</p>
                    </div>
                </div>
            </div>

            <!-- Active Cases -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Active Cases</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeCases }}</p>
                        <p class="text-xs text-gray-500">+2 vs last month</p>
                    </div>
                </div>
            </div>

            <!-- Completed Cases -->
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Completed Cases</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $completedCases }}</p>
                        <p class="text-xs text-gray-500">+3 vs last month</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upcoming Consultations -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Upcoming Consultations</h2>
                        <a href="{{ route('client.consultations') }}" class="text-sm text-blue-600 hover:text-blue-700">View All</a>
                    </div>
                    <div class="p-6">
                        @if($upcomingConsultations->count() > 0)
                            <div class="space-y-4">
                                @foreach($upcomingConsultations as $consultation)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-blue-600">
                                                        {{ substr($this->getLawyerName($consultation->lawyer), 0, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <h3 class="text-sm font-medium text-gray-900">
                                                    {{ $this->getLawyerName($consultation->lawyer) }}
                                                </h3>
                                                <p class="text-sm text-gray-600">{{ ucfirst($consultation->consultation_type) }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $consultation->selected_date ? $consultation->selected_date->format('M d') : 'TBD' }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                {{ $consultation->selected_date ? $consultation->selected_date->format('g:i A') : '' }}
                                            </p>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                @if($consultation->status === 'accepted') bg-green-100 text-green-800
                                                @elseif($consultation->status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($consultation->status) }}
                                            </span>
                                        </div>
                                        <div class="flex space-x-2">
                                            @if($consultation->meeting_link)
                                                <a href="{{ $consultation->meeting_link }}" target="_blank" 
                                                   class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700">
                                                    Join
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming consultations</h3>
                                <p class="mt-1 text-sm text-gray-500">Book a consultation to get started.</p>
                                <div class="mt-6">
                                    <a href="{{ route('client.nearby-lawyers') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Book New Consultation
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('client.nearby-lawyers') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Book New Consultation
                        </a>
                        <a href="{{ route('client.nearby-lawyers') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Find a Lawyer
                        </a>
                        <a href="{{ route('client.consultations') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            View Consultations
                        </a>
                        <a href="{{ route('messages') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            View Messages
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                    </div>
                    <div class="p-6">
                        @if(count($recentActivity) > 0)
                            <div class="space-y-4">
                                @foreach($recentActivity as $activity)
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center">
                                                @if($activity['icon'] === 'calendar')
                                                    <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                @elseif($activity['icon'] === 'document')
                                                    <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-900">{{ $activity['message'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $activity['time'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-sm text-gray-500">No recent activity</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 