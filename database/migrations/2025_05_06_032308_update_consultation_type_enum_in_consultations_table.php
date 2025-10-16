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
        // For MySQL, we need to alter the column
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE consultations MODIFY consultation_type ENUM('online', 'in_house', 'Online Consultation', 'In-House Consultation') NOT NULL");
            
            // Update existing records
            DB::table('consultations')->where('consultation_type', 'online')->update(['consultation_type' => 'Online Consultation']);
            DB::table('consultations')->where('consultation_type', 'in_house')->update(['consultation_type' => 'In-House Consultation']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            // Update records back to original values
            DB::table('consultations')->where('consultation_type', 'Online Consultation')->update(['consultation_type' => 'online']);
            DB::table('consultations')->where('consultation_type', 'In-House Consultation')->update(['consultation_type' => 'in_house']);
            
            // Restore original enum values
            DB::statement("ALTER TABLE consultations MODIFY consultation_type ENUM('online', 'in_house') NOT NULL");
        }
    }
};
