<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Subscription Plans for {{ ucfirst($userRole) }}s</h2>
                
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
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($plans as $plan)
                    <div class="bg-white rounded-lg shadow-md p-6 relative {{ $activeSubscription && $activeSubscription->plan_id == $plan->id ? 'border-2 border-green-500' : 'border border-gray-200' }}">
                        @if($activeSubscription && $activeSubscription->plan_id == $plan->id)
                            <div class="absolute top-0 right-0 bg-green-500 text-white px-2 py-1 rounded-bl text-xs">
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
                                    
                                    <form action="{{ route('subscriptions.cancel') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                            Cancel subscription
                                        </button>
                                    </form>
                                </div>
                            @elseif($plan->name != 'Free')
                                <a href="{{ route('subscriptions.checkout', $plan) }}" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-md">
                                    Upgrade to {{ $plan->name }}
                                </a>
                            @else
                                <button disabled class="w-full bg-gray-100 text-gray-500 py-2 px-4 rounded-md cursor-not-allowed">
                                    Current Tier
                                </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-8 bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Why Upgrade Your Subscription?</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Pro and Max plans increase your visibility to potential clients.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Get prioritized in search results with Pro, or top placement with Max.</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="h-5 w-5 text-blue-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Max tier subscribers are featured on the homepage rotation, significantly increasing client exposure.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 