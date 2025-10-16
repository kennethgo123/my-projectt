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
        Schema::table('case_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('case_tasks', 'assigned_to_type')) {
                $table->string('assigned_to_type')->default('client')->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('case_tasks', 'assigned_to_type')) {
                $table->dropColumn('assigned_to_type');
            }
        });
    }
};
