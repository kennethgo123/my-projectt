<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight font-raleway">
            {{ __('Account Under Review') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-6">
                        <svg class="w-8 h-8 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-800 mb-2 font-raleway">Your Profile is Pending Approval</h2>
                    
                    <p class="text-gray-600 mb-6 font-open-sans">
                        Thank you for completing your profile! Your information is currently being reviewed by our administration team.
                    </p>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 text-left">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 font-open-sans">
                                    @if(auth()->user()->isLawyer() || auth()->user()->isLawFirm())
                                        We're currently verifying your professional credentials and information. This process typically takes 1-2 business days. 
                                        You'll receive an email notification once your account has been approved.
                                    @else
                                        We're currently verifying your account information. This process typically takes 1-2 business days. 
                                        You'll receive an email notification and a system notification once your account has been approved.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-3 font-raleway">What happens next?</h3>
                        <ul class="list-disc list-inside text-left space-y-2 text-gray-600 font-open-sans">
                            <li>Our admin team will review your submitted information and documents</li>
                            <li>Once approved, you'll receive an email notification and an in-app notification</li>
                            <li>You'll then have full access to all platform features</li>
                            @if(auth()->user()->isLawyer() || auth()->user()->isLawFirm())
                                <li>Your profile will be visible to potential clients seeking legal assistance</li>
                                <li>Clients will be able to request consultations with you</li>
                            @endif
                            @if(auth()->user()->isClient())
                                <li>You'll be able to browse and contact lawyers for consultations</li>
                                <li>You'll receive notifications when lawyers respond to your consultation requests</li>
                            @endif
                        </ul>
                    </div>
                    
                    <div class="mt-8">
                        <p class="text-sm text-gray-500 font-open-sans">
                            If you have any questions, please contact our support team at <a href="mailto:support@lexcav.com" class="text-blue-600 hover:text-blue-800">support@lexcav.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 