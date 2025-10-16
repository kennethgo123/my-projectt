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
            $table->text('office_address')->nullable()->after('address');
            $table->text('google_maps_link')->nullable()->after('office_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->dropColumn(['office_address', 'google_maps_link']);
        });
    }
};
