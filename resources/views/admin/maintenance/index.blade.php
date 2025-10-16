<x-layouts.admin>
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">System Maintenance Management</h1>
                <p class="text-gray-600">Schedule and manage system maintenance windows</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>
        
        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Active Maintenance -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            @if($activeSchedules->count() > 0)
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2 animate-pulse"></div>
                                Active Maintenance ({{ $activeSchedules->count() }})
                            @else
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                No Active Maintenance
                            @endif
                        </h3>
                        
                        @if($activeSchedules->count() > 0)
                            <div class="space-y-4">
                                @foreach($activeSchedules as $schedule)
                                    <div class="border border-red-200 bg-red-50 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-red-900">{{ $schedule->title }}</h4>
                                                @if($schedule->description)
                                                    <p class="text-red-800 text-sm mt-1">{{ $schedule->description }}</p>
                                                @endif
                                                <div class="mt-2 text-xs text-red-700">
                                                    <p><strong>Started:</strong> {{ $schedule->start_datetime->format('M j, Y g:i A') }}</p>
                                                    <p><strong>Ends:</strong> {{ $schedule->end_datetime->format('M j, Y g:i A') }}</p>
                                                    <p><strong>Created by:</strong> {{ $schedule->creator->name }}</p>
                                                </div>
                                            </div>
                                            @if(auth()->user()->hasPermission('enable_maintenance_mode'))
                                                <form action="{{ route('admin.maintenance.cancel', $schedule) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this maintenance?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">System is currently running normally.</p>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Maintenance -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Upcoming Maintenance</h3>
                        
                        @if($upcomingSchedules->count() > 0)
                            <div class="space-y-4">
                                @foreach($upcomingSchedules as $schedule)
                                    <div class="border border-blue-200 bg-blue-50 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-blue-900">{{ $schedule->title }}</h4>
                                                @if($schedule->description)
                                                    <p class="text-blue-800 text-sm mt-1">{{ $schedule->description }}</p>
                                                @endif
                                                <div class="mt-2 text-xs text-blue-700">
                                                    <p><strong>Starts:</strong> {{ $schedule->start_datetime->format('M j, Y g:i A') }}</p>
                                                    <p><strong>Ends:</strong> {{ $schedule->end_datetime->format('M j, Y g:i A') }}</p>
                                                    <p><strong>Duration:</strong> {{ $schedule->start_datetime->diffForHumans($schedule->end_datetime, true) }}</p>
                                                    <p><strong>Created by:</strong> {{ $schedule->creator->name }}</p>
                                                </div>
                                            </div>
                                            @if(auth()->user()->hasPermission('enable_maintenance_mode'))
                                                <form action="{{ route('admin.maintenance.cancel', $schedule) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this scheduled maintenance?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No upcoming maintenance scheduled.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Controls Panel -->
            <div class="space-y-6">
                @if(auth()->user()->hasPermission('enable_maintenance_mode'))
                    <!-- Immediate Maintenance -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-orange-600">Enable Immediate Maintenance</h3>
                            <form action="{{ route('admin.maintenance.immediate') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="immediate_title" class="block text-sm font-medium text-gray-700">Title</label>
                                    <input type="text" name="title" id="immediate_title" value="{{ old('title') }}" required 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="mb-4">
                                    <label for="immediate_description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="immediate_description" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="duration" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                                    <select name="duration" id="duration" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="15">15 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="60" selected>1 hour</option>
                                        <option value="120">2 hours</option>
                                        <option value="240">4 hours</option>
                                        <option value="480">8 hours</option>
                                    </select>
                                </div>
                                <button type="submit" 
                                        onclick="return confirm('This will immediately enable maintenance mode. Continue?')"
                                        class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                                    Enable Now
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->hasPermission('schedule_maintenance'))
                    <!-- Schedule Maintenance -->
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Schedule Maintenance</h3>
                            <form action="{{ route('admin.maintenance.store') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                    <input type="text" name="title" id="title" value="{{ old('title') }}" required 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="3" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="start_datetime" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                                    <input type="datetime-local" name="start_datetime" id="start_datetime" value="{{ old('start_datetime') }}" required 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="mb-4">
                                    <label for="end_datetime" class="block text-sm font-medium text-gray-700">End Date & Time</label>
                                    <input type="datetime-local" name="end_datetime" id="end_datetime" value="{{ old('end_datetime') }}" required 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Schedule Maintenance
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($recentSchedules->count() > 0)
            <!-- Recent Maintenance History -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Maintenance History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Period</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSchedules as $schedule)
                                    <tr>
                                        <td class="py-2 px-4 border-b border-gray-200">
                                            <div>
                                                <div class="font-medium">{{ $schedule->title }}</div>
                                                @if($schedule->description)
                                                    <div class="text-gray-500 text-sm">{{ Str::limit($schedule->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-2 px-4 border-b border-gray-200 text-sm">
                                            <div>{{ $schedule->start_datetime->format('M j, Y g:i A') }}</div>
                                            <div>{{ $schedule->end_datetime->format('M j, Y g:i A') }}</div>
                                        </td>
                                        <td class="py-2 px-4 border-b border-gray-200">
                                            @if($schedule->isCurrentlyActive())
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Active</span>
                                            @elseif($schedule->is_completed)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Completed</span>
                                            @elseif($schedule->start_datetime->isFuture())
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Scheduled</span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Finished</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border-b border-gray-200 text-sm">{{ $schedule->creator->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.admin> 