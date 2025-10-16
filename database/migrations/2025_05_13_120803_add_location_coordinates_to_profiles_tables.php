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
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('google_maps_link');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });

        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('google_maps_link');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
