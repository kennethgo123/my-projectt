<?php

/**
 * This script updates all existing consultation request notifications
 * to use the client's first and last name instead of just "User"
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Notification;
use App\Models\Consultation;
use App\Models\User;

echo "Starting notification fix...\n";

// Get all consultation request notifications
$notifications = Notification::where('type', 'consultation_request')
    ->where('content', 'LIKE', '%User has requested a consultation%')
    ->get();

echo "Found " . $notifications->count() . " notifications to update.\n";

foreach ($notifications as $notification) {
    // Find the lawyer who received this notification
    $lawyer = User::find($notification->user_id);
    
    if (!$lawyer) {
        echo "Lawyer not found for notification ID {$notification->id}, skipping.\n";
        continue;
    }
    
    // Find a consultation where this lawyer was requested
    $consultation = Consultation::where('lawyer_id', $lawyer->id)
        ->orderBy('created_at', 'desc')
        ->first();
    
    if (!$consultation) {
        echo "No consultation found for lawyer ID {$lawyer->id}, skipping notification ID {$notification->id}.\n";
        continue;
    }
    
    // Get the client
    $client = User::find($consultation->client_id);
    
    if (!$client) {
        echo "Client not found for consultation ID {$consultation->id}, skipping notification ID {$notification->id}.\n";
        continue;
    }
    
    // Get client profile and name
    $clientProfile = $client->clientProfile;
    
    if ($clientProfile && !empty($clientProfile->first_name) && !empty($clientProfile->last_name)) {
        $clientName = $clientProfile->first_name . ' ' . $clientProfile->last_name;
    } else if ($clientProfile && !empty($clientProfile->first_name)) {
        $clientName = $clientProfile->first_name;
    } else {
        $clientName = $client->name;
    }
    
    // Update the notification content
    $notification->content = "{$clientName} has requested a consultation with you.";
    $notification->save();
    
    echo "Updated notification ID {$notification->id} to use name: {$clientName}\n";
}

echo "Finished updating notifications.\n"; 