<!-- Active Search Query Indicator -->
@if($search)
<div class="bg-white rounded-xl shadow-md mb-6 p-4 border-l-4 border-emerald-500">
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <span class="text-gray-600 font-open-sans">Search results for:</span>
            <span class="ml-2 font-semibold text-emerald-700 font-raleway">{{ $search }}</span>
        </div>
        <button wire:click="$set('search', '')" class="p-1.5 rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
@endif

<!-- Loading Indicator -->
<div wire:loading class="text-center py-16 bg-white rounded-2xl shadow-md">
    <div class="inline-block animate-spin rounded-full h-10 w-10 border-t-3 border-b-3 border-emerald-600"></div>
    <p class="mt-4 text-gray-600 font-open-sans">Finding the best legal experts for you...</p>
</div>

<!-- Results -->
<div wire:loading.remove>
    @if(count($lawyers) > 0 || count($lawFirms) > 0 || count($lawFirmLawyers) > 0)
        <div class="space-y-10">
            <!-- Independent Lawyers, Law Firm Lawyers, and Law Firms -->
            <div class="grid grid-cols-1 gap-6">
                <!-- Individual Lawyers -->
                @foreach($lawyers as $lawyer)
                    @include('livewire.lawyers.components.lawyer-card', ['lawyer' => $lawyer, 'type' => 'lawyer'])
                @endforeach
                
                <!-- Law Firm Lawyers -->
                @foreach($lawFirmLawyers as $firmLawyer)
                    @include('livewire.lawyers.components.lawyer-card', ['lawyer' => $firmLawyer, 'type' => 'firmLawyer'])
                @endforeach
                
                <!-- Law Firms -->
                @foreach($lawFirms as $lawFirm)
                    @include('livewire.lawyers.components.lawfirm-card', ['lawFirm' => $lawFirm])
                @endforeach
            </div>
        </div>
    @else
        <!-- No Results Message -->
        <div class="text-center py-12 bg-white rounded-lg shadow-md">
            <svg class="mx-auto h-12 w-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mt-2 text-base font-medium text-emerald-800 font-raleway">No results found</h3>
            <p class="mt-1 text-sm text-gray-500 font-open-sans">We couldn't find any lawyers matching your search criteria. Please try different filters or search terms.</p>
            <div class="mt-6">
                <button wire:click="resetFilters" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 font-raleway">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Reset Filters
                </button>
            </div>
        </div>
    @endif
</div> 