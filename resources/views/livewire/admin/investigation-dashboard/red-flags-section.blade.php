<!-- Red Flags -->
@if(count($redFlags) > 0)
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-red-900 mb-4 font-raleway">⚠️ Red Flags Detected</h3>
        <div class="space-y-3">
            @foreach($redFlags as $flag)
                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-red-800 font-raleway">{{ $flag['description'] }}</p>
                        <p class="text-xs text-red-600 font-open-sans">Severity: {{ ucfirst($flag['severity']) }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        {{ $flag['severity'] === 'high' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $flag['count'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
