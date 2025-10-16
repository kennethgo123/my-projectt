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
            if (!Schema::hasColumn('case_events', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_completed')->constrained('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_events', function (Blueprint $table) {
            if (Schema::hasColumn('case_events', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
