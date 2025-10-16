<div>
    <!-- Page Header -->
    <div class="pb-5 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Case Details: {{ $case->case_number }}
        </h3>
        
        <div class="mt-3 flex sm:mt-0 sm:ml-4">
            <a href="{{ route('law-firm.cases') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Cases
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mt-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button wire:click="setActiveTab('overview')" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Overview
            </button>
        </nav>
    </div>

    <div class="mt-6">
        @if($activeTab === 'overview')
            <!-- Case information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Case Information
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Details and status of the case.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Title
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $case->title }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Description
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $case->description }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Status
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($case->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($case->status === 'accepted') bg-blue-100 text-blue-800
                                    @elseif($case->status === 'rejected') bg-red-100 text-red-800
                                    @elseif($case->status === 'contract_sent') bg-indigo-100 text-indigo-800
                                    @elseif($case->status === 'contract_signed') bg-green-100 text-green-800
                                    @elseif($case->status === 'active') bg-emerald-100 text-emerald-800
                                    @elseif($case->status === 'closed') bg-gray-100 text-gray-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                </span>
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Created at
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                {{ $case->created_at->format('M d, Y h:i A') }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Client Information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Client Information
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @if($case->client->clientProfile)
                                    {{ $case->client->clientProfile->first_name }} {{ $case->client->clientProfile->last_name }}
                                @else
                                    {{ $case->client->name }}
                                @endif
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Contact</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <div>Email: {{ $case->client->email }}</div>
                                @if($case->client->clientProfile)
                                    <div>Phone: {{ $case->client->clientProfile->phone ?? 'Not provided' }}</div>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Lawyer Information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Lawyer Information
                    </h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                @if($case->lawyer->lawyerProfile)
                                    {{ $case->lawyer->lawyerProfile->first_name }} {{ $case->lawyer->lawyerProfile->last_name }}
                                @else
                                    {{ $case->lawyer->name }}
                                @endif
                            </dd>
                        </div>
                        @if($case->lawyer->lawyerProfile)
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Specialization</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    {{ $case->lawyer->lawyerProfile->specialization ?? 'Not specified' }}
                                </dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Contact</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <div>Email: {{ $case->lawyer->email }}</div>
                                    <div>Phone: {{ $case->lawyer->lawyerProfile->phone ?? 'Not provided' }}</div>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Contract Section -->
            @if($case->contract_path)
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Contract</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Status: {{ ucfirst(str_replace('_', ' ', $case->contract_status)) }}
                        </p>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5">
                        <div class="mt-1 text-sm text-gray-900">
                            <a href="{{ Storage::url($case->contract_path) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Contract
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Case Updates -->
            @if($case->caseUpdates && $case->caseUpdates->count() > 0)
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Case Updates</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <ul class="divide-y divide-gray-200">
                            @foreach($case->caseUpdates as $update)
                                <li class="px-4 py-4">
                                    <div class="flex justify-between">
                                        <div class="font-medium text-indigo-600">{{ $update->title }}</div>
                                        <div class="text-sm text-gray-500">{{ $update->created_at->format('M d, Y h:i A') }}</div>
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">{{ $update->content }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div> 