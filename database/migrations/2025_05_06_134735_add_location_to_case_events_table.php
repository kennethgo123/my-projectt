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
            if (!Schema::hasColumn('case_events', 'location')) {
                $table->string('location')->nullable()->after('event_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_events', function (Blueprint $table) {
            if (Schema::hasColumn('case_events', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};
