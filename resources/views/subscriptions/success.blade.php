<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-center">
                    <!-- Success icon -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Subscription Activated!</h2>
                    
                    @if (session('message'))
                        <p class="mb-6 text-gray-600">{{ session('message') }}</p>
                    @else
                        <p class="mb-6 text-gray-600">Your subscription has been successfully activated. Thank you for upgrading your plan!</p>
                    @endif
                    
                    <div class="bg-gray-50 rounded-lg p-6 text-left mb-6">
                        <h3 class="text-lg font-semibold mb-4">What's Next?</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Your profile's visibility has been enhanced with your new subscription plan.</span>
                            </li>
                            @if(Auth::user()->activeSubscription && Auth::user()->activeSubscription->plan->name === 'Pro')
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Your profile now appears with priority in search results with a Pro badge.</span>
                                </li>
                            @endif
                            @if(Auth::user()->activeSubscription && Auth::user()->activeSubscription->plan->name === 'Max')
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Your profile will be featured on the homepage, giving you maximum visibility to potential clients.</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-5 w-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>You'll appear at the top of search results with a premium Max badge.</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                    
                    <div class="mt-8 space-x-4">
                        <a href="{{ route('account.subscription') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Manage Subscription
                        </a>
                        
                        @if(Auth::user()->isLawyer())
                            <a href="{{ route('lawyer.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Go to Dashboard
                            </a>
                        @elseif(Auth::user()->isLawFirm())
                            <a href="{{ route('law-firm.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Go to Dashboard
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 