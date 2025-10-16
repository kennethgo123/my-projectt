<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the lawyer subscription plans
        $lawyerPlans = [
            [
                'name' => 'Free',
                'description' => 'Basic listing for lawyers',
                'monthly_price' => 0.00,
                'annual_price' => 0.00,
                'features' => [
                    'Basic profile listing',
                    'Standard search results positioning',
                    'Client messaging'
                ],
                'for_role' => 'lawyer'
            ],
            [
                'name' => 'Pro',
                'description' => 'Enhanced visibility with priority in search results',
                'monthly_price' => 1500.00,
                'annual_price' => 15000.00,
                'features' => [
                    'All Free features',
                    'Priority in search results',
                    'Enhanced profile badge',
                    'Advanced analytics dashboard'
                ],
                'for_role' => 'lawyer'
            ],
            [
                'name' => 'Max',
                'description' => 'Maximum visibility with top placement in search and featured on homepage',
                'monthly_price' => 4000.00,
                'annual_price' => 40000.00,
                'features' => [
                    'All Pro features',
                    'Top placement in search results',
                    'Featured on homepage rotation',
                    'Premium profile badge',
                    'Dedicated support',
                    'Monthly performance reports'
                ],
                'for_role' => 'lawyer'
            ],
        ];

        // Create the law firm subscription plans
        $lawFirmPlans = [
            [
                'name' => 'Free',
                'description' => 'Basic listing for law firms',
                'monthly_price' => 0.00,
                'annual_price' => 0.00,
                'features' => [
                    'Basic firm listing',
                    'Standard search results positioning',
                    'Client messaging'
                ],
                'for_role' => 'law_firm'
            ],
            [
                'name' => 'Pro',
                'description' => 'Enhanced visibility with priority in search results',
                'monthly_price' => 4000.00,
                'annual_price' => 40000.00,
                'features' => [
                    'All Free features',
                    'Priority in search results',
                    'Enhanced profile badge',
                    'Advanced analytics dashboard',
                    'Improved firm visibility'
                ],
                'for_role' => 'law_firm'
            ],
            [
                'name' => 'Max',
                'description' => 'Maximum visibility with top placement in search and featured on homepage',
                'monthly_price' => 10000.00,
                'annual_price' => 100000.00,
                'features' => [
                    'All Pro features',
                    'Top placement in search results',
                    'Featured on homepage rotation',
                    'Premium profile badge',
                    'Dedicated support',
                    'Monthly performance reports',
                    'Firm showcase in featured section'
                ],
                'for_role' => 'law_firm'
            ],
        ];

        // Combine all plans and create them
        $allPlans = array_merge($lawyerPlans, $lawFirmPlans);

        foreach ($allPlans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name'], 'for_role' => $plan['for_role']],
                $plan
            );
        }
    }
}
