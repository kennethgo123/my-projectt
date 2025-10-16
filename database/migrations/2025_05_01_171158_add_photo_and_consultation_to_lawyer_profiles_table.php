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
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            // If we have both photo_path and profile_photo_path, migrate data from profile_photo_path to photo_path
            if (Schema::hasColumn('lawyer_profiles', 'profile_photo_path') && Schema::hasColumn('lawyer_profiles', 'photo_path')) {
                // Copy data from profile_photo_path to photo_path where photo_path is null
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
            
            // Add consultation type fields if they don't exist
            if (!Schema::hasColumn('lawyer_profiles', 'offers_online_consultation')) {
                $table->boolean('offers_online_consultation')->default(false);
            }
            
            if (!Schema::hasColumn('lawyer_profiles', 'offers_inhouse_consultation')) {
                $table->boolean('offers_inhouse_consultation')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('lawyer_profiles', 'offers_online_consultation')) {
                $table->dropColumn('offers_online_consultation');
            }
            
            if (Schema::hasColumn('lawyer_profiles', 'offers_inhouse_consultation')) {
                $table->dropColumn('offers_inhouse_consultation');
            }
        });
    }
};
