<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Law Firm Subscription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Your Law Firm\'s Subscription') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('As a lawyer under ') }} <span class="font-semibold">{{ $firmName }}</span>, {{ __('the firm\'s subscription applies to your account.') }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        @if($firmSubscription)
                            <div class="mb-4 flex justify-between items-center">
                                <div class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium">
                                    {{ __('Active Subscription') }}
                                </div>
                                <div class="inline-flex items-center px-3 py-1 
                                    @if($firmSubscription->plan->name == 'Max') 
                                        bg-purple-100 text-purple-800 
                                    @elseif($firmSubscription->plan->name == 'Pro') 
                                        bg-blue-100 text-blue-800 
                                    @else 
                                        bg-gray-100 text-gray-800 
                                    @endif 
                                    rounded-full text-sm font-semibold">
                                    Current: {{ $firmSubscription->plan->name }} Plan
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Plan:') }}</p>
                                    <p class="font-semibold">{{ $firmSubscription->plan->name }} Plan</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Billing Cycle:') }}</p>
                                    <p class="font-semibold">{{ ucfirst($firmSubscription->billing_cycle) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Started On:') }}</p>
                                    <p class="font-semibold">{{ $firmSubscription->starts_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Expires On:') }}</p>
                                    <p class="font-semibold">
                                        {{ $firmSubscription->ends_at ? $firmSubscription->ends_at->format('M d, Y') : 'Never' }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <h4 class="text-md font-medium text-gray-900 mb-2">{{ __('Plan Features:') }}</h4>
                                <ul class="space-y-2">
                                    @php
                                        $features = is_array($firmSubscription->plan->features) 
                                            ? $firmSubscription->plan->features 
                                            : json_decode($firmSubscription->plan->features) ?: [];
                                    @endphp
                                    
                                    @foreach($features as $feature)
                                        <li class="flex items-start">
                                            <svg class="h-5 w-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="ml-2">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 20h.01M12 4h.01M7 14h.01M17 14h.01M12 14h.01M9 18h.01M15 18h.01M7 10h.01M17 10h.01M12 10h.01"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No Active Subscription') }}</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('Your law firm does not have an active subscription.') }}
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    {{ __('Contact your law firm administrator for any subscription-related inquiries.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 