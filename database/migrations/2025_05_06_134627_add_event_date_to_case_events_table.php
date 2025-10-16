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
        Schema::table('case_events', function (Blueprint $table) {
            if (!Schema::hasColumn('case_events', 'event_date')) {
                $table->date('event_date')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('case_events', 'event_time')) {
                $table->time('event_time')->nullable()->after('event_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_events', function (Blueprint $table) {
            if (Schema::hasColumn('case_events', 'event_date')) {
                $table->dropColumn('event_date');
            }
            
            if (Schema::hasColumn('case_events', 'event_time')) {
                $table->dropColumn('event_time');
            }
        });
    }
};
