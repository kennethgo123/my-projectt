<!-- Interaction Statistics -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 font-raleway">Interaction Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $interactionStats['total_messages'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-open-sans">Total Messages</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $interactionStats['total_consultations'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-open-sans">Consultations</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $interactionStats['total_cases'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 font-open-sans">Legal Cases</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $interactionStats['avg_response_time'] ?? 'N/A' }}</div>
                <div class="text-xs text-gray-500 font-open-sans">Avg Response</div>
            </div>
        </div>
        <div class="mt-4 text-xs text-gray-500 font-open-sans text-center">
            First Interaction: {{ $interactionStats['first_interaction'] ?? 'N/A' }} | 
            Last Interaction: {{ $interactionStats['last_interaction'] ?? 'N/A' }}
        </div>
        @if(isset($interactionStats['privacy_note']))
            <div class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-xs text-blue-700 font-open-sans text-center">
                    🔒 {{ $interactionStats['privacy_note'] }}
                </p>
            </div>
        @endif
    </div>
</div>
