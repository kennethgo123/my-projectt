<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session()->has('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Investigation Header -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-2 font-raleway">Investigation Dashboard</h2>
                            <p class="text-gray-600 font-open-sans">Report #{{ $report->id }} - {{ $report->category_label }}</p>
                        </div>
                        <div class="flex space-x-3">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $investigation->priority_color }}">
                                {{ $investigation->priority_label }}
                            </span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $investigation->status_color }}">
                                {{ $investigation->status_label }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Reporter Information -->
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-900 mb-2 font-raleway">Reporter (Client)</h3>
                            <p class="text-sm text-blue-800 font-open-sans">{{ $report->reporter_name }}</p>
                            <p class="text-sm text-blue-600 font-open-sans">{{ $report->reporter_email }}</p>
                            <p class="text-xs text-blue-500 font-open-sans">Report Date: {{ $report->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                        
                        <!-- Reported User Information -->
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-orange-900 mb-2 font-raleway">Reported {{ ucfirst($report->reported_type) }}</h3>
                            <p class="text-sm text-orange-800 font-open-sans">{{ $report->reported_name }}</p>
                            <p class="text-sm text-orange-600 font-open-sans">{{ $report->reportedUser->email }}</p>
                            @if($report->service_date)
                                <p class="text-xs text-orange-500 font-open-sans">Service Date: {{ $report->service_date->format('M j, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Report Description -->
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-sm font-medium text-gray-900 mb-2 font-raleway">Report Description</h3>
                        <p class="text-sm text-gray-700 font-open-sans">{{ $report->description }}</p>
                        @if($report->timeline_of_events)
                            <div class="mt-3">
                                <h4 class="text-xs font-medium text-gray-800 mb-1 font-raleway">Timeline of Events</h4>
                                <p class="text-xs text-gray-600 font-open-sans">{{ $report->timeline_of_events }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Investigation Panel -->
                <div class="lg:col-span-2 space-y-6">
                    @include('livewire.admin.investigation-dashboard.statistics-section')
                    @include('livewire.admin.investigation-dashboard.red-flags-section')
                    @include('livewire.admin.investigation-dashboard.timeline-section')
                </div>

                <!-- Investigation Management Panel -->
                <div class="space-y-6">
                    @include('livewire.admin.investigation-dashboard.management-section')
                </div>
            </div>
        </div>
    </div>
</div>