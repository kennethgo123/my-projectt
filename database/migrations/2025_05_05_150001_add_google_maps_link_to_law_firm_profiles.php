<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('law_firm_profiles', 'google_maps_link')) {
                $table->text('google_maps_link')->nullable()->after('office_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('law_firm_profiles', 'google_maps_link')) {
                $table->dropColumn('google_maps_link');
            }
        });
    }
}; 