<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First make sure the photo_path column exists
        if (!Schema::hasColumn('lawyer_profiles', 'photo_path')) {
            Schema::table('lawyer_profiles', function (Blueprint $table) {
                $table->string('photo_path')->nullable()->after('linkedin');
            });
        }
        
        // Now transfer data from profile_photo_path to photo_path if needed
        if (Schema::hasColumn('lawyer_profiles', 'profile_photo_path')) {
            $profiles = DB::table('lawyer_profiles')
                ->whereNull('photo_path')
                ->whereNotNull('profile_photo_path')
                ->get();
            
            foreach ($profiles as $profile) {
                DB::table('lawyer_profiles')
                    ->where('id', $profile->id)
                    ->update(['photo_path' => $profile->profile_photo_path]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's ensuring the column exists
    }
};
