<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First check if lawyer_profiles table exists
        if (!Schema::hasTable('lawyer_profiles')) {
            return;
        }

        // Check if the photo_path column already exists
        if (!Schema::hasColumn('lawyer_profiles', 'photo_path')) {
            Schema::table('lawyer_profiles', function (Blueprint $table) {
                $table->string('photo_path')->nullable();
            });
            Log::info('Added photo_path column to lawyer_profiles table');
        }
        
        // Now check if profile_photo_path exists and transfer data
        if (Schema::hasColumn('lawyer_profiles', 'profile_photo_path')) {
            // Migrate data from profile_photo_path to photo_path where photo_path is null
            $profiles = DB::table('lawyer_profiles')
                ->whereNull('photo_path')
                ->whereNotNull('profile_photo_path')
                ->get();
            
            foreach ($profiles as $profile) {
                DB::table('lawyer_profiles')
                    ->where('id', $profile->id)
                    ->update(['photo_path' => $profile->profile_photo_path]);
            }
            
            Log::info('Migrated ' . count($profiles) . ' records from profile_photo_path to photo_path');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration ensures data consistency, no need to reverse
    }
};
