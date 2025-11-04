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
        // Update lawyer_profiles table
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            // Change city from ENUM to VARCHAR to support full city names with special characters
            // First, we need to modify the column structure
        });
        
        // Use raw SQL to change ENUM to VARCHAR since Laravel doesn't support changing ENUM directly
        DB::statement("ALTER TABLE `lawyer_profiles` MODIFY COLUMN `city` VARCHAR(255) NULL");
        
        // Add barangay column
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->string('barangay', 255)->nullable()->after('city');
        });
        
        // Update law_firm_profiles table
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            // Will modify city column and add barangay
        });
        
        DB::statement("ALTER TABLE `law_firm_profiles` MODIFY COLUMN `city` VARCHAR(255) NULL");
        
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->string('barangay', 255)->nullable()->after('city');
        });
        
        // Update law_firm_lawyers table if it has city column
        if (Schema::hasColumn('law_firm_lawyers', 'city')) {
            Schema::table('law_firm_lawyers', function (Blueprint $table) {
                // Will modify city column and add barangay
            });
            
            DB::statement("ALTER TABLE `law_firm_lawyers` MODIFY COLUMN `city` VARCHAR(255) NULL");
            
            Schema::table('law_firm_lawyers', function (Blueprint $table) {
                $table->string('barangay', 255)->nullable()->after('city');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove barangay column
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->dropColumn('barangay');
        });
        
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->dropColumn('barangay');
        });
        
        if (Schema::hasColumn('law_firm_lawyers', 'barangay')) {
            Schema::table('law_firm_lawyers', function (Blueprint $table) {
                $table->dropColumn('barangay');
            });
        }
        
        // Revert city back to ENUM (optional - you may want to keep it as VARCHAR)
        // Note: This will fail if there are values that don't match the original ENUM
        // DB::statement("ALTER TABLE `lawyer_profiles` MODIFY COLUMN `city` ENUM('Cavite City', 'Dasmarinas', 'General Trias', 'Imus', 'Tagaytay', 'Trece Martires', 'Bacoor') NULL");
        // DB::statement("ALTER TABLE `law_firm_profiles` MODIFY COLUMN `city` ENUM('Cavite City', 'Dasmarinas', 'General Trias', 'Imus', 'Tagaytay', 'Trece Martires', 'Bacoor') NULL");
    }
};
