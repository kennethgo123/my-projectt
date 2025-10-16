@php
// Refactored helper functions for subscription badges with requested design changes
function renderMaxBadge() {
    return '<span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-gradient-to-r from-yellow-300 to-amber-500 text-gray-800 shadow-md border border-yellow-300">
                <svg class="h-4 w-4 mr-1 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="font-bold">Featured Legal Professional</span>
            </span>';
}

function renderProBadge() {
    return '<span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-gradient-to-r from-gray-300 to-gray-500 text-gray-900 shadow-md border border-gray-300">
                <svg class="h-4 w-4 mr-1 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                </svg>
                <span class="font-bold">Certified Legal Professional</span>
            </span>';
}

// Function to get subscription plan name with proper inheritance and priority
function getSubscriptionPlanName($user) {
    if (!$user) {
        return null;
    }
    
    $subscriptionPlan = null;
    
    // Check if user has direct subscription
    if ($user->activeSubscription && $user->activeSubscription->plan) {
        $subscriptionPlan = $user->activeSubscription->plan->name;
    }
    
    // Check if user is a lawyer belonging to a law firm
    if ($user->role->name === 'lawyer' && $user->belongsToLawFirm() && $user->firm) {
        // Get law firm's subscription
        $firmSubscription = $user->firmSubscription();
        if ($firmSubscription && $firmSubscription->plan) {
            $firmPlan = $firmSubscription->plan->name;
            
            // Prioritize higher tier subscription (Max > Pro > Free)
            if ($firmPlan === 'Max' || ($firmPlan === 'Pro' && $subscriptionPlan !== 'Max')) {
                $subscriptionPlan = $firmPlan;
            }
        }
    }
    
    return $subscriptionPlan;
}

// Function to render subscription badge based on subscription type
function renderSubscriptionBadge($user) {
    if (!$user) {
        return '';
    }
    
    $planName = getSubscriptionPlanName($user);
    
    if ($planName === 'Max') {
        return renderMaxBadge();
    } elseif ($planName === 'Pro') {
        return renderProBadge();
    }
    
    return '';
}
@endphp 