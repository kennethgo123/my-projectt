<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LawFirmLawyer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateLawFirmLawyerUserIds extends Command
{
    protected $signature = 'lexcav:update-lawyer-user-ids';
    protected $description = 'Update user_id in law_firm_lawyers table for existing records';

    public function handle()
    {
        $this->info('Starting to update law_firm_lawyers user_id column...');
        
        // Get all law firm lawyers without user_id
        $lawFirmLawyers = LawFirmLawyer::whereNull('user_id')->get();
        $this->info("Found {$lawFirmLawyers->count()} law firm lawyers without user_id");
        
        $updated = 0;
        
        foreach ($lawFirmLawyers as $lawyer) {
            // Get full name
            $fullName = trim($lawyer->first_name . ' ' . ($lawyer->middle_name ? $lawyer->middle_name . ' ' : '') . $lawyer->last_name);
            
            // Find users with matching name
            $matchingUsers = User::where('name', 'like', "%{$fullName}%")
                ->where(function($query) {
                    $query->whereHas('role', function($q) {
                        $q->where('name', 'lawyer');
                    });
                })
                ->get();
                
            if ($matchingUsers->count() === 1) {
                // If exactly one match, update the record
                $lawyer->user_id = $matchingUsers->first()->id;
                $lawyer->save();
                $updated++;
                $this->info("Updated user_id for lawyer {$fullName} (ID: {$lawyer->id}) to user ID: {$lawyer->user_id}");
            } elseif ($matchingUsers->count() > 1) {
                $this->warn("Multiple matches found for {$fullName}, please update manually");
                foreach ($matchingUsers as $user) {
                    $this->line(" - User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}");
                }
            } else {
                $this->warn("No matching user found for {$fullName}");
            }
        }
        
        $this->info("Updated user_id for {$updated} law firm lawyers");
        
        // Now show which lawyers are still without user_id
        $remaining = LawFirmLawyer::whereNull('user_id')->count();
        if ($remaining > 0) {
            $this->warn("{$remaining} law firm lawyers still have null user_id");
        } else {
            $this->info("All law firm lawyers have been linked to users");
        }
        
        return Command::SUCCESS;
    }
} 