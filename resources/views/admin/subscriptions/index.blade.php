<x-layouts.admin>
    <div class="max-w-7xl mx-auto">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Subscription Management</h2>
                <div class="text-sm text-gray-600">
                    <span class="font-semibold">Total Revenue:</span> ₱{{ number_format($stats['monthly_revenue'] + $stats['annual_revenue'], 2) }}
                </div>
            </div>
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700">Total Subscriptions</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                    <div class="mt-2 text-sm text-gray-500">All subscription records</div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700">Active Subscriptions</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $stats['active'] }}</p>
                    <div class="mt-2 text-sm text-gray-500">Currently active subscriptions</div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700">Pro Tier</h3>
                    <p class="text-3xl font-bold text-indigo-600">{{ $stats['pro_tier'] }}</p>
                    <div class="mt-2 text-sm text-gray-500">Active Pro subscriptions</div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700">Max Tier</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['max_tier'] }}</p>
                    <div class="mt-2 text-sm text-gray-500">Active Max subscriptions</div>
                </div>
            </div>
            
            <!-- Revenue Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700">Monthly Subscription Revenue</h3>
                    <p class="text-3xl font-bold text-green-600">₱{{ number_format($stats['monthly_revenue'], 2) }}</p>
                    <div class="mt-2 text-sm text-gray-500">Revenue from monthly billing cycle</div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-700">Annual Subscription Revenue</h3>
                    <p class="text-3xl font-bold text-green-600">₱{{ number_format($stats['annual_revenue'], 2) }}</p>
                    <div class="mt-2 text-sm text-gray-500">Revenue from annual billing cycle</div>
                </div>
            </div>
            
            <!-- Plan Distribution -->
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Subscription Plan Distribution</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($planStats as $planStat)
                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded">
                            <div>
                                <span class="font-medium">{{ $planStat->name }}</span>
                                <span class="text-sm text-gray-500 ml-2">({{ ucfirst($planStat->for_role) }}s)</span>
                            </div>
                            <div class="flex items-center">
                                <span class="text-lg font-semibold">{{ $planStat->count }}</span>
                                <span class="ml-2 px-2 py-1 text-xs rounded {{ $planStat->name === 'Free' ? 'bg-gray-200 text-gray-800' : ($planStat->name === 'Pro' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $planStat->name }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Filters -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Filter Subscriptions</h3>
                <form action="{{ route('admin.subscriptions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="plan" class="block text-sm font-medium text-gray-700 mb-1">Plan</label>
                        <select id="plan" name="plan" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">All Plans</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ $planId == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} ({{ ucfirst($plan->for_role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">All Statuses</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="canceled" {{ $status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                            <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                        <select id="user_type" name="user_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">All Types</option>
                            <option value="lawyer" {{ $userType === 'lawyer' ? 'selected' : '' }}>Lawyer</option>
                            <option value="law_firm" {{ $userType === 'law_firm' ? 'selected' : '' }}>Law Firm</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Name, email, etc." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <input type="text" id="date_range" name="date_range" value="{{ $dateRange }}" placeholder="YYYY-MM-DD to YYYY-MM-DD" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    
                    <div class="md:col-span-2 flex items-end space-x-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Filter
                        </button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Subscriptions Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Plan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Billing
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dates
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($subscriptions as $subscription)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ $subscription->user->profile_photo_url }}" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
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
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $subscription->user->email }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ ucfirst($subscription->user->role->name) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subscription->plan->name === 'Free' ? 'bg-gray-100 text-gray-800' : ($subscription->plan->name === 'Pro' ? 'bg-indigo-100 text-indigo-800' : 'bg-purple-100 text-purple-800') }}">
                                        {{ $subscription->plan->name }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        For {{ ucfirst($subscription->plan->for_role) }}s
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : ($subscription->status === 'canceled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ ucfirst($subscription->billing_cycle) }}</div>
                                    <div class="text-sm text-gray-500">
                                        @if($subscription->billing_cycle === 'monthly')
                                            ₱{{ number_format($subscription->plan->monthly_price, 2) }}/month
                                        @else
                                            ₱{{ number_format($subscription->plan->annual_price, 2) }}/year
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div><span class="font-medium">Start:</span> {{ $subscription->starts_at->format('M d, Y') }}</div>
                                    @if($subscription->ends_at)
                                        <div><span class="font-medium">End:</span> {{ $subscription->ends_at->format('M d, Y') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                    @if($subscription->status === 'active')
                                        <form action="{{ route('admin.subscriptions.cancel', $subscription) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this subscription?')">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        
                        @if(count($subscriptions) === 0)
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No subscriptions found.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $subscriptions->links() }}
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // For date range picker or any additional JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Add any client-side functionality here
        });
    </script>
    @endpush
</x-layouts.admin> 