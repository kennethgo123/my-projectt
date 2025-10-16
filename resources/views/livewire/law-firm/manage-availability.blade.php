<div>
    <div class="mb-4">
        <label for="selectedLawyer" class="block text-sm font-medium text-gray-700">Select Lawyer</label>
        <select id="selectedLawyer" wire:model.live="selectedLawyerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
            <option value="">-- Select a Lawyer --</option>
            @if(count($lawyers) > 0)
                @foreach ($lawyers as $lawyer)
                    <option value="{{ $lawyer['id'] }}">{{ $lawyer['name'] }} ({{ $lawyer['email'] }})</option>
                @endforeach
            @else
                <option value="" disabled>No lawyers found in your firm.</option>
            @endif
        </select>
    </div>

    @if ($selectedLawyerId)
        <div wire:key="manage-availability-{{ $selectedLawyerId }}">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                Manage Availability for {{ collect($lawyers)->firstWhere('id', $selectedLawyerId)['name'] ?? 'Selected Lawyer' }}
            </h3>
            @livewire('lawyer.manage-availability', ['lawyerId' => $selectedLawyerId])
        </div>
    @else
        <p class="text-gray-500">Please select a lawyer to manage their availability.</p>
    @endif
</div> 