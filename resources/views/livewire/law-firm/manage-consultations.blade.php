<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="mb-6">
            <div class="sm:hidden">
                <label for="tabs" class="sr-only">Select a tab</label>
                <select id="tabs" name="tabs" class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="consultations" {{ request()->routeIs('law-firm.consultations') ? 'selected' : '' }}>Manage Consultations</option>
                    <option value="availability" {{ request()->routeIs('law-firm.availability') ? 'selected' : '' }}>Set Availability</option>
                </select>
            </div>
            <div class="hidden sm:block">
                <nav class="flex space-x-4 border-b" aria-label="Tabs">
                    <a href="{{ route('law-firm.consultations') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('law-firm.consultations') ? 'bg-indigo-100 text-indigo-700 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Manage Consultations
                    </a>
                    <a href="{{ route('law-firm.availability') }}" class="px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('law-firm.availability') ? 'bg-indigo-100 text-indigo-700 border-b-2 border-indigo-500' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Set Availability
                    </a>
                </nav>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 lg:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 md:mb-0">Manage Firm Consultations</h2>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <div>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search client name..." 
                                class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <select wire:model.live="status" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="accepted">Accepted</option>
                                <option value="declined">Declined</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p>{{ session('message') }}</p>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Client
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('status')">
                                    Status
                                    @if($sortField === 'status')
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Assignment
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                                    Created
                                    @if($sortField === 'created_at')
                                        @if($sortDirection === 'asc') ↑ @else ↓ @endif
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($consultations as $consultation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    @if ($consultation->client->clientProfile)
                                                        {{ $consultation->client->clientProfile->first_name }} {{ $consultation->client->clientProfile->last_name }}
                                                    @else
                                                        {{ $consultation->client->name ?? 'Client #' . $consultation->client->id }}
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $consultation->client->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $consultation->consultation_type }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($consultation->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($consultation->status === 'accepted') bg-blue-100 text-blue-800
                                            @elseif($consultation->status === 'completed') bg-green-100 text-green-800
                                            @elseif($consultation->status === 'declined' || $consultation->status === 'cancelled') bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($consultation->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($consultation->assign_as_entity)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Assigned to Firm
                                            </span>
                                        @elseif ($consultation->specific_lawyer_id)
                                            @php
                                                $specificLawyer = \App\Models\User::find($consultation->specific_lawyer_id);
                                                $lawyerName = 'Unknown Lawyer';
                                                
                                                if ($specificLawyer) {
                                                    if ($specificLawyer->lawFirmLawyer) {
                                                        $lawyerName = $specificLawyer->lawFirmLawyer->first_name . ' ' . $specificLawyer->lawFirmLawyer->last_name;
                                                    } elseif ($specificLawyer->lawyerProfile) {
                                                        $lawyerName = $specificLawyer->lawyerProfile->first_name . ' ' . $specificLawyer->lawyerProfile->last_name;
                                                    } else {
                                                        $lawyerName = $specificLawyer->name;
                                                    }
                                                }
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Assigned: {{ $lawyerName }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Needs Assignment
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $consultation->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            @if($consultation->status === 'pending')
                                                <button 
                                                    wire:click="acceptConsultation({{ $consultation->id }})"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    Accept
                                                </button>
                                            @endif

                                            @if($consultation->status === 'accepted')
                                                @if($consultation->consultation_type === 'Online Consultation')
                                                    <div class="flex items-center space-x-2">
                                                        @if($consultation->meeting_link)
                                                            <a href="{{ $consultation->meeting_link }}" target="_blank" 
                                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                                </svg>
                                                                Join Meeting
                                                            </a>
                                                            <button
                                                                wire:click="showCustomLinkForm({{ $consultation->id }})"
                                                                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                Change Link
                                                            </button>
                                                        @else
                                                            <button
                                                                wire:click="showCustomLinkForm({{ $consultation->id }})"
                                                                class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                Add Meeting Link
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                                <button 
                                                    wire:click="showCompleteForm({{ $consultation->id }})" 
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Mark Complete
                                                </button>
                                            @endif

                                            @if($consultation->status === 'completed')
                                                @if($consultation->case)
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                        <svg class="mr-1 h-3 w-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Case Request Sent
                                                    </span>
                                                @else
                                                    <button 
                                                        wire:click="showStartCaseForm({{ $consultation->id }})" 
                                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        Start Case
                                                    </button>
                                                @endif
                                            @endif

                                            <button 
                                                wire:click="openDetailsModal({{ $consultation->id }})"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ !$consultation->specific_lawyer_id && !$consultation->assign_as_entity ? 'Manage' : 'View Details' }}
                                            </button>
                                            <a 
                                                href="{{ route('messages', ['consultation' => $consultation->id]) }}" 
                                                class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Message
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No consultations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $consultations->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Consultation Details Modal -->
    <div x-data="{ show: @entangle('showDetailsModal') }"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div x-show="show" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div x-show="show" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-3xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                
                <div class="flex items-center justify-between pb-3 border-b">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Consultation Details</h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mt-4">
                    @if($consultationDetails)
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 font-raleway">
                                        @if($consultationDetails->client->clientProfile)
                                            Consultation with {{ $consultationDetails->client->clientProfile->first_name }} {{ $consultationDetails->client->clientProfile->last_name }}
                                            @if($consultationDetails->client->status === 'approved')
                                                <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="mr-1 h-3 w-3 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    Verified User
                                                </span>
                                            @endif
                                        @else
                                            Consultation with {{ $consultationDetails->client->name ?? 'Client #' . $consultationDetails->client->id }}
                                        @endif
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 font-open-sans">
                                        Requested on {{ $consultationDetails->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span @class([
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        'bg-yellow-100 text-yellow-800' => $consultationDetails->status === 'pending',
                                        'bg-green-100 text-green-800' => $consultationDetails->status === 'accepted' || $consultationDetails->status === 'completed',
                                        'bg-red-100 text-red-800' => $consultationDetails->status === 'declined' || $consultationDetails->status === 'cancelled',
                                    ])>
                                        {{ ucfirst($consultationDetails->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 font-raleway">Client Information</h4>
                                    @if($consultationDetails->client->clientProfile)
                                        <div class="mt-1 text-sm text-gray-500 font-open-sans">
                                            <p><span class="font-medium">Email:</span> {{ $consultationDetails->client->email }}</p>
                                            @if($consultationDetails->client->clientProfile->contact_number)
                                                <p><span class="font-medium">Contact:</span> {{ $consultationDetails->client->clientProfile->contact_number }}</p>
                                            @endif
                                            @if($consultationDetails->client->clientProfile->city)
                                                <p><span class="font-medium">Location:</span> {{ $consultationDetails->client->clientProfile->city }}</p>
                                            @endif
                                            <p class="mt-1">
                                                <span class="font-medium">Status:</span>
                                                @if($consultationDetails->client->status === 'approved')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <svg class="mr-1 h-3 w-3 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                        Verified
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        {{ ucfirst($consultationDetails->client->status) }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    @else
                                        <p class="mt-1 text-sm text-gray-500 font-open-sans">
                                            <span class="font-medium">Email:</span> {{ $consultationDetails->client->email }}
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 font-raleway">Consultation Type</h4>
                                    <p class="mt-1 text-sm text-gray-500 font-open-sans">
                                        {{ ucfirst($consultationDetails->consultation_type) }}
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-gray-900 font-raleway">Assignment Status</h4>
                                    <p class="mt-1 text-sm text-gray-500 font-open-sans">
                                        @if ($consultationDetails->assign_as_entity)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Assigned to Firm
                                            </span>
                                        @elseif ($consultationDetails->specific_lawyer_id)
                                            @php
                                                $lawyer = \App\Models\User::find($consultationDetails->specific_lawyer_id);
                                                $lawyerName = 'Unknown Lawyer';
                                                
                                                if ($lawyer) {
                                                    if ($lawyer->lawFirmLawyer) {
                                                        $lawyerName = $lawyer->lawFirmLawyer->first_name . ' ' . $lawyer->lawFirmLawyer->last_name;
                                                    } elseif ($lawyer->lawyerProfile) {
                                                        $lawyerName = $lawyer->lawyerProfile->first_name . ' ' . $lawyer->lawyerProfile->last_name;
                                                    } else {
                                                        $lawyerName = $lawyer->name;
                                                    }
                                                }
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Assigned: {{ $lawyerName }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Needs Assignment
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <h4 class="text-sm font-medium text-gray-900 font-raleway">Preferred Dates</h4>
                                    <div class="mt-1 space-y-1">
                                        @foreach(json_decode($consultationDetails->preferred_dates) as $date)
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-gray-500 font-open-sans">
                                                    {{ \Carbon\Carbon::parse($date)->format('M d, Y g:i A') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-900 font-raleway">Description</h4>
                                <p class="mt-1 text-sm text-gray-500 font-open-sans">
                                    {{ $consultationDetails->description }}
                                </p>
                            </div>

                            @if($consultationDetails->documents)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-900 font-raleway">Attached Documents</h4>
                                    <div class="mt-2 space-y-2">
                                        @foreach(json_decode($consultationDetails->documents) as $document)
                                            <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-md border border-gray-200">
                                                <div class="flex items-center">
                                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                    </svg>
                                                    <span class="text-sm text-gray-700 font-medium">{{ basename($document) }}</span>
                                                </div>
                                                <a href="{{ Storage::url($document) }}" target="_blank" class="ml-4 text-sm text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($consultationDetails->status === 'pending' && !$consultationDetails->specific_lawyer_id && !$consultationDetails->assign_as_entity)
                                <div class="mt-6 border-t border-gray-200 pt-6">
                                    <h4 class="text-sm font-medium text-gray-900 font-raleway">Assign Lawyer</h4>
                                    
                                    <div class="mt-3">
                                        <label for="detail-lawyer" class="block text-sm font-medium text-gray-700">Select Lawyer or Assign to Firm</label>
                                        <select id="detail-lawyer" wire:model="assignedLawyerId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="">-- Select a lawyer or assign to firm --</option>
                                            <option value="__firm__" class="font-medium text-indigo-600">
                                                Assign to {{ auth()->user()->lawFirmProfile->firm_name ?? 'Law Firm' }}
                                            </option>
                                            <optgroup label="Individual Lawyers">
                                                @foreach($firmLawyers as $lawyer)
                                                    <option value="{{ $lawyer->id }}">
                                                        @if($lawyer->lawFirmLawyer)
                                                            {{ $lawyer->lawFirmLawyer->first_name }} {{ $lawyer->lawFirmLawyer->last_name }}
                                                        @elseif($lawyer->lawyerProfile)
                                                            {{ $lawyer->lawyerProfile->first_name }} {{ $lawyer->lawyerProfile->last_name }}
                                                        @else
                                                            {{ $lawyer->name ?? "Lawyer #".$lawyer->id }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        </select>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <h5 class="text-sm font-medium text-gray-700">Select Consultation Time</h5>
                                        <div class="mt-2 space-y-3">
                                            @foreach(json_decode($consultationDetails->preferred_dates) as $index => $date)
                                                <div class="flex items-center">
                                                    <input id="preferred-date-{{ $index }}"
                                                           name="selectedDate" 
                                                           type="radio"
                                                           value="{{ $date }}" 
                                                           wire:model="selectedDate"
                                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                                    <label for="preferred-date-{{ $index }}" class="ml-3 text-sm text-gray-600 font-open-sans">
                                                        {{ \Carbon\Carbon::parse($date)->format('M d, Y g:i A') }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                
                                    <div class="mt-6 flex justify-end">
                                        <button 
                                            wire:click="assignLawyerWithTime"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Assign & Accept
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Office Address Section (show only for in-house consultations) -->
                            @if($consultationDetails->consultation_type === 'In-House Consultation' && $consultationDetails->status === 'accepted')
                                <div class="mt-6 border-t border-gray-200 pt-6">
                                    <div class="flex items-center mb-2">
                                        <svg class="h-5 w-5 text-emerald-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <h4 class="text-sm font-medium text-emerald-900 font-raleway">Office Location</h4>
                                    </div>
                                    <p class="text-sm text-gray-700 font-open-sans leading-relaxed">
                                        {{ auth()->user()->lawFirmProfile->office_address }}
                                    </p>
                                    @if(auth()->user()->lawFirmProfile->lat && auth()->user()->lawFirmProfile->lng)
                                        <div class="mt-2">
                                            <a href="https://www.openstreetmap.org/?mlat={{ auth()->user()->lawFirmProfile->lat }}&mlon={{ auth()->user()->lawFirmProfile->lng }}&zoom=18" 
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 font-raleway">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                Open in OpenStreetMap
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Completed Consultation Results (show only for completed consultations) -->
                            @if($consultationDetails->status === 'completed')
                                <div class="mt-6 border-t border-gray-200 pt-6">
                                    <div class="flex items-center mb-2">
                                        <svg class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h4 class="text-sm font-medium text-green-900 font-raleway">Consultation Results</h4>
                                    </div>
                                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                                        <p class="text-sm text-gray-700 font-open-sans whitespace-pre-line leading-relaxed">
                                            {{ $consultationDetails->consultation_results }}
                                        </p>
                                        
                                        @if($consultationDetails->meeting_notes)
                                            <div class="mt-4 pt-4 border-t border-gray-100">
                                                <h5 class="text-sm font-medium text-gray-700 font-raleway mb-2">Meeting Notes</h5>
                                                <p class="text-sm text-gray-700 font-open-sans whitespace-pre-line">
                                                    {{ $consultationDetails->meeting_notes }}
                                                </p>
                                            </div>
                                        @endif
                                        
                                        @if($consultationDetails->consultation_document_path)
                                            <div class="mt-4 pt-4 border-t border-gray-100">
                                                <h5 class="text-sm font-medium text-gray-700 font-raleway mb-2">Consultation Document</h5>
                                                <a href="{{ Storage::url($consultationDetails->consultation_document_path) }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 font-raleway">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    Download Document
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-4 text-center text-gray-500">
                            No consultation details available.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Meeting Link Modal -->
    @if($showCustomLinkModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 font-raleway" id="modal-title">
                                Custom Meeting Link
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 font-open-sans">
                                    Enter a custom meeting link for your online consultation (e.g., Google Meet, Zoom, etc.)
                                </p>
                                <div class="mt-4">
                                    <input
                                        type="url"
                                        wire:model="customMeetingLink"
                                        placeholder="https://meet.google.com/xyz-abcd-123"
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md font-open-sans"
                                    />
                                    @error('customMeetingLink')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="saveCustomMeetingLink" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm font-raleway">
                        Save Link
                    </button>
                    <button type="button" wire:click="$set('showCustomLinkModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-raleway">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Complete Consultation Modal -->
    @if($showCompleteModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 font-raleway" id="modal-title">
                                Complete Consultation
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 font-open-sans">
                                    Mark this consultation as complete and provide the results and findings. This information will be shared with the client.
                                </p>
                                <div class="mt-4">
                                    <label for="consultationResults" class="block text-sm font-medium text-gray-700 font-raleway">Consultation Results and Findings (Required)</label>
                                    <textarea
                                        id="consultationResults"
                                        wire:model="consultationResults"
                                        rows="4"
                                        class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md font-open-sans"
                                        placeholder="Enter the consultation results and findings here..."></textarea>
                                    @error('consultationResults')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mt-4">
                                    <label for="meetingNotes" class="block text-sm font-medium text-gray-700 font-raleway">Meeting Notes (Optional)</label>
                                    <textarea
                                        id="meetingNotes"
                                        wire:model="meetingNotes"
                                        rows="3"
                                        class="mt-1 shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md font-open-sans"
                                        placeholder="Add any additional notes from the meeting (optional)..."></textarea>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 font-raleway">Consultation Document (Optional)</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload a file</span>
                                                    <input id="file-upload" name="file-upload" type="file" wire:model="consultationDocument" class="sr-only">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, JPEG, PNG up to 10MB</p>
                                        </div>
                                    </div>
                                    @error('consultationDocument')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                        
                                    <div wire:loading wire:target="consultationDocument" class="mt-2 text-sm text-green-600">
                                        Uploading document...
                                    </div>
                                    
                                    @if($consultationDocument)
                                    <div class="mt-2 text-sm text-gray-700">
                                        Selected file: <span class="font-medium">{{ $consultationDocument->getClientOriginalName() }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="completeConsultation" wire:loading.attr="disabled" wire:target="completeConsultation, consultationDocument" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm font-raleway">
                        <span wire:loading.remove wire:target="completeConsultation">Complete Consultation</span>
                        <span wire:loading wire:target="completeConsultation">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                    <button type="button" wire:click="$set('showCompleteModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-raleway">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Start Case Modal -->
    @if($showStartCaseModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 font-raleway" id="modal-title">
                                Start New Case
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 font-open-sans">
                                    Start a new case with this client. You'll need to provide case details and upload a contract document that will be sent to the client for review and signature.
                                </p>
                                
                                <div class="mt-4 space-y-4">
                                    <!-- Case Title -->
                                    <div>
                                        <label for="caseTitle" class="block text-sm font-medium text-gray-700 font-raleway">Case Title</label>
                                        <input 
                                            type="text" 
                                            id="caseTitle" 
                                            wire:model="caseTitle" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-open-sans"
                                            placeholder="Enter a title for this case"
                                        >
                                        @error('caseTitle')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Case Description -->
                                    <div>
                                        <label for="caseDescription" class="block text-sm font-medium text-gray-700 font-raleway">Case Description</label>
                                        <textarea 
                                            id="caseDescription" 
                                            wire:model="caseDescription" 
                                            rows="4" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-open-sans"
                                            placeholder="Describe the case and legal services required"
                                        ></textarea>
                                        @error('caseDescription')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <!-- Contract Document Upload -->
                                    <div>
                                        <label for="contract-upload" class="block text-sm font-medium text-gray-700 font-raleway">Contract Document</label>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="flex text-sm text-gray-600">
                                                    <label for="contract-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                        <span>Upload a contract document</span>
                                                        <input id="contract-upload" wire:model="contractDocument" type="file" class="sr-only">
                                                    </label>
                                                    <p class="pl-1">or drag and drop</p>
                                                </div>
                                                <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 10MB</p>
                                            </div>
                                        </div>
                                        @error('contractDocument')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        
                                        <div wire:loading wire:target="contractDocument" class="mt-2 text-sm text-indigo-600">
                                            Uploading document...
                                        </div>
                                        
                                        @if($contractDocument)
                                        <div class="mt-2 text-sm text-gray-700">
                                            <span class="font-medium">Selected document:</span> {{ $contractDocument->getClientOriginalName() }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="startCase" wire:loading.attr="disabled" wire:target="startCase, contractDocument" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm font-raleway">
                        <span wire:loading.remove wire:target="startCase">Create Case</span>
                        <span wire:loading wire:target="startCase">Processing...</span>
                    </button>
                    <button type="button" wire:click="$set('showStartCaseModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-raleway">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div> 