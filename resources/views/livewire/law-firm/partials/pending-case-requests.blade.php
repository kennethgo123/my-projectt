@if($pendingCases->isNotEmpty())
    <div class="bg-amber-50 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-medium text-amber-800 mb-2">Pending Case Requests</h3>
        <p class="text-sm text-amber-600 mb-4">These cases have been requested by clients and require your review before proceeding.</p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Case Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Consultation</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingCases as $case)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            @if($case->client->clientProfile)
                                                {{ $case->client->clientProfile->first_name }} {{ $case->client->clientProfile->last_name }}
                                            @else
                                                {{ $case->client->name }}
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $case->client->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $case->title }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($case->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $case->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($case->consultation)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Yes
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="viewDetails({{ $case->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                        View Details
                                    </button>
                                    <button wire:click="showAction({{ $case->id }}, 'accept')" class="ml-2 text-green-600 hover:text-green-900 font-medium">
                                        Accept
                                    </button>
                                    <button wire:click="showAction({{ $case->id }}, 'reject')" class="ml-2 text-red-600 hover:text-red-900 font-medium">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif 