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
        Schema::table('lawyer_availabilities', function (Blueprint $table) {
            $table->boolean('has_lunch_break')->default(false)->after('is_available');
            $table->time('lunch_start_time')->nullable()->after('has_lunch_break');
            $table->time('lunch_end_time')->nullable()->after('lunch_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_availabilities', function (Blueprint $table) {
            $table->dropColumn(['has_lunch_break', 'lunch_start_time', 'lunch_end_time']);
        });
    }
};
