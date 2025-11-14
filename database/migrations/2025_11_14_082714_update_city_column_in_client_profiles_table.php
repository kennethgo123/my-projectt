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
        Schema::table('client_profiles', function (Blueprint $table) {
            // Change city from ENUM to VARCHAR to support all city names
            if (Schema::hasColumn('client_profiles', 'city')) {
                DB::statement("ALTER TABLE `client_profiles` MODIFY COLUMN `city` VARCHAR(255) NULL");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            // Revert city back to ENUM (optional - you may want to keep it as VARCHAR)
            // This is commented out as it's better to keep VARCHAR for flexibility
            // DB::statement("ALTER TABLE `client_profiles` MODIFY COLUMN `city` ENUM('Cavite City', 'Dasmarinas', 'General Trias', 'Imus', 'Tagaytay', 'Trece Martires', 'Bacoor') NULL");
        });
    }
};
