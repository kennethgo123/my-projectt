<!-- Timeline Filters -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <h3 class="text-lg font-medium text-gray-900 font-raleway">Interaction Timeline ({{ $timelineCount }} items)</h3>
            <button wire:click="exportTimeline" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-md hover:bg-blue-700 font-open-sans">
                Export Timeline
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1 font-raleway">Date Range</label>
                <select wire:model.live="selectedDateRange" class="block w-full text-xs border-gray-300 rounded-md font-open-sans">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                    <option value="0">All time</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1 font-raleway">Type</label>
                <select wire:model.live="filterType" class="block w-full text-xs border-gray-300 rounded-md font-open-sans">
                    <option value="">All Types</option>
                    @foreach($this->timelineTypes as $type)
                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1 font-raleway">Category</label>
                <select wire:model.live="filterCategory" class="block w-full text-xs border-gray-300 rounded-md font-open-sans">
                    <option value="">All Categories</option>
                    @foreach($this->timelineCategories as $category)
                        <option value="{{ $category }}">{{ ucfirst(str_replace('_', ' ', $category)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1 font-raleway">Severity</label>
                <select wire:model.live="filterSeverity" class="block w-full text-xs border-gray-300 rounded-md font-open-sans">
                    <option value="">All Severity</option>
                    <option value="normal">Normal</option>
                    <option value="warning">Warning</option>
                </select>
            </div>
        </div>

        <!-- Timeline Items -->
        <div class="space-y-4 max-h-96 overflow-y-auto">
            @forelse($timeline as $index => $item)
                <div class="border-l-4 {{ $item['severity'] === 'warning' ? 'border-yellow-400' : 'border-blue-400' }} pl-4 pb-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <!-- Icon based on type -->
                                <div class="flex-shrink-0">
                                    @include('livewire.admin.investigation-dashboard.timeline-icon', ['iconType' => $item['icon']])
                                </div>
                                <h4 class="text-sm font-medium text-gray-900 font-raleway">{{ $item['title'] }}</h4>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $item['actor_type'] === 'lawyer' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $item['actor'] }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 font-open-sans">{{ $item['description'] }}</p>
                            <p class="text-xs text-gray-500 mt-1 font-open-sans">{{ $item['formatted_time'] }}</p>
                        </div>
                        <button wire:click="toggleTimelineDetails({{ $index }})" 
                                class="ml-4 text-blue-600 hover:text-blue-800 text-xs font-open-sans">
                            {{ isset($showTimelineDetails[$index]) ? 'Hide' : 'Details' }}
                        </button>
                    </div>
                    
                    <!-- Expanded Details -->
                    @if(isset($showTimelineDetails[$index]))
                        <div class="mt-3 p-3 bg-gray-50 rounded-md">
                            <div class="text-sm text-gray-700 mb-2 font-open-sans">
                                <strong>Full Content:</strong><br>
                                {{ $item['full_content'] }}
                            </div>
                            @if(!empty($item['metadata']))
                                <div class="text-xs text-gray-600 font-open-sans">
                                    <strong>Metadata:</strong>
                                    @foreach($item['metadata'] as $key => $value)
                                        @if($value)
                                            <br><em>{{ ucfirst(str_replace('_', ' ', $key)) }}:</em> {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 font-open-sans">
                    <p>No interactions found for the selected criteria.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
