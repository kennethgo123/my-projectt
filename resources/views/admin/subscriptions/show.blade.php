<x-layouts.admin>
    <div class="max-w-7xl mx-auto">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Subscription Details</h2>
                <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Back to All Subscriptions
                </a>
            </div>
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif
            
            <!-- Subscription Details -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Subscription Information</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Plan</dt>
                            <dd class="mt-1 text-base font-semibold text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $subscription->plan->name === 'Free' ? 'bg-gray-100 text-gray-800' : ($subscription->plan->name === 'Pro' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $subscription->plan->name }}
                                </span>
                                <span class="text-sm text-gray-500 ml-2">({{ ucfirst($subscription->plan->for_role) }})</span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : ($subscription->status === 'canceled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Billing Cycle</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ ucfirst($subscription->billing_cycle) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Price</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                @if($subscription->billing_cycle === 'monthly')
                                    ₱{{ number_format($subscription->plan->monthly_price, 2) }}/month
                                @else
                                    ₱{{ number_format($subscription->plan->annual_price, 2) }}/year
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $subscription->starts_at->format('F d, Y') }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">End Date</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                {{ $subscription->ends_at ? $subscription->ends_at->format('F d, Y') : 'Never' }}
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Auto Renew</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $subscription->auto_renew ? 'Yes' : 'No' }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $subscription->payment_method ?? 'Not specified' }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment ID</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                <span class="font-mono text-sm">{{ $subscription->payment_id ?? 'Not specified' }}</span>
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $subscription->created_at->format('F d, Y H:i:s') }}</dd>
                        </div>
                    </dl>
                </div>
                
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Plan Details</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Plan Name</dt>
                            <dd class="mt-1 text-base font-semibold text-gray-900">{{ $subscription->plan->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">For Role</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ ucfirst($subscription->plan->for_role) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $subscription->plan->description }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Monthly Price</dt>
                            <dd class="mt-1 text-base text-gray-900">₱{{ number_format($subscription->plan->monthly_price, 2) }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Annual Price</dt>
                            <dd class="mt-1 text-base text-gray-900">₱{{ number_format($subscription->plan->annual_price, 2) }}</dd>
                        </div>
                        
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Features</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach($subscription->plan->features as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    </dl>
                </div>
                
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">User Information</h3>
                    
                    <div class="flex items-center mb-6">
                        <div class="flex-shrink-0 h-16 w-16">
                            <img class="h-16 w-16 rounded-full" src="{{ $subscription->user->profile_photo_url }}" alt="User profile photo">
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-semibold text-gray-900">
                                @if($subscription->user->role->name === 'lawyer')
                                    @if($subscription->user->lawyerProfile)
                                        {{ $subscription->user->lawyerProfile->first_name }} {{ $subscription->user->lawyerProfile->last_name }}
                                    @else
                                        {{ $subscription->user->name }}
                                    @endif
                                @elseif($subscription->user->role->name === 'law_firm')
                                    @if($subscription->user->lawFirmProfile)
                                        {{ $subscription->user->lawFirmProfile->firm_name }}
                                    @else
                                        {{ $subscription->user->name }}
                                    @endif
                                @else
                                    {{ $subscription->user->name }}
                                @endif
                            </h4>
                            <div class="text-sm text-gray-500 mt-1">
                                <div>{{ $subscription->user->email }}</div>
                                <div class="mt-1">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($subscription->user->role->name) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($subscription->user->role->name === 'lawyer' && $subscription->user->lawyerProfile)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-700 mb-2">Lawyer Profile</h5>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <div>
                                    <dt class="text-gray-500">Full Name</dt>
                                    <dd class="font-medium">{{ $subscription->user->lawyerProfile->first_name }} {{ $subscription->user->lawyerProfile->last_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Phone</dt>
                                    <dd class="font-medium">{{ $subscription->user->lawyerProfile->phone ?? 'Not provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Address</dt>
                                    <dd class="font-medium">{{ $subscription->user->lawyerProfile->address ?? 'Not provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @elseif($subscription->user->role->name === 'law_firm' && $subscription->user->lawFirmProfile)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h5 class="font-medium text-gray-700 mb-2">Law Firm Profile</h5>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <div>
                                    <dt class="text-gray-500">Firm Name</dt>
                                    <dd class="font-medium">{{ $subscription->user->lawFirmProfile->firm_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Phone</dt>
                                    <dd class="font-medium">{{ $subscription->user->lawFirmProfile->phone ?? 'Not provided' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Address</dt>
                                    <dd class="font-medium">{{ $subscription->user->lawFirmProfile->address ?? 'Not provided' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Back to All Subscriptions
                </a>
                
                @if($subscription->status === 'active')
                    <form action="{{ route('admin.subscriptions.cancel', $subscription) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Are you sure you want to cancel this subscription?')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Cancel Subscription
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin> 