<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-800">Subscription Management</h2>
                    
                    @if($activeSubscription)
                        <div class="flex items-center">
                            <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-lg flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Current plan: <span class="font-semibold">{{ $activeSubscription->plan->name }}</span></span>
                            </div>
                        </div>
                    @endif
                </div>
                
                @if (session('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('message') }}
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        {{ session('error') }}
                    </div>
                @endif
                
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Your Subscription Details</h3>
                    
                    @if($activeSubscription)
                        <div class="bg-gray-50 rounded-lg p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-600">Plan:</p>
                                    <p class="font-semibold">{{ $activeSubscription->plan->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Billing Cycle:</p>
                                    <p class="font-semibold">{{ ucfirst($activeSubscription->billing_cycle) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Started On:</p>
                                    <p class="font-semibold">{{ $activeSubscription->starts_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Expires On:</p>
                                    <p class="font-semibold">
                                        {{ $activeSubscription->ends_at ? $activeSubscription->ends_at->format('M d, Y') : 'Never' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Status:</p>
                                    <p class="font-semibold capitalize">{{ $activeSubscription->status }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Amount:</p>
                                    <p class="font-semibold">
                                        ₱{{ number_format($activeSubscription->billing_cycle === 'monthly' ? $activeSubscription->plan->monthly_price : $activeSubscription->plan->annual_price, 2) }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($activeSubscription->status === 'active')
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <form action="{{ route('subscriptions.cancel') }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription? This will remove any premium benefits immediately.')">
                                        @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded">
                                            Cancel Subscription
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-yellow-800">
                            <div class="flex items-center">
                                <svg class="h-6 w-6 text-yellow-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>You don't have an active subscription. Choose a plan below to enhance your visibility.</span>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Available Plans for {{ ucfirst($userRole) }}s</h3>
                    
                    @if($activeSubscription)
                    <div class="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded">
                        Your current plan: <span class="font-semibold">{{ $activeSubscription->plan->name }}</span>
                    </div>
                    @else
                    <div class="text-sm bg-gray-100 text-gray-800 px-3 py-1 rounded">
                        Your current plan: <span class="font-semibold">Free</span>
                    </div>
                    @endif
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    @foreach($plans as $plan)
                    <div class="bg-white rounded-lg shadow-md p-6 relative 
                        @if($activeSubscription && $activeSubscription->plan_id == $plan->id)
                            border-2 border-green-500 ring-2 ring-green-500 ring-opacity-50
                        @elseif(!$activeSubscription && $plan->name == 'Free')
                            border-2 border-gray-300
                        @else
                            border border-gray-200
                        @endif
                    ">
                        @if($activeSubscription && $activeSubscription->plan_id == $plan->id)
                            <div class="absolute top-0 right-0 bg-green-500 text-white px-2 py-1 rounded-bl text-xs font-semibold">
                                Current Plan
                            </div>
                        @elseif(!$activeSubscription && $plan->name == 'Free')
                            <div class="absolute top-0 right-0 bg-gray-500 text-white px-2 py-1 rounded-bl text-xs font-semibold">
                                Current Plan
                            </div>
                        @endif
                        
                        <h3 class="text-xl font-semibold mb-4">{{ $plan->name }}</h3>
                        
                        @if($plan->name != 'Free')
                            <p class="text-3xl font-bold mb-2">₱{{ number_format($plan->monthly_price, 0) }}<span class="text-sm text-gray-500">/month</span></p>
                            <p class="text-sm text-gray-600 mb-6">or ₱{{ number_format($plan->annual_price, 0) }}/year (save {{ round((1 - $plan->annual_price / ($plan->monthly_price * 12)) * 100) }}%)</p>
                        @else
                            <p class="text-3xl font-bold mb-2">Free</p>
                            <p class="text-sm text-gray-600 mb-6">Basic features</p>
                        @endif
                        
                        <div class="border-t border-gray-100 my-4 pt-4">
                            <h4 class="font-medium text-gray-700 mb-2">Features:</h4>
                            <ul class="space-y-2">
                                @php
                                    $features = is_string($plan->features) ? json_decode($plan->features) : $plan->features;
                                    if (!is_array($features) && !is_object($features)) {
                                        $features = [];
                                    }
                                @endphp
                                
                                @foreach($features as $feature)
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-600">{{ $feature }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <div class="mt-6">
                            @if($activeSubscription && $activeSubscription->plan_id == $plan->id)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">
                                        Expires: {{ $activeSubscription->ends_at ? $activeSubscription->ends_at->format('M d, Y') : 'Never' }}
                                    </span>
                                </div>
                            @elseif($plan->name != 'Free' && (!$activeSubscription || $activeSubscription->plan->name == 'Free' || ($plan->name == 'Max' && $activeSubscription->plan->name == 'Pro')))
                                <a href="{{ route('subscriptions.checkout', $plan) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md">
                                    Upgrade to {{ $plan->name }}
                                </a>
                            @elseif(!$activeSubscription && $plan->name == 'Free')
                                <button disabled class="w-full bg-gray-100 text-gray-500 py-2 px-4 rounded-md cursor-not-allowed">
                                    Current Tier
                                </button>
                            @elseif($activeSubscription && $plan->name == 'Free')
                                <button disabled class="w-full bg-gray-100 text-gray-500 py-2 px-4 rounded-md cursor-not-allowed">
                                    Basic Plan
                                </button>
                            @elseif($activeSubscription && (($plan->name == 'Pro' && $activeSubscription->plan->name == 'Max')))
                                <button disabled class="w-full bg-gray-100 text-gray-500 py-2 px-4 rounded-md cursor-not-allowed">
                                    Current plan is higher tier
                                </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="bg-blue-50 rounded-lg p-6 text-blue-800">
                    <h4 class="font-semibold mb-2">Why Upgrade Your Subscription?</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Pro and Max plans increase your visibility to potential clients.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Get prioritized in search results with Pro, or top placement with Max.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Max subscribers are featured on the homepage, significantly increasing client exposure.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 