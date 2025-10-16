<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\UserFeaturedSlot;
use Illuminate\Console\Command;

class RotateFeaturedProfessionals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lexcav:rotate-featured';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate which Max tier subscribers are featured on the homepage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting rotation of featured professionals...');
        
        // Get all active Max tier subscriptions
        $maxSubscriptions = Subscription::where('status', 'active')
            ->whereHas('plan', function($query) {
                $query->where('name', 'Max');
            })
            ->where(function($query) {
                $query->whereDate('ends_at', '>', now())
                      ->orWhereNull('ends_at');
            })
            ->with('user')
            ->get();
            
        $this->info("Found {$maxSubscriptions->count()} Max tier subscribers");
        
        if ($maxSubscriptions->count() <= 4) {
            // If 4 or fewer, all are featured all the time
            $this->info("4 or fewer Max subscribers - all will be featured");
            
            // Make sure all have active featured slots
            foreach ($maxSubscriptions as $subscription) {
                UserFeaturedSlot::updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'user_id' => $subscription->user_id,
                        'feature_starts_at' => now()->startOfDay(),
                        'feature_ends_at' => $subscription->ends_at ?? now()->addYears(10),
                        'is_active' => true,
                        'rotation_order' => 0 // All have same priority
                    ]
                );
            }
            
            $this->info("All Max subscribers are now featured");
            return;
        }
        
        // Create rotation schedule based on fairness algorithm
        $today = now()->format('Y-m-d');
        $dayOfYear = now()->dayOfYear;
        
        // Reset all rotation orders based on today's date
        $this->info("Deactivating all existing featured slots");
        UserFeaturedSlot::whereHas('subscription', function($query) {
            $query->where('status', 'active')
                ->whereHas('plan', function($q) {
                    $q->where('name', 'Max');
                });
        })->update(['is_active' => false]);
        
        // Calculate which 4 should be featured today based on day of year
        $totalCount = $maxSubscriptions->count();
        $startIndex = $dayOfYear % $totalCount;
        
        $this->info("Selecting 4 professionals starting from index $startIndex");
        
        // Activate the next 4 in rotation
        for ($i = 0; $i < min(4, $totalCount); $i++) {
            $index = ($startIndex + $i) % $totalCount;
            $subscription = $maxSubscriptions[$index];
            
            if ($subscription->user) {
                $this->info("Featuring user #{$subscription->user_id} at position " . ($i + 1));
                
                UserFeaturedSlot::updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'user_id' => $subscription->user_id,
                        'feature_starts_at' => now()->startOfDay(),
                        'feature_ends_at' => now()->endOfDay(),
                        'is_active' => true,
                        'rotation_order' => $i
                    ]
                );
            } else {
                $this->error("User not found for subscription #{$subscription->id}");
            }
        }
        
        $this->info('Featured professionals rotation completed');
    }
}
