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
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            $table->text('office_address')->nullable()->after('address');
            $table->boolean('show_office_address')->default(false)->after('office_address');
            $table->text('google_maps_link')->nullable()->after('show_office_address');
            $table->decimal('lat', 10, 7)->nullable()->after('google_maps_link');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            $table->dropColumn(['office_address', 'show_office_address', 'google_maps_link', 'lat', 'lng']);
        });
    }
};
