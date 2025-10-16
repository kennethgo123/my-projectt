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
        Schema::table('consultations', function (Blueprint $table) {
            // Add can_start_case column if it doesn't exist
            if (!Schema::hasColumn('consultations', 'can_start_case')) {
                $table->boolean('can_start_case')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Only drop if it exists to prevent errors
            if (Schema::hasColumn('consultations', 'can_start_case')) {
                $table->dropColumn('can_start_case');
            }
        });
    }
};
