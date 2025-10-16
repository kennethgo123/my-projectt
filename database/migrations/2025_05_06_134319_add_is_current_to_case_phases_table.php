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
        Schema::table('case_phases', function (Blueprint $table) {
            if (!Schema::hasColumn('case_phases', 'is_current')) {
                $table->boolean('is_current')->default(false)->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_phases', function (Blueprint $table) {
            if (Schema::hasColumn('case_phases', 'is_current')) {
                $table->dropColumn('is_current');
            }
        });
    }
};
