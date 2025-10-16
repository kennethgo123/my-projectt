<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\UserFeaturedSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    /**
     * Display subscription plans
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role->name;
        
        // Show only plans available for the user's role
        $plans = SubscriptionPlan::where('for_role', $userRole)->get();
        $activeSubscription = $user->activeSubscription;
        
        // Ensure features is properly cast to an array
        $plans->each(function($plan) {
            if (is_string($plan->features)) {
                $plan->features = json_decode($plan->features) ?: [];
            }
        });
        
        return view('subscriptions.index', [
            'plans' => $plans,
            'activeSubscription' => $activeSubscription,
            'userRole' => $userRole
        ]);
    }
    
    /**
     * Show the checkout page for a specific plan
     */
    public function checkout(SubscriptionPlan $plan)
    {
        // Make sure the user is eligible for this plan (role check)
        $user = Auth::user();
        if ($plan->for_role !== $user->role->name) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'This subscription plan is not available for your account type.');
        }
        
        return view('subscriptions.checkout', [
            'plan' => $plan,
        ]);
    }
    
    /**
     * Create a PayMongo payment intent
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,annual',
            'payment_method' => 'required|in:card,gcash',
        ]);
        
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $user = Auth::user();
        
        // Calculate amount based on billing cycle
        $amount = $request->billing_cycle === 'monthly' 
            ? $plan->monthly_price 
            : $plan->annual_price;
        
        // Amount needs to be in smallest currency unit (centavos)
        $amountInCents = $amount * 100;
        
        // Store subscription data in session
        session([
            'subscription_data' => [
                'plan_id' => $plan->id,
                'billing_cycle' => $request->billing_cycle,
            ]
        ]);
        
        // For GCash, create a source instead of payment intent
        if ($request->payment_method === 'gcash') {
            $client = new \GuzzleHttp\Client();
            
            try {
                $response = $client->post(
                    'https://api.paymongo.com/v1/sources',
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key')),
                        ],
                        'json' => [
                            'data' => [
                                'attributes' => [
                                    'amount' => $amountInCents,
                                    'redirect' => [
                                        'success' => route('subscriptions.success'),
                                        'failed' => route('subscriptions.cancel')
                                    ],
                                    'type' => 'gcash',
                                    'currency' => 'PHP',
                                    'billing' => [
                                        'name' => $this->getUserFullName($user),
                                        'email' => $user->email,
                                    ]
                                ]
                            ]
                        ]
                    ]
                );

                $source = json_decode($response->getBody()->getContents(), true);
                
                // Store source ID in session
                session(['payment_source_id' => $source['data']['id']]);
                
                return response()->json([
                    'success' => true,
                    'checkout_url' => $source['data']['attributes']['redirect']['checkout_url']
                ]);
                
            } catch (\Exception $e) {
                \Log::error('GCash source creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
        
        // For card payments, create a payment intent
        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->post(
                'https://api.paymongo.com/v1/payment_intents',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key')),
                    ],
                    'json' => [
                        'data' => [
                            'attributes' => [
                                'amount' => $amountInCents,
                                'payment_method_allowed' => ['card'],
                                'payment_method_options' => [
                                    'card' => ['request_three_d_secure' => 'automatic']
                                ],
                                'currency' => 'PHP',
                                'description' => $plan->name . ' Plan - ' . ucfirst($request->billing_cycle),
                                'statement_descriptor' => 'LEXCAV Subscription',
                                'metadata' => [
                                    'user_id' => (string)$user->id,
                                    'plan_id' => (string)$plan->id,
                                    'billing_cycle' => $request->billing_cycle,
                                    'plan_name' => $plan->name
                                ]
                            ]
                        ]
                    ]
                ]
            );

            $paymentIntent = json_decode($response->getBody()->getContents(), true);
            
            // Store the payment intent ID in session
            session(['payment_intent_id' => $paymentIntent['data']['id']]);
            
            return response()->json([
                'success' => true,
                'client_key' => $paymentIntent['data']['attributes']['client_key'],
                'payment_intent_id' => $paymentIntent['data']['id']
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Payment intent creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Process card payment
     */
    public function processCardPayment(Request $request)
    {
        $paymentIntentId = $request->input('payment_intent_id') ?? session('payment_intent_id');
        $paymentMethodId = $request->input('payment_method_id');
        $subscriptionData = session('subscription_data');
        
        if (!$paymentIntentId || !$paymentMethodId || !$subscriptionData) {
            return response()->json([
                'success' => false,
                'message' => 'Missing payment data. Please try again.'
            ], 400);
        }
        
        // Attach payment method to payment intent
        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->post(
                "https://api.paymongo.com/v1/payment_intents/{$paymentIntentId}/attach",
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key')),
                    ],
                    'json' => [
                        'data' => [
                            'attributes' => [
                                'payment_method' => $paymentMethodId,
                                'return_url' => route('subscriptions.success')
                            ]
                        ]
                    ]
                ]
            );

            $result = json_decode($response->getBody()->getContents(), true);
            $status = $result['data']['attributes']['status'] ?? '';
            
            if ($status !== 'succeeded' && $status !== 'awaiting_next_action') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment was not successful. Please try again.'
                ], 400);
            }
            
            // If payment requires additional action (3D Secure)
            if ($status === 'awaiting_next_action') {
                $nextAction = $result['data']['attributes']['next_action'] ?? null;
                if ($nextAction && isset($nextAction['redirect']['url'])) {
                    return response()->json([
                        'success' => true,
                        'requires_action' => true,
                        'redirect_url' => $nextAction['redirect']['url']
                    ]);
                }
            }
            
            // If we're here, the payment was successful
            // Create subscription
            if ($this->createSubscriptionFromSession($paymentIntentId)) {
                $response = response()->json([
                    'success' => true,
                    'redirect' => route('account.subscription')
                ]);
                
                // Clear payment session data only after preparing the response
                $this->clearPaymentSessionData();
                
                return $response;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription. Please contact support.'
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear any cached subscription data for a user
     */
    private function clearSubscriptionCache($userId)
    {
        // Clear any cached subscription data
        \Cache::forget('user_subscription_' . $userId);
        \Cache::forget('user_active_subscription_' . $userId);
    }

    /**
     * Create a subscription from session data
     */
    private function createSubscriptionFromSession($paymentId = null)
    {
        $subscriptionData = session('subscription_data');
        
        if (!$subscriptionData) {
            \Log::warning('createSubscriptionFromSession called with no subscription_data in session.');
            return false;
        }
        
        $plan = SubscriptionPlan::findOrFail($subscriptionData['plan_id']);
        $user = Auth::user();
        $billingCycle = $subscriptionData['billing_cycle'];
        
        // Calculate end date based on billing cycle
        $endsAt = $billingCycle === 'monthly' 
            ? now()->addMonth() 
            : now()->addYear();

        // Deactivate any existing active subscriptions for this user
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'inactive', 'ends_at' => now()->subSecond()]);
        
        // Create the subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'starts_at' => now(),
            'ends_at' => $endsAt,
            'status' => 'active',
            'payment_method' => 'paymongo',
            'payment_id' => $paymentId ?? ('source_payment_'.Str::random(10)),
        ]);
        
        // Manage UserFeaturedSlots
        // First, deactivate any existing active featured slots for this user
        UserFeaturedSlot::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false, 'feature_ends_at' => now()->subSecond()]);

        // For Max plan subscribers, create a featured slot
        if ($plan->name === 'Max') {
            UserFeaturedSlot::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'feature_starts_at' => now(),
                'feature_ends_at' => $endsAt,
                'is_active' => true,
                'rotation_order' => (UserFeaturedSlot::max('rotation_order') ?? 0) + 1,
            ]);
        }
        
        // Clear subscription cache
        $this->clearSubscriptionCache($user->id);
        
        // Return success
        return true;
    }
    
    /**
     * Process subscription after payment confirmation
     */
    public function processSubscription(Request $request)
    {
        $paymentIntentId = session('payment_intent_id');
        $paymentSourceId = session('payment_source_id');
        $subscriptionDataExists = session()->has('subscription_data');

        try {
            // If we have a source ID (GCash)
            if ($paymentSourceId) {
                $client = new \GuzzleHttp\Client();
                $response = $client->get(
                    "https://api.paymongo.com/v1/sources/{$paymentSourceId}",
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key')),
                        ]
                    ]
                );
                $source = json_decode($response->getBody()->getContents(), true);
                $status = $source['data']['attributes']['status'] ?? '';

                if ($status === 'chargeable' || $status === 'paid') {
                    if (!$subscriptionDataExists) {
                        \Log::error('GCash source successful but subscription_data missing from session.', ['source_id' => $paymentSourceId]);
                        $this->clearPaymentSessionData();
                        return redirect()->route('subscriptions.index')->with('error', 'Subscription data was missing after payment. Please contact support or try again.');
                    }
                    if ($this->createSubscriptionFromSession($paymentSourceId)) {
                        $this->clearPaymentSessionData();
                        return redirect()->route('account.subscription')->with('message', 'Your subscription has been activated successfully!');
                    } else {
                        \Log::error('GCash source successful but createSubscriptionFromSession failed (e.g. session data became invalid)._log', ['source_id' => $paymentSourceId]);
                        $this->clearPaymentSessionData();
                        return redirect()->route('subscriptions.index')->with('error', 'Failed to finalize subscription after payment. Please contact support.');
                    }
                } else {
                    \Log::warning('GCash source not chargeable or paid.', ['source_id' => $paymentSourceId, 'status' => $status]);
                    $this->clearPaymentSessionData();
                    return redirect()->route('subscriptions.index')->with('error', 'Your GCash payment was not completed successfully. Please try again.');
                }
            }
            
            // If we have a payment intent ID (Card 3DS callback)
            if ($paymentIntentId) {
                $client = new \GuzzleHttp\Client();
                $response = $client->get(
                    "https://api.paymongo.com/v1/payment_intents/{$paymentIntentId}",
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key')),
                        ]
                    ]
                );
                $paymentIntent = json_decode($response->getBody()->getContents(), true);
                $status = $paymentIntent['data']['attributes']['status'] ?? '';

                if ($status === 'succeeded') {
                    if (!$subscriptionDataExists) {
                        \Log::error('Card payment intent successful but subscription_data missing.', ['intent_id' => $paymentIntentId]);
                        $this->clearPaymentSessionData();
                        return redirect()->route('subscriptions.index')->with('error', 'Subscription data was missing after payment. Please contact support or try again.');
                    }
                    if ($this->createSubscriptionFromSession($paymentIntentId)) {
                        $this->clearPaymentSessionData();
                        return redirect()->route('account.subscription')->with('message', 'Your subscription has been activated successfully!');
                    } else {
                        \Log::error('Card payment intent successful but createSubscriptionFromSession failed.', ['intent_id' => $paymentIntentId]);
                        $this->clearPaymentSessionData();
                        return redirect()->route('subscriptions.index')->with('error', 'Failed to finalize subscription after payment. Please contact support.');
                    }
                } else {
                    \Log::warning('Card payment intent not succeeded.', ['intent_id' => $paymentIntentId, 'status' => $status]);
                    $this->clearPaymentSessionData();
                    return redirect()->route('subscriptions.index')->with('error', 'Your card payment was not completed successfully. Please try again.');
                }
            }

            // No relevant session ID found or subscription_data missing at the start
            \Log::warning('processSubscription called with no payment_source_id or payment_intent_id, or subscription_data was missing.', [
                'has_source_id' => !empty($paymentSourceId),
                'has_intent_id' => !empty($paymentIntentId),
                'has_subscription_data' => $subscriptionDataExists
            ]);
            $this->clearPaymentSessionData();
            return redirect()->route('subscriptions.index')->with('error', 'Payment session expired or critical data missing. Please try choosing your plan again.');

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            \Log::error('PayMongo API client error in processSubscription', [
                'error' => $e->getMessage(), 
                'response_body' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : 'No response body',
                'trace' => $e->getTraceAsString()
            ]);
            $this->clearPaymentSessionData();
            return redirect()->route('subscriptions.index')->with('error', 'There was an issue communicating with the payment gateway. Please try again or contact support.');
        } catch (\Exception $e) {
            \Log::error('General error in processSubscription', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->clearPaymentSessionData();
            return redirect()->route('subscriptions.index')->with('error', 'An unexpected error occurred while processing your subscription. Please contact support.');
        }
    }
    
    private function clearPaymentSessionData()
    {
        session()->forget(['payment_intent_id', 'payment_source_id', 'subscription_data']);
    }
    
    /**
     * Process subscription payment and create subscription record (Direct method for testing)
     */
    public function process(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);
        
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $user = Auth::user();
        
        // Check if the user is eligible for this plan (role check)
        if ($plan->for_role !== $user->role->name) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'This subscription plan is not available for your account type.');
        }
        
        // Calculate end date based on billing cycle
        $endsAt = $request->billing_cycle === 'monthly' 
            ? now()->addMonth() 
            : now()->addYear();
        
        // Create the subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => $request->billing_cycle,
            'starts_at' => now(),
            'ends_at' => $endsAt,
            'status' => 'active',
            'payment_method' => 'paymongo',
            'payment_id' => 'test_payment_'.Str::random(10),
        ]);
        
        // For Max plan subscribers, create a featured slot
        if ($plan->name === 'Max') {
            UserFeaturedSlot::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'feature_starts_at' => now(),
                'feature_ends_at' => $endsAt,
                'is_active' => true,
                'rotation_order' => UserFeaturedSlot::max('rotation_order') + 1,
            ]);
        }
        
        return redirect()
            ->route('subscriptions.success')
            ->with('message', 'Your subscription has been activated successfully!');
    }
    
    /**
     * Show success page after subscription
     */
    public function success(Request $request)
    {
        // Delegate processing to processSubscription and return its redirect response
        return $this->processSubscription($request);
    }
    
    /**
     * Cancel a subscription - THIS IS FOR USER-INITIATED CANCELLATION OF AN ACTIVE SUB.
     * Note: PayMongo's 'failed' redirect for GCash might also point to a route handled by this or a similarly named method.
     * This specific method's logic might need review if it's also handling payment failures from PayMongo.
     */
    public function cancel()
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription;
        
        if ($subscription) {
            $subscription->status = 'canceled';
            $subscription->save();
            
            // Deactivate any featured slots if this was a Max plan
            UserFeaturedSlot::where('subscription_id', $subscription->id)
                ->update(['is_active' => false]);
            
            return back()->with('message', 'Your subscription has been canceled.');
        }
        
        return back()->with('error', 'No active subscription found.');
    }
    
    /**
     * Account management subscription page
     */
    public function accountSubscription()
    {
        $user = Auth::user();
        $userRole = $user->role->name;
        
        // For lawyers under a law firm, redirect or show the firm's subscription
        if ($user->belongsToLawFirm()) {
            $firmSubscription = $user->firmSubscription();
            $firmName = $user->firm->lawFirmProfile ? $user->firm->lawFirmProfile->firm_name : $user->firm->name;
            
            return view('account.firm-subscription', [
                'firmSubscription' => $firmSubscription,
                'firmName' => $firmName,
                'userRole' => $userRole
            ]);
        }
        
        // Show only plans available for the user's role
        $plans = SubscriptionPlan::where('for_role', $userRole)->get();
        $activeSubscription = $user->activeSubscription;
        
        // Ensure features is properly cast to an array
        $plans->each(function($plan) {
            if (is_string($plan->features)) {
                $plan->features = json_decode($plan->features) ?: [];
            }
        });
        
        return view('account.subscription', [
            'plans' => $plans,
            'activeSubscription' => $activeSubscription,
            'userRole' => $userRole
        ]);
    }

    /**
     * Get the user's full name based on their role and profile type
     */
    private function getUserFullName($user)
    {
        // For client users
        if ($user->isClient() && $user->clientProfile) {
            return trim($user->clientProfile->first_name . ' ' . $user->clientProfile->last_name);
        }
        
        // For lawyer users (with firm)
        if ($user->isLawyer() && $user->lawFirmLawyer) {
            return trim($user->lawFirmLawyer->first_name . ' ' . $user->lawFirmLawyer->last_name);
        }
        
        // For lawyer users (independent)
        if ($user->isLawyer() && $user->lawyerProfile) {
            return trim($user->lawyerProfile->first_name . ' ' . $user->lawyerProfile->last_name);
        }
        
        // For law firm users
        if ($user->isLawFirm() && $user->lawFirmProfile) {
            return $user->lawFirmProfile->firm_name;
        }
        
        // Fallback to user name
        return $user->name ?: 'LexCav User';
    }
}
