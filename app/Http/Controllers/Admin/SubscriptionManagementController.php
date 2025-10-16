<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionManagementController extends Controller
{
    /**
     * Display a listing of the subscriptions.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $planId = $request->input('plan');
        $status = $request->input('status');
        $userType = $request->input('user_type');
        $search = $request->input('search');
        $dateRange = $request->input('date_range');

        // Query subscriptions with all relationships
        $subscriptionsQuery = Subscription::query()
            ->with([
                'user' => function($query) {
                    $query->with(['lawyerProfile', 'lawFirmProfile']);
                }, 
                'plan'
            ])
            ->orderBy('created_at', 'desc');

        // Apply filters if set
        if ($planId) {
            $subscriptionsQuery->where('subscription_plan_id', $planId);
        }

        if ($status) {
            $subscriptionsQuery->where('status', $status);
        }

        if ($userType) {
            $subscriptionsQuery->whereHas('user', function($query) use ($userType) {
                $query->whereHas('role', function($q) use ($userType) {
                    $q->where('name', $userType);
                });
            });
        }

        if ($search) {
            $subscriptionsQuery->whereHas('user', function($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhereHas('lawyerProfile', function($q) use ($search) {
                        $q->where('first_name', 'like', "%$search%")
                          ->orWhere('last_name', 'like', "%$search%");
                    })
                    ->orWhereHas('lawFirmProfile', function($q) use ($search) {
                        $q->where('firm_name', 'like', "%$search%");
                    });
            });
        }

        if ($dateRange) {
            list($startDate, $endDate) = explode(' to ', $dateRange);
            $subscriptionsQuery->whereBetween('created_at', [$startDate, $endDate.' 23:59:59']);
        }

        // Get subscription statistics
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'canceled' => Subscription::where('status', 'canceled')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'pro_tier' => Subscription::whereHas('plan', function($q) {
                $q->where('name', 'Pro');
            })->where('status', 'active')->count(),
            'max_tier' => Subscription::whereHas('plan', function($q) {
                $q->where('name', 'Max');
            })->where('status', 'active')->count(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'annual_revenue' => $this->calculateAnnualRevenue(),
        ];

        // Get all subscription plans for filter dropdown
        $plans = SubscriptionPlan::all();
        
        // Get subscription counts by plan
        $planStats = DB::table('subscriptions')
            ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->where('subscriptions.status', 'active')
            ->select('subscription_plans.name', 'subscription_plans.for_role', DB::raw('count(*) as count'))
            ->groupBy('subscription_plans.name', 'subscription_plans.for_role')
            ->get();

        // Get paginated results
        $subscriptions = $subscriptionsQuery->paginate(15)->withQueryString();

        return view('admin.subscriptions.index', compact(
            'subscriptions', 
            'stats', 
            'plans', 
            'planStats',
            'planId',
            'status',
            'userType',
            'search',
            'dateRange'
        ));
    }

    /**
     * Show the details for a specific subscription.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['user' => function($query) {
            $query->with(['lawyerProfile', 'lawFirmProfile', 'role']);
        }, 'plan']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'canceled',
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription canceled successfully');
    }

    /**
     * Calculate monthly revenue from active subscriptions.
     */
    private function calculateMonthlyRevenue()
    {
        return DB::table('subscriptions')
            ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->where('subscriptions.status', 'active')
            ->where('subscriptions.billing_cycle', 'monthly')
            ->sum('subscription_plans.monthly_price');
    }

    /**
     * Calculate annual revenue from active subscriptions.
     */
    private function calculateAnnualRevenue()
    {
        return DB::table('subscriptions')
            ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->where('subscriptions.status', 'active')
            ->where('subscriptions.billing_cycle', 'annual')
            ->sum('subscription_plans.annual_price');
    }
} 