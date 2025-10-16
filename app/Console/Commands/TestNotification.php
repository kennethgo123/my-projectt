<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AppNotification;
use App\Models\User;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test creating a notification to verify the fix';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1; // Default to user ID 1 if not provided
        
        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }
        
        try {
            $notification = AppNotification::create([
                'user_id' => $userId,
                'type' => 'test_notification',
                'title' => 'Test Notification',
                'content' => 'This is a test notification to verify the fix.',
                'link' => '/'
            ]);
            
            $this->info("Test notification created successfully with ID: {$notification->id}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to create notification: {$e->getMessage()}");
            $this->line("Stack trace: {$e->getTraceAsString()}");
            return 1;
        }
    }
}
