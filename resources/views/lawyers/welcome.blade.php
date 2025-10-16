<x-app-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Subscription Status Card -->
            <div class="mb-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Your Subscription Plan</h2>
                            @php
                                $activeSubscription = auth()->user()->activeSubscription;
                                $firmSubscription = auth()->user()->belongsToLawFirm() ? auth()->user()->firmSubscription() : null;
                                $displaySubscription = $firmSubscription ?? $activeSubscription;
                            @endphp
                            
                            @if(auth()->user()->belongsToLawFirm())
                                <p class="mt-1 text-sm text-gray-500">You're using your law firm's subscription plan.</p>
                            @endif
                            
                            @if($displaySubscription)
                                <div class="mt-2 flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $displaySubscription->plan->name === 'Free' ? 'bg-gray-100 text-gray-800' : ($displaySubscription->plan->name === 'Pro' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                        {{ $displaySubscription->plan->name }} Plan
                                    </span>
                                    <span class="ml-2 text-sm text-gray-500">
                                        ({{ ucfirst($displaySubscription->billing_cycle) }} billing)
                                    </span>
                                </div>
                            @else
                                <div class="mt-2 flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Free Plan
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        @if((!$displaySubscription || $displaySubscription->plan->name === 'Free') && !auth()->user()->belongsToLawFirm())
                            <div class="flex flex-col items-end">
                                <p class="text-sm text-gray-600 mb-3">Upgrade your plan to reach more potential clients!</p>
                                <a href="{{ route('account.subscription') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Upgrade Plan
                                </a>
                            </div>
                        @elseif($displaySubscription && $displaySubscription->plan->name !== 'Free' && !auth()->user()->belongsToLawFirm())
                            <div>
                                <a href="{{ route('account.subscription') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Manage Subscription
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="text-center">
                <!-- Welcome Text -->
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    Welcome, 
                    @if(auth()->user()->lawFirmLawyer)
                        {{ auth()->user()->lawFirmLawyer->first_name }}
                    @elseif(auth()->user()->lawyerProfile)
                        {{ auth()->user()->lawyerProfile->first_name }}
                    @else
                        {{ auth()->user()->name }}
                    @endif
                </h1>
                <h2 class="text-3xl font-semibold text-gray-900 mb-8">
                    Let's start with
                </h2>
                <h3 class="text-4xl font-bold text-gray-900 mb-12">
                    Optimizing your profile
                </h3>

                <!-- Description -->
                <p class="text-lg text-gray-600 mb-12">
                    It's the best way to get recognized by clients by letting them know you more.
                </p>

                <!-- Optimize Profile Button -->
                <a href="{{ route('profile.optimize') }}" class="!bg-green-600 !text-white inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md shadow-sm hover:!bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                    OPTIMIZE MY PROFILE
                </a>
            </div>
        </div>
    </div>
</x-app-layout> 